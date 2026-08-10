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
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST') {
        // Upload image
        if (isset($_FILES['image']) && isset($_POST['product_id'])) {
            $productId = (int)$_POST['product_id'];
            $result = $controller->uploadImage($productId, $_FILES['image']);
            echo json_encode(['success' => true, 'data' => $result], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Action (set_primary, reorder)
        $rawInput = file_get_contents('php://input');
        $payload  = json_decode($rawInput, true);
        
        if (is_array($payload) && isset($payload['action'])) {
            if ($payload['action'] === 'set_primary' && isset($payload['product_id'], $payload['image_id'])) {
                $controller->setPrimaryImage((int)$payload['product_id'], (int)$payload['image_id']);
                echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
                exit;
            }

            if ($payload['action'] === 'reorder' && isset($payload['product_id'], $payload['ordered_ids']) && is_array($payload['ordered_ids'])) {
                $controller->reorderImages((int)$payload['product_id'], $payload['ordered_ids']);
                echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Petición inválida'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de imagen inválido'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $controller->removeImage($id);
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);

} catch (\InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error interno: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
