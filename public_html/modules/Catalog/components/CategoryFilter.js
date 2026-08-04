/**
 * Componente que renderiza los controles de filtrado y búsqueda.
 * @param {Array} categories 
 * @param {string} selectedCategory 
 * @param {string} searchTerm 
 * @returns {string}
 */
export function CategoryFilter(categories = [], selectedCategory = '', searchTerm = '') {
  const categoryPills = [
    { name: 'Todas', slug: '' },
    ...categories
  ].map(cat => {
    const isActive = cat.slug === selectedCategory ? 'active' : '';
    return `<button class="category-pill ${isActive}" data-category="${cat.slug}">${cat.name}</button>`;
  }).join('');

  return `
    <div class="catalog-filters">
      <div class="search-box">
        <input 
          type="text" 
          id="catalog-search-input" 
          placeholder="Buscar repuesto, código o marca (ej. Stihl, cadena, MS 250)..." 
          value="${searchTerm}" 
          autocomplete="off"
        />
        <button id="catalog-search-btn" class="btn btn-primary">Buscar</button>
      </div>

      <div class="category-pills-wrapper">
        <span class="filter-label">Categorías:</span>
        <div class="category-pills">
          ${categoryPills}
        </div>
      </div>
    </div>
  `;
}
