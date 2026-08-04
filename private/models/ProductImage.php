<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Models;

use RepuestosDelLitoral\Config\Database;

class ProductImage {
    public static function allByProduct(int $productId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT id, product_id, url, sort_order, is_primary 
            FROM product_images 
            WHERE product_id = ? 
            ORDER BY is_primary DESC, sort_order ASC, id ASC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
}
