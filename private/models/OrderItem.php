<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Models;

use RepuestosDelLitoral\Config\Database;

class OrderItem {
    /**
     * Inserta múltiples ítems pertenecientes a una orden dentro de la base de datos.
     */
    public static function createMany(int $orderId, array $items): void {
        $db = Database::getConnection();

        $sql = "
            INSERT INTO order_items (
                order_id,
                product_id,
                quantity,
                unit_price
            ) VALUES (
                :order_id,
                :product_id,
                :quantity,
                :unit_price
            )
        ";

        $stmt = $db->prepare($sql);

        foreach ($items as $item) {
            $stmt->execute([
                ':order_id'   => $orderId,
                ':product_id' => $item['product_id'],
                ':quantity'   => $item['quantity'],
                ':unit_price' => $item['unit_price']
            ]);
        }
    }
}
