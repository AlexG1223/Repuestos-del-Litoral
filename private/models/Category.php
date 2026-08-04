<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Models;

use RepuestosDelLitoral\Config\Database;
use PDO;

class Category {
    public static function all(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT id, parent_id, name, slug, created_at 
            FROM categories 
            ORDER BY parent_id IS NOT NULL, name ASC
        ");
        return $stmt->fetchAll();
    }

    public static function findBySlug(string $slug): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT id, parent_id, name, slug, created_at 
            FROM categories 
            WHERE slug = ? 
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
