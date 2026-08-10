<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../private/services/SessionService.php';
require_once __DIR__ . '/../../../private/services/AdminGuard.php';
\RepuestosDelLitoral\Services\AdminGuard::requireApi();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/../../../private/config/database.php';
require_once __DIR__ . '/../../../private/models/Category.php';
require_once __DIR__ . '/../../../private/models/ProductImage.php';
require_once __DIR__ . '/../../../private/models/Product.php';
require_once __DIR__ . '/../../../private/controllers/AdminProductController.php';

use RepuestosDelLitoral\Controllers\AdminProductController;

try {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de producto inválido'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $controller = new AdminProductController();
    $controller->delete($id);

    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error interno: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
