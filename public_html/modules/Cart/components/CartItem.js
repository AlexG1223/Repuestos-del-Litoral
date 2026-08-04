import { formatCurrency, getImageUrl } from '../../../globals/main.js';

/**
 * Componente que renderiza una fila dentro del drawer del carrito.
 * @param {Object} item { productId, name, unitPrice, quantity, image, stock }
 * @returns {string} HTML String
 */
export function CartItem(item) {
  const imageUrl = getImageUrl(item.image);
  const subtotal = item.unitPrice * item.quantity;

  return `
    <div class="cart-item" data-product-id="${item.productId}">
      <div class="cart-item-img-wrapper">
        <img src="${imageUrl}" alt="${item.name}" onerror="this.onerror=null; this.src='/assets/uploads/products/placeholder.jpg';" />
      </div>

      <div class="cart-item-details">
        <h4 class="cart-item-title">${item.name}</h4>
        <div class="cart-item-unit-price">${formatCurrency(item.unitPrice)} c/u</div>
        
        <div class="cart-item-actions">
          <div class="cart-qty-controls">
            <button type="button" class="btn-cart-qty cart-qty-minus" data-id="${item.productId}">-</button>
            <span class="cart-qty-val">${item.quantity}</span>
            <button type="button" class="btn-cart-qty cart-qty-plus" data-id="${item.productId}" ${item.quantity >= item.stock ? 'disabled' : ''}>+</button>
          </div>

          <button type="button" class="btn-cart-remove" data-id="${item.productId}" title="Quitar producto">
            🗑️
          </button>
        </div>
      </div>

      <div class="cart-item-subtotal">
        ${formatCurrency(subtotal)}
      </div>
    </div>
  `;
}
