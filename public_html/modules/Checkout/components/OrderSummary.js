import { formatCurrency, getImageUrl } from '../../../globals/main.js';

/**
 * Componente que renderiza el resumen del pedido a la par del formulario.
 * @param {Array} items 
 * @param {number} total 
 * @returns {string} HTML String
 */
export function OrderSummary(items = [], total = 0) {
  const itemsHtml = items.map(item => {
    const imgUrl = getImageUrl(item.image);
    const subtotal = item.unitPrice * item.quantity;
    return `
      <div class="summary-item">
        <div class="summary-item-img">
          <img src="${imgUrl}" alt="${item.name}" onerror="this.onerror=null; this.src='/assets/uploads/products/placeholder.jpg';" />
        </div>
        <div class="summary-item-info">
          <span class="summary-item-name">${item.name}</span>
          <span class="summary-item-qty">${formatCurrency(item.unitPrice)} x ${item.quantity}</span>
        </div>
        <div class="summary-item-subtotal">
          ${formatCurrency(subtotal)}
        </div>
      </div>
    `;
  }).join('');

  return `
    <div class="order-summary-box">
      <h3 class="summary-title">🛒 Resumen de tu Compra</h3>
      <div class="summary-items-list">
        ${itemsHtml}
      </div>

      <div class="summary-total-block">
        <div class="summary-total-row">
          <span>Total del pedido:</span>
          <span class="total-price">${formatCurrency(total)}</span>
        </div>
        <p class="summary-notice">
          ℹ️ Los precios incluyen IVA. El pago y envío se coordinan directamente en la conversación de WhatsApp.
        </p>
      </div>
    </div>
  `;
}
