<?php
require_once __DIR__ . '/../../private/services/SessionService.php';
require_once __DIR__ . '/../../private/services/AdminGuard.php';

\RepuestosDelLitoral\Services\AdminGuard::requirePage();

$pageTitle = "Dashboard Admin - Repuestos del Litoral";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  
  <link rel="stylesheet" href="/globals/main.css">
  <link rel="stylesheet" href="/modules/Admin/shared/styles/admin-layout.css">
  
  <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
</head>
<body class="admin-body">
  
  <!-- Contenedor del Sidebar -->
  <div id="admin-sidebar-root"></div>

  <!-- Contenido Principal -->
  <main class="admin-main">
    <header class="admin-header">
      <h1>Panel de Administración</h1>
    </header>

    <div class="admin-content">
      <div class="admin-card">
        <h2>Bienvenido, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin') ?></h2>
        <p>Selecciona una opción del menú lateral para comenzar a gestionar el sitio.</p>
        <p>Desde aquí podrás dar de alta, editar y eliminar <b>productos</b>, así como revisar las solicitudes de <b>clientes mayoristas</b> y crear nuevos usuarios de forma manual.</p>
      </div>
    </div>
  </main>

  <script type="module">
    import { renderAdminSidebar } from '/modules/Admin/shared/components/AdminSidebar.js';
    renderAdminSidebar(window.location.pathname);
  </script>
</body>
</html>
