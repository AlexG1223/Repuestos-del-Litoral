import { fetchProductDetail } from '../services/detailService.js';
import { ProductDetailView } from '../components/ProductDetailView.js';
import { addItem } from '../../Cart/services/cartService.js';

export function useProductDetail() {
  let container = null;
  let currentSlug = null;

  async function init() {
    container = document.getElementById('detail-root');
    if (!container) return;

    const urlParams = new URLSearchParams(window.location.search);
    currentSlug = urlParams.get('slug');

    if (!currentSlug) {
      renderError('No se ha especificado un producto válido.');
      return;
    }

    document.addEventListener('auth:changed', () => {
      if (currentSlug) loadDetail(currentSlug);
    });

    loadDetail(currentSlug);
  }

  async function loadDetail(slug) {
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

    // Botón Agregar al Carrito desde detalle
    const btnAddCart = container.querySelector('#btn-add-to-cart-detail');
    if (btnAddCart) {
      btnAddCart.onclick = () => {
        const qty = parseInt(qtyInput ? qtyInput.value : 1, 10) || 1;
        const primaryImg = product.images && product.images.length > 0 ? product.images[0].url : '/assets/uploads/products/placeholder.jpg';
        
        addItem({
          id: product.id,
          name: product.name,
          retail_price: product.price || product.retail_price,
          unitPrice: product.price || product.retail_price,
          primary_image: primaryImg,
          stock: product.stock
        }, qty);

        const originalText = btnAddCart.innerHTML;
        btnAddCart.innerHTML = '✓ ¡Agregado al Carrito!';
        btnAddCart.style.backgroundColor = 'var(--color-success)';
        btnAddCart.disabled = true;

        setTimeout(() => {
          btnAddCart.innerHTML = originalText;
          btnAddCart.style.backgroundColor = '';
          btnAddCart.disabled = false;
        }, 1500);
      };
    }
  }

  function render(product) {
    container.innerHTML = ProductDetailView(product);
    attachEvents(product);
  }

  return { init };
}
