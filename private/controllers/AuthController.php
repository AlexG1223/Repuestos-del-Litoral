<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Controllers;

use RepuestosDelLitoral\Models\User;
use RepuestosDelLitoral\Services\SessionService;

class AuthController {

    /**
     * Procesa el registro de un nuevo usuario.
     */
    public function register(array $payload): array {
        $errors = $this->validateRegisterPayload($payload);
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors'  => $errors
            ];
        }

        $email = strtolower(trim((string)$payload['email']));

        // Verificar si el email ya existe
        if (User::findByEmail($email) !== null) {
            return [
                'success' => false,
                'errors'  => [
                    'email' => 'Este correo electrónico ya se encuentra registrado.'
                ]
            ];
        }

        // Crear usuario en base de datos
        $userId = User::create([
            'name'            => trim((string)$payload['name']),
            'email'           => $email,
            'phone'           => isset($payload['phone']) ? trim((string)$payload['phone']) : null,
            'password'        => (string)$payload['password'],
            'wants_wholesale' => !empty($payload['wantsWholesale']),
            'business_name'   => isset($payload['businessName']) ? trim((string)$payload['businessName']) : null
        ]);

        $user = User::findById($userId);
        if (!$user) {
            return [
                'success' => false,
                'error'   => 'No se pudo crear la cuenta de usuario.'
            ];
        }

        // Iniciar sesión automáticamente
        SessionService::login($user);

        return [
            'success' => true,
            'data'    => User::sanitize($user)
        ];
    }

    /**
     * Procesa el inicio de sesión de usuario.
     */
    public function login(array $payload): array {
        $email = isset($payload['email']) ? strtolower(trim((string)$payload['email'])) : '';
        $password = isset($payload['password']) ? (string)$payload['password'] : '';

        if ($email === '' || $password === '') {
            return [
                'success' => false,
                'error'   => 'Por favor ingrese su email y contraseña.'
            ];
        }

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return [
                'success' => false,
                'error'   => 'Email o contraseña incorrectos.'
            ];
        }

        // Iniciar sesión
        SessionService::login($user);

        return [
            'success' => true,
            'data'    => User::sanitize($user)
        ];
    }

    /**
     * Cierra la sesión activa.
     */
    public function logout(): void {
        SessionService::logout();
    }

    /**
     * Devuelve los datos del usuario actual de la sesión.
     */
    public function me(): ?array {
        return SessionService::currentUser();
    }

    /**
     * Validaciones para el formulario de registro.
     */
    private function validateRegisterPayload(array $payload): array {
        $errors = [];

        // Nombre
        $name = isset($payload['name']) ? trim((string)$payload['name']) : '';
        if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
            $errors['name'] = 'El nombre es obligatorio (mínimo 2 caracteres).';
        }

        // Email
        $email = isset($payload['email']) ? strtolower(trim((string)$payload['email'])) : '';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Ingrese un correo electrónico válido.';
        }

        // Teléfono
        $phone = isset($payload['phone']) ? trim((string)$payload['phone']) : '';
        $digitsOnly = preg_replace('/\D/', '', $phone);
        if (strlen($digitsOnly) < 8) {
            $errors['phone'] = 'Ingrese un teléfono de contacto válido (mínimo 8 dígitos).';
        }

        // Contraseña
        $password = isset($payload['password']) ? (string)$payload['password'] : '';
        if (strlen($password) < 8) {
            $errors['password'] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        // Confirmar contraseña
        $confirmPassword = isset($payload['confirmPassword']) ? (string)$payload['confirmPassword'] : '';
        if ($password !== $confirmPassword) {
            $errors['confirmPassword'] = 'Las contraseñas no coinciden.';
        }

        // Nombre de negocio si solicitó mayorista
        $wantsWholesale = !empty($payload['wantsWholesale']);
        $businessName = isset($payload['businessName']) ? trim((string)$payload['businessName']) : '';
        if ($wantsWholesale && mb_strlen($businessName) < 2) {
            $errors['businessName'] = 'Indique el nombre de su negocio o empresa para la cuenta mayorista.';
        }

        return $errors;
    }
}
