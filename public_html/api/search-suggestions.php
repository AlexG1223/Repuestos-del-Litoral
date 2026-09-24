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
    $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
    if ($q === '' || mb_strlen($q) < 2) {
        echo json_encode([
            'success' => true,
            'data'    => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $controller = new CatalogController();
    $suggestions = $controller->getSearchSuggestions($q);

    echo json_encode([
        'success' => true,
        'data'    => $suggestions
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error al obtener sugerencias: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
