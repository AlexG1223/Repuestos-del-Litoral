<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Models;

use RepuestosDelLitoral\Config\Database;
use PDO;

class Order {
    public static function ensureTable(): void {
        static $ensured = false;
        if ($ensured) return;
        
        $db = Database::getConnection();
        if ($db->inTransaction()) {
            return;
        }

        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS orders (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id INT UNSIGNED NULL,
                    customer_name VARCHAR(150) NOT NULL,
                    customer_phone VARCHAR(30) NOT NULL,
                    customer_email VARCHAR(150) NULL,
                    customer_address VARCHAR(255) NULL,
                    price_tier ENUM('retail','wholesale') NOT NULL DEFAULT 'retail',
                    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                    payment_method VARCHAR(50) NOT NULL DEFAULT 'whatsapp',
                    status VARCHAR(50) NOT NULL DEFAULT 'pendiente',
                    external_reference VARCHAR(255) NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Si la tabla ya existía pero con la estructura antigua, agregar las columnas faltantes
            $columns = $db->query("SHOW COLUMNS FROM orders")->fetchAll(PDO::FETCH_COLUMN);

            if (!in_array('customer_email', $columns)) {
                $db->exec("ALTER TABLE orders ADD COLUMN customer_email VARCHAR(150) NULL AFTER customer_phone");
            }
            if (!in_array('payment_method', $columns)) {
                $db->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) NOT NULL DEFAULT 'whatsapp' AFTER total");
            }
            if (!in_array('external_reference', $columns)) {
                $db->exec("ALTER TABLE orders ADD COLUMN external_reference VARCHAR(255) NULL AFTER status");
            }
            if (!in_array('updated_at', $columns)) {
                $db->exec("ALTER TABLE orders ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            }

            // Cambiar columna status a VARCHAR(50) por compatibilidad con ENUMs antiguos
            $db->exec("ALTER TABLE orders MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pendiente'");

            $ensured = true;
        } catch (\Throwable $e) {}
    }

    /**
     * Inserta una nueva orden en la base de datos y devuelve el ID generado.
     */
    public static function create(array $data): int {
        self::ensureTable();
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

    /**
     * Procesa el cambio de estado de pago de una orden de forma idempotente,
     * actualizando external_reference y descontando stock si pasa a 'pagado'.
     */
    public static function processPaymentStatus(int $orderId, string $status, ?string $externalRef = null): bool {
        $order = self::findById($orderId);
        if (!$order) {
            return false;
        }

        $alreadyFulfilled = in_array($order['status'], ['pagado', 'finalizado'], true);
        $newFulfilled     = in_array($status, ['pagado', 'finalizado'], true);

        // Idempotencia: Si ya fue procesado como pagado/finalizado y cambia a pagado/finalizado, solo actualizar estado
        if ($alreadyFulfilled && $newFulfilled) {
            self::updateStatus($orderId, $status);
            if ($externalRef && empty($order['external_reference'])) {
                self::updateExternalReference($orderId, $externalRef);
            }
            return true;
        }

        $db = Database::getConnection();

        try {
            $db->beginTransaction();

            // 1. Actualizar estado
            $stmt = $db->prepare("UPDATE orders SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $status, ':id' => $orderId]);

            // 2. Actualizar external_reference si fue provisto
            if ($externalRef) {
                $stmtRef = $db->prepare("UPDATE orders SET external_reference = :ref WHERE id = :id");
                $stmtRef->execute([':ref' => $externalRef, ':id' => $orderId]);
            }

            // 3. Si el nuevo estado es 'pagado' o 'finalizado' y antes no lo estaba, descontar stock en BD
            if ($newFulfilled && !$alreadyFulfilled) {
                require_once __DIR__ . '/OrderItem.php';
                $items = OrderItem::findByOrderId($orderId);
                $stockStmt = $db->prepare("UPDATE products SET stock = GREATEST(0, stock - :qty) WHERE id = :product_id");

                foreach ($items as $item) {
                    $stockStmt->execute([
                        ':qty'        => (int)$item['quantity'],
                        ':product_id' => (int)$item['product_id']
                    ]);
                }
            }

            $db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }


    public static function delete(int $id): bool {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            $stmtItem = $db->prepare("DELETE FROM order_items WHERE order_id = :id");
            $stmtItem->execute([':id' => $id]);

            $stmtOrder = $db->prepare("DELETE FROM orders WHERE id = :id");
            $stmtOrder->execute([':id' => $id]);

            $db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public static function findById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        return $order ?: null;
    }

    public static function findByIdWithItems(int $id): ?array {
        $order = self::findById($id);
        if (!$order) {
            return null;
        }
        require_once __DIR__ . '/OrderItem.php';
        $order['items'] = OrderItem::findByOrderId($id);
        return $order;
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

        require_once __DIR__ . '/OrderItem.php';
        foreach ($items as &$order) {
            $order['items'] = OrderItem::findByOrderId((int)$order['id']);
        }
        unset($order);

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
