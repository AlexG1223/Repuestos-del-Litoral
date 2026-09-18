import { ProductCard } from './ProductCard.js';

/**
 * Componente que genera el marcado de la lista de productos con Infinite Scroll y Sección de Recomendados.
 * @param {Array} products 
 * @param {Object} meta { total, page, per_page, total_pages }
 * @param {Object} options { isLoadingMore, hasMore, recommendedProducts }
 * @returns {string}
 */
export function ProductList(products, meta, options = {}) {
  const { isLoadingMore = false, hasMore = true, recommendedProducts = [] } = options;

  if (!products || products.length === 0) {
    let recsHtml = '';
    if (recommendedProducts && recommendedProducts.length > 0) {
      const recCards = recommendedProducts.map(product => ProductCard(product)).join('');
      recsHtml = `
        <div class="recommendations-section">
          <div class="recommendations-header">
            <span class="recommendations-badge">💡 TE PUEDE INTERESAR</span>
            <h3>Productos Destacados y Recomendados</h3>
          </div>
          <div class="catalog-grid recommendations-grid">
            ${recCards}
          </div>
        </div>
      `;
    }

    return `
      <div class="catalog-empty">
        <div class="empty-icon">🔍</div>
        <h3>No encontramos ese producto en la web</h3>
        <p>Aún estamos cargando nuestro catálogo completo. Si buscas un repuesto específico, consúltanos directamente y te confirmamos disponibilidad inmediata.</p>
        <a href="https://wa.me/59899655283?text=Hola!%20Busco%20un%20repuesto%20que%20no%20encuentro%20en%20el%20sitio..." target="_blank" rel="noopener noreferrer" class="notice-wpp-btn" style="margin-top: 1.2rem; display: inline-flex;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
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
    <div class="catalog-grid" id="catalog-main-grid">
      ${cardsHtml}
    </div>
    ${loaderHtml}
    ${recommendationsHtml}
  `;
}
