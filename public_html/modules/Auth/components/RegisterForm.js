/**
 * Componente que renderiza el formulario de Registro de Usuario Exclusivo para Mayoristas.
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

  // Por defecto la opción de cuenta mayorista se mantiene activa
  const wantsWholesale = formData.wantsWholesale !== false;

  return `
    <form id="register-form" class="auth-form-box" novalidate>
      
      <!-- Cartel de Aclaración para Compradores Particulares / Minoristas -->
      <div class="auth-retail-notice">
        <div class="notice-header">
          <span class="notice-badge-info">ℹ️ INFORMACIÓN IMPORTANTE SOBRE TU COMPRA</span>
        </div>
        <p class="retail-notice-main">
          <strong>¿Deseas comprar como cliente particular?</strong><br>
          <strong>¡NO necesitas crear ninguna cuenta!</strong> Puedes agregar directamente tus productos al carrito y finalizar tu pedido sin registrarte.
        </p>
        <a href="/index.php" class="btn btn-outline btn-sm btn-block" style="margin-top:0.3rem; text-decoration:none; text-align:center; font-weight:700;">
          🛒 Ir a la Tienda a Comprar Directamente
        </a>
      </div>

      <!-- Encabezado de Registro Exclusivo para Mayoristas -->
      <div class="auth-wholesale-header">
        <span class="wholesale-tag">🏢 REGISTRO EXCLUSIVO PARA MAYORISTAS</span>
        <h2 class="auth-title" style="margin-top:0.4rem;">Solicitud de Cuenta Mayorista</h2>
        <p class="auth-subtitle">Formulario para talleres, comercios y revendedores que desean solicitar tarifa de precio al por mayor.</p>
      </div>

      ${genErr}

      <div class="form-group ${errors.name ? 'has-error' : ''}">
        <label for="reg-name">Nombre y Apellido del Titular <span class="required">*</span></label>
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
        <label for="reg-phone">Teléfono / WhatsApp de Contacto <span class="required">*</span></label>
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

      <!-- Opción de Cuenta Mayorista (Activa) -->
      <div class="form-checkbox-group" style="display:none;">
        <label class="checkbox-label">
          <input 
            type="checkbox" 
            id="reg-wants-wholesale" 
            name="wantsWholesale" 
            checked
          />
          <span>Solicitar cuenta de Cliente Mayorista (requiere aprobación manual)</span>
        </label>
      </div>

      <!-- Campo de Nombre del Negocio -->
      <div class="form-group ${errors.businessName ? 'has-error' : ''}" id="business-name-group">
        <label for="reg-business">Nombre de tu Empresa / Taller / Negocio <span class="required">*</span></label>
        <input 
          type="text" 
          id="reg-business" 
          name="businessName" 
          value="${formData.businessName || ''}" 
          placeholder="Ej. Taller Mecánico El Litoral" 
          required
        />
        ${businessErr}
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

      <button type="submit" class="btn btn-primary btn-block" style="margin-top:1rem;" ${isSubmitting ? 'disabled' : ''}>
        ${isSubmitting ? 'Enviando solicitud...' : 'Enviar Solicitud Mayorista'}
      </button>

      <div class="auth-footer-links">
        <span>¿Ya posees una cuenta mayorista aprobada?</span>
        <a href="/login.php">Iniciar Sesión Mayorista</a>
      </div>
    </form>
  `;
}
