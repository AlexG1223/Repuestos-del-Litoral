<?php
declare(strict_types=1);

require_once __DIR__ . '/../private/config/database.php';
require_once __DIR__ . '/../private/config/settings.php';
require_once __DIR__ . '/../private/models/Category.php';
require_once __DIR__ . '/../private/models/ProductImage.php';
require_once __DIR__ . '/../private/models/Product.php';
require_once __DIR__ . '/../private/services/SessionService.php';
require_once __DIR__ . '/../private/services/PricingService.php';
require_once __DIR__ . '/../private/services/SeoService.php';
require_once __DIR__ . '/../private/controllers/CatalogController.php';

use RepuestosDelLitoral\Controllers\CatalogController;
use RepuestosDelLitoral\Services\SeoService;

$slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
$controller = new CatalogController();
$product = null;
if ($slug !== '') {
    try {
        $product = $controller->getProductDetail($slug);
    } catch (\Throwable $e) {
        $product = null;
    }
}

$baseUrl = SeoService::getBaseUrl();

if ($product) {
    $productSeo = SeoService::getProductSeoMeta($product);
    $pageTitle = htmlspecialchars($productSeo['title']);
    $rawDesc = !empty($product['description']) ? strip_tags($product['description']) : ($product['name'] . ' disponible en Repuestos del Litoral, Dolores, Soriano, Uruguay.');
    $metaDescription = htmlspecialchars($productSeo['meta_description']);
    $canonicalUrl = $baseUrl . '/producto/' . rawurlencode($product['slug']);

    $primaryImg = '/assets/uploads/products/placeholder.jpg';
    if (!empty($product['images']) && is_array($product['images'])) {
        $primaryImg = $product['images'][0]['url'] ?? $primaryImg;
    }
    $imageUrl = str_starts_with($primaryImg, 'http') ? $primaryImg : $baseUrl . $primaryImg;

    $productSchema = SeoService::getProductSchema($product);
    $localSchema = SeoService::getLocalBusinessSchema();
    
    $breadcrumbItems = [
        ['name' => 'Inicio', 'url' => '/inicio.php'],
        ['name' => 'Tienda', 'url' => '/index.php']
    ];
    if (!empty($product['category_name']) && !empty($product['category_slug'])) {
        $breadcrumbItems[] = ['name' => $product['category_name'], 'url' => '/index.php?category=' . rawurlencode($product['category_slug'])];
    }
    $breadcrumbItems[] = ['name' => $product['name'], 'url' => '/producto/' . rawurlencode($product['slug'])];
    $breadcrumbSchema = SeoService::getBreadcrumbSchema($breadcrumbItems);

} else {
    $pageTitle = 'Ficha de Producto | Repuestos del Litoral';
    $metaDescription = 'Consulte precio, detalles técnicos y disponibilidad de repuestos en Repuestos del Litoral Uruguay.';
    $canonicalUrl = $baseUrl . '/index.php';
    $productSchema = null;
    $localSchema = SeoService::getLocalBusinessSchema();
    $breadcrumbSchema = null;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php require_once __DIR__ . '/includes/gtm-head.php'; ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <meta name="description" content="<?= $metaDescription ?>">
  <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
  <link rel="icon" href="/assets/img/logo.png" type="image/x-icon">

  <?php if ($product): ?>
  <!-- Open Graph / Social Media -->
  <meta property="og:title" content="<?= htmlspecialchars($product['name']) ?> | Repuestos del Litoral">
  <meta property="og:description" content="<?= $metaDescription ?>">
  <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
  <meta property="og:type" content="product">
  <meta property="og:image" content="<?= htmlspecialchars($imageUrl) ?>">
  <meta property="product:price:amount" content="<?= number_format((float)($product['display_price'] ?? $product['retail_price']), 2, '.', '') ?>">
  <meta property="product:price:currency" content="UYU">

  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
  <?= json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>
  <script type="application/ld+json">
  <?= json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>
  <?php endif; ?>

  <script type="application/ld+json">
  <?= json_encode($localSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>

  <!-- Estilos globales y del módulo Detalle de Producto -->
  <link rel="stylesheet" href="/globals/main.css">
  <link rel="stylesheet" href="/modules/ProductDetail/styles/detail.css">
</head>

<body>

  <!-- Cabecera compartida -->
  <?php require_once __DIR__ . '/includes/header.php'; ?>

  <!-- Punto de Entrada de Detalle de Producto Interactive JS -->
  <main class="main-content">
    <div id="detail-root" data-slug="<?php echo htmlspecialchars($slug); ?>">
      <!-- Se renderizará dinámicamente mediante useProductDetail.js -->
      <div class="loading-spinner">
        <div class="spinner"></div>
        <p>Cargando información del producto...</p>
      </div>
    </div>

    <?php if ($product): ?>
    <!-- Fallback HTML plano para Crawlers y Motor de IA (GEO) sin ejecución JS -->
    <noscript>
      <article class="product-seo-fallback" style="padding: 2rem 1.5rem; max-width: var(--max-width); margin: 0 auto; background: white; border-radius: 8px;">
        <nav aria-label="Breadcrumb" style="margin-bottom: 1rem; color: #666; font-size: 0.9rem;">
          <a href="/inicio.php">Inicio</a> &gt; 
          <a href="/index.php">Tienda</a> &gt; 
          <?php if (!empty($product['category_name'])): ?>
            <a href="/index.php?category=<?= htmlspecialchars($product['category_slug']) ?>"><?= htmlspecialchars($product['category_name']) ?></a> &gt; 
          <?php endif; ?>
          <span><?= htmlspecialchars($product['name']) ?></span>
        </nav>

        <h1 style="font-family: var(--font-heading); color: var(--color-dark); font-size: 2rem; margin-bottom: 0.5rem;">
          <?= htmlspecialchars($product['name']) ?>
        </h1>

        <p style="color: #666; font-size: 0.95rem; margin-bottom: 1rem;">
          Código / SKU: <strong><?= htmlspecialchars($product['code'] ?? 'N/A') ?></strong> | 
          Categoría: <strong><?= htmlspecialchars($product['category_name'] ?? 'General') ?></strong>
        </p>

        <div style="display: flex; gap: 2rem; flex-wrap: wrap; margin-top: 1.5rem;">
          <div style="flex: 1; min-width: 280px; max-width: 450px;">
            <img src="<?= htmlspecialchars($imageUrl) ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?> - Repuestos del Litoral Dolores Soriano" 
                 style="width: 100%; height: auto; border-radius: 8px; border: 1px solid #eee;">
          </div>
          <div style="flex: 1; min-width: 280px;">
            <div style="font-size: 1.8rem; font-weight: bold; color: var(--color-primary); margin-bottom: 1rem;">
              UYU $<?= number_format((float)($product['display_price'] ?? $product['retail_price']), 2) ?>
            </div>
            <p style="margin-bottom: 1rem; color: <?= ((int)$product['stock'] > 0) ? 'green' : 'red' ?>; font-weight: bold;">
              <?= ((int)$product['stock'] > 0) ? '✔ Stock Disponible' : '❌ Agotado' ?>
            </p>
            <div style="line-height: 1.6; color: #333;">
              <h2 style="font-size: 1.2rem; margin-bottom: 0.5rem;">Descripción y Especificaciones:</h2>
              <p><?= nl2br(htmlspecialchars($rawDesc)) ?></p>
            </div>
            <div style="margin-top: 1.5rem; padding: 1rem; background: #FFF9F2; border-radius: 6px; font-size: 0.9rem;">
              <p>📍 <strong>Vendido por Repuestos del Litoral</strong> — Asencio 1930, Dolores, Soriano, Uruguay.</p>
              <p>📞 Consultas telefónicas: 4534 4109 | WhatsApp: 099 655 283</p>
            </div>
            
            <!-- Enlazado interno SEO hacia categorías principales -->
            <div style="margin-top: 1.5rem; border-top: 1px dashed #eee; padding-top: 1rem; font-size: 0.88rem; color: #555;">
              <p><strong>Explora categorías relacionadas:</strong></p>
              <ul style="list-style: none; padding-left: 0; margin-top: 0.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                <li>👉 <a href="/index.php?category=motosierras" style="color: var(--color-primary); text-decoration: underline;">Motosierras a Nafta y Repuestos</a></li>
                <li>👉 <a href="/index.php?category=desmalezadoras" style="color: var(--color-primary); text-decoration: underline;">Desmalezadoras y Repuestos</a></li>
                <li>👉 <a href="/motosierra-a-nafta.php" style="color: var(--color-primary); text-decoration: underline;">Guía de Motosierras a Nafta</a></li>
                <li>👉 <a href="/index.php" style="color: var(--color-primary); text-decoration: underline;">Ferretería y Herramientas de Jardín</a></li>
              </ul>
            </div>
          </div>
        </div>
      </article>
    </noscript>
    <?php endif; ?>
  </main>

  <!-- Botón Flotante de WhatsApp -->
  <?php require_once __DIR__ . '/includes/whatsapp-button.php'; ?>

  <!-- Pie de página compartido -->
  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <!-- Script Principal de la Aplicación -->
  <script type="module" src="/app.js?v=1.0.3"></script>
</body>

</html>