<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../private/services/SessionService.php';
require_once __DIR__ . '/../../../private/services/AdminGuard.php';
\RepuestosDelLitoral\Services\AdminGuard::requireApi();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/../../../private/config/database.php';
require_once __DIR__ . '/../../../private/models/User.php';
require_once __DIR__ . '/../../../private/controllers/AdminUserController.php';

use RepuestosDelLitoral\Controllers\AdminUserController;

try {
    $rawInput = file_get_contents('php://input');
    $payload  = json_decode($rawInput, true);

    if (!is_array($payload) || !isset($payload['user_id'], $payload['action'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Petición inválida'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $userId = (int)$payload['user_id'];
    $controller = new AdminUserController();

    if ($payload['action'] === 'approve') {
        $controller->approve($userId);
    } elseif ($payload['action'] === 'revoke') {
        $controller->revoke($userId);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Acción desconocida'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error interno: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
