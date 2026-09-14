<?php
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Repuestos del Litoral');
}

// Inyección de snippet GTM (noscript) al inicio de <body>
require_once __DIR__ . '/gtm-body.php';
?>
<header class="site-header">
  <div class="header-container">
    <a href="/inicio.php" class="brand-logo">
      <img src="/assets/img/logo.png" alt="Logo <?php echo htmlspecialchars(SITE_NAME); ?>" onerror="this.onerror=null; this.src='/assets/uploads/products/placeholder.jpg';">
      <div class="brand-title">
        Repuestos <span>del Litoral</span>
      </div>
    </a>

    <!-- Acciones en Header (Carrito + Botón Menú Móvil) -->
    <div class="header-actions">
      <!-- Botón de Carrito -->
      <button type="button" class="header-cart-btn" id="btn-header-cart" title="Ver Carrito de Compras">
        <svg class="cart-icon-svg" viewBox="0 0 24 24">
          <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
        </svg>
        <span class="cart-btn-label">Carrito</span>
        <span class="cart-badge badge-empty" id="cart-badge-count">0</span>
      </button>

      <!-- Botón de Menú Hamburguesa Móvil -->
      <button type="button" class="mobile-menu-toggle" id="btn-mobile-menu" aria-label="Abrir menú de navegación" aria-expanded="false">
        <svg class="hamburger-icon" viewBox="0 0 24 24" width="26" height="26">
          <path class="icon-open" fill="currentColor" d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
          <path class="icon-close" fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
        </svg>
      </button>
    </div>

    <!-- Menú de Navegación Principal / Desplegable en Móviles -->
    <?php 
      $currentScript = $_SERVER['SCRIPT_NAME'] ?? '';
      $isInicio = ($currentScript === '/inicio.php' || $currentScript === '/');
    ?>
    <nav class="main-nav" id="main-nav">
      <ul>
        <li><a href="/inicio.php" class="<?php echo $isInicio ? 'active' : ''; ?>">Inicio</a></li>
        <li><a href="/index.php" class="<?php echo $currentScript === '/index.php' ? 'active' : ''; ?>">Tienda</a></li>
        <li><a href="/faq.php" class="<?php echo $currentScript === '/faq.php' ? 'active' : ''; ?>">Preguntas Frecuentes</a></li>
        <li><a href="https://wa.me/59899655283" target="_blank" rel="noopener">Contacto Directo</a></li>

        <!-- Estado de Autenticación de Usuario (Login / Registro / Mi Cuenta) -->
        <li id="header-auth-root"></li>
      </ul>
    </nav>
  </div>
</header>

<!-- Contenedor donde se inyecta dinámicamente el Drawer del Carrito -->
<div id="cart-drawer-root"></div>
