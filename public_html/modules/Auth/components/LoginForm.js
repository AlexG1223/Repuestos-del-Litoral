/**
 * Componente que renderiza el formulario de Inicio de Sesión (para Mayoristas/Admin).
 * @param {Object} formData 
 * @param {string|null} error 
 * @param {boolean} isSubmitting 
 * @returns {string} HTML String
 */
export function LoginForm(formData = {}, error = null, isSubmitting = false) {
  const errorHtml = error ? `<div class="alert-error" style="margin-bottom:1.25rem;">⚠️ ${error}</div>` : '';

  return `
    <form id="login-form" class="auth-form-box" novalidate>
      
      <!-- Cartel Informativo para Compras Particulares -->
      <div class="auth-retail-notice">
        <p class="retail-notice-main" style="margin: 0; font-size: 0.85rem;">
          💡 <strong>¿Deseas comprar como cliente particular?</strong><br>
          No necesitas iniciar sesión. Ve directamente al <a href="/index.php" style="color:var(--color-primary); font-weight:bold;">Catálogo de la Tienda</a> y agrega tus productos al carrito.
        </p>
      </div>

      <h2 class="auth-title" style="margin-top: 0.5rem;">🔑 Acceso a Cuenta</h2>
      <p class="auth-subtitle">Ingresa con tu correo y contraseña para acceder a tarifas mayoristas y panel comercial.</p>

      ${errorHtml}

      <div class="form-group">
        <label for="login-email">Correo Electrónico</label>
        <input 
          type="email" 
          id="login-email" 
          name="email" 
          value="${formData.email || ''}" 
          placeholder="tu@email.com" 
          required 
          autocomplete="email"
        />
      </div>

      <div class="form-group">
        <label for="login-password">Contraseña</label>
        <input 
          type="password" 
          id="login-password" 
          name="password" 
          placeholder="••••••••" 
          required 
          autocomplete="current-password"
        />
      </div>

      <button type="submit" class="btn btn-primary btn-block" style="margin-top:1rem;" ${isSubmitting ? 'disabled' : ''}>
        ${isSubmitting ? 'Ingresando...' : 'Iniciar Sesión'}
      </button>

      <div class="auth-footer-links">
        <span>¿Eres taller, comercio o revendedor y no tienes cuenta?</span>
        <a href="/registro.php">Solicitar cuenta mayorista</a>
      </div>
    </form>
  `;
}
