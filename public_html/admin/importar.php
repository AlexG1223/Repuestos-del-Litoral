<?php
require_once __DIR__ . '/../../private/services/SessionService.php';
require_once __DIR__ . '/../../private/services/AdminGuard.php';
require_once __DIR__ . '/../../private/config/database.php';
require_once __DIR__ . '/../../private/models/Category.php';

\RepuestosDelLitoral\Services\AdminGuard::requirePage();

$pageTitle = "Importación de Catálogo - Repuestos del Litoral";
$categories = \RepuestosDelLitoral\Models\Category::all();
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
  <link rel="stylesheet" href="/modules/Admin/Import/styles/import.css">
  
  <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
</head>
<body class="admin-body">
  
  <div id="admin-sidebar-root"></div>

  <main class="admin-main">
    <header class="admin-header">
      <h1>Importar Catálogo (PDF)</h1>
    </header>

    <div class="admin-content">
        <div id="import-root" data-categories='<?= htmlspecialchars(json_encode($categories), ENT_QUOTES, 'UTF-8') ?>'>
            <!-- Acá se monta la aplicación JS de importación -->
            <p>Cargando herramienta de importación...</p>
        </div>
    </div>
  </main>

  <script type="module">
    import { renderAdminSidebar } from '/modules/Admin/shared/components/AdminSidebar.js';
    import { initImportApp } from '/modules/Admin/Import/hooks/useImport.js';
    
    document.addEventListener('DOMContentLoaded', () => {
      renderAdminSidebar(window.location.pathname);
      initImportApp('import-root');
    });
  </script>
</body>
</html>
