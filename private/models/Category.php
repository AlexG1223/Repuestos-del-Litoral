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

    public static function create(string $name, ?int $parentId = null): int {
        $db = Database::getConnection();
        $slug = self::generateSlug($name);

        $stmt = $db->prepare("INSERT INTO categories (parent_id, name, slug) VALUES (?, ?, ?)");
        $stmt->execute([$parentId, trim($name), $slug]);
        return (int)$db->lastInsertId();
    }

    private static function generateSlug(string $name): string {
        $slug = preg_replace('~[^\pL\d]+~u', '-', $name);
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        $slug = preg_replace('~[^-\w]+~', '', $slug);
        $slug = trim($slug, '-');
        $slug = preg_replace('~-+~', '-', $slug);
        $slug = strtolower($slug);

        if (empty($slug)) {
            $slug = 'categoria';
        }

        $db = Database::getConnection();
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
            $stmt->execute([$slug]);
            
            if (!$stmt->fetch()) {
                break;
            }
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
