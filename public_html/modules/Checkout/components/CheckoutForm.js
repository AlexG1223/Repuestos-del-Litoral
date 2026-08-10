/**
 * Componente que renderiza el formulario de datos del comprador para el Checkout.
 * @param {Object} formData { customerName, customerEmail, customerPhone, customerAddress, paymentMethod }
 * @param {Object} errors { customerName, customerEmail, customerPhone, general }
 * @param {boolean} isSubmitting
 * @returns {string} HTML String
 */
export function CheckoutForm(formData = {}, errors = {}, isSubmitting = false) {
  const nameErr = errors.customerName ? `<span class="field-error">${errors.customerName}</span>` : '';
  const emailErr = errors.customerEmail ? `<span class="field-error">${errors.customerEmail}</span>` : '';
  const phoneErr = errors.customerPhone ? `<span class="field-error">${errors.customerPhone}</span>` : '';
  const genErr = errors.general ? `<div class="alert-error" style="margin-bottom:1rem;">⚠️ ${errors.general}</div>` : '';

  const paymentMethod = formData.paymentMethod || 'whatsapp';

  return `
    <form id="checkout-form" class="checkout-form-box" novalidate>
      <h2 class="form-title">📝 Datos del Comprador</h2>
      <p class="form-subtitle">Complete la información para registrar su pedido.</p>

      ${genErr}

      <div class="form-group ${errors.customerName ? 'has-error' : ''}">
        <label for="customerName">Nombre y Apellido <span class="required">*</span></label>
        <input 
          type="text" 
          id="customerName" 
          name="customerName" 
          value="${formData.customerName || ''}" 
          placeholder="Ej. Juan Pérez" 
          required 
        />
        ${nameErr}
      </div>

      <div class="form-group ${errors.customerEmail ? 'has-error' : ''}">
        <label for="customerEmail">Correo Electrónico <span class="required">*</span></label>
        <input 
          type="email" 
          id="customerEmail" 
          name="customerEmail" 
          value="${formData.customerEmail || ''}" 
          placeholder="Ej. juan@correo.com" 
          required 
        />
        ${emailErr}
      </div>

      <div class="form-group ${errors.customerPhone ? 'has-error' : ''}">
        <label for="customerPhone">Teléfono / WhatsApp de contacto <span class="required">*</span></label>
        <input 
          type="tel" 
          id="customerPhone" 
          name="customerPhone" 
          value="${formData.customerPhone || ''}" 
          placeholder="Ej. 099 123 456" 
          required 
        />
        ${phoneErr}
      </div>

      <div class="form-group">
        <label for="customerAddress">Dirección de Envío <span class="optional">(Opcional - Dejar vacío si retira en el local)</span></label>
        <textarea 
          id="customerAddress" 
          name="customerAddress" 
          rows="2" 
          placeholder="Ej. Av. Italia 1234, Apto 201, Montevideo"
        >${formData.customerAddress || ''}</textarea>
      </div>

      <h2 class="form-title" style="margin-top: 2rem;">💳 Método de Pago</h2>
      <div class="form-group">
        <label style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem; cursor:pointer;">
          <input type="radio" name="paymentMethod" value="whatsapp" ${paymentMethod === 'whatsapp' ? 'checked' : ''} />
          <span>Coordinar pago y envío por WhatsApp</span>
        </label>
        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
          <input type="radio" name="paymentMethod" value="mercado_pago" ${paymentMethod === 'mercado_pago' ? 'checked' : ''} />
          <span>Pagar ahora con Mercado Pago (Tarjetas, Redpagos, Abitab)</span>
        </label>
      </div>

      <div class="form-actions">
        <button type="submit" id="btn-submit-order" class="btn btn-primary btn-submit-checkout" ${isSubmitting ? 'disabled' : ''}>
          ${isSubmitting ? 'Procesando pedido...' : (paymentMethod === 'whatsapp' ? '💬 Confirmar Pedido (WhatsApp)' : '💳 Pagar con Mercado Pago')}
        </button>
      </div>
    </form>
  `;
}
