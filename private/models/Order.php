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
                customer_email,
                customer_phone,
                customer_address,
                price_tier,
                total,
                payment_method,
                status,
                external_reference,
                created_at
            ) VALUES (
                :user_id,
                :customer_name,
                :customer_email,
                :customer_phone,
                :customer_address,
                :price_tier,
                :total,
                :payment_method,
                :status,
                :external_reference,
                NOW()
            )
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':user_id'          => $data['user_id'] ?? null,
            ':customer_name'    => $data['customer_name'],
            ':customer_email'   => $data['customer_email'] ?? null,
            ':customer_phone'   => $data['customer_phone'],
            ':customer_address' => $data['customer_address'] ?? null,
            ':price_tier'       => $data['price_tier'] ?? 'retail',
            ':total'            => $data['total'],
            ':payment_method'   => $data['payment_method'] ?? 'whatsapp',
            ':status'           => $data['status'] ?? 'pendiente',
            ':external_reference'=> $data['external_reference'] ?? null
        ]);

        return (int)$db->lastInsertId();
    }

    public static function updateStatus(int $id, string $status): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE orders SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public static function updateExternalReference(int $id, string $ref): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE orders SET external_reference = :ref WHERE id = :id");
        return $stmt->execute([':ref' => $ref, ':id' => $id]);
    }

    public static function findById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        return $order ?: null;
    }

    public static function paginate(int $page = 1, int $perPage = 20): array {
        $db = Database::getConnection();
        $offset = ($page - 1) * $perPage;

        $countStmt = $db->query("SELECT COUNT(*) FROM orders");
        $total = (int)$countStmt->fetchColumn();

        $stmt = $db->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        return [
            'items'       => $items,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages
        ];
    }
}
