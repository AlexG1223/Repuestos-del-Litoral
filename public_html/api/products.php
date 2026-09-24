<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../../private/config/database.php';
require_once __DIR__ . '/../../private/config/settings.php';
require_once __DIR__ . '/../../private/models/Category.php';
require_once __DIR__ . '/../../private/models/ProductImage.php';
require_once __DIR__ . '/../../private/models/Product.php';
require_once __DIR__ . '/../../private/services/SessionService.php';
require_once __DIR__ . '/../../private/services/PricingService.php';
require_once __DIR__ . '/../../private/services/SearchService.php';
require_once __DIR__ . '/../../private/controllers/CatalogController.php';

use RepuestosDelLitoral\Controllers\CatalogController;

try {
    $controller = new CatalogController();

    // Si viene el parámetro slug, devolver detalle de producto
    if (isset($_GET['slug']) && trim((string)$_GET['slug']) !== '') {
        $slug = trim((string)$_GET['slug']);
        $product = $controller->getProductDetail($slug);

        if (!$product) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error'   => 'Producto no encontrado'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data'    => $product
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Listado paginado de productos
    $result = $controller->listProducts($_GET);

    $responsePayload = [
        'success' => true,
        'data'    => $result['items'] ?? [],
        'meta'    => [
            'total'       => $result['total'] ?? 0,
            'page'        => $result['page'] ?? 1,
            'per_page'    => $result['per_page'] ?? 12,
            'total_pages' => $result['total_pages'] ?? 1,
            'did_you_mean' => $result['did_you_mean'] ?? null,
            'is_fallback' => $result['is_fallback'] ?? false
        ]
    ];

    if (!empty($result['recommended'])) {
        $responsePayload['recommended'] = $result['recommended'];
    }

    echo json_encode($responsePayload, JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error interno del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
