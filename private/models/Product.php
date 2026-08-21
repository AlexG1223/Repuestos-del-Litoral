<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Models;

use RepuestosDelLitoral\Config\Database;
use PDO;

class Product {
    /**
     * Devuelve una lista paginada de productos con sus filtros e imagen principal.
     */
    public static function paginate(int $page = 1, int $perPage = 12, array $filters = []): array {
        $db = Database::getConnection();
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $where = ["p.active = 1"];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = ?";
            $params[] = (int)$filters['category_id'];
        } elseif (!empty($filters['category_slug'])) {
            $where[] = "c.slug = ?";
            $params[] = $filters['category_slug'];
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . trim($filters['search']) . '%';
            $where[] = "(p.name LIKE ? OR p.code LIKE ? OR p.description LIKE ? OR CAST(p.retail_price AS CHAR) LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = implode(' AND ', $where);

        $countSql = "
            SELECT COUNT(DISTINCT p.id) as total 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE {$whereClause}
        ";
        $stmtCount = $db->prepare($countSql);
        $stmtCount->execute($params);
        $total = (int)($stmtCount->fetchColumn() ?: 0);

        $sql = "
            SELECT 
                p.id,
                p.category_id,
                c.name AS category_name,
                c.slug AS category_slug,
                p.code,
                p.name,
                p.slug,
                p.description,
                p.retail_price,
                p.wholesale_price,
                p.stock,
                p.created_at,
                (
                    SELECT url 
                    FROM product_images pi 
                    WHERE pi.product_id = p.id 
                    ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC 
                    LIMIT 1
                ) AS primary_image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereClause}
            ORDER BY p.name ASC, p.id DESC
            LIMIT ? OFFSET ?
        ";

        $stmt = $db->prepare($sql);
        
        $bindIndex = 1;
        foreach ($params as $param) {
            $stmt->bindValue($bindIndex++, $param);
        }
        $stmt->bindValue($bindIndex++, $perPage, PDO::PARAM_INT);
        $stmt->bindValue($bindIndex++, $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $items = $stmt->fetchAll();

        foreach ($items as &$item) {
            $item['retail_price'] = (float)$item['retail_price'];
            $item['wholesale_price'] = isset($item['wholesale_price']) ? (float)$item['wholesale_price'] : null;
            $item['stock'] = (int)$item['stock'];
            if (empty($item['primary_image'])) {
                $item['primary_image'] = '/assets/uploads/products/placeholder.jpg';
            }
        }
        unset($item);

        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        return [
            'items'       => $items,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages
        ];
    }

