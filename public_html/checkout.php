<?php
declare(strict_types=1);

require_once __DIR__ . '/../private/config/settings.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout — Finalizar Pedido | Repuestos del Litoral</title>
  <meta name="description" content="Complete sus datos de contacto para registrar y enviar su pedido por WhatsApp a Repuestos del Litoral.">
  <link rel="icon" href="/assets/img/logo.png" type="image/x-icon">
  
  <!-- Estilos globales, del módulo Cart y del Checkout -->
  <link rel="stylesheet" href="/globals/main.css">
  <link rel="stylesheet" href="/modules/Cart/styles/cart.css">
  <link rel="stylesheet" href="/modules/Checkout/styles/checkout.css">
</head>
<body>

  <!-- Cabecera compartida -->
  <?php require_once __DIR__ . '/includes/header.php'; ?>

  <!-- Punto de Entrada del Checkout Interactive JS -->
  <main class="main-content">
    <div id="checkout-root">
      <div class="loading-spinner">
        <div class="spinner"></div>
        <p>Cargando checkout...</p>
      </div>
    </div>
  </main>

  <!-- Botón Flotante de WhatsApp -->
  <?php require_once __DIR__ . '/includes/whatsapp-button.php'; ?>

  <!-- Pie de página compartido -->
  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <!-- Script Principal de la Aplicación -->
  <script type="module" src="/app.js"></script>
</body>
</html>
