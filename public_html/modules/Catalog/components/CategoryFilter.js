/**
 * Componente que renderiza los controles de filtrado y búsqueda con subcategorías desplegables estilo menú vertical (hover y click).
 * @param {Array} categories 
 * @param {string} selectedCategory 
 * @param {string} searchTerm 
 * @returns {string}
 */
export function CategoryFilter(categories = [], selectedCategory = '', searchTerm = '') {
  // Separar categorías principales y mapear subcategorías
  const parents = [];
  const childrenMap = {};

  categories.forEach(cat => {
    const isParent = !cat.parent_id || cat.parent_id == 0 || cat.parent_id === '0';
    if (isParent) {
      parents.push({ ...cat, subcategories: [] });
    } else {
      const pid = String(cat.parent_id);
      if (!childrenMap[pid]) {
        childrenMap[pid] = [];
      }
      childrenMap[pid].push(cat);
    }
  });

  parents.forEach(p => {
    const pid = String(p.id);
    if (childrenMap[pid]) {
      p.subcategories = childrenMap[pid];
    }
  });

  const isAllActive = selectedCategory === '' ? 'active' : '';
  let pillsHtml = `<button class="category-pill ${isAllActive}" data-category="">Todas</button>`;

  parents.forEach(parent => {
    const hasSubs = parent.subcategories && parent.subcategories.length > 0;
    const isParentActive = parent.slug === selectedCategory;
    const hasActiveChild = hasSubs && parent.subcategories.some(s => s.slug === selectedCategory);
    
    if (hasSubs) {
      const activeGroupClass = (isParentActive || hasActiveChild) ? 'active' : '';
      
      const subsHtml = parent.subcategories.map(sub => {
        const isSubActive = sub.slug === selectedCategory ? 'active' : '';
        return `
          <button type="button" class="category-dropdown-item ${isSubActive}" data-category="${sub.slug}">
            ${sub.name}
          </button>
        `;
      }).join('');

      pillsHtml += `
        <div class="category-dropdown-wrapper">
          <div class="category-pill-group ${activeGroupClass}">
            <button type="button" class="category-pill category-parent-btn ${isParentActive ? 'active' : ''}" data-category="${parent.slug}">
              ${parent.name}
            </button>
            <button type="button" class="category-dropdown-toggle" aria-label="Ver subcategorías de ${parent.name}">
              <svg class="caret-icon" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
              </svg>
            </button>
          </div>
          <div class="category-dropdown-menu">
            ${subsHtml}
          </div>
        </div>
      `;
    } else {
      const activeClass = isParentActive ? 'active' : '';
      pillsHtml += `
        <button type="button" class="category-pill ${activeClass}" data-category="${parent.slug}">
          ${parent.name}
        </button>
      `;
    }
  });

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
          ${pillsHtml}
        </div>
      </div>
    </div>
  `;
}
