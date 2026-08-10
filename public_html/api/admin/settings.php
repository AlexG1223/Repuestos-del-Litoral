<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../private/config/database.php';
require_once __DIR__ . '/../../../private/models/Setting.php';
require_once __DIR__ . '/../../../private/services/SessionService.php';
require_once __DIR__ . '/../../../private/controllers/SettingsController.php';

use RepuestosDelLitoral\Controllers\SettingsController;
use RepuestosDelLitoral\Services\SessionService;

SessionService::start();

// Validar que sea admin para editar o ver en el panel
if (!SessionService::isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

try {
    $controller = new SettingsController();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $settings = $controller->getSettings();
        echo json_encode(['success' => true, 'data' => $settings]);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $result = $controller->updateSettings($input);
        echo json_encode($result);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
