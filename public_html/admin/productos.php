<?php
require_once __DIR__ . '/../../private/services/SessionService.php';
require_once __DIR__ . '/../../private/services/AdminGuard.php';
require_once __DIR__ . '/../../private/config/database.php';
require_once __DIR__ . '/../../private/models/Category.php';

\RepuestosDelLitoral\Services\AdminGuard::requirePage();

$categories = \RepuestosDelLitoral\Models\Category::all();
$pageTitle = "Gestión de Productos - Repuestos del Litoral";
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
  <link rel="stylesheet" href="/modules/Admin/Products/styles/admin-products.css">
  
  <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
</head>
<body class="admin-body">
  
  <div id="admin-sidebar-root"></div>

  <main class="admin-main">
    <header class="admin-header">
      <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
        <h1>Productos</h1>
        <a href="/admin/producto-editar.php" class="admin-btn"> + Nuevo Producto</a>
      </div>
    </header>

    <div class="admin-content">
      <div class="admin-card">
        <div class="admin-filter-bar">
          <input type="text" id="admin-product-search" class="admin-form-control" placeholder="Buscar por código o nombre...">
          <select id="admin-filter-category" class="admin-form-control" style="width: auto;">
            <option value="">Todas las Categorías</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <select id="admin-filter-status" class="admin-form-control" style="width: auto;">
            <option value="">Todos los Estados</option>
            <option value="1">Activos</option>
            <option value="0">Inactivos</option>
          </select>
        </div>

        <div id="admin-product-table-root"></div>
      </div>
    </div>
  </main>

  <script type="module">
    import { renderAdminSidebar } from '/modules/Admin/shared/components/AdminSidebar.js';
    import { useAdminProducts } from '/modules/Admin/Products/hooks/useAdminProducts.js';
    
    document.addEventListener('DOMContentLoaded', () => {
      renderAdminSidebar(window.location.pathname);
      const { initList } = useAdminProducts();
      initList();
    });
  </script>
</body>
</html>