    /**
     * Busca un producto por su slug y recupera su galería completa.
     */
    public static function findBySlug(string $slug): ?array {
        $db = Database::getConnection();
        $cleanSlug = trim(urldecode($slug));
        $normalizedSlug = strtolower(trim((string)preg_replace('~[^\pL\d]+~u', '-', $cleanSlug), '-'));

        $sql = "
            SELECT 
                p.id,
                p.category_id,
                c.name AS category_name,
                c.slug AS category_slug,
                p.code,
                p.name,
                p.slug,
                p.description,
                p.retail_price,
                p.wholesale_price,
                p.stock,
                p.active,
                p.created_at,
                p.updated_at
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE (p.slug = ? OR p.slug = ? OR p.code = ?" . (is_numeric($cleanSlug) ? " OR p.id = ?" : "") . ") AND p.active = 1
            LIMIT 1
        ";
        
        $params = [$cleanSlug, $normalizedSlug, $cleanSlug];
        if (is_numeric($cleanSlug)) {
            $params[] = (int)$cleanSlug;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $product = $stmt->fetch();

        if (!$product) {
            return null;
        }

        $product['retail_price'] = (float)$product['retail_price'];
        $product['wholesale_price'] = isset($product['wholesale_price']) ? (float)$product['wholesale_price'] : null;
        $product['stock'] = (int)$product['stock'];
        $product['images'] = ProductImage::allByProduct((int)$product['id']);

        if (empty($product['images'])) {
            $product['images'] = [
                [
                    'id' => 0,
                    'product_id' => $product['id'],
                    'url' => '/assets/uploads/products/placeholder.jpg',
                    'sort_order' => 1,
                    'is_primary' => 1
                ]
            ];
        }

        return $product;
    }

    /**
     * ADMINISTRACIÓN: Devuelve una lista de productos (incluyendo inactivos).
     */
    public static function allForAdmin(array $filters): array {
        $db = Database::getConnection();

        $where = ["1=1"];
        $params = [];

        if (!empty($filters['search'])) {
            $searchTerm = '%' . trim($filters['search']) . '%';
            $where[] = "(p.name LIKE ? OR p.code LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }

        if (isset($filters['active']) && $filters['active'] !== '') {
            $where[] = "p.active = ?";
            $params[] = (int)$filters['active'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT 
                p.id,
                p.category_id,
                c.name AS category_name,
                p.code,
                p.name,
                p.retail_price,
                p.wholesale_price,
                p.stock,
                p.active,
                (
                    SELECT url 
                    FROM product_images pi 
                    WHERE pi.product_id = p.id 
                    ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC 
                    LIMIT 1
                ) AS primary_image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereClause}
            ORDER BY p.name ASC, p.id DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        foreach ($items as &$item) {
            $item['retail_price'] = (float)$item['retail_price'];
            $item['wholesale_price'] = isset($item['wholesale_price']) ? (float)$item['wholesale_price'] : null;
            $item['stock'] = (int)$item['stock'];
            $item['active'] = (int)$item['active'];
            if (empty($item['primary_image'])) {
                $item['primary_image'] = '/assets/uploads/products/placeholder.jpg';
            }
        }

        return $items;
    }

    /**
     * ADMINISTRACIÓN: Busca un producto por su ID incluyendo galería para edición.
     */
    public static function findByIdForAdmin(int $id): ?array {
        $db = Database::getConnection();
        $sql = "
            SELECT 
                p.*
            FROM products p
            WHERE p.id = ?
            LIMIT 1
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) {
            return null;
        }

        $product['retail_price'] = (float)$product['retail_price'];
        $product['wholesale_price'] = isset($product['wholesale_price']) ? (float)$product['wholesale_price'] : null;
        $product['stock'] = (int)$product['stock'];
        $product['active'] = (int)$product['active'];
        $product['images'] = ProductImage::allByProduct((int)$product['id']);

        return $product;
    }

    /**
     * ADMINISTRACIÓN: Crea un nuevo producto.
     */
    public static function create(array $data): int {
        $db = Database::getConnection();
        $slug = self::generateSlug($data['name']);

        $sql = "
            INSERT INTO products (category_id, code, name, slug, description, retail_price, wholesale_price, stock, active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            $data['category_id'] ? (int)$data['category_id'] : null,
            trim($data['code'] ?? ''),
            trim($data['name']),
            $slug,
            $data['description'] ?? '',
            (float)($data['retail_price'] ?? 0),
            isset($data['wholesale_price']) && $data['wholesale_price'] !== '' ? (float)$data['wholesale_price'] : null,
            (int)($data['stock'] ?? 0),
            (int)($data['active'] ?? 1)
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * ADMINISTRACIÓN: Actualiza un producto existente.
     */
    public static function update(int $id, array $data): void {
        $db = Database::getConnection();
        
        $rawSlug = isset($data['slug']) ? trim((string)$data['slug']) : '';
        $slugToProcess = $rawSlug !== '' ? $rawSlug : $data['name'];
        $slug = self::generateSlug($slugToProcess, $id);

        $sql = "
            UPDATE products 
            SET category_id = ?, code = ?, name = ?, slug = ?, description = ?, 
                retail_price = ?, wholesale_price = ?, stock = ?, active = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            $data['category_id'] ? (int)$data['category_id'] : null,
            trim($data['code'] ?? ''),
            trim($data['name']),
            $slug,
            $data['description'] ?? '',
            (float)($data['retail_price'] ?? 0),
            isset($data['wholesale_price']) && $data['wholesale_price'] !== '' ? (float)$data['wholesale_price'] : null,
            (int)($data['stock'] ?? 0),
            (int)($data['active'] ?? 1),
            $id
        ]);
    }

    /**
     * ADMINISTRACIÓN: Cambia el estado de activo (Baja Lógica).
     */
    public static function setActive(int $id, bool $active): void {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE products SET active = ? WHERE id = ?");
        $stmt->execute([$active ? 1 : 0, $id]);
    }

    /**
     * ADMINISTRACIÓN: Elimina permanentemente un producto de la base de datos.
     */
    public static function deletePermanently(int $id): void {
        $db = Database::getConnection();

        // Eliminar carpeta de imágenes del producto si existe en disco
        $uploadDir = dirname(__DIR__, 2) . '/public_html/assets/uploads/products/' . $id;
        if (is_dir($uploadDir)) {
            $files = glob($uploadDir . '/*');
            if ($files) {
                foreach ($files as $file) {
                    if (is_file($file)) unlink($file);
                }
            }
            @rmdir($uploadDir);
        }

        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Busca un producto por su código.
     */
    public static function findByCode(string $code): ?array {
        $db = Database::getConnection();
        $sql = "SELECT * FROM products WHERE code = ? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([trim($code)]);
        $product = $stmt->fetch();
        return $product ?: null;
    }

    /**
     * Helper: Genera un slug único a partir de un string.
     */
    private static function generateSlug(string $name, ?int $ignoreId = null): string {
        // Sacar acentos y caracteres raros
        $slug = preg_replace('~[^\pL\d]+~u', '-', $name);
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        $slug = preg_replace('~[^-\w]+~', '', $slug);
        $slug = trim($slug, '-');
        $slug = preg_replace('~-+~', '-', $slug);
        $slug = strtolower($slug);

        if (empty($slug)) {
            $slug = 'producto';
        }

        $db = Database::getConnection();
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $sql = "SELECT id FROM products WHERE slug = ?";
            $params = [$slug];
            if ($ignoreId !== null) {
                $sql .= " AND id != ?";
                $params[] = $ignoreId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            if (!$stmt->fetch()) {
                break;
            }
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
