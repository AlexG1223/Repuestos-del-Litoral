import { getCart, getTotal, clear } from '../../Cart/services/cartService.js';
import { submitOrder } from '../services/checkoutService.js';
import { CheckoutForm } from '../components/CheckoutForm.js';
import { OrderSummary } from '../components/OrderSummary.js';

export function useCheckout() {
  let container = null;
  let state = {
    formData: { customerName: '', customerPhone: '', customerAddress: '' },
    errors: {},
    isSubmitting: false,
    noticeMessage: null
  };

  function init() {
    container = document.getElementById('checkout-root');
    if (!container) return;

    render();
  }

  async function handleSubmit(e) {
    e.preventDefault();
    if (state.isSubmitting) return;

    const cartItems = getCart();
    const total = getTotal();

    if (cartItems.length === 0) {
      state.errors = { general: 'El carrito está vacío.' };
      render();
      return;
    }

    const minOrder = window.APP_CONFIG?.minOrderAmount || 2000;
    if (total < minOrder) {
      state.errors = { general: `El pedido mínimo es de $U ${minOrder}.` };
      render();
      return;
    }

    const form = container.querySelector('#checkout-form');
    if (!form) return;

    const formData = new FormData(form);
    state.formData = {
      customerName: (formData.get('customerName') || '').toString().trim(),
      customerEmail: (formData.get('customerEmail') || '').toString().trim(),
      customerPhone: (formData.get('customerPhone') || '').toString().trim(),
      customerAddress: (formData.get('customerAddress') || '').toString().trim(),
      paymentMethod: (formData.get('paymentMethod') || 'whatsapp').toString()
    };

    state.isSubmitting = true;
    state.errors = {};
    render();

    try {
      const res = await submitOrder(state.formData, cartItems);

      if (!res.success) {
        state.errors = res.errors || {};
        state.isSubmitting = false;
        render();
        return;
      }

      // Si hubo ítems salteados o ajustados por el servidor por cambios de stock
      if (res.data.skippedItems && res.data.skippedItems.length > 0) {
        const warnings = res.data.skippedItems.map(i => `- ${i.name}: ${i.reason}`).join('\n');
        alert(`Atención, algunos productos sufrieron ajustes de stock:\n\n${warnings}`);
      }

      // Limpiar el carrito local
      clear();

      if (res.data.paymentMethod === 'mercado_pago' && res.data.init_point) {
        window.location.href = res.data.init_point;
        return;
      }

      // Redirigir a WhatsApp con el mensaje formateado por el backend
      const whatsappMessage = res.data.whatsappMessage;
      const targetPhone = '59892492756';
      const waUrl = `https://wa.me/${targetPhone}?text=${encodeURIComponent(whatsappMessage)}`;

      window.location.href = waUrl;

    } catch (err) {
      state.errors = { general: err.message || 'Error inesperado al conectar con el servidor.' };
      state.isSubmitting = false;
      render();
    }
  }

  function render() {
    if (!container) return;

    const cartItems = getCart();
    const total = getTotal();

    if (cartItems.length === 0 && !state.isSubmitting) {
      container.innerHTML = `
        <div class="checkout-empty-box">
          <div class="empty-icon">🛒</div>
          <h2>Tu carrito está vacío</h2>
          <p>No tienes productos seleccionados para finalizar el pedido.</p>
          <a href="/index.php#catalogo" class="btn btn-primary" style="margin-top: 1.5rem;">
            Explorar Catálogo de Repuestos
          </a>
        </div>
      `;
      return;
    }

    const formHtml = CheckoutForm(state.formData, state.errors, state.isSubmitting);
    const summaryHtml = OrderSummary(cartItems, total);

    container.innerHTML = `
      <section class="checkout-section">
        <nav class="breadcrumb">
          <a href="/index.php">Inicio</a> &gt; 
          <a href="/index.php#catalogo">Catálogo</a> &gt; 
          <span>Checkout</span>
        </nav>

        <h1 class="checkout-page-title">Finalizar Pedido</h1>

        <div class="checkout-layout">
          <div class="checkout-left">
            ${formHtml}
          </div>
          <div class="checkout-right">
            ${summaryHtml}
          </div>
        </div>
      </section>
    `;

    const form = container.querySelector('#checkout-form');
    if (form) {
      form.onsubmit = handleSubmit;
    }
  }

  return { init };
}
