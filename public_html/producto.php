<?php
declare(strict_types=1);

require_once __DIR__ . '/../private/config/settings.php';

$slug = isset($_GET['slug']) ? htmlspecialchars((string)$_GET['slug'], ENT_QUOTES, 'UTF-8') : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ficha de Producto | Repuestos del Litoral</title>
  <meta name="description" content="Consulte precio, detalles técnicos y disponibilidad de repuestos en Repuestos del Litoral Uruguay.">
  <link rel="icon" href="/assets/img/favicon.ico" type="image/x-icon">
  
  <!-- Estilos globales y del módulo Detalle de Producto -->
  <link rel="stylesheet" href="/globals/main.css">
  <link rel="stylesheet" href="/modules/ProductDetail/styles/detail.css">
</head>
<body>

  <!-- Cabecera compartida -->
  <?php require_once __DIR__ . '/includes/header.php'; ?>

  <!-- Punto de Entrada de Detalle de Producto Interactive JS -->
  <main class="main-content">
    <div id="detail-root" data-slug="<?php echo $slug; ?>">
      <!-- Se renderizará dinámicamente mediante useProductDetail.js -->
      <div class="loading-spinner">
        <div class="spinner"></div>
        <p>Cargando información del producto...</p>
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
