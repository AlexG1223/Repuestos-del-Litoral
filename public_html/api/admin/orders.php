<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../private/config/database.php';
require_once __DIR__ . '/../../../private/models/Order.php';
require_once __DIR__ . '/../../../private/models/OrderItem.php';
require_once __DIR__ . '/../../../private/services/SessionService.php';

use RepuestosDelLitoral\Models\Order;
use RepuestosDelLitoral\Models\OrderItem;
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
        if (isset($_GET['id'])) {
            $orderId = (int)$_GET['id'];
            $order = Order::findByIdWithItems($orderId);
            if (!$order) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Pedido no encontrado']);
                exit;
            }
            echo json_encode(['success' => true, 'data' => $order]);
            exit;
        }

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

        if ($action === 'delete') {
            Order::delete($orderId);
        } elseif ($action === 'finalize') {
            Order::processPaymentStatus($orderId, 'finalizado');
        } elseif ($action === 'pay') {
            Order::processPaymentStatus($orderId, 'pagado');
        } elseif ($action === 'cancel') {
            Order::updateStatus($orderId, 'cancelado');
        } elseif ($action === 'pending' || $action === 'reopen') {
            Order::updateStatus($orderId, 'pendiente');
        } else {
            throw new \Exception("Acción no válida ({$action})");
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
