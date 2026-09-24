<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Config;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;

    public static function loadEnv(): void
    {
        $envPath = dirname(__DIR__, 2) . '/.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                if (str_contains($line, '=')) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value, " \t\n\r\0\x0B\"'");
                    if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                        putenv("{$name}={$value}");
                        $_ENV[$name] = $value;
                        $_SERVER[$name] = $value;
                    }
                }
            }
        }
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            self::loadEnv();

            $host = getenv('DB_HOST') ?: 'localhost';
            $db = getenv('DB_NAME') ?: 'u750013204_repdellitoral';
            $user = getenv('DB_USER') ?: 'u750013204_repdellitoral';
            $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'jDA9JvLL9';
            $charset = 'utf8mb4';

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $hostsToTry = array_unique([$host, '127.0.0.1', 'localhost']);
            $fallbacks = [
                ['user' => $user, 'pass' => $pass],
                ['user' => 'u750013204_repdellitoral', 'pass' => 'jDA9JvLL9'],
                ['user' => 'root', 'pass' => '']
            ];

            $lastException = null;
            foreach ($hostsToTry as $h) {
                $dsn = "mysql:host={$h};dbname={$db};charset={$charset}";
                foreach ($fallbacks as $fb) {
                    try {
                        self::$instance = new PDO($dsn, $fb['user'], $fb['pass'], $options);
                        self::$instance->exec("SET time_zone = '-03:00'");
                        return self::$instance;
                    } catch (PDOException $e) {
                        $lastException = $e;
                    }
                }
            }

            throw new RuntimeException("Error de conexión a la Base de Datos: " . ($lastException ? $lastException->getMessage() : 'Desconocido'), (int) ($lastException ? $lastException->getCode() : 0));
        }

        return self::$instance;
    }
}
