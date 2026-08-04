/**
 * Componente que renderiza el formulario de Inicio de Sesión.
 * @param {Object} formData 
 * @param {string|null} error 
 * @param {boolean} isSubmitting 
 * @returns {string} HTML String
 */
export function LoginForm(formData = {}, error = null, isSubmitting = false) {
  const errorHtml = error ? `<div class="alert-error" style="margin-bottom:1.25rem;">⚠️ ${error}</div>` : '';

  return `
    <form id="login-form" class="auth-form-box" novalidate>
      <h2 class="auth-title">🔑 Iniciar Sesión</h2>
      <p class="auth-subtitle">Ingresa tu cuenta para acceder a tus pedidos y precios especiales.</p>

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
        <span>¿No tienes una cuenta aún?</span>
        <a href="/registro.php">Crear cuenta nueva</a>
      </div>
    </form>
  `;
}
