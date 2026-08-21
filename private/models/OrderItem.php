<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Models;

use RepuestosDelLitoral\Config\Database;

class OrderItem {
    public static function ensureTable(): void {
        static $ensured = false;
        if ($ensured) return;
        
        $db = Database::getConnection();
        if ($db->inTransaction()) {
            return;
        }

        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS order_items (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    order_id INT UNSIGNED NOT NULL,
                    product_id INT UNSIGNED NOT NULL,
                    product_name VARCHAR(255) NULL,
                    product_code VARCHAR(100) NULL,
                    quantity INT NOT NULL,
                    unit_price DECIMAL(12,2) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $columns = $db->query("SHOW COLUMNS FROM order_items")->fetchAll(\PDO::FETCH_COLUMN);

            if (!in_array('product_name', $columns)) {
                $db->exec("ALTER TABLE order_items ADD COLUMN product_name VARCHAR(255) NULL AFTER product_id");
            }
            if (!in_array('product_code', $columns)) {
                $db->exec("ALTER TABLE order_items ADD COLUMN product_code VARCHAR(100) NULL AFTER product_name");
            }

            $ensured = true;
        } catch (\Throwable $e) {}
    }

    /**
     * Inserta múltiples ítems pertenecientes a una orden dentro de la base de datos.
     */
    public static function createMany(int $orderId, array $items): void {
        self::ensureTable();
        $db = Database::getConnection();

        $sql = "
            INSERT INTO order_items (
                order_id,
                product_id,
                product_name,
                product_code,
                quantity,
                unit_price
            ) VALUES (
                :order_id,
                :product_id,
                :product_name,
                :product_code,
                :quantity,
                :unit_price
            )
        ";

        $stmt = $db->prepare($sql);

        foreach ($items as $item) {
            $stmt->execute([
                ':order_id'     => $orderId,
                ':product_id'   => $item['product_id'],
                ':product_name' => $item['product_name'] ?? $item['name'] ?? null,
                ':product_code' => $item['product_code'] ?? $item['code'] ?? null,
                ':quantity'     => $item['quantity'],
                ':unit_price'   => $item['unit_price']
            ]);
        }
    }

    /**
     * Obtiene los ítems asociados a una orden incluyendo nombre, código e imagen de producto.
     */
    public static function findByOrderId(int $orderId): array {
        self::ensureTable();
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                oi.*, 
                COALESCE(oi.product_name, p.name, CONCAT('Producto #', oi.product_id)) AS product_name,
                COALESCE(oi.product_code, p.code, '') AS product_code,
                (
                    SELECT url 
                    FROM product_images pi 
                    WHERE pi.product_id = p.id 
                    ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC 
                    LIMIT 1
                ) AS product_image
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = :order_id
        ");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}


