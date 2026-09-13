<?php
declare(strict_types=1);

require_once __DIR__ . '/../private/config/settings.php';
require_once __DIR__ . '/../private/services/SeoService.php';

use RepuestosDelLitoral\Services\SeoService;

$baseUrl = SeoService::getBaseUrl();

$qaList = [
    [
        'question' => '¿Dónde puedo comprar repuestos para tractor y maquinaria agrícola en Dolores y Soriano?',
        'answer' => 'En Repuestos del Litoral, ubicado en Asencio 1930, Dolores (Soriano, Uruguay), encontrarás un completo stock de repuestos para maquinaria agrícola e industrial, motosierras, desmalezadoras y motores. Teléfono: 4534 4109 / WhatsApp: 099 655 283.'
    ],
    [
        'question' => '¿Venden artículos de ferretería y herramientas en Dolores, Uruguay?',
        'answer' => 'Sí, contamos con un amplio catálogo de artículos de ferretería industrial, herramientas manuales, insumos de taller, accesorios para desmalezado y productos de mantenimiento.'
    ],
    [
        'question' => '¿Tienen calzado de trabajo y botas de campo?',
        'answer' => 'Ofrecemos calzado de seguridad, botas de trabajo y calzado industrial de alta durabilidad pensado para labores agrícolas, rurales y de taller.'
    ],
    [
        'question' => '¿Qué variedad de mates y artículos de pesca ofrecen?',
        'answer' => 'Disponemos de mates de calabaza y madera, bombillas de alpaca y acero inoxidable, termos y marroquinería, así como equipamiento completo de pesca deportiva (cañas, reeles y accesorios).'
    ],
    [
        'question' => '¿Venden alimentos y productos para mascotas en Repuestos del Litoral?',
        'answer' => 'Sí, incorporamos una selección de productos y alimentos para mascotas y animales de granja, cubriendo las necesidades del hogar y el campo.'
    ],
    [
        'question' => '¿Cuáles son la dirección y horarios de atención en Dolores, Soriano?',
        'answer' => 'Estamos en Asencio 1930, Dolores, Soriano. Horarios: Lunes a Viernes de 08:00 a 12:00 y de 14:00 a 18:00 hs. Sábados de 08:00 a 12:00 hs.'
    ],
    [
        'question' => '¿Realizan envíos a todo Uruguay?',
        'answer' => 'Sí, realizamos envíos a todo el territorio uruguayo a través de agencias de carga, atendiendo a clientes en Soriano, Río Negro, Colonia y el resto del país.'
    ]
];

