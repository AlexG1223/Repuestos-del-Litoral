<?php
require_once __DIR__ . '/../private/config/database.php';

try {
    $db = \RepuestosDelLitoral\Config\Database::getConnection();

    $email = 'admin@repuestos.com';
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        die("El administrador ya existe. Inicia sesión con: " . $email);
    }

    $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, role, approved, created_at) VALUES ('Administrador', ?, ?, 'admin', 1, NOW())");
    $stmt->execute([$email, $hash]);

    echo "<h1>¡Administrador creado con éxito!</h1>";
    echo "<p><strong>Email:</strong> {$email}</p>";
    echo "<p><strong>Contraseña:</strong> {$password}</p>";
    echo "<br><a href='/login.php'>Ir al login</a>";
    echo "<br><br><small>Por seguridad, borra este archivo (setup-admin.php) luego de usarlo.</small>";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
