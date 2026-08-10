<?php
declare(strict_types=1);

ini_set('max_execution_time', '300'); // 5 mins max

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

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
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['importId'])) {
        throw new \Exception("Falta importId.");
    }

    $filename = $input['filename'] ?? 'Catálogo PDF';

    $controller = new AdminImportController();
    $result = $controller->confirm($input['importId'], $filename);

    echo json_encode([
        'success' => true,
        'data' => $result
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
