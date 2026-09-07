import { fetchProducts, fetchCategories } from '../services/catalogService.js';
import { addItem } from '../../Cart/services/cartService.js';
import { ProductList } from '../components/ProductList.js';
import { CategoryFilter } from '../components/CategoryFilter.js';

export function useCatalog() {
  let container = null;
  let state = {
    currentPage: 1,
    selectedCategory: '',
    searchTerm: '',
    categories: [],
    products: [],
    meta: { total: 0, page: 1, per_page: 12, total_pages: 1 },
    isLoading: false,
    error: null
  };

  async function init() {
    container = document.getElementById('catalog-root');
    if (!container) return;

    // Leer parámetros de la URL actual
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('category')) state.selectedCategory = urlParams.get('category');
    if (urlParams.has('search')) state.searchTerm = urlParams.get('search');
    if (urlParams.has('page')) state.currentPage = parseInt(urlParams.get('page'), 10) || 1;

    // Escuchar cambios de sesión para refrescar precios sin recargar
    document.addEventListener('auth:changed', () => {
      loadProducts();
    });

    try {
      state.isLoading = true;
      render();
      state.categories = await fetchCategories();
      await loadProducts();
    } catch (err) {
      state.error = err.message;
      state.isLoading = false;
      render();
    }
  }

  async function loadProducts() {
    state.isLoading = true;
    state.error = null;
    render();

    try {
      const data = await fetchProducts({
        page: state.currentPage,
        category: state.selectedCategory,
        search: state.searchTerm
      });
      state.products = data.data;
      state.meta = data.meta;
    } catch (err) {
      state.error = err.message;
    } finally {
      state.isLoading = false;
      render();
    }
  }

  function updateUrlParams() {
    const params = new URLSearchParams();
    if (state.selectedCategory) params.set('category', state.selectedCategory);
    if (state.searchTerm) params.set('search', state.searchTerm);
    if (state.currentPage > 1) params.set('page', state.currentPage);

    const newUrl = `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}#catalogo`;
    window.history.replaceState({}, '', newUrl);
  }

  function attachEvents() {
    if (!container) return;

    // Búsqueda
    const searchInput = container.querySelector('#catalog-search-input');
    const searchBtn = container.querySelector('#catalog-search-btn');

    if (searchBtn && searchInput) {
      const handleSearch = () => {
        state.searchTerm = searchInput.value.trim();
        state.currentPage = 1;
        updateUrlParams();
        loadProducts();
      };

      searchBtn.onclick = handleSearch;
      searchInput.onkeyup = (e) => {
        if (e.key === 'Enter') handleSearch();
      };
    }

    // Categorías y subcategorías
    const catButtons = container.querySelectorAll('.category-pill, .category-dropdown-item');
    catButtons.forEach(btn => {
      btn.onclick = (e) => {
        e.stopPropagation();
        const cat = btn.getAttribute('data-category');
        if (cat !== null && state.selectedCategory !== cat) {
          state.selectedCategory = cat;
          state.currentPage = 1;
          
          // Cerrar desplegables abiertos
          container.querySelectorAll('.category-dropdown-wrapper.open').forEach(w => w.classList.remove('open'));

          updateUrlParams();
          loadProducts();
        }
      };
    });

    // Toggle manual para abrir/cerrar subcategorías en móviles/touch
    const dropdownToggles = container.querySelectorAll('.category-dropdown-toggle');
    dropdownToggles.forEach(toggle => {
      toggle.onclick = (e) => {
        e.stopPropagation();
        const wrapper = toggle.closest('.category-dropdown-wrapper');
        if (wrapper) {
          wrapper.classList.toggle('open');
        }
      };
    });

    // Cerrar desplegables al hacer clic fuera
    document.addEventListener('click', () => {
      container.querySelectorAll('.category-dropdown-wrapper.open').forEach(w => w.classList.remove('open'));
    });

    // Paginación
    const pageBtns = container.querySelectorAll('.pagination-btn');
    pageBtns.forEach(btn => {
      btn.onclick = () => {
        const targetPage = parseInt(btn.getAttribute('data-page'), 10);
        if (targetPage && targetPage !== state.currentPage && targetPage >= 1 && targetPage <= state.meta.total_pages) {
          state.currentPage = targetPage;
          updateUrlParams();
          loadProducts();
          
          const catalogTitle = document.getElementById('catalogo');
          if (catalogTitle) {
            catalogTitle.scrollIntoView({ behavior: 'smooth' });
          }
        }
      };
    });

    // Eventos de Agregar al Carrito directo desde la tarjeta
    const addCartBtns = container.querySelectorAll('.btn-add-cart');
    addCartBtns.forEach(btn => {
      btn.onclick = () => {
        try {
          const rawData = btn.getAttribute('data-product');
          if (rawData) {
            const product = JSON.parse(rawData);
            addItem(product, 1);

            const originalText = btn.innerHTML;
            btn.innerHTML = '✓ ¡Agregado!';
            btn.classList.add('added-success');
            btn.disabled = true;

            setTimeout(() => {
              btn.innerHTML = originalText;
              btn.classList.remove('added-success');
              btn.disabled = false;
            }, 1500);
          }
        } catch (e) {
          console.error('Error al agregar producto al carrito:', e);
        }
      };
    });
  }

  function render() {
    if (!container) return;

    const filterHtml = CategoryFilter(state.categories, state.selectedCategory, state.searchTerm);

    let contentHtml = '';
    if (state.isLoading) {
      contentHtml = `
        <div class="loading-spinner">
          <div class="spinner"></div>
          <p>Cargando productos...</p>
        </div>
      `;
    } else if (state.error) {
      contentHtml = `
        <div class="alert-error">
          <p>⚠️ ${state.error}</p>
          <button class="btn btn-outline" style="margin-top: 1rem;" onclick="location.reload()">Reintentar</button>
        </div>
      `;
    } else {
      contentHtml = ProductList(state.products, state.meta);
    }

    container.innerHTML = `
      <section class="catalog-section" id="catalogo">
        <h2 class="catalog-header-title">Catálogo de Repuestos y Maquinaria</h2>
        ${filterHtml}
        <div class="catalog-content">
          ${contentHtml}
        </div>
      </section>
    `;

    if (!state.isLoading && !state.error) {
      attachEvents();
    }
  }

  return { init };
}
