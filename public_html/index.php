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

  <!-- Banner Héroe / Presentación -->
  <section class="hero-banner"
    style="background: linear-gradient(rgba(26,26,26,0.85), rgba(26,26,26,0.85)), url('/assets/uploads/products/placeholder.jpg') center/cover; padding: 4rem 1.5rem; text-align: center; color: white;">
    <div style="max-width: 800px; margin: 0 auto;">
      <h1
        style="font-family: var(--font-heading); font-size: 2.5rem; text-transform: uppercase; font-weight: 900; margin-bottom: 1rem; color: var(--color-primary);">
        Repuestos del Litoral
      </h1>
      <p style="font-size: 1.15rem; color: #E0E0E0; margin-bottom: 1.5rem;">
        Especialistas en repuestos y servicio para motosierras, desmalezadoras y maquinaria de jardín. Venta minorista y
        mayorista.
      </p>
      <a href="#catalogo" class="btn btn-primary" style="padding: 0.9rem 2rem; font-size: 1rem;">Ver Catálogo de
        Productos</a>
    </div>
  </section>

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