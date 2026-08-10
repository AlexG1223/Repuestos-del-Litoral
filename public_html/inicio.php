<?php
declare(strict_types=1);

require_once __DIR__ . '/../private/config/settings.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inicio | Repuestos del Litoral</title>
  <meta name="description"
    content="Venta minorista y mayorista de repuestos originales, motosierras, desmalezadoras, cadenas, lubricantes y accesorios. Envíos a todo Uruguay.">
  <link rel="icon" href="/assets/img/logo.png" type="image/x-icon">

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
      max-width: 800px;
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
        style="font-family: var(--font-heading); font-size: 3.5rem; text-transform: uppercase; font-weight: 900; margin-bottom: 1rem; color: var(--color-primary); text-shadow: 2px 4px 10px rgba(0,0,0,0.5);">
        Repuestos del Litoral
      </h1>
      <p
        style="font-size: 1.25rem; color: #E0E0E0; margin-bottom: 2.5rem; font-weight: 500; text-shadow: 1px 2px 5px rgba(0,0,0,0.5);">
        Especialistas en repuestos y servicios de reparación para motosierras, desmalezadoras y maquinaria de jardín.
        Venta minorista y
        mayorista.
      </p>
      <a href="/index.php" class="btn btn-primary"
        style="padding: 1rem 3rem; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(245,130,31,0.4); border-radius: 30px;">
        Ver Catálogo de Productos
      </a>
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
        Todo lo que necesitas en un solo lugar
      </h2>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">

        <!-- Tarjeta 1 -->
        <div
          style="background: white; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-card); transition: var(--transition);">
          <div style="height: 250px; overflow: hidden;">
            <img src="/assets/img/card-1.jpg" alt="Herramientas y Accesorios"
              style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
              onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
          </div>
          <div style="padding: 1.5rem;">
            <h3
              style="font-family: var(--font-heading); font-size: 1.25rem; color: var(--color-primary); margin-bottom: 0.5rem;">
              Amplio Stock de Repuestos</h3>
            <p style="color: var(--color-text-muted); font-size: 0.95rem;">Encuentra piezas originales y genéricas de
              alta calidad para mantener tus equipos siempre funcionando.</p>
          </div>
        </div>

        <!-- Tarjeta 2 -->
        <div
          style="background: white; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-card); transition: var(--transition);">
          <div style="height: 250px; overflow: hidden;">
            <img src="/assets/img/card-2.jpg" alt="Herramientas Manuales"
              style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
              onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
          </div>
          <div style="padding: 1.5rem;">
            <h3
              style="font-family: var(--font-heading); font-size: 1.25rem; color: var(--color-primary); margin-bottom: 0.5rem;">
              Herramientas Manuales</h3>
            <p style="color: var(--color-text-muted); font-size: 0.95rem;">Contamos con herramientas de precisión y
              durabilidad para todo tipo de reparaciones y ajustes.</p>
          </div>
        </div>

        <!-- Tarjeta 3 -->
        <div
          style="background: white; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-card); transition: var(--transition);">
          <div style="height: 250px; overflow: hidden;">
            <img src="/assets/img/card-3.jpg" alt="Maquinaria de Jardín"
              style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
              onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
          </div>
          <div style="padding: 1.5rem;">
            <h3
              style="font-family: var(--font-heading); font-size: 1.25rem; color: var(--color-primary); margin-bottom: 0.5rem;">
              Maquinaria y Equipos</h3>
            <p style="color: var(--color-text-muted); font-size: 0.95rem;">Desde desmalezadoras hasta motosierras,
              descubre nuestra línea completa de maquinaria para profesionales.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- Sección de Ubicación (Mapa) -->
  <section style="padding: 4rem 1.5rem; background-color: var(--color-dark); color: white; text-align: center;">
    <div style="max-width: var(--max-width); margin: 0 auto;">
      <h2
        style="font-family: var(--font-heading); font-size: 2rem; color: var(--color-primary); margin-bottom: 1rem; text-transform: uppercase;">
        Visítanos en nuestro local
      </h2>
      <p style="font-size: 1.1rem; color: #CCCCCC; margin-bottom: 3rem;">
        📍 Asencio 1930, Dolores, Soriano
      </p>

      <div style="border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-card); height: 450px;">
        <iframe
          src="https://maps.google.com/maps?q=Asencio+1930,+Dolores,+Soriano,+Uruguay&t=&z=16&ie=UTF8&iwloc=&output=embed"
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
  <script type="module" src="/app.js"></script>
</body>

</html>