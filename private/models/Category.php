<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Models;

use RepuestosDelLitoral\Config\Database;
use PDO;

class Category {
    /**
     * Devuelve todas las categorías ordenadas jerárquicamente.
     */
    public static function all(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT c.id, c.parent_id, c.name, c.slug, c.created_at, p.name AS parent_name 
            FROM categories c
            LEFT JOIN categories p ON c.parent_id = p.id
            ORDER BY COALESCE(c.parent_id, c.id) ASC, c.parent_id IS NOT NULL ASC, c.name ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Devuelve únicamente las categorías principales (sin padre).
     */
    public static function allParents(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT id, parent_id, name, slug, created_at 
            FROM categories 
            WHERE parent_id IS NULL 
            ORDER BY name ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Devuelve la estructura jerárquica en forma de árbol (padres con sus subcategorías).
     */
    public static function getTree(): array {
        $all = self::all();
        $parents = [];
        $childrenMap = [];

        foreach ($all as $cat) {
            $cat['subcategories'] = [];
            if ($cat['parent_id'] === null) {
                $parents[$cat['id']] = $cat;
            } else {
                $childrenMap[$cat['parent_id']][] = $cat;
            }
        }

        foreach ($parents as $id => &$parent) {
            if (isset($childrenMap[$id])) {
                $parent['subcategories'] = $childrenMap[$id];
            }
        }
        unset($parent);

        return array_values($parents);
    }

    /**
     * Busca una categoría por ID.
     */
    public static function find(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT c.id, c.parent_id, c.name, c.slug, c.created_at, p.name AS parent_name
            FROM categories c
            LEFT JOIN categories p ON c.parent_id = p.id
            WHERE c.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Busca una categoría por slug.
     */
    public static function findBySlug(string $slug): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT c.id, c.parent_id, c.name, c.slug, c.created_at, p.name AS parent_name
            FROM categories c
            LEFT JOIN categories p ON c.parent_id = p.id
            WHERE c.slug = ? 
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Crea una nueva categoría o subcategoría.
     */
    public static function create(string $name, ?int $parentId = null): int {
        $db = Database::getConnection();
        $parentId = ($parentId !== null && $parentId > 0) ? $parentId : null;
        $slug = self::generateSlug($name);

        $stmt = $db->prepare("INSERT INTO categories (parent_id, name, slug) VALUES (?, ?, ?)");
        $stmt->execute([$parentId, trim($name), $slug]);
        return (int)$db->lastInsertId();
    }

    /**
     * Actualiza una categoría existente.
     */
    public static function update(int $id, string $name, ?int $parentId = null): bool {
        $db = Database::getConnection();
        $parentId = ($parentId !== null && $parentId > 0) ? $parentId : null;
        
        // Evitar que una categoría sea su propio padre
        if ($parentId === $id) {
            $parentId = null;
        }

        $slug = self::generateSlug($name, $id);

        $stmt = $db->prepare("
            UPDATE categories 
            SET name = ?, parent_id = ?, slug = ? 
            WHERE id = ?
        ");
        return $stmt->execute([trim($name), $parentId, $slug, $id]);
    }

    /**
     * Elimina una categoría por ID.
     */
    public static function delete(int $id): bool {
        $db = Database::getConnection();

        // 1. Reasignar subcategorías a NULL para no dejarlas huérfanas con padre inexistente
        $stmtChild = $db->prepare("UPDATE categories SET parent_id = NULL WHERE parent_id = ?");
        $stmtChild->execute([$id]);

        // 2. Reasignar productos de esta categoría a NULL
        $stmtProd = $db->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?");
        $stmtProd->execute([$id]);

        // 3. Eliminar la categoría
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Genera un slug único para la categoría.
     */
    private static function generateSlug(string $name, ?int $ignoreId = null): string {
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
            if ($ignoreId !== null) {
                $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
                $stmt->execute([$slug, $ignoreId]);
            } else {
                $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
                $stmt->execute([$slug]);
            }
            
            if (!$stmt->fetch()) {
                break;
            }
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}

