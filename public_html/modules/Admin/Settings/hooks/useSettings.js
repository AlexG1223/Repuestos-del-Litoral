export function useSettings() {
  let settings = {};
  let isSaving = false;
  let successMessage = '';
  let errorMessage = '';

  const container = document.getElementById('settings-root');

  async function fetchSettings() {
    try {
      const res = await fetch('/api/admin/settings.php');
      const json = await res.json();
      if (json.success) {
        settings = json.data;
        render();
      } else {
        throw new Error(json.error || 'Error al cargar configuraciones');
      }
    } catch (err) {
      errorMessage = err.message;
      render();
    }
  }

  async function saveSettings(e) {
    e.preventDefault();
    if (isSaving) return;

    isSaving = true;
    successMessage = '';
    errorMessage = '';
    render();

    const fd = new FormData(e.target);
    const data = {
      min_order_amount: fd.get('min_order_amount')
    };

    try {
      const res = await fetch('/api/admin/settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const json = await res.json();
      
      if (json.success) {
        successMessage = 'Configuraciones guardadas exitosamente.';
        settings = { ...settings, ...data };
      } else {
        throw new Error(json.error || 'Error al guardar');
      }
    } catch (err) {
      errorMessage = err.message;
    } finally {
      isSaving = false;
      render();
    }
  }

  function render() {
    if (!container) return;

    let alertHtml = '';
    if (successMessage) alertHtml = `<div class="badge badge-success" style="margin-bottom: 1rem; padding: 1rem; display:block;">✅ ${successMessage}</div>`;
    if (errorMessage) alertHtml = `<div class="alert-error">⚠️ ${errorMessage}</div>`;

    container.innerHTML = `
      <div class="settings-container">
        ${alertHtml}
        <form id="settings-form">
          <div class="settings-group">
            <label for="min_order_amount">Monto Mínimo de Pedido ($U)</label>
            <input 
              type="number" 
              id="min_order_amount" 
              name="min_order_amount" 
              value="${settings.min_order_amount || ''}" 
              placeholder="Ej. 2000"
              required 
            />
            <small style="color: var(--color-text-muted); display:block; margin-top:0.5rem;">
              El usuario no podrá enviar un pedido a WhatsApp si el subtotal del carrito es menor a este monto.
            </small>
          </div>

          <div class="settings-actions">
            <button type="submit" class="btn btn-primary" ${isSaving ? 'disabled' : ''}>
              ${isSaving ? 'Guardando...' : 'Guardar Cambios'}
            </button>
          </div>
        </form>
      </div>
    `;

    const form = document.getElementById('settings-form');
    if (form) form.addEventListener('submit', saveSettings);
  }

  return { init: fetchSettings };
}
