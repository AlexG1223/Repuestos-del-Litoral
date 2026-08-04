import { ProductCard } from './ProductCard.js';

/**
 * Componente que genera el marcado de la lista de productos y la paginación.
 * @param {Array} products 
 * @param {Object} meta { total, page, per_page, total_pages }
 * @returns {string}
 */
export function ProductList(products, meta) {
  if (!products || products.length === 0) {
    return `
      <div class="catalog-empty">
        <div class="empty-icon">🔍</div>
        <h3>No se encontraron productos</h3>
        <p>Intenta ajustar el término de búsqueda o selecciona otra categoría.</p>
      </div>
    `;
  }

  const cardsHtml = products.map(product => ProductCard(product)).join('');

  // Generador de Paginación
  let paginationHtml = '';
  if (meta && meta.total_pages > 1) {
    const currentPage = meta.page;
    const totalPages = meta.total_pages;

    let pageButtonsHtml = '';

    // Botón anterior
    const prevDisabled = currentPage <= 1 ? 'disabled' : '';
    pageButtonsHtml += `<button class="pagination-btn" data-page="${currentPage - 1}" ${prevDisabled}>&laquo; Anterior</button>`;

    // Números de página
    for (let i = 1; i <= totalPages; i++) {
      const activeClass = i === currentPage ? 'active' : '';
      pageButtonsHtml += `<button class="pagination-btn ${activeClass}" data-page="${i}">${i}</button>`;
    }

    // Botón siguiente
    const nextDisabled = currentPage >= totalPages ? 'disabled' : '';
    pageButtonsHtml += `<button class="pagination-btn" data-page="${currentPage + 1}" ${nextDisabled}>Siguiente &raquo;</button>`;

    paginationHtml = `
      <nav class="catalog-pagination">
        <div class="pagination-info">Página ${currentPage} de ${totalPages} (${meta.total} productos)</div>
        <div class="pagination-buttons">${pageButtonsHtml}</div>
      </nav>
    `;
  }

  return `
    <div class="catalog-grid">
      ${cardsHtml}
    </div>
    ${paginationHtml}
  `;
}
