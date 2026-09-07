<?php
require_once __DIR__ . '/../../private/services/SessionService.php';
require_once __DIR__ . '/../../private/services/AdminGuard.php';

\RepuestosDelLitoral\Services\AdminGuard::requirePage();

$pageTitle = "Gestión de Categorías - Repuestos del Litoral";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  
  <link rel="icon" href="/assets/img/logo.png" type="image/x-icon">
  <link rel="shortcut icon" href="/assets/img/logo.png" type="image/x-icon">
  <link rel="stylesheet" href="/globals/main.css">
  <link rel="stylesheet" href="/modules/Admin/shared/styles/admin-layout.css">
  
  <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
  <style>
    .cat-tree-container {
      display: flex;
      flex-direction: column;
      gap: 1rem;
      margin-top: 1.5rem;
    }
    .cat-card {
      background: #ffffff;
      border: 1px solid var(--border-color, #e2e8f0);
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
      overflow: hidden;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .cat-card:hover {
      border-color: #cbd5e1;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    .cat-parent-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1.25rem 1.5rem;
      background: var(--surface-variant, #f8fafc);
      border-bottom: 1px solid #f1f5f9;
    }
    .cat-parent-title {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-size: 1.15rem;
      font-weight: 700;
      color: #1e293b;
    }
    .cat-parent-title span.badge {
      background: #0284c7;
      color: white;
      font-size: 0.75rem;
      padding: 0.2rem 0.6rem;
      border-radius: 20px;
      font-weight: 600;
    }
    .cat-actions {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .cat-sub-list {
      list-style: none;
      padding: 0.5rem 0;
      margin: 0;
    }
    .cat-sub-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.85rem 1.5rem 0.85rem 3rem;
      border-bottom: 1px dashed #f1f5f9;
      transition: background 0.15s ease;
    }
    .cat-sub-item:last-child {
      border-bottom: none;
    }
    .cat-sub-item:hover {
      background: #f8fafc;
    }
    .cat-sub-title {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.95rem;
      font-weight: 500;
      color: #334155;
    }
    .cat-sub-title::before {
      content: "↳";
      color: #94a3b8;
      font-size: 1.1rem;
    }
    .slug-tag {
      font-size: 0.8rem;
      color: #64748b;
      background: #f1f5f9;
      padding: 0.15rem 0.5rem;
      border-radius: 4px;
      font-family: monospace;
    }
    
    /* Stats grid */
    .cat-stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    .stat-card {
      background: white;
      padding: 1.25rem;
      border-radius: 10px;
      border: 1px solid #e2e8f0;
    }
    .stat-card .val {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary-color, #166534);
    }
    .stat-card .lbl {
      font-size: 0.85rem;
      color: #64748b;
      margin-top: 0.25rem;
    }

    /* Modal styling */
    .cat-modal-backdrop {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(15, 23, 42, 0.5);
      backdrop-filter: blur(3px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s ease;
    }
    .cat-modal-backdrop.active {
      opacity: 1;
      pointer-events: auto;
    }
    .cat-modal {
      background: white;
      width: 100%;
      max-width: 480px;
      border-radius: 12px;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
      padding: 1.75rem;
      transform: translateY(-20px);
      transition: transform 0.2s ease;
    }
    .cat-modal-backdrop.active .cat-modal {
      transform: translateY(0);
    }
    .cat-modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.25rem;
    }
    .cat-modal-header h3 {
      margin: 0;
      font-size: 1.2rem;
      color: #0f172a;
    }
    .cat-modal-close {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: #64748b;
    }
  </style>
</head>
<body class="admin-body">
  
  <!-- Contenedor del Sidebar -->
  <div id="admin-sidebar-root"></div>

  <!-- Contenido Principal -->
  <main class="admin-main">
    <header class="admin-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
      <div>
        <h1 style="margin:0;">Gestión de Categorías y Subcategorías</h1>
        <p style="margin: 0.25rem 0 0 0; color: #64748b; font-size: 0.9rem;">Organiza el catálogo de productos con categorías principales y subcategorías desplegables.</p>
      </div>
      <button id="btn-add-main-cat" class="admin-btn" style="display: flex; align-items: center; gap: 0.5rem;">
        <span>➕</span> Nueva Categoría Principal
      </button>
    </header>

    <div class="admin-content">
      <!-- Estadísticas rápidas -->
      <div class="cat-stats-grid">
        <div class="stat-card">
          <div class="val" id="stat-total-parents">0</div>
          <div class="lbl">Categorías Principales</div>
        </div>
        <div class="stat-card">
          <div class="val" id="stat-total-subs">0</div>
          <div class="lbl">Subcategorías Desplegables</div>
        </div>
        <div class="stat-card">
          <div class="val" id="stat-total-cats">0</div>
          <div class="lbl">Total en Sistema</div>
        </div>
      </div>

      <!-- Árbol de Categorías -->
      <div class="admin-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <h2 style="margin: 0; font-size: 1.15rem;">Estructura Jerárquica</h2>
          <button id="btn-refresh-tree" class="admin-btn secondary" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
            🔄 Recargar
          </button>
        </div>

        <div id="category-tree-root" class="cat-tree-container">
          <div style="text-align: center; padding: 2rem; color: #64748b;">Cargando categorías...</div>
        </div>
      </div>
    </div>
  </main>

  <!-- Modal para Crear / Editar Categoría -->
  <div id="cat-modal-backdrop" class="cat-modal-backdrop">
    <div class="cat-modal">
      <div class="cat-modal-header">
        <h3 id="cat-modal-title">Agregar Categoría</h3>
        <button class="cat-modal-close" id="btn-close-modal">&times;</button>
      </div>
      <form id="cat-modal-form">
        <input type="hidden" id="cat-form-id" name="id" value="0">

        <div class="admin-form-group">
          <label for="cat-form-name">Nombre de la Categoría *</label>
          <input type="text" id="cat-form-name" name="name" class="admin-form-control" placeholder="Ej: Motosierras, Cadenas..." required>
        </div>

        <div class="admin-form-group" style="margin-top: 1rem;">
          <label for="cat-form-parent">Categoría Padre</label>
          <select id="cat-form-parent" name="parent_id" class="admin-form-control">
            <option value="">-- Ninguna (Es Categoría Principal) --</option>
          </select>
          <small style="color: #64748b; margin-top: 0.25rem; display: block;">
            Si seleccionas un padre, esta categoría se convertirá en una subcategoría desplegable.
          </small>
        </div>

        <div id="cat-modal-msg" style="margin-top: 1rem;"></div>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
          <button type="button" class="admin-btn secondary" id="btn-cancel-modal">Cancelar</button>
          <button type="submit" class="admin-btn" id="btn-save-cat">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <script type="module">
    import { renderAdminSidebar } from '/modules/Admin/shared/components/AdminSidebar.js';
    renderAdminSidebar(window.location.pathname);

    // Estado global de categorías
    let categoriesTree = [];
    let flatCategories = [];

    const treeRoot = document.getElementById('category-tree-root');
    const modalBackdrop = document.getElementById('cat-modal-backdrop');
    const modalForm = document.getElementById('cat-modal-form');
    const modalTitle = document.getElementById('cat-modal-title');
    const formIdInput = document.getElementById('cat-form-id');
    const formNameInput = document.getElementById('cat-form-name');
    const formParentSelect = document.getElementById('cat-form-parent');
    const modalMsg = document.getElementById('cat-modal-msg');

    // Cargar datos
    async function loadCategoryData() {
      try {
        treeRoot.innerHTML = '<div style="text-align: center; padding: 2rem; color: #64748b;">Cargando categorías...</div>';
        
        // Obtener árbol
        const resTree = await fetch('/api/admin/categories.php?tree=1');
        const dataTree = await resTree.json();

        // Obtener plano
        const resFlat = await fetch('/api/admin/categories.php');
        const dataFlat = await resFlat.json();

        if (dataTree.success && dataFlat.success) {
          categoriesTree = dataTree.data;
          flatCategories = dataFlat.data;
          renderTree();
          updateStats();
          updateParentSelectOptions();
        } else {
          treeRoot.innerHTML = '<div style="color: red; padding: 1rem;">Error cargando categorías del servidor.</div>';
        }
      } catch (err) {
        console.error(err);
        treeRoot.innerHTML = '<div style="color: red; padding: 1rem;">Error de conexión con la API.</div>';
      }
    }

    // Actualizar estadísticas
    function updateStats() {
      const parents = categoriesTree.length;
      let subs = 0;
      categoriesTree.forEach(c => {
        subs += (c.subcategories ? c.subcategories.length : 0);
      });
      document.getElementById('stat-total-parents').textContent = parents;
      document.getElementById('stat-total-subs').textContent = subs;
      document.getElementById('stat-total-cats').textContent = parents + subs;
    }

    // Llenar selector de padres en el modal
    function updateParentSelectOptions(currentEditId = 0) {
      formParentSelect.innerHTML = '<option value="">-- Ninguna (Es Categoría Principal) --</option>';
      flatCategories.forEach(c => {
        // Solo permitir asociar a categorías principales (o que no sean la misma que estamos editando)
        if (c.parent_id === null && c.id != currentEditId) {
          const opt = document.createElement('option');
          opt.value = c.id;
          opt.textContent = c.name;
          formParentSelect.appendChild(opt);
        }
      });
    }

    // Renderizar Árbol en el DOM
    function renderTree() {
      if (!categoriesTree || categoriesTree.length === 0) {
        treeRoot.innerHTML = `
          <div style="text-align: center; padding: 3rem 1rem; color: #64748b;">
            <p style="font-size: 1.1rem; margin-bottom: 1rem;">No hay categorías registradas.</p>
            <button id="btn-empty-add" class="admin-btn">➕ Crear Primera Categoría</button>
          </div>
        `;
        document.getElementById('btn-empty-add')?.addEventListener('click', () => openModal(0, null));
        return;
      }

      let html = '';
      categoriesTree.forEach(parent => {
        const subCount = parent.subcategories ? parent.subcategories.length : 0;
        
        let subsHtml = '';
        if (subCount > 0) {
          subsHtml = '<ul class="cat-sub-list">' + parent.subcategories.map(sub => `
            <li class="cat-sub-item">
              <div class="cat-sub-title">
                <span>${escapeHtml(sub.name)}</span>
                <span class="slug-tag">/${escapeHtml(sub.slug)}</span>
              </div>
              <div class="cat-actions">
                <button class="admin-btn secondary btn-edit-cat" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" data-id="${sub.id}" data-name="${escapeHtml(sub.name)}" data-parent="${parent.id}">✏️ Editar</button>
                <button class="admin-btn danger btn-delete-cat" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" data-id="${sub.id}" data-name="${escapeHtml(sub.name)}">🗑️ Eliminar</button>
              </div>
            </li>
          `).join('') + '</ul>';
        } else {
          subsHtml = '<div style="padding: 1rem 1.5rem 1rem 3rem; color: #94a3b8; font-size: 0.88rem; font-style: italic;">Sin subcategorías agregadas.</div>';
        }

        html += `
          <div class="cat-card">
            <div class="cat-parent-header">
              <div class="cat-parent-title">
                <span>📁 ${escapeHtml(parent.name)}</span>
                <span class="badge">${subCount} subcategoría${subCount !== 1 ? 's' : ''}</span>
                <span class="slug-tag">/${escapeHtml(parent.slug)}</span>
              </div>
              <div class="cat-actions">
                <button class="admin-btn secondary btn-add-sub" style="font-size: 0.82rem; padding: 0.35rem 0.7rem;" data-parent-id="${parent.id}">
                  ➕ Subcategoría
                </button>
                <button class="admin-btn secondary btn-edit-cat" style="font-size: 0.82rem; padding: 0.35rem 0.7rem;" data-id="${parent.id}" data-name="${escapeHtml(parent.name)}" data-parent="">
                  ✏️ Editar
                </button>
                <button class="admin-btn danger btn-delete-cat" style="font-size: 0.82rem; padding: 0.35rem 0.7rem;" data-id="${parent.id}" data-name="${escapeHtml(parent.name)}">
                  🗑️ Eliminar
                </button>
              </div>
            </div>
            ${subsHtml}
          </div>
        `;
      });

      treeRoot.innerHTML = html;

      // Asignar listeners a los botones de acción dinámicos
      document.querySelectorAll('.btn-add-sub').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const parentId = parseInt(btn.getAttribute('data-parent-id'), 10);
          openModal(0, parentId);
        });
      });

      document.querySelectorAll('.btn-edit-cat').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = parseInt(btn.getAttribute('data-id'), 10);
          const name = btn.getAttribute('data-name');
          const parentAttr = btn.getAttribute('data-parent');
          const parentId = parentAttr ? parseInt(parentAttr, 10) : null;
          openModal(id, parentId, name);
        });
      });

      document.querySelectorAll('.btn-delete-cat').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = parseInt(btn.getAttribute('data-id'), 10);
          const name = btn.getAttribute('data-name');
          confirmDelete(id, name);
        });
      });
    }

    // Modal Control
    function openModal(id = 0, parentId = null, name = '') {
      modalMsg.innerHTML = '';
      formIdInput.value = id;
      formNameInput.value = name;
      
      updateParentSelectOptions(id);
      formParentSelect.value = parentId ? parentId : '';

      if (id > 0) {
        modalTitle.textContent = 'Editar Categoría';
      } else {
        modalTitle.textContent = parentId ? 'Agregar Subcategoría' : 'Agregar Categoría Principal';
      }

      modalBackdrop.classList.add('active');
      setTimeout(() => formNameInput.focus(), 100);
    }

    function closeModal() {
      modalBackdrop.classList.remove('active');
    }

    document.getElementById('btn-add-main-cat').addEventListener('click', () => openModal(0, null));
    document.getElementById('btn-close-modal').addEventListener('click', closeModal);
    document.getElementById('btn-cancel-modal').addEventListener('click', closeModal);
    document.getElementById('btn-refresh-tree').addEventListener('click', loadCategoryData);

    modalBackdrop.addEventListener('click', (e) => {
      if (e.target === modalBackdrop) closeModal();
    });

    // Guardar Categoría / Subcategoría
    modalForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      modalMsg.innerHTML = '<span style="color: blue;">Guardando...</span>';

      const id = parseInt(formIdInput.value, 10);
      const name = formNameInput.value.trim();
      const parentId = formParentSelect.value ? parseInt(formParentSelect.value, 10) : null;

      const method = id > 0 ? 'PUT' : 'POST';
      const bodyPayload = { id, name, parent_id: parentId };
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      try {
        const res = await fetch('/api/admin/categories.php', {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          },
          body: JSON.stringify(bodyPayload)
        });
        const data = await res.json();

        if (data.success) {
          modalMsg.innerHTML = '<span style="color: green; font-weight: bold;">¡Guardado exitosamente!</span>';
          setTimeout(() => {
            closeModal();
            loadCategoryData();
          }, 400);
        } else {
          modalMsg.innerHTML = `<span style="color: red;">Error: ${escapeHtml(data.error || 'No se pudo guardar')}</span>`;
        }
      } catch (err) {
        modalMsg.innerHTML = '<span style="color: red;">Error de conexión.</span>';
      }
    });

    // Eliminar Categoría
    async function confirmDelete(id, name) {
      if (!confirm(`¿Estás seguro de que deseas eliminar la categoría "${name}"?\n\nLos productos asociados quedarán sin categoría y las subcategorías (si las tiene) pasarán a ser principales.`)) {
        return;
      }

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      try {
        const res = await fetch(`/api/admin/categories.php?id=${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-Token': csrfToken
          }
        });
        const data = await res.json();

        if (data.success) {
          alert('Categoría eliminada correctamente.');
          loadCategoryData();
        } else {
          alert('Error: ' + (data.error || 'No se pudo eliminar.'));
        }
      } catch (err) {
        alert('Error de conexión al eliminar la categoría.');
      }
    }

    function escapeHtml(text) {
      if (!text) return '';
      return text.replace(/[&<>"']/g, function(m) {
        return {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#039;'
        }[m];
      });
    }

    // Cargar al iniciar
    document.addEventListener('DOMContentLoaded', loadCategoryData);
  </script>
</body>
</html>
