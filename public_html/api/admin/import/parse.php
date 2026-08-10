<?php
declare(strict_types=1);

ini_set('max_execution_time', '300'); // Permitir hasta 5 min de procesamiento

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    if (!isset($_FILES['pdf_file'])) {
        throw new \Exception("No se envió ningún archivo PDF.");
    }

    $controller = new AdminImportController();
    $result = $controller->parseUpload($_FILES['pdf_file'], $_POST);

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
