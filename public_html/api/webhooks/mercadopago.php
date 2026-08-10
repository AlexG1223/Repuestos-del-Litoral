<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../private/config/database.php';
require_once __DIR__ . '/../../../private/models/Order.php';

use RepuestosDelLitoral\Models\Order;

// Log para debug
$logFile = __DIR__ . '/webhook_log.txt';
$input = file_get_contents('php://input');
if (!empty($input)) {
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $input . PHP_EOL, FILE_APPEND);
}

$data = json_decode($input, true);

if (isset($data['type']) && $data['type'] === 'payment') {
    $paymentId = $data['data']['id'] ?? null;
    if ($paymentId) {
        $token = getenv('MP_ACCESS_TOKEN');
        if (!$token) {
            http_response_code(500);
            exit;
        }

        $ch = curl_init("https://api.mercadopago.com/v1/payments/{$paymentId}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $paymentInfo = json_decode($response, true);
            $externalReference = $paymentInfo['external_reference'] ?? null;
            $status = $paymentInfo['status'] ?? null;

            if ($externalReference && $status) {
                $orderId = (int)$externalReference;
                $order = Order::findById($orderId);

                if ($order && $order['status'] === 'pendiente') {
                    if ($status === 'approved') {
                        Order::updateStatus($orderId, 'pagado');
                    } elseif (in_array($status, ['rejected', 'cancelled'])) {
                        Order::updateStatus($orderId, 'rechazado');
                    }
                }
            }
        }
    }
}

http_response_code(200);
echo "OK";
