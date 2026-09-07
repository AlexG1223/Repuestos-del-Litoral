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
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        if (isset($_GET['tree']) && $_GET['tree'] == '1') {
            $data = Category::getTree();
        } elseif (isset($_GET['parents']) && $_GET['parents'] == '1') {
            $data = Category::allParents();
        } else {
            $data = Category::all();
        }

        echo json_encode([
            'success' => true,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'POST') {
        $rawInput = file_get_contents('php://input');
        $payload  = json_decode($rawInput, true);

        $name = trim((string)($payload['name'] ?? ''));
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'El nombre de la categoría es obligatorio.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $parentId = isset($payload['parent_id']) && is_numeric($payload['parent_id']) ? (int)$payload['parent_id'] : null;
        $id = Category::create($name, $parentId);

        echo json_encode([
            'success' => true,
            'message' => 'Categoría creada exitosamente.',
            'data'    => ['id' => $id, 'name' => $name, 'parent_id' => $parentId]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        $rawInput = file_get_contents('php://input');
        $payload  = json_decode($rawInput, true);

        $id = isset($payload['id']) ? (int)$payload['id'] : 0;
        $name = trim((string)($payload['name'] ?? ''));

        if ($id <= 0 || empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Datos inválidos para actualizar la categoría.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $parentId = isset($payload['parent_id']) && is_numeric($payload['parent_id']) ? (int)$payload['parent_id'] : null;
        $success = Category::update($id, $name, $parentId);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'No se pudo actualizar la categoría.'], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $rawInput = file_get_contents('php://input');
            $payload  = json_decode($rawInput, true);
            $id = isset($payload['id']) ? (int)$payload['id'] : 0;
        }

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de categoría no especificado.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $success = Category::delete($id);
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Categoría eliminada correctamente.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'No se pudo eliminar la categoría.'], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error interno: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
