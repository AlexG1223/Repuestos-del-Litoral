<?php
require_once __DIR__ . '/../../private/services/SessionService.php';
require_once __DIR__ . '/../../private/services/AdminGuard.php';

\RepuestosDelLitoral\Services\AdminGuard::requirePage();

$pageTitle = "Gestión de Mayoristas - Repuestos del Litoral";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  
  <link rel="icon" href="/assets/img/logo.png" type="image/x-icon">
  <link rel="shortcut icon" href="/assets/img/logo.png" type="image/x-icon">
  <link rel="stylesheet" href="/globals/main.css">
  <link rel="stylesheet" href="/modules/Admin/shared/styles/admin-layout.css">
  <link rel="stylesheet" href="/modules/Admin/Wholesale/styles/admin-wholesale.css">
  
  <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
</head>
<body class="admin-body">
  
  <div id="admin-sidebar-root"></div>

  <main class="admin-main">
    <header class="admin-header">
      <h1>Clientes Mayoristas</h1>
    </header>

    <div class="admin-content">
      
      <!-- Alta manual de mayorista -->
      <div style="margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1rem;">Alta Manual de Mayorista</h2>
        <div id="admin-wholesale-form-root"></div>
      </div>

      <!-- Listado de clientes -->
      <div class="admin-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
          <h2 style="margin: 0;">Directorio de Clientes</h2>
          <select id="admin-filter-wholesale" class="admin-form-control" style="width: auto;">
            <option value="all">Todos</option>
            <option value="pending">Pendientes de Aprobación</option>
            <option value="approved">Aprobados</option>
          </select>
        </div>

        <div id="admin-wholesale-table-root"></div>
      </div>

    </div>
  </main>

  <script type="module">
    import { renderAdminSidebar } from '/modules/Admin/shared/components/AdminSidebar.js';
    import { useAdminWholesale } from '/modules/Admin/Wholesale/hooks/useAdminWholesale.js';
    import { renderWholesaleForm } from '/modules/Admin/Wholesale/components/WholesaleForm.js';
    
    document.addEventListener('DOMContentLoaded', () => {
      renderAdminSidebar(window.location.pathname);
      const { initList, loadClients } = useAdminWholesale();
      initList();

      renderWholesaleForm('admin-wholesale-form-root', () => {
        loadClients(); // Recargar lista al crear
      });
    });
  </script>
</body>
</html>
