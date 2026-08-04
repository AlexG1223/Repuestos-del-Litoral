<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/../../../private/config/database.php';
require_once __DIR__ . '/../../../private/config/settings.php';
require_once __DIR__ . '/../../../private/models/User.php';
require_once __DIR__ . '/../../../private/services/SessionService.php';
require_once __DIR__ . '/../../../private/controllers/AuthController.php';

use RepuestosDelLitoral\Controllers\AuthController;

try {
    $rawInput = file_get_contents('php://input');
    $payload  = json_decode($rawInput, true);

    if (!is_array($payload)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Cuerpo JSON inválido.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $controller = new AuthController();
    $result = $controller->login($payload);

    if (!$result['success']) {
        http_response_code(401);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error en el servidor: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
