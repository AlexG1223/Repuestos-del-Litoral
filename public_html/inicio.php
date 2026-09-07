<?php
declare(strict_types=1);

require_once __DIR__ . '/../private/config/settings.php';
require_once __DIR__ . '/../private/services/SeoService.php';

use RepuestosDelLitoral\Services\SeoService;

$baseUrl = SeoService::getBaseUrl();
$localSchema = SeoService::getLocalBusinessSchema();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Repuestos del Litoral | Repuestos de Maquinaria, Ferretería, Calzado, Mates y Pesca en Dolores, Soriano</title>
  <meta name="description"
    content="Venta minorista y mayorista de repuestos para maquinaria agrícola e industrial, artículos de ferretería, calzado de trabajo, mates, artículos de pesca y productos para mascotas. Ubicados en Asencio 1930, Dolores, Soriano, Uruguay. Envíos a todo el país.">
  <link rel="canonical" href="<?= htmlspecialchars($baseUrl . '/inicio.php') ?>">
  <link rel="icon" href="/assets/img/logo.png" type="image/x-icon">

  <!-- Open Graph / Redes Sociales -->
  <meta property="og:title" content="Repuestos del Litoral | Repuestos, Ferretería, Calzado y Mas en Dolores, Soriano">
  <meta property="og:description" content="Repuestos de maquinaria agrícola/industrial, ferretería, calzado de trabajo, mates, artículos de pesca y mascotas en Dolores, Soriano, Uruguay.">
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
      overflow: hidden;
    }

    .carousel-bg {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      opacity: 0;
      transition: opacity 1.5s ease-in-out, transform 8s linear;
      transform: scale(1.05);
      z-index: 1;
    }

    .carousel-bg.active {
      opacity: 1;
      transform: scale(1);
    }

    .hero-content {
      position: relative;
      z-index: 2;
      max-width: 850px;
      margin: 0 auto;
      animation: fadeInUp 1.2s ease-out forwards;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(40px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .category-pills {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 0.75rem;
      margin-top: 1.5rem;
    }

    .category-pill {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(5px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: white;
      padding: 0.4rem 1rem;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 600;
    }
  </style>
</head>

<body>

  <!-- Cabecera compartida -->
  <?php require_once __DIR__ . '/includes/header.php'; ?>

  <!-- Banner Héroe / Presentación Animado -->
  <section class="hero-banner" id="hero-carousel">

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
        style="font-size: 1.25rem; color: #E0E0E0; margin-bottom: 1.5rem; font-weight: 500; text-shadow: 1px 2px 5px rgba(0,0,0,0.5);">
        Tu comercio integral en <strong>Dolores, Soriano (Uruguay)</strong>. Especialistas en repuestos de maquinaria agrícola e industrial, herramientas de ferretería, calzado de trabajo, mates, artículos de pesca y mascotas. Venta minorista y mayorista.
      </p>

      <div class="category-pills">
        <span class="category-pill">🚜 Repuestos Agrícolas</span>
        <span class="category-pill">⚙️ Ferretería Industrial</span>
        <span class="category-pill">🥾 Calzado de Trabajo</span>
        <span class="category-pill">🧉 Mates & Regionales</span>
        <span class="category-pill">🎣 Pesca & Camping</span>
        <span class="category-pill">🐾 Mascotas</span>
      </div>

      <div style="margin-top: 2rem;">
        <a href="/index.php" class="btn btn-primary"
          style="padding: 1rem 3rem; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(245,130,31,0.4); border-radius: 30px;">
          Explorar Catálogo Completo
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
        Todo lo que necesitas en un solo lugar — Dolores, Soriano
      </h2>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">

        <!-- Tarjeta 1 -->
        <article
          style="background: white; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-card); transition: var(--transition);">
          <div style="height: 250px; overflow: hidden;">
            <img src="/assets/img/card-1.jpg" alt="Repuestos para Maquinaria Agrícola e Industrial en Dolores, Soriano"
              loading="lazy"
              style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
              onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
          </div>
          <div style="padding: 1.5rem;">
            <h3
              style="font-family: var(--font-heading); font-size: 1.25rem; color: var(--color-primary); margin-bottom: 0.5rem;">
              Repuestos de Maquinaria</h3>
            <p style="color: var(--color-text-muted); font-size: 0.95rem;">Piezas originales y genéricas para tractores, motosierras, desmalezadoras y motores agrícolas en Soriano y el Litoral.</p>
          </div>
        </article>

        <!-- Tarjeta 2 -->
        <article
          style="background: white; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-card); transition: var(--transition);">
          <div style="height: 250px; overflow: hidden;">
            <img src="/assets/img/card-2.jpg" alt="Ferretería y Calzado de Trabajo en Dolores Uruguay"
              loading="lazy"
              style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
              onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
          </div>
          <div style="padding: 1.5rem;">
            <h3
              style="font-family: var(--font-heading); font-size: 1.25rem; color: var(--color-primary); margin-bottom: 0.5rem;">
              Ferretería & Calzado de Trabajo</h3>
            <p style="color: var(--color-text-muted); font-size: 0.95rem;">Herramientas manuales, insumos de taller y calzado de seguridad resistente para el trabajo rural y urbano.</p>
          </div>
        </article>

        <!-- Tarjeta 3 -->
        <article
          style="background: white; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-card); transition: var(--transition);">
          <div style="height: 250px; overflow: hidden;">
            <img src="/assets/img/card-3.jpg" alt="Mates, Artículos de Pesca y Mascotas en Dolores"
              loading="lazy"
              style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
              onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
          </div>
          <div style="padding: 1.5rem;">
            <h3
              style="font-family: var(--font-heading); font-size: 1.25rem; color: var(--color-primary); margin-bottom: 0.5rem;">
              Mates, Pesca & Mascotas</h3>
            <p style="color: var(--color-text-muted); font-size: 0.95rem;">Equipamiento de pesca para el Río San Salvador, mates criollos, termos y productos para el cuidado de tus mascotas.</p>
          </div>
        </article>

      </div>
    </div>
  </section>

  <!-- Sección de Ubicación (Mapa) -->
  <section style="padding: 4rem 1.5rem; background-color: var(--color-dark); color: white; text-align: center;">
    <div style="max-width: var(--max-width); margin: 0 auto;">
      <h2
        style="font-family: var(--font-heading); font-size: 2rem; color: var(--color-primary); margin-bottom: 1rem; text-transform: uppercase;">
        Visítanos en nuestro local en Dolores
      </h2>
      <p style="font-size: 1.1rem; color: #CCCCCC; margin-bottom: 1rem;">
        📍 Asencio 1930, Dolores, Departamento de Soriano, Uruguay | Tel. 4534 4109
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