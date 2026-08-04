<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../../../private/config/database.php';
require_once __DIR__ . '/../../../private/config/settings.php';
require_once __DIR__ . '/../../../private/models/User.php';
require_once __DIR__ . '/../../../private/services/SessionService.php';
require_once __DIR__ . '/../../../private/controllers/AuthController.php';

use RepuestosDelLitoral\Controllers\AuthController;

try {
    $controller = new AuthController();
    $user = $controller->me();

    echo json_encode([
        'success' => true,
        'data'    => $user
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
