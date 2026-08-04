<?php
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Repuestos del Litoral');
}
?>
<header class="site-header">
  <div class="header-container">
    <a href="/index.php" class="brand-logo">
      <img src="/assets/img/logo.png" alt="Logo <?php echo htmlspecialchars(SITE_NAME); ?>" onerror="this.onerror=null; this.src='/assets/uploads/products/placeholder.jpg';">
      <div class="brand-title">
        Repuestos <span>del Litoral</span>
      </div>
    </a>

    <nav class="main-nav">
      <ul>
        <li><a href="/index.php" class="<?php echo $_SERVER['SCRIPT_NAME'] === '/index.php' ? 'active' : ''; ?>">Inicio</a></li>
        <li><a href="/index.php#catalogo" class="nav-catalog-link">Catálogo</a></li>
        <li><a href="https://wa.me/59899655283" target="_blank" rel="noopener">Contacto Directo</a></li>
      </ul>
    </nav>
  </div>
</header>
