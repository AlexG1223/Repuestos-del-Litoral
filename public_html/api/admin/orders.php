<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../private/config/database.php';
require_once __DIR__ . '/../../../private/models/Order.php';
require_once __DIR__ . '/../../../private/services/SessionService.php';

use RepuestosDelLitoral\Models\Order;
use RepuestosDelLitoral\Services\SessionService;

SessionService::start();

// Validar permisos admin
if (!SessionService::isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'GET') {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $result = Order::paginate($page, 20);
        echo json_encode(['success' => true, 'data' => $result]);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $orderId = isset($input['id']) ? (int)$input['id'] : 0;
        $action = isset($input['action']) ? trim((string)$input['action']) : '';

        if (!$orderId || !$action) {
            throw new \Exception("Parámetros inválidos");
        }

        $order = Order::findById($orderId);
        if (!$order) {
            throw new \Exception("Pedido no encontrado");
        }

        $currentStatus = $order['status'];

        if ($action === 'cancel' && $currentStatus === 'pendiente') {
            Order::updateStatus($orderId, 'cancelado');
        } elseif ($action === 'finalize' && $currentStatus === 'pagado') {
            Order::updateStatus($orderId, 'finalizado');
        } else {
            throw new \Exception("Transición de estado no permitida para el estado actual ({$currentStatus})");
        }

        echo json_encode(['success' => true]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
