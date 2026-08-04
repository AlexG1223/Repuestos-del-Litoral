/**
 * Componente que renderiza el formulario de datos del comprador para el Checkout.
 * @param {Object} formData { customerName, customerPhone, customerAddress }
 * @param {Object} errors { customerName, customerPhone, general }
 * @param {boolean} isSubmitting
 * @returns {string} HTML String
 */
export function CheckoutForm(formData = {}, errors = {}, isSubmitting = false) {
  const nameErr = errors.customerName ? `<span class="field-error">${errors.customerName}</span>` : '';
  const phoneErr = errors.customerPhone ? `<span class="field-error">${errors.customerPhone}</span>` : '';
  const genErr = errors.general ? `<div class="alert-error" style="margin-bottom:1rem;">⚠️ ${errors.general}</div>` : '';

  return `
    <form id="checkout-form" class="checkout-form-box" novalidate>
      <h2 class="form-title">📝 Datos del Comprador</h2>
      <p class="form-subtitle">Complete la información para registrar su pedido. La compra se coordinará directamente por WhatsApp.</p>

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

      <div class="form-actions">
        <button type="submit" id="btn-submit-order" class="btn btn-primary btn-submit-checkout" ${isSubmitting ? 'disabled' : ''}>
          ${isSubmitting ? 'Procesando pedido...' : '💬 Confirmar Pedido y Enviar a WhatsApp'}
        </button>
      </div>
    </form>
  `;
}
