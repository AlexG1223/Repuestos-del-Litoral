<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Controllers;

use RepuestosDelLitoral\Models\User;
use InvalidArgumentException;

class AdminUserController {

    public function listWholesale(?string $filter): array {
        $approvedFilter = null;
        if ($filter === 'approved') {
            $approvedFilter = true;
        } elseif ($filter === 'pending') {
            $approvedFilter = false;
        }

        $users = User::listWholesale($approvedFilter);
        
        // Remove password hashes before sending
        return array_map([User::class, 'sanitize'], $users);
    }

    public function approve(int $userId): void {
        User::setApproved($userId, true);
    }

    public function revoke(int $userId): void {
        User::setApproved($userId, false);
    }

    public function createWholesaleClient(array $payload): array {
        $name         = trim((string)($payload['name'] ?? ''));
        $email        = strtolower(trim((string)($payload['email'] ?? '')));
        $phone        = trim((string)($payload['phone'] ?? ''));
        $businessName = trim((string)($payload['businessName'] ?? ''));

        if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
            throw new InvalidArgumentException('El nombre debe tener entre 2 y 150 caracteres.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Formato de email inválido.');
        }

        if (mb_strlen($businessName) < 2) {
            throw new InvalidArgumentException('El nombre del negocio es obligatorio para clientes mayoristas.');
        }

        $existingUser = User::findByEmail($email);
        if ($existingUser) {
            throw new InvalidArgumentException('Ya existe un usuario registrado con ese email.');
        }

        $data = [
            'name'          => $name,
            'email'         => $email,
            'phone'         => $phone,
            'business_name' => $businessName
        ];

        return User::createByAdmin($data);
    }
}
