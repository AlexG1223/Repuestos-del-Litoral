import { getItemCount } from '../services/cartService.js';

/**
 * Actualiza el número visible en el badge del carrito en el header.
 */
export function updateCartBadge() {
  const badge = document.getElementById('cart-badge-count');
  if (!badge) return;

  const count = getItemCount();
  badge.textContent = count;

  if (count > 0) {
    badge.classList.remove('badge-empty');
  } else {
    badge.classList.add('badge-empty');
  }
}
