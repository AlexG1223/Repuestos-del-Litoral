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

      if (data.recommended && data.recommended.length > 0) {
        state.recommendedProducts = data.recommended;
      } else if (!state.hasMore) {
        await loadRecommendedProducts();
      }

      // Tracking de evento 'search' si el usuario realizó una búsqueda
      if (state.searchTerm && window.dataLayer) {
        window.dataLayer.push({
          event: 'search',
          search_term: state.searchTerm,
          results_count: state.meta.total || 0
        });
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

      if (!state.hasMore && state.recommendedProducts.length === 0) {
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
    const searchBoxContainer = container.querySelector('.search-box');

    if (searchBtn && searchInput) {
      const handleSearch = () => {
        state.searchTerm = searchInput.value.trim();
        closeSuggestions();
        updateUrlParams();
        resetAndLoadProducts();
      };

      searchBtn.onclick = handleSearch;
      searchInput.onkeyup = (e) => {
        if (e.key === 'Enter') {
          handleSearch();
        }
      };

      // Autocompletado / Live Suggestions (Typeahead)
      let debounceTimer = null;
      let suggestionsDropdown = null;

      const closeSuggestions = () => {
        if (suggestionsDropdown && suggestionsDropdown.parentNode) {
          suggestionsDropdown.parentNode.removeChild(suggestionsDropdown);
        }
        suggestionsDropdown = null;
      };

      searchInput.oninput = () => {
        const val = searchInput.value.trim();
        clearTimeout(debounceTimer);

        if (val.length < 2) {
          closeSuggestions();
          return;
        }

        debounceTimer = setTimeout(async () => {
          try {
            const res = await fetch(`/api/search-suggestions.php?q=${encodeURIComponent(val)}`);
            const data = await res.json();
            if (!data.success || !data.data || data.data.length === 0) {
              closeSuggestions();
              return;
            }

            closeSuggestions();

            suggestionsDropdown = document.createElement('div');
            suggestionsDropdown.className = 'search-suggestions-dropdown';

            const itemsHtml = data.data.map(item => `
              <a href="/producto/${item.slug}" class="suggestion-item">
                <img src="${item.primary_image}" alt="${item.name}" onerror="this.src='/assets/uploads/products/placeholder.jpg';" />
                <div class="suggestion-info">
                  <div class="suggestion-title">${item.name}</div>
                  <div class="suggestion-meta">
                    ${item.code ? `<span class="suggestion-code">SKU: ${item.code}</span>` : ''}
                    ${item.category_name ? `<span class="suggestion-cat">${item.category_name}</span>` : ''}
                  </div>
                </div>
                <div class="suggestion-price">$${item.retail_price.toLocaleString('es-UY', { minimumFractionDigits: 2 })}</div>
              </a>
            `).join('');

            suggestionsDropdown.innerHTML = `
              <div class="suggestions-list">${itemsHtml}</div>
              <div class="suggestions-footer">
                <span>Presiona <strong>Enter</strong> o haz clic en Buscar para ver todos los resultados</span>
              </div>
            `;

            if (searchBoxContainer) {
              searchBoxContainer.style.position = 'relative';
              searchBoxContainer.appendChild(suggestionsDropdown);
            }
          } catch (e) {
            console.warn('Error al cargar sugerencias de búsqueda:', e);
          }
        }, 220);
      };

      document.addEventListener('click', (e) => {
        if (searchBoxContainer && !searchBoxContainer.contains(e.target)) {
          closeSuggestions();
        }
      });
    }

    // Clics en sugerencia "¿Quisiste decir...?"
    const didYouMeanBtns = container.querySelectorAll('.did-you-mean-btn');
    didYouMeanBtns.forEach(btn => {
      btn.onclick = () => {
        const suggestion = btn.getAttribute('data-search');
        if (suggestion) {
          state.searchTerm = suggestion;
          if (searchInput) searchInput.value = suggestion;
          updateUrlParams();
          resetAndLoadProducts();
        }
      };
    });

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
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984 0 1.76.459 3.475 1.33 4.985l-1.413 5.161 5.282-1.384c1.455.794 3.093 1.213 4.787 1.214h.004c5.506 0 9.989-4.478 9.99-9.985.001-2.668-1.034-5.176-2.922-7.065-1.888-1.889-4.397-2.928-7.068-2.93zm0 1.836c2.18 0 4.23.849 5.773 2.392 1.543 1.543 2.392 3.593 2.391 5.773 0 4.498-3.66 8.158-8.158 8.158-1.439 0-2.846-.381-4.085-1.101l-.293-.173-3.037.796.81-2.96-.19-.303c-.792-1.261-1.21-2.724-1.21-4.214 0-4.498 3.66-8.158 8.158-8.158zm-3.513 4.542c-.195 0-.512.073-.78.366-.268.293-1.025 1.001-1.025 2.441s1.049 2.832 1.196 3.027c.146.195 2.062 3.148 4.996 4.415.698.301 1.243.481 1.667.616.701.223 1.339.191 1.844.116.562-.084 1.732-.708 1.976-1.391.244-.683.244-1.269.171-1.391-.073-.122-.268-.195-.561-.342-.293-.146-1.732-.854-2.001-.952-.268-.098-.464-.146-.659.146-.195.293-.756.952-.927 1.147-.171.195-.342.22-.635.073-.293-.146-1.238-.456-2.358-1.454-.871-.776-1.459-1.734-1.63-2.027-.171-.293-.018-.451.129-.597.132-.132.293-.342.439-.513.146-.171.195-.293.293-.488.098-.195.049-.366-.024-.512-.073-.146-.659-1.587-.903-2.172-.238-.57-.48-.492-.659-.501l-.561-.007z"/></svg>
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
