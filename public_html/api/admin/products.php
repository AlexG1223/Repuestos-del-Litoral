<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../private/services/SessionService.php';
require_once __DIR__ . '/../../../private/services/AdminGuard.php';
\RepuestosDelLitoral\Services\AdminGuard::requireApi();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../private/config/database.php';
require_once __DIR__ . '/../../../private/models/Category.php';
require_once __DIR__ . '/../../../private/models/ProductImage.php';
require_once __DIR__ . '/../../../private/models/Product.php';
require_once __DIR__ . '/../../../private/controllers/AdminProductController.php';

use RepuestosDelLitoral\Controllers\AdminProductController;

try {
    $controller = new AdminProductController();

    if (isset($_GET['id']) && (int)$_GET['id'] > 0) {
        $product = $controller->get((int)$_GET['id']);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Producto no encontrado'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        echo json_encode(['success' => true, 'data' => $product], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $filters = [
        'search'      => $_GET['search'] ?? '',
        'category_id' => $_GET['category_id'] ?? '',
        'active'      => $_GET['active'] ?? ''
    ];

    $items = $controller->list($filters);

    echo json_encode([
        'success' => true,
        'data'    => $items
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error interno: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
