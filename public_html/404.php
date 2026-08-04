<?php
declare(strict_types=1);

http_response_code(404);
require_once __DIR__ . '/../private/config/settings.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Página No Encontrada (404) | Repuestos del Litoral</title>
  <link rel="icon" href="/assets/img/logo.png" type="image/x-icon">
  <link rel="stylesheet" href="/globals/main.css">
</head>

<body>

  <?php require_once __DIR__ . '/includes/header.php'; ?>

  <main class="main-content">
    <div
      style="background-color: white; padding: 4rem 2rem; text-align: center; border-radius: var(--radius-md); box-shadow: var(--shadow-card); margin: 2rem 0;">
      <h1 style="font-size: 5rem; color: var(--color-primary); font-family: var(--font-heading); margin-bottom: 0;">404
      </h1>
      <h2 style="font-family: var(--font-heading); font-size: 1.8rem; margin-bottom: 1rem; color: var(--color-dark);">
        Página No Encontrada</h2>
      <p style="color: var(--color-text-muted); max-width: 500px; margin: 0 auto 2rem;">
        La página o el producto que estás buscando no existe, ha sido movido o cambió de dirección.
      </p>
      <a href="/index.php#catalogo" class="btn btn-primary">Volver al Catálogo de Productos</a>
    </div>
  </main>

  <?php require_once __DIR__ . '/includes/whatsapp-button.php'; ?>
  <?php require_once __DIR__ . '/includes/footer.php'; ?>

</body>

</html>