$faqSchema = SeoService::getFaqSchema($qaList);
$localSchema = SeoService::getLocalBusinessSchema();
$breadcrumbSchema = SeoService::getBreadcrumbSchema([
    ['name' => 'Inicio', 'url' => '/inicio.php'],
    ['name' => 'Preguntas Frecuentes & SEO Local', 'url' => '/faq.php']
]);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php require_once __DIR__ . '/includes/gtm-head.php'; ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Preguntas Frecuentes y Cobertura Local | Repuestos del Litoral (Dolores, Soriano)</title>
  <meta name="description"
    content="Preguntas frecuentes sobre repuestos de maquinaria, ferretería, calzado de trabajo, mates, pesca y mascotas en Repuestos del Litoral, Dolores, Soriano, Uruguay.">
  <link rel="canonical" href="<?= htmlspecialchars($baseUrl . '/faq.php') ?>">
  <link rel="icon" href="/assets/img/logo.png" type="image/x-icon">

  <!-- Open Graph -->
  <meta property="og:title" content="Preguntas Frecuentes | Repuestos del Litoral (Dolores, Soriano)">
  <meta property="og:description" content="Información fáctica sobre repuestos agrícolas, ferretería, calzado, mates, pesca y mascotas en Dolores, Soriano.">
  <meta property="og:url" content="<?= htmlspecialchars($baseUrl . '/faq.php') ?>">
  <meta property="og:type" content="website">
  <meta property="og:image" content="<?= htmlspecialchars($baseUrl . '/assets/img/inicio-1.jpg') ?>">

  <!-- Estilos globales -->
  <link rel="stylesheet" href="/globals/main.css">

  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
  <?= json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>
  <script type="application/ld+json">
  <?= json_encode($localSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>
  <script type="application/ld+json">
  <?= json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>

  <style>
    .faq-hero {
      background: linear-gradient(135deg, var(--color-dark, #1A1A1A), #333333);
      color: white;
      padding: 3.5rem 1.5rem;
      text-align: center;
    }
    .faq-hero h1 {
      font-family: var(--font-heading);
      font-size: 2.5rem;
      color: var(--color-primary, #F5821F);
      margin-bottom: 0.75rem;
      text-transform: uppercase;
    }
    .faq-hero p {
      font-size: 1.15rem;
      color: #E0E0E0;
      max-width: 800px;
      margin: 0 auto;
    }
    .faq-container {
      max-width: 900px;
      margin: 3rem auto;
      padding: 0 1.5rem;
    }
    .faq-item {
      background: white;
      border-radius: var(--radius-md, 8px);
      padding: 1.5rem 2rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      border-left: 4px solid var(--color-primary, #F5821F);
    }
    .faq-item h2 {
      font-family: var(--font-heading);
      font-size: 1.25rem;
      color: var(--color-dark, #1A1A1A);
      margin-bottom: 0.75rem;
    }
    .faq-item p {
      color: #4A5568;
      line-height: 1.6;
      font-size: 1rem;
    }
    .local-info-box {
      background-color: #FFF9F2;
      border: 1px solid #FEEBC8;
      border-radius: 8px;
      padding: 2rem;
      margin-top: 3rem;
      text-align: center;
    }
    .local-info-box h3 {
      font-family: var(--font-heading);
      color: var(--color-dark, #1A1A1A);
      font-size: 1.5rem;
      margin-bottom: 1rem;
    }
  </style>
</head>

<body>

  <!-- Cabecera compartida -->
  <?php require_once __DIR__ . '/includes/header.php'; ?>

  <section class="faq-hero">
    <h1>Preguntas Frecuentes y Cobertura Local</h1>
    <p>Respuestas claras sobre nuestros servicios, productos de ferretería, repuestos de maquinaria, calzado, pesca, mates y envíos en Soriano y todo Uruguay.</p>
  </section>

  <main class="faq-container">
    <?php foreach ($qaList as $qa): ?>
      <article class="faq-item">
        <h2><?= htmlspecialchars($qa['question']) ?></h2>
        <p><?= htmlspecialchars($qa['answer']) ?></p>
      </article>
    <?php endforeach; ?>

    <section class="local-info-box">
      <h3>📍 Repuestos del Litoral — Dolores, Soriano</h3>
      <p style="margin-bottom: 0.5rem; color: #2D3748;">
        <strong>Dirección:</strong> Asencio 1930, Dolores, Soriano, CP 75200, Uruguay.
      </p>
      <p style="margin-bottom: 0.5rem; color: #2D3748;">
        <strong>Teléfono:</strong> 4534 4109 | <strong>WhatsApp:</strong> 099 655 283
      </p>
      <p style="color: #718096; font-size: 0.95rem;">
        Atendemos a todo el Litoral de Uruguay: Dolores, Mercedes, Young, Fray Bentos, Nueva Palmira y envíos a todo el país.
      </p>
    </section>
  </main>

  <!-- Botón Flotante de WhatsApp -->
  <?php require_once __DIR__ . '/includes/whatsapp-button.php'; ?>

  <!-- Pie de página compartido -->
  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <!-- Script Principal de la Aplicación -->
  <script type="module" src="/app.js?v=1.0.3"></script>
</body>

</html>
