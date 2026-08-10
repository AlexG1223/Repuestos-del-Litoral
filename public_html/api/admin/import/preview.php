<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../../../../private/config/database.php';
require_once __DIR__ . '/../../../../private/config/settings.php';
require_once __DIR__ . '/../../../../private/models/User.php';
require_once __DIR__ . '/../../../../private/services/SessionService.php';
require_once __DIR__ . '/../../../../private/services/AdminGuard.php';
require_once __DIR__ . '/../../../../private/services/PdfImportService.php';
require_once __DIR__ . '/../../../../private/services/ImportStagingService.php';
require_once __DIR__ . '/../../../../private/models/Product.php';
require_once __DIR__ . '/../../../../private/models/ProductImage.php';
require_once __DIR__ . '/../../../../private/models/ImportLog.php';
require_once __DIR__ . '/../../../../private/controllers/AdminImportController.php';

use RepuestosDelLitoral\Services\AdminGuard;
use RepuestosDelLitoral\Controllers\AdminImportController;

AdminGuard::requireApi();

try {
    if (empty($_GET['importId'])) {
        throw new \Exception("Falta importId.");
    }

    $importId = $_GET['importId'];
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 50;

    $controller = new AdminImportController();
    $result = $controller->getPreview($importId, $page, $perPage);

    echo json_encode([
        'success' => true,
        'data' => $result['items'],
        'meta' => [
            'total' => $result['total'],
            'page' => $result['page'],
            'per_page' => $result['per_page'],
            'total_pages' => $result['total_pages']
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
