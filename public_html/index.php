<?php
declare(strict_types=1);

require_once __DIR__ . '/../private/config/settings.php';
require_once __DIR__ . '/../private/services/SeoService.php';
require_once __DIR__ . '/../private/models/Category.php';
require_once __DIR__ . '/../private/models/Product.php';

use RepuestosDelLitoral\Services\SeoService;
use RepuestosDelLitoral\Models\Category;
use RepuestosDelLitoral\Models\Product;

$baseUrl = SeoService::getBaseUrl();
$localSchema = SeoService::getLocalBusinessSchema();
$breadcrumbSchema = SeoService::getBreadcrumbSchema([
    ['name' => 'Inicio', 'url' => '/inicio.php'],
    ['name' => 'Tienda y Catálogo', 'url' => '/index.php']
]);

$selectedCategory = isset($_GET['category']) ? trim((string)$_GET['category']) : null;
$seoMeta = SeoService::getCategorySeoMeta($selectedCategory);

$canonicalUrl = $baseUrl . '/index.php' . ($selectedCategory ? '?category=' . rawurlencode($selectedCategory) : '');

// Cargar categorías e ítems principales para fallback SSR
$categories = [];
$featuredProducts = [];
try {
    $categories = Category::all();
    $paginateResult = Product::paginate(1, 12, $selectedCategory ? ['category' => $selectedCategory] : []);
    $featuredProducts = $paginateResult['items'] ?? [];
} catch (\Throwable $e) {
    // Fallback silencioso
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php require_once __DIR__ . '/includes/gtm-head.php'; ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($seoMeta['title']) ?></title>
  <meta name="description" content="<?= htmlspecialchars($seoMeta['meta_description']) ?>">
  <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
  <link rel="icon" href="/assets/img/logo.png" type="image/x-icon">

  <!-- Open Graph -->
  <meta property="og:title" content="<?= htmlspecialchars($seoMeta['title']) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($seoMeta['meta_description']) ?>">
  <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
  <meta property="og:type" content="website">
  <meta property="og:image" content="<?= htmlspecialchars($baseUrl . '/assets/img/inicio-1.jpg') ?>">

  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
  <?= json_encode($localSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>
  <script type="application/ld+json">
  <?= json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>

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

    <!-- Fallback semántico HTML para Crawlers e IAs sin ejecución JS -->
    <noscript>
      <section style="padding: 2rem 1.5rem; max-width: var(--max-width); margin: 0 auto;">
        <h1><?= htmlspecialchars($seoMeta['h1']) ?></h1>
        <p>📍 Repuestos del Litoral — Asencio 1930, Dolores, Soriano, Uruguay. Venta minorista y mayorista.</p>
        
        <h2>Categorías Disponibles</h2>
        <ul>
          <?php foreach ($categories as $cat): ?>
            <li>
              <a href="/index.php?category=<?= htmlspecialchars($cat['slug']) ?>">
                <?= htmlspecialchars($cat['name']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>

        <h2>Productos Destacados en Catálogo</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; margin-top: 1rem;">
          <?php foreach ($featuredProducts as $prod): ?>
            <article style="border: 1px solid #ddd; padding: 1rem; border-radius: 8px;">
              <h3>
                <a href="/producto/<?= htmlspecialchars($prod['slug']) ?>">
                  <?= htmlspecialchars($prod['name']) ?>
                </a>
              </h3>
              <p>Código: <?= htmlspecialchars($prod['code'] ?? 'N/A') ?></p>
              <p>Precio: UYU $<?= number_format((float)$prod['retail_price'], 2) ?></p>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    </noscript>
  </main>

  <!-- Botón Flotante de WhatsApp -->
  <?php require_once __DIR__ . '/includes/whatsapp-button.php'; ?>

  <!-- Pie de página compartido -->
  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <!-- Script Principal de la Aplicación -->
  <script type="module" src="/app.js?v=1.0.3"></script>
</body>

</html>