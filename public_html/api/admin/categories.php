<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../private/services/SessionService.php';
require_once __DIR__ . '/../../../private/services/AdminGuard.php';
\RepuestosDelLitoral\Services\AdminGuard::requireApi();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../private/config/database.php';
require_once __DIR__ . '/../../../private/models/Category.php';

use RepuestosDelLitoral\Models\Category;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $categories = Category::all();
        echo json_encode([
            'success' => true,
            'data'    => $categories
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rawInput = file_get_contents('php://input');
        $payload  = json_decode($rawInput, true);

        if (!is_array($payload) || empty(trim((string)($payload['name'] ?? '')))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nombre de categoría inválido'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $id = Category::create($payload['name']);
        echo json_encode([
            'success' => true,
            'data'    => ['id' => $id, 'name' => trim($payload['name'])]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error interno: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
