<?php
declare(strict_types=1);

require_once __DIR__ . '/../private/config/database.php';
require_once __DIR__ . '/../private/config/settings.php';
require_once __DIR__ . '/../private/models/Product.php';
require_once __DIR__ . '/../private/models/ProductImage.php';
require_once __DIR__ . '/../private/services/SeoService.php';

use RepuestosDelLitoral\Services\SeoService;
use RepuestosDelLitoral\Models\Product;

$baseUrl = SeoService::getBaseUrl();
$localSchema = SeoService::getLocalBusinessSchema();

$topBestSellers = [];
try {
  $topBestSellers = Product::getTopBestSellers(3);
} catch (\Throwable $e) {
  $topBestSellers = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php require_once __DIR__ . '/includes/gtm-head.php'; ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Repuestos del Litoral | Repuestos y Reparación de Máquinas de Jardín y Equipos a Motor</title>
  <meta name="description"
    content="Tu mejor solución en repuestos originales y alternativos, reparación y servicio técnico especializado para motosierras, desmalezadoras, cortacéspedes, tractores de jardín, generadores y motobombas. Envíos a todo el país.">
  <link rel="canonical" href="<?= htmlspecialchars($baseUrl . '/inicio.php') ?>">
  <link rel="icon" href="/assets/img/logo.png" type="image/x-icon">

  <!-- Open Graph / Redes Sociales -->
  <meta property="og:title" content="Repuestos del Litoral | Solución en Repuestos y Equipos a Motor">
  <meta property="og:description"
    content="Repuestos originales y alternativos, servicio técnico especializado y envíos a todo el país para motosierras, desmalezadoras, cortacéspedes, tractores y generadores. Tu máquina en buenas manos.">
  <meta property="og:url" content="<?= htmlspecialchars($baseUrl . '/inicio.php') ?>">
  <meta property="og:type" content="website">
  <meta property="og:image" content="<?= htmlspecialchars($baseUrl . '/assets/img/inicio-1.jpg') ?>">

  <!-- Datos estructurados Schema.org -->
  <script type="application/ld+json">
  <?= json_encode($localSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>

  <!-- Estilos globales -->
  <link rel="stylesheet" href="/globals/main.css">

  <style>
    /* Estilos para el carrusel animado del hero */
    .hero-banner {
      position: relative;
      padding: 6rem 1.5rem;
      text-align: center;
      color: white;
      min-height: 70vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      overflow: hidden;
    }

    .carousel-bg {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      opacity: 0;
      transition: opacity 1.5s ease-in-out;
      z-index: 1;
    }

    .carousel-bg.active {
      opacity: 1;
    }

    .hero-content {
      position: relative;
      z-index: 2;
      max-width: 850px;
    }
  </style>
</head>

<body>

  <!-- Cabecera compartida -->
  <?php require_once __DIR__ . '/includes/header.php'; ?>

  <!-- Sección Hero Principal con Carrusel Animado -->
  <section class="hero-banner">

    <!-- Imágenes de fondo que rotarán -->
    <div class="carousel-bg active"
      style="background: linear-gradient(rgba(26,26,26,0.65), rgba(26,26,26,0.9)), url('/assets/img/inicio-1.jpg') center/cover;">
    </div>
    <div class="carousel-bg"
      style="background: linear-gradient(rgba(26,26,26,0.65), rgba(26,26,26,0.9)), url('/assets/img/inicio-2.jpg') center/cover;">
    </div>
    <div class="carousel-bg"
      style="background: linear-gradient(rgba(26,26,26,0.65), rgba(26,26,26,0.9)), url('/assets/img/inicio-3.jpg') center/cover;">
    </div>

    <div class="hero-content">
      <h1
        style="font-family: var(--font-heading); font-size: 3.2rem; text-transform: uppercase; font-weight: 900; margin-bottom: 1rem; color: var(--color-primary); text-shadow: 2px 4px 10px rgba(0,0,0,0.5);">
        Repuestos del Litoral
      </h1>
      <p
        style="font-size: 1.35rem; color: #FFFFFF; margin-bottom: 1rem; font-weight: 700; text-shadow: 1px 2px 5px rgba(0,0,0,0.7);">
        Tu mejor solución en repuestos y reparación de máquinas de jardín y equipos a motor
      </p>
      <p
        style="font-size: 1.1rem; color: #E0E0E0; margin-bottom: 1.5rem; font-weight: 400; text-shadow: 1px 2px 5px rgba(0,0,0,0.5); max-width: 800px; margin-left: auto; margin-right: auto; line-height: 1.6;">
        Ofrecemos repuestos originales y alternativos para motosierras, desmalezadoras, cortacéspedes, tractores de
        jardín, generadores y motobombas. Brindamos atención personalizada, servicio técnico especializado y envíos a
        todo el país para mantener tu máquina siempre en buenas manos desde <strong>Dolores, Soriano (Uruguay)</strong>.
      </p>


      <div style="margin-top: 2rem;">
        <a href="/index.php" class="btn btn-primary"
          style="padding: 1rem 3rem; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(245,130,31,0.4); border-radius: 30px;">
          Explorar Catálogo de Repuestos
        </a>
      </div>
    </div>
  </section>

  <script>
    // Lógica simple para rotar las imágenes del carrusel cada 5 segundos
    document.addEventListener('DOMContentLoaded', () => {
      const backgrounds = document.querySelectorAll('.carousel-bg');
      let currentIndex = 0;

      setInterval(() => {
        backgrounds[currentIndex].classList.remove('active');
        currentIndex = (currentIndex + 1) % backgrounds.length;
        backgrounds[currentIndex].classList.add('active');
      }, 5000);
    });
  </script>


  <!-- Sección de Destacados con las 3 imágenes -->
  <section style="padding: 4rem 1.5rem; background-color: var(--color-light); text-align: center;">
    <div style="max-width: var(--max-width); margin: 0 auto;">
      <h2
        style="font-family: var(--font-heading); font-size: 2rem; color: var(--color-dark); margin-bottom: 3rem; text-transform: uppercase;">
        Especialistas en repuestos y máquinas de jardín — Dolores, Soriano
      </h2>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">

        <!-- Tarjeta 1 -->
        <article
          style="background: white; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-card); transition: var(--transition);">
          <div style="height: 250px; overflow: hidden;">
            <img src="/assets/img/card-1.jpeg" alt="Máquinas de Jardín y Equipos a Motor en Dolores, Soriano"
              loading="lazy" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
              onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
          </div>
          <div style="padding: 1.5rem;">
            <h3
              style="font-family: var(--font-heading); font-size: 1.25rem; color: var(--color-primary); margin-bottom: 0.5rem;">
              Máquinas de Jardín & Equipos a Motor</h3>
            <p style="color: var(--color-text-muted); font-size: 0.95rem;">Repuestos y piezas para motosierras,
              desmalezadoras, cortacéspedes, tractores de jardín, generadores y motobombas.</p>
          </div>
        </article>

        <!-- Tarjeta 2 -->
        <article
          style="background: white; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-card); transition: var(--transition);">
          <div style="height: 250px; overflow: hidden;">
            <img src="/assets/img/card-2.jpg" alt="Repuestos Originales y Alternativos con Envíos a todo el país"
              loading="lazy" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
              onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
          </div>
          <div style="padding: 1.5rem;">
            <h3
              style="font-family: var(--font-heading); font-size: 1.25rem; color: var(--color-primary); margin-bottom: 0.5rem;">
              Repuestos Originales & Alternativos</h3>
            <p style="color: var(--color-text-muted); font-size: 0.95rem;">Amplia variedad de componentes con atención
              personalizada y envíos rápidos a todo el país.</p>
          </div>
        </article>

        <!-- Tarjeta 3 -->
        <article
          style="background: white; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-card); transition: var(--transition);">
          <div style="height: 250px; overflow: hidden;">
            <img src="/assets/img/card-3.jpg" alt="Servicio Técnico Especializado en Dolores" loading="lazy"
              style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
              onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
          </div>
          <div style="padding: 1.5rem;">
            <h3
              style="font-family: var(--font-heading); font-size: 1.25rem; color: var(--color-primary); margin-bottom: 0.5rem;">
              Servicio Técnico Especializado</h3>
            <p style="color: var(--color-text-muted); font-size: 0.95rem;">Mantenimiento integral y reparación experta
              de equipos a motor. Tu máquina en las mejores manos.</p>
          </div>
        </article>

      </div>
    </div>
  </section>

  <?php if (!empty($topBestSellers)): ?>
    <!-- Sección de Productos Más Vendidos -->
    <section style="padding: 4rem 1.5rem; background-color: #F8F9FA; border-top: 1px solid #E2E8F0;">
      <div style="max-width: var(--max-width); margin: 0 auto; text-align: center;">
        <h2
          style="font-family: var(--font-heading); font-size: 2rem; color: var(--color-dark); text-transform: uppercase; margin-bottom: 0.5rem;">
          🔥 Productos Más Vendidos
        </h2>
        <p style="color: var(--color-text-muted); font-size: 1.05rem; margin-bottom: 2.5rem;">
          Los repuestos y herramientas más elegidos por nuestros clientes
        </p>

        <div
          style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; text-align: left;">
          <?php foreach ($topBestSellers as $prod):
            $prodUrl = '/producto/' . rawurlencode($prod['slug']);
            $price = (float) ($prod['display_price'] ?? $prod['retail_price']);
            $imgUrl = !empty($prod['primary_image']) ? $prod['primary_image'] : '/assets/uploads/products/placeholder.jpg';
            ?>
            <article
              style="background: white; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-card); display: flex; flex-direction: column; position: relative; border: 1px solid #EDF2F7; transition: transform 0.25s, box-shadow 0.25s;"
              onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.1)';"
              onmouseout="this.style.transform='none'; this.style.boxShadow='var(--shadow-card)';">

              <!-- Insignia Destacada -->
              <span
                style="position: absolute; top: 12px; left: 12px; background: var(--color-primary); color: white; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; padding: 0.3rem 0.75rem; border-radius: 20px; z-index: 2; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                🔥 Top Venta
              </span>

              <!-- Imagen del Producto (Clickable) -->
              <a href="<?= htmlspecialchars($prodUrl) ?>"
                style="display: block; height: 230px; overflow: hidden; background: #FFF;">
                <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy"
                  style="width: 100%; height: 100%; object-fit: contain; padding: 1rem; transition: transform 0.3s;"
                  onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='scale(1)'">
              </a>

              <!-- Contenido del Producto -->
              <div
                style="padding: 1.25rem; display: flex; flex-direction: column; flex: 1; justify-content: space-between;">
                <div>
                  <?php if (!empty($prod['category_name'])): ?>
                    <span
                      style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-primary); font-weight: 700; letter-spacing: 0.5px; display: block; margin-bottom: 0.3rem;">
                      <?= htmlspecialchars($prod['category_name']) ?>
                    </span>
                  <?php endif; ?>

                  <h3
                    style="font-family: var(--font-heading); font-size: 1.1rem; color: var(--color-dark); margin: 0 0 0.75rem 0; line-height: 1.35;">
                    <a href="<?= htmlspecialchars($prodUrl) ?>"
                      style="color: inherit; text-decoration: none; transition: color 0.2s;"
                      onmouseover="this.style.color='var(--color-primary)'"
                      onmouseout="this.style.color='var(--color-dark)'">
                      <?= htmlspecialchars($prod['name']) ?>
                    </a>
                  </h3>
                </div>

                <div>
                  <div style="font-size: 1.3rem; font-weight: 800; color: var(--color-dark); margin-bottom: 1rem;">
                    UYU $<?= number_format($price, 2) ?>
                  </div>

                  <a href="<?= htmlspecialchars($prodUrl) ?>" class="btn btn-primary"
                    style="width: 100%; box-sizing: border-box; text-align: center; justify-content: center; font-size: 0.9rem; border-radius: var(--radius-sm);">
                    Ver Detalles del Producto
                  </a>
                </div>
              </div>

            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- Sección de Ubicación (Mapa) -->
  <section style="padding: 4rem 1.5rem; background-color: var(--color-dark); color: white; text-align: center;">
    <div style="max-width: var(--max-width); margin: 0 auto;">
      <h2
        style="font-family: var(--font-heading); font-size: 2rem; color: var(--color-primary); margin-bottom: 1rem; text-transform: uppercase;">
        Visítanos en nuestro local en Dolores
      </h2>
      <p style="font-size: 1.1rem; color: #CCCCCC; margin-bottom: 0.5rem;">
        📍 Asencio 1930, Dolores, Departamento de Soriano, Uruguay | Tel. 4534 4109
      </p>
      <p style="font-size: 1rem; color: var(--color-primary); margin-bottom: 1.5rem;">
        🕒 Horarios de atención: Lunes a Viernes de 08:00 a 12:00 y 14:00 a 18:00 hs | Sábados de 08:00 a 12:00 hs
      </p>
      <div style="margin-bottom: 2rem;">
        <a href="https://www.google.com/maps/place/Semiller%C3%ADa+My.Vi.Da/@-33.5279796,-58.248913,13.57z/data=!4m10!1m2!2m1!1sAsencio+1930,+Dolores,+Soriano,+Uruguay!3m6!1s0x95a5291884f9ab0f:0x6aa8bf331873e2d1!8m2!3d-33.5352914!4d-58.2143246!15sCidBc2VuY2lvIDE5MzAsIERvbG9yZXMsIFNvcmlhbm8sIFVydWd1YXlaJiIkYXNlbmNpbyAxOTMwIGRvbG9yZXMgc29yaWFubyB1cnVndWF5kgEOaGFyZHdhcmVfc3RvcmXgAQA!16s%2Fg%2F11sg5q09v5?hl=en-US&entry=ttu&g_ep=EgoyMDI2MDgxMi4wIKXMDSoASAFQAw%3D%3D"
          target="_blank" rel="noopener noreferrer"
          style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.2rem; background-color: var(--color-primary); color: #000; font-weight: bold; border-radius: var(--radius-sm); text-decoration: none;">
          🗺️ Ver en Google Maps
        </a>
      </div>

      <div style="border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-card); height: 450px;">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3319.4678!2d-58.2143246!3d-33.5352914!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x95a5291884f9ab0f%3A0x6aa8bf331873e2d1!2sSemiller%C3%ADa%20My.Vi.Da!5e0!3m2!1ses!2suy"
          width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    </div>
  </section>

  <!-- Botón Flotante de WhatsApp -->
  <?php require_once __DIR__ . '/includes/whatsapp-button.php'; ?>

  <!-- Pie de página compartido -->
  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <!-- Script Principal de la Aplicación -->
  <script type="module" src="/app.js?v=1.0.3"></script>
</body>

</html>