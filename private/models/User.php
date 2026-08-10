<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Models;

use RepuestosDelLitoral\Config\Database;
use PDO;

class User {
    /**
     * Registra un nuevo usuario en la base de datos con contraseña hasheada.
     */
    public static function create(array $data): int {
        $db = Database::getConnection();

        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $wantsWholesale = !empty($data['wants_wholesale']);
        
        $role = $wantsWholesale ? 'wholesale' : 'retail';
        $approved = $role === 'retail' ? 1 : 0;
        $businessName = $wantsWholesale && !empty($data['business_name']) ? trim((string)$data['business_name']) : null;

        $sql = "
            INSERT INTO users (
                name,
                email,
                phone,
                business_name,
                password_hash,
                role,
                approved,
                created_at
            ) VALUES (
                :name,
                :email,
                :phone,
                :business_name,
                :password_hash,
                :role,
                :approved,
                NOW()
            )
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':name'          => trim((string)$data['name']),
            ':email'         => strtolower(trim((string)$data['email'])),
            ':phone'         => isset($data['phone']) ? trim((string)$data['phone']) : null,
            ':business_name' => $businessName,
            ':password_hash' => $passwordHash,
            ':role'          => $role,
            ':approved'      => $approved
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Busca un usuario por su dirección de email. Devuelve el registro completo (incluye hash para login).
     */
    public static function findByEmail(string $email): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT id, name, email, phone, business_name, password_hash, role, approved, created_at 
            FROM users 
            WHERE email = ? 
            LIMIT 1
        ");
        $stmt->execute([strtolower(trim($email))]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Busca un usuario por su ID. Devuelve el perfil seguro (sin password_hash).
     */
    public static function findById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT id, name, email, phone, business_name, role, approved, created_at 
            FROM users 
            WHERE id = ? 
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if ($user) {
            $user['approved'] = (int)$user['approved'];
        }
        return $user ?: null;
    }

    /**
     * Limpia la contraseña hasheada del objeto de usuario antes de enviarlo al cliente.
     */
    public static function sanitize(array $user): array {
        unset($user['password_hash']);
        if (isset($user['approved'])) {
            $user['approved'] = (int)$user['approved'];
        }
        return $user;
    }

    /**
     * ADMINISTRACIÓN: Lista todos los clientes con rol 'wholesale'.
     * Opcionalmente filtrados por estado de aprobación.
     */
    public static function listWholesale(?bool $approvedFilter = null): array {
        $db = Database::getConnection();
        $sql = "SELECT id, name, email, phone, business_name, role, approved, created_at FROM users WHERE role = 'wholesale'";
        $params = [];
        if ($approvedFilter !== null) {
            $sql .= " AND approved = ?";
            $params[] = $approvedFilter ? 1 : 0;
        }
        $sql .= " ORDER BY created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        foreach ($users as &$user) {
            $user['approved'] = (int)$user['approved'];
        }

        return $users;
    }

    /**
     * ADMINISTRACIÓN: Cambia el estado de aprobación de un usuario.
     */
    public static function setApproved(int $userId, bool $approved): void {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET approved = ? WHERE id = ?");
        $stmt->execute([$approved ? 1 : 0, $userId]);
    }

    /**
     * ADMINISTRACIÓN: Crea un cliente mayorista manualmente y devuelve la contraseña temporal.
     */
    public static function createByAdmin(array $data): array {
        $db = Database::getConnection();

        // Generar contraseña temporal
        $tempPassword = substr(bin2hex(random_bytes(10)), 0, 10);
        $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);

        $sql = "
            INSERT INTO users (
                name,
                email,
                phone,
                business_name,
                password_hash,
                role,
                approved,
                created_at
            ) VALUES (
                :name,
                :email,
                :phone,
                :business_name,
                :password_hash,
                'wholesale',
                1,
                NOW()
            )
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':name'          => trim((string)$data['name']),
            ':email'         => strtolower(trim((string)$data['email'])),
            ':phone'         => isset($data['phone']) ? trim((string)$data['phone']) : null,
            ':business_name' => isset($data['business_name']) ? trim((string)$data['business_name']) : null,
            ':password_hash' => $passwordHash
        ]);

        $userId = (int)$db->lastInsertId();

        return [
            'id' => $userId,
            'temp_password' => $tempPassword
        ];
    }
}
