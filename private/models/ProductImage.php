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

    public static function create(int $productId, string $url, bool $isPrimary): int {
        $db = Database::getConnection();
        
        if ($isPrimary) {
            $stmt = $db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
            $stmt->execute([$productId]);
        }

        // Obtener el mayor sort_order
        $stmt = $db->prepare("SELECT MAX(sort_order) FROM product_images WHERE product_id = ?");
        $stmt->execute([$productId]);
        $maxSort = (int)$stmt->fetchColumn();
        $nextSort = $maxSort + 1;

        $stmt = $db->prepare("INSERT INTO product_images (product_id, url, sort_order, is_primary) VALUES (?, ?, ?, ?)");
        $stmt->execute([$productId, $url, $nextSort, $isPrimary ? 1 : 0]);
        return (int)$db->lastInsertId();
    }

    public static function delete(int $imageId): void {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT url FROM product_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $image = $stmt->fetch();

        if ($image) {
            $url = $image['url'];
            $basePath = dirname(__DIR__, 2) . '/public_html';
            $filePath = $basePath . parse_url($url, PHP_URL_PATH);
            
            // Borrar archivo físico si es un upload de nuestro sistema (no placeholder ni URL externa)
            if (str_starts_with($url, '/assets/uploads/products/') && file_exists($filePath) && is_file($filePath)) {
                @unlink($filePath);
            }

            $stmt = $db->prepare("DELETE FROM product_images WHERE id = ?");
            $stmt->execute([$imageId]);
        }
    }

    public static function setPrimary(int $productId, int $imageId): void {
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
            $stmt->execute([$productId]);

            $stmt = $db->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ? AND product_id = ?");
            $stmt->execute([$imageId, $productId]);
            
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function reorder(int $productId, array $orderedIds): void {
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("UPDATE product_images SET sort_order = ? WHERE id = ? AND product_id = ?");
            $order = 1;
            foreach ($orderedIds as $id) {
                $stmt->execute([$order++, (int)$id, $productId]);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
