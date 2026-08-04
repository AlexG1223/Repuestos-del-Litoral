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

        // Filtro por categoría (id o slug)
        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = ?";
            $params[] = (int)$filters['category_id'];
        } elseif (!empty($filters['category_slug'])) {
            $where[] = "c.slug = ?";
            $params[] = $filters['category_slug'];
        }

        // Filtro por búsqueda textual (nombre o código)
        if (!empty($filters['search'])) {
            $searchTerm = '%' . trim($filters['search']) . '%';
            $where[] = "(p.name LIKE ? OR p.code LIKE ? OR p.description LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = implode(' AND ', $where);

        // Consulta de recuento total
        $countSql = "
            SELECT COUNT(DISTINCT p.id) as total 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE {$whereClause}
        ";
        $stmtCount = $db->prepare($countSql);
        $stmtCount->execute($params);
        $total = (int)($stmtCount->fetchColumn() ?: 0);

        // Consulta de productos
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
            ORDER BY p.id DESC
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

        // Formateo ligero de items (asegurar tipos)
        foreach ($items as &$item) {
            $item['retail_price'] = (float)$item['retail_price'];
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
                p.stock,
                p.active,
                p.created_at,
                p.updated_at
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.slug = ? AND p.active = 1
            LIMIT 1
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$slug]);
        $product = $stmt->fetch();

        if (!$product) {
            return null;
        }

        $product['retail_price'] = (float)$product['retail_price'];
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
}
