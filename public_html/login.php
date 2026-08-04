<?php
declare(strict_types=1);

require_once __DIR__ . '/../private/config/settings.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión | Repuestos del Litoral</title>
  <meta name="description" content="Acceda a su cuenta en Repuestos del Litoral para consultar estado de pedidos y tarifas comerciales.">
  <link rel="icon" href="/assets/img/logo.png" type="image/x-icon">
  
  <link rel="stylesheet" href="/globals/main.css">
  <link rel="stylesheet" href="/modules/Auth/styles/auth.css">
</head>
<body>

  <!-- Cabecera compartida -->
  <?php require_once __DIR__ . '/includes/header.php'; ?>

  <!-- Punto de entrada del login -->
  <main class="main-content">
    <div id="login-root">
      <div class="loading-spinner">
        <div class="spinner"></div>
        <p>Cargando inicio de sesión...</p>
      </div>
    </div>
  </main>

  <!-- Botón Flotante de WhatsApp -->
  <?php require_once __DIR__ . '/includes/whatsapp-button.php'; ?>

  <!-- Pie de página compartido -->
  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <script type="module" src="/app.js"></script>
</body>
</html>
