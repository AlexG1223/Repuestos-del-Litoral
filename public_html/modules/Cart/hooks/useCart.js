import { getCart, getTotal, updateQuantity, removeItem } from '../services/cartService.js';
import { updateCartBadge } from '../components/CartBadge.js';
import { CartDrawer } from '../components/CartDrawer.js';

export function useCart() {
  let isDrawerOpen = false;

  function init() {
    updateCartBadge();

    // Escuchar cambios en el carrito globales
    document.addEventListener('cart:updated', () => {
      updateCartBadge();
      renderDrawer();
    });

    // Escuchar botón del header para abrir carrito
    const headerCartBtn = document.getElementById('btn-header-cart');
    if (headerCartBtn) {
      headerCartBtn.onclick = (e) => {
        e.preventDefault();
        openDrawer();
      };
    }

    initDrawer();
  }

  function initDrawer() {
    renderDrawer();
  }

  function openDrawer() {
    isDrawerOpen = true;
    const drawerAside = document.getElementById('cart-drawer-aside');
    const overlay = document.getElementById('cart-drawer-overlay');
    if (drawerAside && overlay) {
      drawerAside.classList.add('open');
      overlay.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeDrawer() {
    isDrawerOpen = false;
    const drawerAside = document.getElementById('cart-drawer-aside');
    const overlay = document.getElementById('cart-drawer-overlay');
    if (drawerAside && overlay) {
      drawerAside.classList.remove('open');
      overlay.classList.remove('open');
      document.body.style.overflow = '';
    }
  }

  function renderDrawer() {
    const root = document.getElementById('cart-drawer-root');
    if (!root) return;

    const cart = getCart();
    const total = getTotal();

    root.innerHTML = CartDrawer(cart, total);

    // Mantener estado abierto si estaba abierto antes de re-renderizar
    if (isDrawerOpen) {
      const drawerAside = document.getElementById('cart-drawer-aside');
      const overlay = document.getElementById('cart-drawer-overlay');
      if (drawerAside && overlay) {
        drawerAside.classList.add('open');
        overlay.classList.add('open');
      }
    }

    attachDrawerEvents();
  }

  function attachDrawerEvents() {
    const root = document.getElementById('cart-drawer-root');
    if (!root) return;

    // Cerrar drawer
    const btnClose = root.querySelector('#btn-close-cart-drawer');
    const overlay = root.querySelector('#cart-drawer-overlay');

    if (btnClose) btnClose.onclick = closeDrawer;
    if (overlay) overlay.onclick = closeDrawer;

    const closeLinks = root.querySelectorAll('.close-cart-drawer');
    closeLinks.forEach(link => {
      link.onclick = closeDrawer;
    });

    // Modificar cantidad (-)
    const minusBtns = root.querySelectorAll('.cart-qty-minus');
    minusBtns.forEach(btn => {
      btn.onclick = () => {
        const id = parseInt(btn.getAttribute('data-id'), 10);
        const item = getCart().find(i => i.productId === id);
        if (item) {
          updateQuantity(id, item.quantity - 1);
        }
      };
    });

    // Modificar cantidad (+)
    const plusBtns = root.querySelectorAll('.cart-qty-plus');
    plusBtns.forEach(btn => {
      btn.onclick = () => {
        const id = parseInt(btn.getAttribute('data-id'), 10);
        const item = getCart().find(i => i.productId === id);
        if (item) {
          updateQuantity(id, item.quantity + 1);
        }
      };
    });

    // Eliminar ítem
    const removeBtns = root.querySelectorAll('.btn-cart-remove');
    removeBtns.forEach(btn => {
      btn.onclick = () => {
        const id = parseInt(btn.getAttribute('data-id'), 10);
        removeItem(id);
      };
    });
  }

  return { init, initDrawer, openDrawer, closeDrawer };
}
