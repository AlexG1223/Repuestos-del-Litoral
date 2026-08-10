export function renderUploadForm(containerId, categories, onSubmit) {
  const container = document.getElementById(containerId);
  if (!container) return;

  let options = '<option value="">(Sin categoría por defecto)</option>';
  categories.forEach(c => {
    options += `<option value="${c.id}">${c.name}</option>`;
  });

  container.innerHTML = `
    <div class="import-step" id="step-upload">
      <h3>1. Subir PDF del Catálogo</h3>
      <p>Seleccioná el archivo PDF de tu proveedor e indicá los márgenes de ganancia.</p>
      
      <form id="import-form">
        <div class="admin-form-group">
          <label>Archivo PDF (Max 20MB)</label>
          <input type="file" id="pdf_file" accept=".pdf" class="admin-form-control" required>
        </div>
        
        <div class="admin-form-group">
          <label>Margen Minorista (%) *</label>
          <input type="number" id="retail_margin" class="admin-form-control" min="0" step="1" required placeholder="Ej: 50">
        </div>

        <div class="admin-form-group">
          <label>Margen Mayorista (%) (Opcional)</label>
          <input type="number" id="wholesale_margin" class="admin-form-control" min="0" step="1" placeholder="Ej: 20">
          <small>Dejar vacío si prefieres cargar los precios mayoristas después.</small>
        </div>

        <div class="admin-form-group">
          <label>Categoría por defecto (Opcional)</label>
          <select id="category_id" class="admin-form-control">
            ${options}
          </select>
        </div>

        <button type="submit" class="admin-btn admin-btn-primary">Procesar PDF</button>
      </form>
    </div>
  `;

  document.getElementById('import-form').addEventListener('submit', (e) => {
    e.preventDefault();
    
    const file = document.getElementById('pdf_file').files[0];
    if (!file) return;
    
    const margins = {
        retail: document.getElementById('retail_margin').value,
        wholesale: document.getElementById('wholesale_margin').value
    };
    const categoryId = document.getElementById('category_id').value;
    
    onSubmit(file, margins, categoryId);
  });
}
