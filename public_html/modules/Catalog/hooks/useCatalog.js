import { fetchProducts, fetchCategories } from '../services/catalogService.js';
import { addItem } from '../../Cart/services/cartService.js';
import { ProductList } from '../components/ProductList.js';
import { CategoryFilter } from '../components/CategoryFilter.js';

export function useCatalog() {
  let container = null;
  let scrollHandlerAttached = false;

  let state = {
    currentPage: 1,
    selectedCategory: '',
    searchTerm: '',
    categories: [],
    products: [],
    recommendedProducts: [],
    meta: { total: 0, page: 1, per_page: 12, total_pages: 1 },
    isLoading: false,
    isLoadingMore: false,
    isLoadingRecommended: false,
    hasMore: true,
    error: null
  };

  async function init() {
    container = document.getElementById('catalog-root');
    if (!container) return;

    // Leer parámetros de la URL actual
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('category')) state.selectedCategory = urlParams.get('category');
    if (urlParams.has('search')) state.searchTerm = urlParams.get('search');

    // Escuchar cambios de sesión para refrescar precios sin recargar
    document.addEventListener('auth:changed', () => {
      resetAndLoadProducts();
    });

    // Escuchar evento de scroll para Infinite Scroll
    attachScrollListener();

    try {
      state.isLoading = true;
      render();
      state.categories = await fetchCategories();
      await resetAndLoadProducts();
    } catch (err) {
      state.error = err.message;
      state.isLoading = false;
      render();
    }
  }

  function attachScrollListener() {
    if (scrollHandlerAttached) return;
    scrollHandlerAttached = true;

    window.addEventListener('scroll', () => {
      if (state.isLoading || state.isLoadingMore || !state.hasMore) return;

      const scrollBottom = window.innerHeight + window.scrollY;
      const threshold = document.documentElement.scrollHeight - 600;

      if (scrollBottom >= threshold) {
        loadNextPage();
      }
    });
  }

  async function resetAndLoadProducts() {
    state.currentPage = 1;
    state.products = [];
    state.recommendedProducts = [];
    state.hasMore = true;
    state.isLoading = true;
    state.isLoadingMore = false;
    state.error = null;
    render();

    try {
      const data = await fetchProducts({
        page: state.currentPage,
        category: state.selectedCategory,
        search: state.searchTerm
      });

      state.products = data.data || [];
      state.meta = data.meta || { total: 0, page: 1, per_page: 12, total_pages: 1 };
      state.hasMore = state.currentPage < state.meta.total_pages;

      if (!state.hasMore) {
        await loadRecommendedProducts();
      }
    } catch (err) {
      state.error = err.message;
    } finally {
      state.isLoading = false;
      render();
    }
  }

  async function loadNextPage() {
    if (state.isLoadingMore || !state.hasMore) return;

    state.isLoadingMore = true;
    renderContentOnly();

    try {
      const nextPage = state.currentPage + 1;
      const data = await fetchProducts({
        page: nextPage,
        category: state.selectedCategory,
        search: state.searchTerm
      });

      if (data && data.data && data.data.length > 0) {
        state.currentPage = nextPage;
        state.products = [...state.products, ...data.data];
        state.meta = data.meta;
        state.hasMore = state.currentPage < state.meta.total_pages;
      } else {
        state.hasMore = false;
      }

      if (!state.hasMore) {
        await loadRecommendedProducts();
      }
    } catch (err) {
      console.warn('Error al cargar siguiente página de productos:', err);
      state.hasMore = false;
    } finally {
      state.isLoadingMore = false;
      renderContentOnly();
    }
  }

  async function loadRecommendedProducts() {
    if (state.isLoadingRecommended) return;
    state.isLoadingRecommended = true;

    try {
      // Cargar productos destacados generales de la tienda para la sección de recomendaciones
      const recData = await fetchProducts({
        page: 1,
        category: '',
        search: ''
      });

      const existingIds = new Set(state.products.map(p => String(p.id)));
      const candidates = recData.data || [];
      state.recommendedProducts = candidates.filter(p => !existingIds.has(String(p.id))).slice(0, 8);
    } catch (e) {
      console.warn('No se pudieron cargar recomendaciones:', e);
    } finally {
      state.isLoadingRecommended = false;
    }
  }

  function updateUrlParams() {
    const params = new URLSearchParams();
    if (state.selectedCategory) params.set('category', state.selectedCategory);
    if (state.searchTerm) params.set('search', state.searchTerm);

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
        updateUrlParams();
        resetAndLoadProducts();
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

          // Cerrar desplegables abiertos
          container.querySelectorAll('.category-dropdown-wrapper.open').forEach(w => w.classList.remove('open'));

          updateUrlParams();
          resetAndLoadProducts();
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

    // Eventos de Agregar al Carrito
    attachAddToCartEvents();
  }

  function attachAddToCartEvents() {
    if (!container) return;

    const addCartBtns = container.querySelectorAll('.btn-add-cart:not([data-bound="true"])');
    addCartBtns.forEach(btn => {
      btn.setAttribute('data-bound', 'true');
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

  function renderContentOnly() {
    if (!container) return;
    const contentContainer = container.querySelector('.catalog-content');
    if (!contentContainer) {
      render();
      return;
    }

    contentContainer.innerHTML = ProductList(state.products, state.meta, {
      isLoadingMore: state.isLoadingMore,
      hasMore: state.hasMore,
      recommendedProducts: state.recommendedProducts
    });

    attachAddToCartEvents();
  }

  function render() {
    if (!container) return;

    const filterHtml = CategoryFilter(state.categories, state.selectedCategory, state.searchTerm);

    let contentHtml = '';
    if (state.isLoading && state.products.length === 0) {
      contentHtml = `
        <div class="loading-spinner">
          <div class="spinner"></div>
          <p>Cargando productos...</p>
        </div>
      `;
    } else if (state.error && state.products.length === 0) {
      contentHtml = `
        <div class="alert-error">
          <p>⚠️ ${state.error}</p>
          <button class="btn btn-outline" style="margin-top: 1rem;" onclick="location.reload()">Reintentar</button>
        </div>
      `;
    } else {
      contentHtml = ProductList(state.products, state.meta, {
        isLoadingMore: state.isLoadingMore,
        hasMore: state.hasMore,
        recommendedProducts: state.recommendedProducts
      });
    }

    container.innerHTML = `
      <section class="catalog-section" id="catalogo">
        <h2 class="catalog-header-title">Catálogo de Repuestos y Maquinaria</h2>
        
        <div class="catalog-notice-banner">
          <div class="notice-icon">📦</div>
          <div class="notice-text">
            <strong>¿No encuentras el repuesto que buscas?</strong>
            <span>Aún estamos cargando nuestro catálogo completo. Si no ves tu repuesto publicado, ¡consúltanos directamente por WhatsApp!</span>
          </div>
          <a href="https://wa.me/59899655283?text=Hola!%20Busco%20un%20repuesto%20que%20no%20encuentro%20en%20el%20sitio..." target="_blank" rel="noopener noreferrer" class="notice-wpp-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
            Consultar por WhatsApp
          </a>
        </div>

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
