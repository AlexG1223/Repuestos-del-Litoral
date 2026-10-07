import { formatCurrency } from '../../../../globals/main.js';

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

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

    const isPlaceholder = !p.primary_image || p.primary_image.includes('placeholder.jpg');

    return `
      <tr>
        <td>
          <div class="admin-thumb-wrapper btn-open-img-modal" data-id="${p.id}" data-name="${escapeHtml(p.name)}" data-code="${escapeHtml(p.code || '')}" data-img="${p.primary_image}" title="Ver / Descargar imágenes de ${escapeHtml(p.name)}">
            <img src="${p.primary_image}" alt="${escapeHtml(p.name)}" class="admin-table-thumb">
            <span class="admin-thumb-overlay" title="Ver / Descargar">📥</span>
          </div>
        </td>
        <td>${escapeHtml(p.code || '-')}</td>
        <td><strong>${escapeHtml(p.name)}</strong></td>
        <td>${escapeHtml(p.category_name || '-')}</td>
        <td>${formatCurrency(p.retail_price)}</td>
        <td>${wholesalePriceDisplay}</td>
        <td>${p.stock}</td>
        <td>${statusBadge}</td>
        <td>
          <div style="display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;">
            <button type="button" class="admin-btn btn-download-img-direct" data-id="${p.id}" data-name="${escapeHtml(p.name)}" data-code="${escapeHtml(p.code || '')}" data-img="${p.primary_image}" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: #10b981; color: #fff;" title="Descargar imagen del producto" ${isPlaceholder ? 'disabled style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: #ccc; color: #666; cursor: not-allowed;"' : ''}>📥 Descargar</button>
            <a href="/admin/utm-builder.php?product_id=${p.id}" class="admin-btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: #0284c7; color: #fff;" title="Generar link UTM para historias o redes">🔗 UTM</a>
            <a href="/admin/producto-editar.php?id=${p.id}" class="admin-btn secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Editar</a>
            ${toggleStatusBtn}
            <button type="button" class="admin-btn danger btn-delete-product" data-id="${p.id}" data-name="${escapeHtml(p.name)}" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" title="Eliminar permanentemente este producto de la base de datos">Eliminar</button>
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
