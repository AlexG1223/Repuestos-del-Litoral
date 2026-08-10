import { createClient } from '../services/adminUserService.js';

export function renderWholesaleForm(containerId, onCreatedCallback) {
  const container = document.getElementById(containerId);
  if (!container) return;

  container.innerHTML = `
    <form id="admin-wholesale-form" class="admin-card">
      <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
        <div class="admin-form-group" style="flex: 1; min-width: 200px;">
          <label>Nombre del Negocio / Empresa *</label>
          <input type="text" name="businessName" class="admin-form-control" required>
        </div>
        <div class="admin-form-group" style="flex: 1; min-width: 200px;">
          <label>Nombre de Contacto *</label>
          <input type="text" name="name" class="admin-form-control" required>
        </div>
      </div>
      
      <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
        <div class="admin-form-group" style="flex: 1; min-width: 200px;">
          <label>Email *</label>
          <input type="email" name="email" class="admin-form-control" required>
        </div>
        <div class="admin-form-group" style="flex: 1; min-width: 200px;">
          <label>Teléfono</label>
          <input type="text" name="phone" class="admin-form-control">
        </div>
      </div>

      <button type="submit" class="admin-btn">Dar de alta Mayorista</button>
      <div id="wholesale-form-msg" style="margin-top: 1rem;"></div>
    </form>
  `;

  const form = document.getElementById('admin-wholesale-form');
  const msgBox = document.getElementById('wholesale-form-msg');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    msgBox.innerHTML = '<span style="color: blue;">Guardando...</span>';

    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    try {
      const res = await createClient(data);
      if (res.success) {
        msgBox.innerHTML = `
          <div style="padding: 1rem; background: #d4edda; color: #155724; border-radius: 4px; border: 1px solid #c3e6cb;">
            <strong>¡Cliente creado exitosamente!</strong><br>
            Debe iniciar sesión con el email <b>${data.email}</b> y la contraseña temporal: <br>
            <span style="font-family: monospace; font-size: 1.2rem; background: #fff; padding: 0.2rem 0.5rem; border-radius: 4px;">${res.data.temp_password}</span>
          </div>
        `;
        form.reset();
        if (typeof onCreatedCallback === 'function') {
          onCreatedCallback();
        }
      } else {
        msgBox.innerHTML = `<span style="color: red;">Error: ${res.error}</span>`;
      }
    } catch (err) {
      msgBox.innerHTML = `<span style="color: red;">Error de red.</span>`;
    }
  });
}
