/**
 * Componente que renderiza el Sidebar de Administración.
 */
export function renderAdminSidebar(currentPath) {
  const root = document.getElementById('admin-sidebar-root');
  if (!root) return;

  root.innerHTML = `
    <!-- Botón hamburguesa móvil -->
    <button id="admin-sidebar-toggle" class="admin-sidebar-toggle">☰</button>
    
    <!-- Overlay oscuro para cerrar en móvil -->
    <div id="admin-sidebar-overlay" class="admin-sidebar-overlay"></div>

    <aside class="admin-sidebar" id="admin-sidebar">
      <div class="admin-sidebar-brand">
        Admin
      </div>
      <nav class="admin-nav">
        <a href="/admin/index.php" class="${currentPath === '/admin/index.php' || currentPath === '/admin/' ? 'active' : ''}">Dashboard</a>
        <a href="/admin/pedidos.php" class="${currentPath.includes('pedido') ? 'active' : ''}">📦 Pedidos</a>
        <a href="/admin/productos.php" class="${currentPath.includes('producto') ? 'active' : ''}">Productos</a>
        <a href="/admin/categorias.php" class="${currentPath.includes('categoria') ? 'active' : ''}">📁 Categorías</a>
        <a href="/admin/importar.php" class="${currentPath.includes('importar') ? 'active' : ''}">Importar Catálogo</a>
        <a href="/admin/clientes.php" class="${currentPath.includes('clientes') ? 'active' : ''}">Mayoristas</a>
        <a href="/admin/configuracion.php" class="${currentPath.includes('configuracion') ? 'active' : ''}">Configuración</a>
      </nav>
      <div class="admin-nav-footer">
        <a href="/index.php">← Volver al sitio web</a>
      </div>
    </aside>
  `;

  // Lógica para toggle en móvil
  const toggleBtn = document.getElementById('admin-sidebar-toggle');
  const sidebar = document.getElementById('admin-sidebar');
  const overlay = document.getElementById('admin-sidebar-overlay');

  if (toggleBtn && sidebar && overlay) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      overlay.classList.toggle('show');
    });

    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  renderAdminSidebar(window.location.pathname);
});
