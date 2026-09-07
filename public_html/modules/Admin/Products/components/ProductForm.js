import { saveProduct, fetchCategories, createCategory } from '../services/adminProductService.js';
import { renderImageUploader } from './ImageUploader.js';

export async function renderProductForm(containerId, productId = 0, initialData = null) {
  const container = document.getElementById(containerId);
  if (!container) return;

  // Load categories
  let categories = [];
  try {
    const res = await fetchCategories();
    if (res.success) categories = res.data;
  } catch (e) {
    console.error("Error cargando categorías", e);
  }

  const p = initialData || {};

  const catOptions = categories.map(c => {
    const prefix = c.parent_id ? '  └─ ' : '';
    const selected = p.category_id == c.id ? 'selected' : '';
    return `<option value="${c.id}" ${selected}>${prefix}${c.name}</option>`;
  }).join('');

  container.innerHTML = `
    <form id="admin-product-form" class="admin-card">
      <input type="hidden" name="id" value="${productId}">
      
      <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
        <div class="admin-form-group" style="flex: 1; min-width: 250px;">
          <label>Nombre del Producto *</label>
          <input type="text" name="name" class="admin-form-control" value="${p.name || ''}" required>
        </div>
        
        <div class="admin-form-group" style="flex: 1; min-width: 250px;">
          <label>Slug (URL) <small>(autogenerado si se deja vacío)</small></label>
          <input type="text" name="slug" class="admin-form-control" value="${p.slug || ''}">
        </div>
      </div>

      <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
        <div class="admin-form-group" style="flex: 1; min-width: 200px;">
          <label>Categoría</label>
          <div style="display: flex; gap: 0.5rem;">
            <select name="category_id" id="prod-category-select" class="admin-form-control">
              <option value="">-- Sin categoría --</option>
              ${catOptions}
            </select>
            <button type="button" class="admin-btn secondary" id="btn-new-category" title="Nueva Categoría">+</button>
          </div>
        </div>

        <div class="admin-form-group" style="flex: 1; min-width: 150px;">
          <label>Código Interno</label>
          <input type="text" name="code" class="admin-form-control" value="${p.code || ''}">
        </div>
      </div>

      <div class="admin-form-group">
        <label>Descripción</label>
        <textarea name="description" class="admin-form-control" rows="4">${p.description || ''}</textarea>
      </div>

      <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
        <div class="admin-form-group" style="flex: 1; min-width: 120px;">
          <label>Precio Minorista ($U) *</label>
          <input type="number" name="retail_price" step="0.01" class="admin-form-control" value="${p.retail_price || ''}" required>
        </div>
        
        <div class="admin-form-group" style="flex: 1; min-width: 120px;">
          <label>Precio Mayorista ($U)</label>
          <input type="number" name="wholesale_price" step="0.01" class="admin-form-control" value="${p.wholesale_price || ''}">
        </div>

        <div class="admin-form-group" style="flex: 1; min-width: 120px;">
          <label>Stock</label>
          <input type="number" name="stock" class="admin-form-control" value="${p.stock !== undefined ? p.stock : 0}" min="0" required>
        </div>
      </div>

      <div class="admin-form-group">
        <label>
          <input type="checkbox" name="active" value="1" ${p.active === 0 ? '' : 'checked'}> 
          Producto Activo (Visible en catálogo)
        </label>
      </div>

      <button type="submit" class="admin-btn">Guardar Producto</button>
      <div id="product-form-msg" style="margin-top: 1rem;"></div>
    </form>

    ${productId > 0 ? `
      <div class="admin-card" style="margin-top: 2rem;">
        <h3>Imágenes del Producto</h3>
        <div id="admin-image-uploader-root"></div>
      </div>
    ` : '<div class="admin-card" style="margin-top: 2rem; color: #666;">Guarda el producto primero para poder subir imágenes.</div>'}
  `;

  const form = document.getElementById('admin-product-form');
  const msgBox = document.getElementById('product-form-msg');
  const btnNewCat = document.getElementById('btn-new-category');
  const selectCat = document.getElementById('prod-category-select');

  btnNewCat.addEventListener('click', async () => {
    const newCatName = prompt('Nombre de la nueva categoría:');
    if (newCatName && newCatName.trim()) {
      const res = await createCategory(newCatName.trim());
      if (res.success) {
        const opt = document.createElement('option');
        opt.value = res.data.id;
        opt.textContent = res.data.name;
        selectCat.appendChild(opt);
        selectCat.value = res.data.id;
      } else {
        alert('Error: ' + res.error);
      }
    }
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    msgBox.innerHTML = '<span style="color: blue;">Guardando...</span>';
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    data.active = formData.get('active') ? 1 : 0;
    data.stock = parseInt(data.stock, 10);
    if (data.stock < 0) data.stock = 0; // Validation minimum 0 as requested

    try {
      const res = await saveProduct(data);
      if (res.success) {
        msgBox.innerHTML = '<span style="color: green; font-weight: bold;">¡Producto guardado exitosamente!</span>';
        if (productId === 0) {
          // Redirect to edit mode to allow image upload
          setTimeout(() => {
            window.location.href = `/admin/producto-editar.php?id=${res.data.id}`;
          }, 1000);
        }
      } else {
        msgBox.innerHTML = `<span style="color: red;">Error: ${res.error}</span>`;
      }
    } catch (err) {
      msgBox.innerHTML = `<span style="color: red;">Error de red.</span>`;
    }
  });

  if (productId > 0) {
    renderImageUploader('admin-image-uploader-root', productId, p.images || []);
  }
}
