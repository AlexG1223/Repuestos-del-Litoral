<?php
declare(strict_types=1);

require_once __DIR__ . '/../private/config/settings.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php require_once __DIR__ . '/includes/gtm-head.php'; ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi Cuenta | Repuestos del Litoral</title>
  <meta name="description" content="Gestione su cuenta de usuario y consulte el estado de su tarifa mayorista en Repuestos del Litoral.">
  <link rel="icon" href="/assets/img/logo.png" type="image/x-icon">
  
  <link rel="stylesheet" href="/globals/main.css">
  <link rel="stylesheet" href="/modules/Auth/styles/auth.css">
</head>
<body>

  <!-- Cabecera compartida -->
  <?php require_once __DIR__ . '/includes/header.php'; ?>

  <!-- Punto de entrada de Mi Cuenta -->
  <main class="main-content">
    <div id="account-root">
      <div class="loading-spinner">
        <div class="spinner"></div>
        <p>Cargando información de tu cuenta...</p>
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
