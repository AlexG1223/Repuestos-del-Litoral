/**
 * Componente que renderiza el formulario de Registro de Usuario.
 * @param {Object} formData 
 * @param {Object} errors 
 * @param {boolean} isSubmitting 
 * @returns {string} HTML String
 */
export function RegisterForm(formData = {}, errors = {}, isSubmitting = false) {
  const nameErr = errors.name ? `<span class="field-error">${errors.name}</span>` : '';
  const emailErr = errors.email ? `<span class="field-error">${errors.email}</span>` : '';
  const phoneErr = errors.phone ? `<span class="field-error">${errors.phone}</span>` : '';
  const passErr = errors.password ? `<span class="field-error">${errors.password}</span>` : '';
  const confirmErr = errors.confirmPassword ? `<span class="field-error">${errors.confirmPassword}</span>` : '';
  const businessErr = errors.businessName ? `<span class="field-error">${errors.businessName}</span>` : '';
  const genErr = errors.general ? `<div class="alert-error" style="margin-bottom:1.25rem;">⚠️ ${errors.general}</div>` : '';

  const wantsWholesale = !!formData.wantsWholesale;

  return `
    <form id="register-form" class="auth-form-box" novalidate>
      <h2 class="auth-title">📝 Crear Cuenta Nueva</h2>
      <p class="auth-subtitle">Regístrate para acelerar tus compras y acceder a beneficios comerciales.</p>

      ${genErr}

      <div class="form-group ${errors.name ? 'has-error' : ''}">
        <label for="reg-name">Nombre y Apellido <span class="required">*</span></label>
        <input 
          type="text" 
          id="reg-name" 
          name="name" 
          value="${formData.name || ''}" 
          placeholder="Ej. Carlos Rodríguez" 
          required 
        />
        ${nameErr}
      </div>

      <div class="form-group ${errors.email ? 'has-error' : ''}">
        <label for="reg-email">Correo Electrónico <span class="required">*</span></label>
        <input 
          type="email" 
          id="reg-email" 
          name="email" 
          value="${formData.email || ''}" 
          placeholder="tu@email.com" 
          required 
        />
        ${emailErr}
      </div>

      <div class="form-group ${errors.phone ? 'has-error' : ''}">
        <label for="reg-phone">Teléfono / WhatsApp <span class="required">*</span></label>
        <input 
          type="tel" 
          id="reg-phone" 
          name="phone" 
          value="${formData.phone || ''}" 
          placeholder="Ej. 099 123 456" 
          required 
        />
        ${phoneErr}
      </div>

      <div class="form-group ${errors.password ? 'has-error' : ''}">
        <label for="reg-password">Contraseña <span class="required">* (mínimo 8 caracteres)</span></label>
        <input 
          type="password" 
          id="reg-password" 
          name="password" 
          placeholder="••••••••" 
          required 
        />
        ${passErr}
      </div>

      <div class="form-group ${errors.confirmPassword ? 'has-error' : ''}">
        <label for="reg-confirm">Confirmar Contraseña <span class="required">*</span></label>
        <input 
          type="password" 
          id="reg-confirm" 
          name="confirmPassword" 
          placeholder="••••••••" 
          required 
        />
        ${confirmErr}
      </div>

      <!-- Opción de Cuenta Mayorista -->
      <div class="form-checkbox-group">
        <label class="checkbox-label">
          <input 
            type="checkbox" 
            id="reg-wants-wholesale" 
            name="wantsWholesale" 
            ${wantsWholesale ? 'checked' : ''}
          />
          <span>Solicitar cuenta de Cliente Mayorista (requiere aprobación manual)</span>
        </label>
      </div>

      <!-- Campo condicional de Nombre del Negocio -->
      <div class="form-group ${errors.businessName ? 'has-error' : ''}" id="business-name-group" style="${wantsWholesale ? '' : 'display:none;'}">
        <label for="reg-business">Nombre de tu Empresa / Negocio <span class="required">*</span></label>
        <input 
          type="text" 
          id="reg-business" 
          name="businessName" 
          value="${formData.businessName || ''}" 
          placeholder="Ej. Taller Mecánico El Litoral" 
        />
        ${businessErr}
      </div>

      <button type="submit" class="btn btn-primary btn-block" style="margin-top:1rem;" ${isSubmitting ? 'disabled' : ''}>
        ${isSubmitting ? 'Creando cuenta...' : 'Completar Registro'}
      </button>

      <div class="auth-footer-links">
        <span>¿Ya tienes una cuenta registrada?</span>
        <a href="/login.php">Iniciar Sesión</a>
      </div>
    </form>
  `;
}
