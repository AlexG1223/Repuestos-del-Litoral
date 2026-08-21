<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Models;

require_once __DIR__ . '/../config/database.php';

use RepuestosDelLitoral\Config\Database;
use PDO;
use Throwable;

class Setting {

    private static function ensureTable(): void {
        static $ensured = false;
        if ($ensured) {
            return;
        }
        try {
            $db = Database::getConnection();
            $db->exec("
                CREATE TABLE IF NOT EXISTS settings (
                    setting_key VARCHAR(100) PRIMARY KEY,
                    setting_value TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
            $ensured = true;
        } catch (Throwable $e) {
            // Ignorar silenciosamente si ya existe o hay error de permisos
        }
    }

    public static function get(string $key, $default = null) {
        self::ensureTable();
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return $val !== false ? $val : $default;
        } catch (Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, string $value): bool {
        self::ensureTable();
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        return $stmt->execute([$key, $value]);
    }
    
    public static function all(): array {
        self::ensureTable();
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            $results = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[$row['setting_key']] = $row['setting_value'];
            }
            return $results;
        } catch (Throwable $e) {
            return [];
        }
    }
}
