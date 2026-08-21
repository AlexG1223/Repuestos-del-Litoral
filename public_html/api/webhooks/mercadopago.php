<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../private/config/database.php';
require_once __DIR__ . '/../../../private/models/Order.php';
require_once __DIR__ . '/../../../private/models/OrderItem.php';

use RepuestosDelLitoral\Config\Database;
use RepuestosDelLitoral\Models\Order;

// Cargar variables de entorno inmediatamente al iniciar el endpoint
Database::loadEnv();

$logFile = __DIR__ . '/webhook_log.txt';
$input   = file_get_contents('php://input');

if (!empty($input)) {
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $input . PHP_EOL, FILE_APPEND);
}

$webhookSecret = getenv('MP_WEBHOOK_SECRET');

// Verificación de Firma x-signature (si MP_WEBHOOK_SECRET está configurado en .env)
if (!empty($webhookSecret)) {
    $xSignature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
    $xRequestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? '';

    $signatureParts = [];
    if (!empty($xSignature)) {
        $items = explode(',', $xSignature);
        foreach ($items as $item) {
            $kv = explode('=', trim($item), 2);
            if (count($kv) === 2) {
                $signatureParts[$kv[0]] = $kv[1];
            }
        }
    }

    $ts = $signatureParts['ts'] ?? null;
    $v1 = $signatureParts['v1'] ?? null;

    $data = json_decode($input, true) ?: [];
    $dataId = $_GET['data.id'] ?? $_GET['id'] ?? $data['data']['id'] ?? $data['id'] ?? null;

    if ($ts && $v1 && $dataId) {
        $manifest = "id:{$dataId};request-id:{$xRequestId};ts:{$ts};";
        $computedHash = hash_hmac('sha256', $manifest, $webhookSecret);

        if (!hash_equals($computedHash, $v1)) {
            http_response_code(401);
            echo json_encode(['error' => 'Firma HMAC de webhook inválida.']);
            exit;
        }
    }
}

$data = json_decode($input, true) ?: [];
$type = $data['type'] ?? $data['topic'] ?? $_GET['topic'] ?? $_GET['type'] ?? null;

$paymentId = $data['data']['id'] ?? $data['id'] ?? $_GET['data_id'] ?? $_GET['id'] ?? null;

if (($type === 'payment' || isset($data['action'])) && $paymentId) {
    $token = getenv('MP_ACCESS_TOKEN');
    if (!$token) {
        http_response_code(500);
        echo json_encode(['error' => 'MP_ACCESS_TOKEN no configurado']);
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

            $mappedStatus = 'pendiente';
            if ($status === 'approved') {
                $mappedStatus = 'pagado';
            } elseif (in_array($status, ['rejected', 'cancelled', 'refunded', 'charged_back'], true)) {
                $mappedStatus = 'rechazado';
            }

            Order::processPaymentStatus($orderId, $mappedStatus, (string)$paymentId);
        }
    }
}

http_response_code(200);
echo "OK";
