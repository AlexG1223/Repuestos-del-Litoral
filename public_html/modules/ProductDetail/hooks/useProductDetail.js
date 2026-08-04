import { fetchProductDetail } from '../services/detailService.js';
import { ProductDetailView } from '../components/ProductDetailView.js';

export function useProductDetail() {
  let container = null;

  async function init() {
    container = document.getElementById('detail-root');
    if (!container) return;

    const urlParams = new URLSearchParams(window.location.search);
    const slug = urlParams.get('slug');

    if (!slug) {
      renderError('No se ha especificado un producto válido.');
      return;
    }

    renderLoading();

    try {
      const product = await fetchProductDetail(slug);
      render(product);
    } catch (err) {
      renderError(err.message || 'Error al cargar el producto.');
    }
  }

  function renderLoading() {
    container.innerHTML = `
      <div class="loading-spinner">
        <div class="spinner"></div>
        <p>Cargando detalles del producto...</p>
      </div>
    `;
  }

  function renderError(message) {
    container.innerHTML = `
      <div class="product-not-found">
        <div class="error-icon">🛠️</div>
        <h2>Producto no disponible</h2>
        <p>${message}</p>
        <a href="/index.php#catalogo" class="btn btn-primary" style="margin-top:1.5rem;">Volver al Catálogo</a>
      </div>
    `;
  }

  function attachEvents(product) {
    // Cambio de imagen en miniaturas
    const thumbBtns = container.querySelectorAll('.thumb-btn');
    const mainImg = container.querySelector('#detail-main-img');

    thumbBtns.forEach(btn => {
      btn.onclick = () => {
        thumbBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const newUrl = btn.getAttribute('data-img-url');
        if (mainImg && newUrl) {
          mainImg.src = newUrl;
        }
      };
    });

    // Controladores del selector de cantidad
    const qtyInput = container.querySelector('#product-qty');
    const btnMinus = container.querySelector('#qty-minus');
    const btnPlus = container.querySelector('#qty-plus');

    if (qtyInput && btnMinus && btnPlus) {
      btnMinus.onclick = () => {
        let val = parseInt(qtyInput.value, 10) || 1;
        if (val > 1) {
          qtyInput.value = val - 1;
        }
      };

      btnPlus.onclick = () => {
        let val = parseInt(qtyInput.value, 10) || 1;
        const max = parseInt(qtyInput.getAttribute('max'), 10) || 99;
        if (val < max) {
          qtyInput.value = val + 1;
        }
      };
    }
  }

  function render(product) {
    container.innerHTML = ProductDetailView(product);
    attachEvents(product);
  }

  return { init };
}
