/**
 * Componente que renderiza el estado de la cuenta en el header de navegación.
 * @param {Object|null} user 
 * @returns {string} HTML String
 */
export function AccountStatus(user = null) {
  if (!user) {
    return `
      <div class="header-auth-links">
        <a href="/login.php" class="auth-link-btn">Iniciar Sesión</a>
        <a href="/registro.php" class="auth-link-btn auth-link-primary">Registrarme</a>
      </div>
    `;
  }

  const isWholesalePending = user.role === 'wholesale' && user.approved !== 1;
  const isWholesaleApproved = user.role === 'wholesale' && user.approved === 1;

  const roleBadge = isWholesaleApproved 
    ? '<span class="header-role-badge badge-wholesale">Mayorista</span>' 
    : (isWholesalePending ? '<span class="header-role-badge badge-pending" title="Tu cuenta mayorista está en revisión por un administrador. Mientras tanto ves precios minoristas.">Mayorista (En revisión)</span>' : '');

  return `
    <div class="header-user-menu">
      <div class="user-greeting">
        <span class="greeting-text">Hola, <strong>${user.name.split(' ')[0]}</strong></span>
        ${roleBadge}
      </div>
      <div class="user-actions">
        <a href="/mi-cuenta.php" class="nav-user-link">Mi Cuenta</a>
        <button type="button" id="btn-header-logout" class="btn-logout-link">Salir</button>
      </div>
    </div>
  `;
}
