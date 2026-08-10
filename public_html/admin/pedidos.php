<?php
require_once __DIR__ . '/../../private/services/SessionService.php';
require_once __DIR__ . '/../../private/services/AdminGuard.php';

\RepuestosDelLitoral\Services\AdminGuard::requirePage();

$pageTitle = "Pedidos - Admin Repuestos del Litoral";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  
  <link rel="stylesheet" href="/globals/main.css">
  <link rel="stylesheet" href="/modules/Admin/shared/styles/admin-layout.css">
  <link rel="stylesheet" href="/modules/Admin/Orders/styles/orders.css">
  
  <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
</head>
<body class="admin-body">
  
  <div id="admin-sidebar-root"></div>

  <main class="admin-main">
    <header class="admin-header">
      <h1>📦 Gestión de Pedidos</h1>
    </header>

    <div class="admin-content" id="orders-root">
        <div style="padding: 2rem; text-align: center;">Cargando pedidos...</div>
    </div>
  </main>

  <script type="module">
    import { renderAdminSidebar } from '/modules/Admin/shared/components/AdminSidebar.js';
    import { useOrders } from '/modules/Admin/Orders/hooks/useOrders.js';
    
    renderAdminSidebar(window.location.pathname);
    
    const ordersApp = useOrders();
    ordersApp.init();
  </script>
</body>
</html>
