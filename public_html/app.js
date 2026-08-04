import { useCart } from './modules/Cart/hooks/useCart.js';
import { useCatalog } from './modules/Catalog/hooks/useCatalog.js';
import { useProductDetail } from './modules/ProductDetail/hooks/useProductDetail.js';
import { useCheckout } from './modules/Checkout/hooks/useCheckout.js';

document.addEventListener('DOMContentLoaded', () => {
  // Inicialización global del Carrito (icono header y drawer) en todas las páginas
  const cart = useCart();
  cart.init();

  // Inicialización de módulos específicos según el contenedor en el DOM
  if (document.getElementById('catalog-root')) {
    const catalog = useCatalog();
    catalog.init();
  }

  if (document.getElementById('detail-root')) {
    const detail = useProductDetail();
    detail.init();
  }

  if (document.getElementById('checkout-root')) {
    const checkout = useCheckout();
    checkout.init();
  }
});
