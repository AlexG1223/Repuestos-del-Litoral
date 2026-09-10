<?php
declare(strict_types=1);

require_once __DIR__ . '/../private/config/settings.php';
require_once __DIR__ . '/../private/services/SeoService.php';
require_once __DIR__ . '/../private/models/Category.php';
require_once __DIR__ . '/../private/models/Product.php';

use RepuestosDelLitoral\Services\SeoService;
use RepuestosDelLitoral\Models\Product;

$baseUrl = SeoService::getBaseUrl();
$localSchema = SeoService::getLocalBusinessSchema();
$breadcrumbSchema = SeoService::getBreadcrumbSchema([
    ['name' => 'Inicio', 'url' => '/inicio.php'],
    ['name' => 'Motosierras', 'url' => '/index.php?category=motosierras'],
    ['name' => 'Motosierra a Nafta', 'url' => '/motosierra-a-nafta.php']
]);

// Cargar productos de motosierras a nafta para mostrar en la guía
$motosierraProducts = [];
try {
    $paginateResult = Product::paginate(1, 8, ['category' => 'motosierras']);
    $motosierraProducts = $paginateResult['items'] ?? [];
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
  <title>Motosierra a Nafta: Guía de Compra | Repuestos del Litoral</title>
  <meta name="description"
    content="Guía de compra de motosierra a nafta vs eléctrica en Uruguay. Conozca modelos, potencia, repuestos y accesorios en Dolores, Soriano.">
  <link rel="canonical" href="<?= htmlspecialchars($baseUrl . '/motosierra-a-nafta.php') ?>">
  <link rel="icon" href="/assets/img/logo.png" type="image/x-icon">

  <!-- Open Graph -->
  <meta property="og:title" content="Motosierra a Nafta: Guía de Compra | Repuestos del Litoral">
  <meta property="og:description" content="Guía de compra de motosierra a nafta vs eléctrica en Uruguay. Conozca modelos, potencia, repuestos y accesorios en Dolores, Soriano.">
  <meta property="og:url" content="<?= htmlspecialchars($baseUrl . '/motosierra-a-nafta.php') ?>">
  <meta property="og:type" content="article">
  <meta property="og:image" content="<?= htmlspecialchars($baseUrl . '/assets/img/inicio-1.jpg') ?>">

  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
  <?= json_encode($localSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>
  <script type="application/ld+json">
  <?= json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>

  <!-- Estilos globales -->
  <link rel="stylesheet" href="/globals/main.css">
</head>

<body>

  <!-- Cabecera compartida -->
  <?php require_once __DIR__ . '/includes/header.php'; ?>

  <main class="main-content" style="padding: 2rem 1.5rem; max-width: var(--max-width); margin: 0 auto;">
    <nav aria-label="Breadcrumb" style="margin-bottom: 1.5rem; color: #666; font-size: 0.9rem;">
      <a href="/inicio.php">Inicio</a> &gt; 
      <a href="/index.php?category=motosierras">Motosierras</a> &gt; 
      <span>Motosierra a Nafta</span>
    </nav>

    <article style="background: white; padding: 2.5rem 2rem; border-radius: var(--radius-md, 8px); box-shadow: var(--shadow-card, 0 2px 8px rgba(0,0,0,0.05)); line-height: 1.7;">
      <h1 style="font-family: var(--font-heading); color: var(--color-dark); font-size: 2.2rem; margin-bottom: 1rem;">
        Motosierras a Nafta en Uruguay — Guía de Compra y Repuestos
      </h1>

      <p style="font-size: 1.1rem; color: #444; margin-bottom: 1.5rem;">
        La <strong>motosierra a nafta</strong> es la herramienta de corte por excelencia para tareas agrícolas, mantenimiento de campos, tala de montes y preparación de leña en Uruguay. En <strong>Repuestos del Litoral</strong> (Dolores, Soriano), ofrecemos equipos de alta exigencia de marcas como <strong>Stihl</strong> y <strong>Husqvarna</strong>, además de un completo catálogo de repuestos originales.
      </p>

      <h2 style="font-family: var(--font-heading); color: var(--color-primary); font-size: 1.5rem; margin-top: 2rem; margin-bottom: 0.75rem;">
        Ventajas de una Motosierra a Nafta para Trabajo en el Campo
      </h2>
      <ul style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
        <li><strong>Autonomía y Movilidad Total:</strong> Al no depender de cables ni baterías, permite trabajar en cualquier punto del predio o campo sin interrupciones.</li>
        <li><strong>Potencia de Corte Profesional:</strong> Los motores de 2 tiempos de 25cc a más de 70cc garantizan un alto torque para madera dura de montes nativos o eucaliptos.</li>
        <li><strong>Durabilidad y Repuestos Disponibles:</strong> Contamos con stock constante de <a href="/index.php?category=motosierras" style="color: var(--color-primary); text-decoration: underline;">bujías para motosierra, cadenas, espadas y carburadores</a>.</li>
      </ul>

      <h2 style="font-family: var(--font-heading); color: var(--color-primary); font-size: 1.5rem; margin-top: 2rem; margin-bottom: 0.75rem;">
        Motosierras Stihl y Husqvarna a Nafta
      </h2>
      <p style="margin-bottom: 1.5rem;">
        En nuestro local comercial de <strong>Asencio 1930, Dolores, Soriano</strong>, asesoramos a clientes minoristas y mayoristas sobre el modelo idóneo según la frecuencia de uso: desde motosierras compactas de poda hasta modelos de alta cilindrada para tala continua.
      </p>

      <?php if (!empty($motosierraProducts)): ?>
      <h3 style="font-size: 1.3rem; margin-top: 1.5rem; margin-bottom: 1rem;">Modelos y Repuestos de Motosierra a Nafta Disponibles:</h3>
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <?php foreach ($motosierraProducts as $prod): ?>
          <article style="border: 1px solid #eee; padding: 1rem; border-radius: 8px; text-align: center;">
            <h4 style="font-size: 1rem; margin-bottom: 0.5rem;">
              <a href="/producto/<?= htmlspecialchars($prod['slug']) ?>" style="color: var(--color-dark); text-decoration: none; font-weight: bold;">
                <?= htmlspecialchars($prod['name']) ?>
              </a>
            </h4>
            <p style="color: var(--color-primary); font-weight: bold; margin-bottom: 0.5rem;">
              UYU $<?= number_format((float)($prod['display_price'] ?? $prod['retail_price']), 2) ?>
            </p>
            <a href="/producto/<?= htmlspecialchars($prod['slug']) ?>" class="btn btn-primary" style="font-size: 0.85rem; padding: 0.4rem 0.8rem; display: inline-block;">
              Ver Detalles y Repuestos
            </a>
          </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <h2 style="font-family: var(--font-heading); color: var(--color-primary); font-size: 1.5rem; margin-top: 2rem; margin-bottom: 0.75rem;">
        Mantenimiento Preventivo: Cadenas, Espadas y Bujías para Motosierra
      </h2>
      <p style="margin-bottom: 1.5rem;">
        Para asegurar la máxima vida útil de su motosierra a nafta en Soriano y todo el Litoral, recuerde realizar la mezcla correcta de aceite de 2 tiempos, mantener afilada la cadena y sustituir periódicamente los <a href="/index.php?category=motosierras" style="color: var(--color-primary); text-decoration: underline;">filtros de aire y bujías para motosierra</a>.
      </p>

      <div style="margin-top: 2rem; padding: 1.5rem; background: #FFF9F2; border-radius: 8px; border-left: 4px solid var(--color-primary);">
        <h3 style="margin-top: 0; font-size: 1.1rem; color: var(--color-dark);">📍 Repuestos del Litoral — Dolores, Soriano, Uruguay</h3>
        <p style="margin-bottom: 0.5rem;">Dirección: Asencio 1930, Dolores, Soriano.</p>
        <p style="margin-bottom: 0.5rem;">Teléfono: 4534 4109 | WhatsApp: 099 655 283</p>
        <p style="margin-bottom: 0;">Envíos inmediatos a todo el país. Venta minorista y precio diferencial mayorista.</p>
      </div>
    </article>
  </main>

  <!-- Botón Flotante de WhatsApp -->
  <?php require_once __DIR__ . '/includes/whatsapp-button.php'; ?>

  <!-- Pie de página compartido -->
  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <script type="module" src="/app.js"></script>
</body>

</html>
