<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Controllers;

use RepuestosDelLitoral\Config\Database;
use RepuestosDelLitoral\Models\Order;
use RepuestosDelLitoral\Models\OrderItem;
use RepuestosDelLitoral\Models\Product;
use RepuestosDelLitoral\Services\SessionService;
use RepuestosDelLitoral\Services\PricingService;

class CheckoutController {

    /**
     * Procesa la solicitud de checkout, valida datos, recalcula precios en BD según sesión y registra la orden.
     */
    public function submitOrder(array $payload): array {
        $errors = $this->validatePayload($payload);
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors'  => $errors
            ];
        }

        $customerName    = trim((string)$payload['customerName']);
        $customerEmail   = trim((string)$payload['customerEmail']);
        $customerPhone   = trim((string)$payload['customerPhone']);
        $customerAddress = isset($payload['customerAddress']) ? trim((string)$payload['customerAddress']) : '';
        $paymentMethod   = isset($payload['paymentMethod']) && $payload['paymentMethod'] === 'mercado_pago' ? 'mercado_pago' : 'whatsapp';

        $currentUser = SessionService::currentUser();
        $db = Database::getConnection();
        
        $verifiedItems = [];
        $skippedItems  = [];
        $total         = 0.0;
        $appliedTier   = 'retail'; // Por defecto

        // Recalcular precios y stock contra la base de datos aplicando PricingService
        foreach ($payload['items'] as $item) {
            $productId = (int)$item['productId'];
            $qtyRequested = (int)$item['quantity'];

            // Consultar producto directo de la BD por ID
            $stmt = $db->prepare("SELECT id, name, code, retail_price, wholesale_price, stock, active FROM products WHERE id = ? LIMIT 1");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if (!$product || (int)$product['active'] !== 1 || (int)$product['stock'] <= 0) {
                $skippedItems[] = [
                    'productId' => $productId,
                    'name'      => $product['name'] ?? 'Producto no disponible',
                    'reason'    => 'Sin stock o inactivo'
                ];
                continue;
            }

            // Ajustar cantidad al stock disponible si se solicitó más de lo existente
            $stockAvailable = (int)$product['stock'];
            $finalQty = min($qtyRequested, $stockAvailable);

            if ($finalQty < $qtyRequested) {
                $skippedItems[] = [
                    'productId' => $productId,
                    'name'      => $product['name'],
                    'reason'    => "Cantidad ajustada de {$qtyRequested} a {$finalQty} por límite de stock"
                ];
            }

            // Resolver precio server-side usando la sesión activa
            $priceInfo = PricingService::resolvePrice($product, $currentUser);
            $unitPrice = $priceInfo['price'];
            if ($priceInfo['tier'] === 'wholesale') {
                $appliedTier = 'wholesale';
            }

            $itemSubtotal = $unitPrice * $finalQty;
            $total += $itemSubtotal;

            $verifiedItems[] = [
                'product_id' => (int)$product['id'],
                'name'       => $product['name'],
                'code'       => $product['code'],
                'quantity'   => $finalQty,
                'unit_price' => $unitPrice,
                'subtotal'   => $itemSubtotal
            ];
        }

        if (empty($verifiedItems)) {
            return [
                'success' => false,
                'errors'  => [
                    'general' => 'Ninguno de los productos seleccionados tiene stock disponible en este momento.'
                ]
            ];
        }

        // Abrir transacción PDO
        try {
            $db->beginTransaction();

            $orderId = Order::create([
                'user_id'          => $currentUser ? (int)$currentUser['id'] : null,
                'customer_name'    => $customerName,
                'customer_email'   => $customerEmail,
                'customer_phone'   => $customerPhone,
                'customer_address' => $customerAddress !== '' ? $customerAddress : null,
                'price_tier'       => $appliedTier,
                'total'            => $total,
                'payment_method'   => $paymentMethod,
                'status'           => 'pendiente'
            ]);

            OrderItem::createMany($orderId, $verifiedItems);

            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }

        if ($paymentMethod === 'mercado_pago') {
            try {
                $initPoint = $this->createMercadoPagoPreference($orderId, $customerEmail, $verifiedItems);
                return [
                    'success' => true,
                    'data'    => [
                        'orderId'         => $orderId,
                        'total'           => $total,
                        'items'           => $verifiedItems,
                        'skippedItems'    => $skippedItems,
                        'paymentMethod'   => 'mercado_pago',
                        'init_point'      => $initPoint
                    ]
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'errors'  => [
                        'general' => 'Error al contactar con Mercado Pago: ' . $e->getMessage()
                    ]
                ];
            }
        }

        // Construcción centralizada del mensaje de WhatsApp
        $whatsappMessage = $this->buildWhatsappMessage($orderId, $customerName, $customerPhone, $customerAddress, $verifiedItems, $total, $appliedTier);

        return [
            'success' => true,
            'data'    => [
                'orderId'         => $orderId,
                'total'           => $total,
                'items'           => $verifiedItems,
                'skippedItems'    => $skippedItems,
                'paymentMethod'   => 'whatsapp',
                'whatsappMessage' => $whatsappMessage
            ]
        ];
    }

    /**
     * Valida los campos recibidos del cliente.
     */
    private function validatePayload(array $payload): array {
        $errors = [];

        // Nombre
        $name = isset($payload['customerName']) ? trim((string)$payload['customerName']) : '';
        if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
            $errors['customerName'] = 'Por favor ingrese su nombre y apellido (mínimo 2 caracteres).';
        }

        // Email
        $email = isset($payload['customerEmail']) ? trim((string)$payload['customerEmail']) : '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['customerEmail'] = 'Por favor ingrese un email válido.';
        }

        // Teléfono
        $phone = isset($payload['customerPhone']) ? trim((string)$payload['customerPhone']) : '';
        $digitsOnly = preg_replace('/\D/', '', $phone);
        if (strlen($digitsOnly) < 8 || !preg_match('/^[0-9\+\-\s\(\)]+$/', $phone)) {
            $errors['customerPhone'] = 'Ingrese un teléfono de contacto válido (mínimo 8 dígitos).';
        }

        // Ítems
        if (empty($payload['items']) || !is_array($payload['items'])) {
            $errors['items'] = 'El carrito no contiene productos válidos.';
        } else {
            foreach ($payload['items'] as $item) {
                if (empty($item['productId']) || empty($item['quantity']) || (int)$item['quantity'] <= 0) {
                    $errors['items'] = 'Estructura de productos en el carrito inválida.';
                    break;
                }
            }
        }

        return $errors;
    }

    /**
     * Construye el texto formateado para el mensaje de WhatsApp.
     */
    private function buildWhatsappMessage(
        int $orderId, 
        string $name, 
        string $phone, 
        string $address, 
        array $items, 
        float $total,
        string $priceTier = 'retail'
    ): string {
        $orderNumberFormatted = str_pad((string)$orderId, 5, '0', STR_PAD_LEFT);
        $tierLabel = $priceTier === 'wholesale' ? ' (Precio Mayorista Aplicado)' : '';
        
        $lines = [];
        $lines[] = "🛒 *Nuevo pedido{$tierLabel} — Repuestos del Litoral*";
        $lines[] = "";
        $lines[] = "*Cliente:* {$name}";
        $lines[] = "*Teléfono:* {$phone}";
        $lines[] = "*Dirección:* " . ($address !== '' ? $address : "Retira en local");
        $lines[] = "";
        $lines[] = "*Productos:*";

        foreach ($items as $item) {
            $formattedSubtotal = "$U " . number_format($item['subtotal'], 0, ',', '.');
            $lines[] = "- {$item['name']} x{$item['quantity']} — {$formattedSubtotal}";
        }

        $formattedTotal = "$U " . number_format($total, 0, ',', '.');
        $lines[] = "";
        $lines[] = "*Total: {$formattedTotal}*";
        $lines[] = "";
        $lines[] = "Pedido #{$orderNumberFormatted} generado desde sitio web";

        return implode("\n", $lines);
    }

    /**
     * Llama a la API de Mercado Pago vía cURL para crear la preferencia.
     */
    private function createMercadoPagoPreference(int $orderId, string $email, array $items): string {
        $token = getenv('MP_ACCESS_TOKEN');
        if (!$token) {
            throw new \Exception("Mercado Pago no está configurado en el servidor.");
        }

        $mpItems = [];
        foreach ($items as $item) {
            $mpItems[] = [
                'id' => (string)$item['product_id'],
                'title' => $item['name'],
                'quantity' => (int)$item['quantity'],
                'unit_price' => (float)$item['unit_price'],
                'currency_id' => 'UYU'
            ];
        }

        // Webhook config
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        
        $preferenceData = [
            'items' => $mpItems,
            'payer' => [
                'email' => $email
            ],
            'external_reference' => (string)$orderId,
            'notification_url' => $baseUrl . '/api/webhooks/mercadopago.php',
            'back_urls' => [
                'success' => $baseUrl . '/index.php?payment_status=success',
                'pending' => $baseUrl . '/index.php?payment_status=pending',
                'failure' => $baseUrl . '/index.php?payment_status=failure'
            ],
            'auto_return' => 'approved'
        ];

        $ch = curl_init('https://api.mercadopago.com/checkout/preferences');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preferenceData));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 && $httpCode !== 201) {
            throw new \Exception("Error MP: " . $response);
        }

        $result = json_decode($response, true);
        return $result['init_point'];
    }
}
