<?php
require_once __DIR__ . '/../../private/services/SessionService.php';
require_once __DIR__ . '/../../private/services/AdminGuard.php';
require_once __DIR__ . '/../../private/config/database.php';
require_once __DIR__ . '/../../private/models/Product.php';
require_once __DIR__ . '/../../private/models/ProductImage.php';

\RepuestosDelLitoral\Services\AdminGuard::requirePage();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$productData = null;

if ($id > 0) {
    $productData = \RepuestosDelLitoral\Models\Product::findByIdForAdmin($id);
    if (!$productData) {
        die("Producto no encontrado.");
    }
}

$pageTitle = ($id > 0 ? "Editar Producto" : "Nuevo Producto") . " - Repuestos del Litoral";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  
  <link rel="stylesheet" href="/globals/main.css">
  <link rel="stylesheet" href="/modules/Admin/shared/styles/admin-layout.css">
  <link rel="stylesheet" href="/modules/Admin/Products/styles/admin-products.css">
  
  <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
</head>
<body class="admin-body">
  
  <div id="admin-sidebar-root"></div>

  <main class="admin-main">
    <header class="admin-header">
      <div style="display: flex; gap: 1rem; align-items: center;">
        <a href="/admin/productos.php" class="admin-btn secondary">← Volver</a>
        <h1><?= $id > 0 ? "Editar Producto: " . htmlspecialchars($productData['name']) : "Crear Nuevo Producto" ?></h1>
      </div>
    </header>

    <div class="admin-content">
      <div id="admin-product-form-root"></div>
    </div>
  </main>

  <script>
    window.INITIAL_PRODUCT_DATA = <?= $productData ? json_encode($productData, JSON_UNESCAPED_UNICODE) : 'null' ?>;
    window.PRODUCT_ID = <?= $id ?>;
  </script>

  <script type="module">
    import { renderAdminSidebar } from '/modules/Admin/shared/components/AdminSidebar.js';
    import { renderProductForm } from '/modules/Admin/Products/components/ProductForm.js';
    
    document.addEventListener('DOMContentLoaded', () => {
      renderAdminSidebar(window.location.pathname);
      renderProductForm('admin-product-form-root', window.PRODUCT_ID, window.INITIAL_PRODUCT_DATA);
    });
  </script>
</body>
</html>
