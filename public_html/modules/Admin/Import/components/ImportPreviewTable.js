export function renderPreviewTable(containerId, data, meta, onPageChange, onUpdateRow, onConfirm) {
  const container = document.getElementById(containerId);
  if (!container) return;

  const { items, page, total_pages, total } = meta;
  const dataItems = data || [];

  let rowsHtml = '';
  dataItems.forEach((row, idx) => {
    // El índice real en el archivo completo para poder hacer update
    const globalIndex = (page - 1) * meta.per_page + idx;
    
    const actionClass = row.action === 'create' ? 'action-create' : 'action-update';
    const actionText = row.action === 'create' ? 'Nuevo' : 'Actualiza';
    const rowClass = row.excluded ? 'excluded' : '';
    
    rowsHtml += `
      <tr class="${rowClass}" data-global-index="${globalIndex}">
        <td>
          <input type="checkbox" class="exclude-cb" ${row.excluded ? 'checked' : ''} title="Excluir de la importación">
        </td>
        <td><span class="${actionClass}">${actionText}</span></td>
        <td>
          <input type="text" class="edit-code" value="${escapeHtml(row.code)}">
        </td>
        <td>
          <input type="text" class="edit-desc" value="${escapeHtml(row.name)}">
        </td>
        <td>
          <input type="number" step="0.01" class="edit-price" value="${row.retail_price}">
        </td>
        <td>
           <button class="admin-btn admin-btn-sm btn-save-row">💾</button>
        </td>
      </tr>
    `;
  });

  if (dataItems.length === 0) {
      rowsHtml = `<tr><td colspan="6" style="text-align: center;">No se extrajeron productos.</td></tr>`;
  }

  container.innerHTML = `
    <div class="import-step" id="step-table">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <h3>3. Previsualización y Edición (${total} productos)</h3>
          <button id="btn-confirm-import" class="admin-btn admin-btn-success" ${total === 0 ? 'disabled' : ''}>
             Confirmar y Publicar
          </button>
      </div>
      
      <p>Podes editar campos a mano y darle al botón 💾 para guardar la fila, o tildar la casilla de exclusión para saltear un producto.</p>

      <div class="table-responsive">
        <table class="admin-table" style="width: 100%">
          <thead>
            <tr>
              <th style="width: 40px">X</th>
              <th style="width: 80px">Acción</th>
              <th style="width: 15%">Código</th>
              <th>Descripción</th>
              <th style="width: 15%">Precio Minorista</th>
              <th style="width: 60px">Guardar</th>
            </tr>
          </thead>
          <tbody>
            ${rowsHtml}
          </tbody>
        </table>
      </div>

      <div class="pagination">
         <button id="btn-prev" class="admin-btn" ${page <= 1 ? 'disabled' : ''}>Anterior</button>
         <span>Página ${page} de ${total_pages}</span>
         <button id="btn-next" class="admin-btn" ${page >= total_pages ? 'disabled' : ''}>Siguiente</button>
      </div>
    </div>
  `;

  // Listeners para botones de paginación
  const btnPrev = document.getElementById('btn-prev');
  if (btnPrev) btnPrev.addEventListener('click', () => onPageChange(page - 1));
  
  const btnNext = document.getElementById('btn-next');
  if (btnNext) btnNext.addEventListener('click', () => onPageChange(page + 1));

  // Listener confirmación general
  const btnConfirm = document.getElementById('btn-confirm-import');
  if (btnConfirm) btnConfirm.addEventListener('click', onConfirm);

  // Listeners para guardar filas
  container.querySelectorAll('.btn-save-row').forEach(btn => {
      btn.addEventListener('click', (e) => {
          const tr = e.target.closest('tr');
          const globalIndex = parseInt(tr.getAttribute('data-global-index'), 10);
          
          const code = tr.querySelector('.edit-code').value.trim();
          const name = tr.querySelector('.edit-desc').value.trim();
          const price = parseFloat(tr.querySelector('.edit-price').value) || 0;
          const excluded = tr.querySelector('.exclude-cb').checked;

          onUpdateRow(globalIndex, { code, name, retail_price: price, excluded }, tr);
      });
  });
}

function escapeHtml(unsafe) {
    return (unsafe || '').toString().replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}
