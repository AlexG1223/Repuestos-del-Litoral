import { formatCurrency } from '../../../../globals/main.js';

export function ProductTable(products) {
  if (!products || products.length === 0) {
    return '<p>No se encontraron productos.</p>';
  }

  const rows = products.map(p => {
    const statusBadge = p.active 
      ? '<span class="admin-badge success">Activo</span>' 
      : '<span class="admin-badge secondary">Inactivo</span>';
      
    const wholesalePriceDisplay = p.wholesale_price !== null 
      ? formatCurrency(p.wholesale_price) 
      : '-';

    return `
      <tr>
        <td>
          <img src="${p.primary_image}" alt="${p.name}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
        </td>
        <td>${p.code || '-'}</td>
        <td><strong>${p.name}</strong></td>
        <td>${p.category_name || '-'}</td>
        <td>${formatCurrency(p.retail_price)}</td>
        <td>${wholesalePriceDisplay}</td>
        <td>${p.stock}</td>
        <td>${statusBadge}</td>
        <td>
          <a href="/admin/producto-editar.php?id=${p.id}" class="admin-btn secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Editar</a>
          ${p.active ? `<button type="button" class="admin-btn danger btn-delete-product" data-id="${p.id}" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Baja</button>` : ''}
        </td>
      </tr>
    `;
  }).join('');

  return `
    <div class="admin-table-wrapper">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Img</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Categoría</th>
            <th>Precio (Min)</th>
            <th>Precio (May)</th>
            <th>Stock</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          ${rows}
        </tbody>
      </table>
    </div>
  `;
}
