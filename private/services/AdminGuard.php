<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Services;

use RepuestosDelLitoral\Services\SessionService;

class AdminGuard {

    /**
     * Valida que el usuario tenga rol 'admin'. Si no lo tiene, redirige al login.
     * Si la petición es POST/DELETE/PUT, también verifica el token CSRF.
     */
    public static function requirePage(): void {
        SessionService::start();
        $user = SessionService::currentUser();

        if (!$user || $user['role'] !== 'admin') {
            header('Location: /login.php');
            exit;
        }

        // Generar un token CSRF si no existe
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Valida que el usuario tenga rol 'admin' para respuestas API (JSON).
     * Si la petición es POST, DELETE o PUT, verifica el token CSRF.
     */
    public static function requireApi(): void {
        SessionService::start();
        $user = SessionService::currentUser();

        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'No autorizado. Se requieren permisos de administrador.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
            $headers = getallheaders();
            // Soporta distintos cases en el nombre del header
            $csrfToken = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? $headers['X-Csrf-Token'] ?? null;

            if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$csrfToken)) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Token de seguridad CSRF inválido o ausente.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
    }
}
