import { ProductCard } from './ProductCard.js';

/**
 * Componente que genera el marcado de la lista de productos con Infinite Scroll y Sección de Recomendados.
 * @param {Array} products 
 * @param {Object} meta { total, page, per_page, total_pages }
 * @param {Object} options { isLoadingMore, hasMore, recommendedProducts }
 * @returns {string}
 */
export function ProductList(products, meta = {}, options = {}) {
  const { isLoadingMore = false, hasMore = true, recommendedProducts = [] } = options;

  let didYouMeanHtml = '';
  if (meta && meta.did_you_mean) {
    didYouMeanHtml = `
      <div class="did-you-mean-banner">
        <span class="did-you-mean-icon">💡</span>
        <span class="did-you-mean-text">
          ¿Quisiste decir: 
          <button type="button" class="did-you-mean-btn" data-search="${meta.did_you_mean}">
            "${meta.did_you_mean}"
          </button>?
        </span>
      </div>
    `;
  }

  if (!products || products.length === 0) {
    let recsHtml = '';
    const fallbackProducts = (recommendedProducts && recommendedProducts.length > 0) 
      ? recommendedProducts 
      : (options.fallbackProducts || []);

    if (fallbackProducts && fallbackProducts.length > 0) {
      const recCards = fallbackProducts.map(product => ProductCard(product)).join('');
      recsHtml = `
        <div class="recommendations-section">
          <div class="recommendations-header">
            <span class="recommendations-badge">🔥 MÁS VENDIDOS</span>
            <h3>Productos Destacados y Más Buscados</h3>
          </div>
          <div class="catalog-grid recommendations-grid">
            ${recCards}
          </div>
        </div>
      `;
    }

    return `
      ${didYouMeanHtml}
      <div class="catalog-empty">
        <div class="empty-icon">🔍</div>
        <h3>No encontramos coincidencias exactas para tu búsqueda</h3>
        <p>Intenta revisar la ortografía o consulta directamente a nuestros especialistas por WhatsApp. Contamos con stock completo de repuestos y maquinaria.</p>
        <a href="https://wa.me/59899655283?text=Hola!%20Busco%20un%20repuesto%20que%20no%20encuentro%20en%20el%20sitio..." target="_blank" rel="noopener noreferrer" class="notice-wpp-btn" style="margin-top: 1.2rem; display: inline-flex;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984 0 1.76.459 3.475 1.33 4.985l-1.413 5.161 5.282-1.384c1.455.794 3.093 1.213 4.787 1.214h.004c5.506 0 9.989-4.478 9.99-9.985.001-2.668-1.034-5.176-2.922-7.065-1.888-1.889-4.397-2.928-7.068-2.93zm0 1.836c2.18 0 4.23.849 5.773 2.392 1.543 1.543 2.392 3.593 2.391 5.773 0 4.498-3.66 8.158-8.158 8.158-1.439 0-2.846-.381-4.085-1.101l-.293-.173-3.037.796.81-2.96-.19-.303c-.792-1.261-1.21-2.724-1.21-4.214 0-4.498 3.66-8.158 8.158-8.158zm-3.513 4.542c-.195 0-.512.073-.78.366-.268.293-1.025 1.001-1.025 2.441s1.049 2.832 1.196 3.027c.146.195 2.062 3.148 4.996 4.415.698.301 1.243.481 1.667.616.701.223 1.339.191 1.844.116.562-.084 1.732-.708 1.976-1.391.244-.683.244-1.269.171-1.391-.073-.122-.268-.195-.561-.342-.293-.146-1.732-.854-2.001-.952-.268-.098-.464-.146-.659.146-.195.293-.756.952-.927 1.147-.171.195-.342.22-.635.073-.293-.146-1.238-.456-2.358-1.454-.871-.776-1.459-1.734-1.63-2.027-.171-.293-.018-.451.129-.597.132-.132.293-.342.439-.513.146-.171.195-.293.293-.488.098-.195.049-.366-.024-.512-.073-.146-.659-1.587-.903-2.172-.238-.57-.48-.492-.659-.501l-.561-.007z"/></svg>
          Consultar por WhatsApp
        </a>
      </div>
      ${recsHtml}
    `;
  }

  const cardsHtml = products.map(product => ProductCard(product)).join('');

  // Indicador de Carga para Scroll Infinito
  let loaderHtml = '';
  if (isLoadingMore) {
    loaderHtml = `
      <div class="infinite-scroll-loader">
        <div class="spinner-sm"></div>
        <span>Cargando más productos...</span>
      </div>
    `;
  }

  // Sección de Productos Recomendados al terminar la lista de la categoría actual
  let recommendationsHtml = '';
  if (!hasMore && recommendedProducts && recommendedProducts.length > 0) {
    const recCards = recommendedProducts.map(product => ProductCard(product)).join('');
    recommendationsHtml = `
      <div class="recommendations-section">
        <div class="recommendations-header">
          <span class="recommendations-badge">💡 RECOMENDADO PARA TI</span>
          <h3>Otros productos destacados que podrían interesarte</h3>
        </div>
        <div class="catalog-grid recommendations-grid">
          ${recCards}
        </div>
      </div>
    `;
  } else if (!hasMore && products.length > 0) {
    recommendationsHtml = `
      <div class="catalog-end-notice">
        <span class="end-icon">✓</span>
        <p>Has explorado todos los ${products.length} productos de esta sección.</p>
      </div>
    `;
  }

  return `
    ${didYouMeanHtml}
    <div class="catalog-grid" id="catalog-main-grid">
      ${cardsHtml}
    </div>
    ${loaderHtml}
    ${recommendationsHtml}
  `;
}
