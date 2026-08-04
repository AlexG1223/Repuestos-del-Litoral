<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Models;

use RepuestosDelLitoral\Config\Database;
use PDO;

class Order {
    /**
     * Inserta una nueva orden en la base de datos y devuelve el ID generado.
     */
    public static function create(array $data): int {
        $db = Database::getConnection();

        $sql = "
            INSERT INTO orders (
                user_id,
                customer_name,
                customer_phone,
                customer_address,
                price_tier,
                total,
                status,
                created_at
            ) VALUES (
                :user_id,
                :customer_name,
                :customer_phone,
                :customer_address,
                :price_tier,
                :total,
                :status,
                NOW()
            )
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':user_id'          => $data['user_id'] ?? null,
            ':customer_name'    => $data['customer_name'],
            ':customer_phone'   => $data['customer_phone'],
            ':customer_address' => $data['customer_address'] ?? null,
            ':price_tier'       => $data['price_tier'] ?? 'retail',
            ':total'            => $data['total'],
            ':status'           => $data['status'] ?? 'sent_to_whatsapp',
        ]);

        return (int)$db->lastInsertId();
    }
}
