<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../../private/config/database.php';
require_once __DIR__ . '/../../private/config/settings.php';
require_once __DIR__ . '/../../private/models/Category.php';
require_once __DIR__ . '/../../private/services/SessionService.php';
require_once __DIR__ . '/../../private/services/PricingService.php';
require_once __DIR__ . '/../../private/controllers/CatalogController.php';

use RepuestosDelLitoral\Controllers\CatalogController;

try {
    $controller = new CatalogController();
    $categories = $controller->listCategories();

    echo json_encode([
        'success' => true,
        'data'    => $categories
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error al obtener categorías: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
