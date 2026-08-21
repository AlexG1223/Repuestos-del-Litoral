import { useAuth } from './modules/Auth/hooks/useAuth.js';
import { useCart } from './modules/Cart/hooks/useCart.js?v=1.0.3';
import { useCatalog } from './modules/Catalog/hooks/useCatalog.js';
import { useProductDetail } from './modules/ProductDetail/hooks/useProductDetail.js';
import { useCheckout } from './modules/Checkout/hooks/useCheckout.js?v=1.0.3';
import { clear as clearCart } from './modules/Cart/services/cartService.js';

function handlePaymentFeedback() {
  const urlParams = new URLSearchParams(window.location.search);
  const rawStatus = urlParams.get('payment_status') || urlParams.get('collection_status') || urlParams.get('status');

  if (!rawStatus) return;

  let bannerClass = '';
  let icon = '';
  let title = '';
  let message = '';

  const status = rawStatus.toLowerCase();

  if (status === 'success' || status === 'approved') {
    bannerClass = 'success';
    icon = '✅';
    title = '¡Gracias por tu compra!';
    message = 'Tu pago a través de Mercado Pago fue confirmado con éxito. Ya estamos procesando tu pedido.';
    clearCart();
  } else if (status === 'pending' || status === 'in_process') {
    bannerClass = 'pending';
    icon = '⏳';
    title = 'Pago Pendiente de Confirmación';
    message = 'Tu pago con Mercado Pago se encuentra en proceso de verificación. Te informaremos apenas se acredite.';
  } else if (status === 'failure' || status === 'rejected') {
    bannerClass = 'failure';
    icon = '⚠️';
    title = 'No se pudo completar el pago';
    message = 'Tu transacción no pudo ser procesada. Puedes intentar nuevamente con otro medio de pago o acordar por WhatsApp.';
  } else {
    return;
  }

  const mainContent = document.querySelector('.main-content') || document.body;
  const banner = document.createElement('div');
  banner.className = `payment-feedback-banner ${bannerClass}`;
  banner.innerHTML = `
    <div>
      <strong>${icon} ${title}</strong>
      <p style="margin: 0.2rem 0 0 0; font-size: 0.95rem;">${message}</p>
    </div>
    <button type="button" class="btn-close-banner" aria-label="Cerrar">&times;</button>
  `;

  const closeBtn = banner.querySelector('.btn-close-banner');
  if (closeBtn) {
    closeBtn.onclick = () => banner.remove();
  }

  if (mainContent.firstChild) {
    mainContent.insertBefore(banner, mainContent.firstChild);
  } else {
    mainContent.appendChild(banner);
  }

  // Limpiar query params de la URL sin recargar la página
  if (window.history && window.history.replaceState) {
    const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
    window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  // Manejo de notificación de estado de pago de Mercado Pago
  handlePaymentFeedback();

  // Inicialización global de Autenticación (estado de usuario en header)
  const auth = useAuth();
  auth.init();

  // Inicialización global del Carrito (icono header y drawer)
  const cart = useCart();
  cart.init();

  // Inicialización de módulos según el contenedor en el DOM
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

