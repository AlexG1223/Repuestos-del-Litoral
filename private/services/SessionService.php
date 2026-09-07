<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Services;

use RepuestosDelLitoral\Models\User;

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/database.php';

class SessionService {
    private static bool $started = false;

    /**
     * Configura e inicia la sesión nativa de PHP de forma segura.
     */
    public static function start(): void {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $sessionName = 'rdl_session';
        session_name($sessionName);

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

        session_set_cookie_params([
            'lifetime' => 86400 * 30, // 30 días
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_start();
        self::$started = true;
    }

    /**
     * Registra al usuario en la sesión PHP y regenera el ID de sesión.
     */
    public static function login(array $user): void {
        self::start();
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'            => (int)$user['id'],
            'name'          => $user['name'],
            'email'         => $user['email'],
            'phone'         => $user['phone'] ?? null,
            'business_name' => $user['business_name'] ?? null,
            'role'          => $user['role'],
            'approved'      => (int)$user['approved']
        ];
    }

    /**
     * Cierra la sesión y elimina las cookies correspondientes.
     */
    public static function logout(): void {
        self::start();
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
        self::$started = false;
    }

    /**
     * Devuelve el usuario actualmente autenticado o null si no hay sesión activa.
     */
    public static function currentUser(): ?array {
        self::start();

        if (empty($_SESSION['user']) || !is_array($_SESSION['user'])) {
            return null;
        }

        // Consultar estado actualizado en base de datos para sincronizar si fue aprobado recientemente
        $userId = (int)$_SESSION['user']['id'];
        $freshUser = User::findById($userId);

        if (!$freshUser) {
            self::logout();
            return null;
        }

        // Sincronizar datos de la sesión con los datos actuales de la BD
        $_SESSION['user']['role'] = $freshUser['role'];
        $_SESSION['user']['approved'] = (int)$freshUser['approved'];
        $_SESSION['user']['name'] = $freshUser['name'];
        $_SESSION['user']['phone'] = $freshUser['phone'];
        $_SESSION['user']['business_name'] = $freshUser['business_name'];

        return $_SESSION['user'];
    }

    /**
     * Verifica si el usuario actual ha iniciado sesión.
     */
    public static function isLoggedIn(): bool {
        return self::currentUser() !== null;
    }

    /**
     * Verifica si el usuario actual tiene rol de administrador.
     */
    public static function isAdmin(): bool {
        $user = self::currentUser();
        return $user !== null && isset($user['role']) && $user['role'] === 'admin';
    }

    /**
     * Verifica si el usuario actual tiene rol mayorista aprobado.
     */
    public static function isWholesale(): bool {
        $user = self::currentUser();
        if ($user === null || !isset($user['role'])) {
            return false;
        }
        return ($user['role'] === 'wholesale' && (int)($user['approved'] ?? 0) === 1) || $user['role'] === 'admin';
    }
}
