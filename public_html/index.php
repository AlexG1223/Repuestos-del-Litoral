<?php
declare(strict_types=1);

require_once __DIR__ . '/../private/config/settings.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Repuestos del Litoral | Motosierras, Desmalezadoras y Repuestos en Uruguay</title>
  <meta name="description"
    content="Venta minorista y mayorista de repuestos originales, motosierras, desmalezadoras, cadenas, lubricantes y accesorios. Envíos a todo Uruguay.">
  <link rel="icon" href="/assets/img/logo.png" type="image/x-icon">

  <!-- Estilos globales y del módulo Catálogo -->
  <link rel="stylesheet" href="/globals/main.css">
  <link rel="stylesheet" href="/modules/Catalog/styles/catalog.css">
</head>

<body>

  <!-- Cabecera compartida -->
  <?php require_once __DIR__ . '/includes/header.php'; ?>


  <!-- Punto de Entrada del Catálogo Interactive JS -->
  <main class="main-content">
    <div id="catalog-root">
      <!-- Se renderizará dinámicamente mediante useCatalog.js -->
      <div class="loading-spinner">
        <div class="spinner"></div>
        <p>Cargando tienda Repuestos del Litoral...</p>
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