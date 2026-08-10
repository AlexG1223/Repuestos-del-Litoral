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

    const toggleStatusBtn = p.active 
      ? `<button type="button" class="admin-btn warning btn-toggle-status" data-id="${p.id}" data-active="0" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" title="Dar de baja (desactivar del catálogo)">Baja</button>`
      : `<button type="button" class="admin-btn success btn-toggle-status" data-id="${p.id}" data-active="1" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" title="Dar de alta (activar en el catálogo)">Alta</button>`;

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
          <div style="display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;">
            <a href="/admin/producto-editar.php?id=${p.id}" class="admin-btn secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Editar</a>
            ${toggleStatusBtn}
            <button type="button" class="admin-btn danger btn-delete-product" data-id="${p.id}" data-name="${p.name}" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" title="Eliminar permanentemente este producto de la base de datos">Eliminar</button>
          </div>
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
