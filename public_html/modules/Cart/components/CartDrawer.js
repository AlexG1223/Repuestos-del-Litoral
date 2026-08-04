import { CartItem } from './CartItem.js';
import { formatCurrency } from '../../../globals/main.js';

/**
 * Componente que renderiza la estructura completa del panel lateral (drawer) del carrito.
 * @param {Array} items 
 * @param {number} total 
 * @returns {string} HTML String
 */
export function CartDrawer(items = [], total = 0) {
  const isEmpty = items.length === 0;

  const itemsHtml = isEmpty ? `
    <div class="cart-drawer-empty">
      <div class="cart-empty-icon">🛒</div>
      <p>Tu carrito está vacío</p>
      <a href="/index.php#catalogo" class="btn btn-outline btn-sm close-cart-drawer">Explorar Catálogo</a>
    </div>
  ` : items.map(item => CartItem(item)).join('');

  return `
    <div class="cart-drawer-overlay" id="cart-drawer-overlay"></div>
    <aside class="cart-drawer" id="cart-drawer-aside">
      <div class="cart-drawer-header">
        <h3>🛒 Mi Carrito</h3>
        <button type="button" class="btn-close-drawer" id="btn-close-cart-drawer" aria-label="Cerrar carrito">&times;</button>
      </div>

      <div class="cart-drawer-body">
        ${itemsHtml}
      </div>

      ${!isEmpty ? `
        <div class="cart-drawer-footer">
          <div class="cart-subtotal-row">
            <span>Subtotal estimado:</span>
            <strong class="cart-total-amount">${formatCurrency(total)}</strong>
          </div>
          <p class="cart-footer-note">Envío y pago a coordinar por WhatsApp.</p>
          <div class="cart-footer-actions">
            <a href="/checkout.php" class="btn btn-primary btn-block">
              Finalizar Pedido por WhatsApp &raquo;
            </a>
          </div>
        </div>
      ` : ''}
    </aside>
  `;
}
