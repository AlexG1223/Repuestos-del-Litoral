import { uploadImage, removeImage, setPrimaryImage, reorderImages } from '../services/adminProductService.js';

export function renderImageUploader(containerId, productId, images) {
  const container = document.getElementById(containerId);
  if (!container) return;

  // Render basic structure
  container.innerHTML = `
    <div class="image-uploader-controls">
      <input type="file" id="admin-file-input" accept="image/jpeg, image/png, image/webp" style="display: none;" multiple>
      <button type="button" class="admin-btn" id="btn-add-image">Subir Imagen</button>
      <span id="upload-status" style="margin-left: 1rem; color: #666;"></span>
    </div>
    <div class="image-gallery" id="admin-image-gallery" style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1.5rem;"></div>
  `;

  const fileInput = document.getElementById('admin-file-input');
  const btnAdd = document.getElementById('btn-add-image');
  const status = document.getElementById('upload-status');
  const gallery = document.getElementById('admin-image-gallery');

  let currentImages = [...images];

  function drawGallery() {
    gallery.innerHTML = '';
    
    if (currentImages.length === 0) {
      gallery.innerHTML = '<p>No hay imágenes subidas.</p>';
      return;
    }

    currentImages.forEach((img, index) => {
      // Don't allow delete/setPrimary if it's the placeholder (id = 0)
      const isPlaceholder = img.id === 0;

      const card = document.createElement('div');
      card.className = `image-card ${img.is_primary ? 'primary' : ''}`;
      card.style.cssText = `
        position: relative; width: 150px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden;
        ${img.is_primary ? 'border-color: var(--color-primary); box-shadow: 0 0 5px var(--color-primary);' : ''}
      `;

      card.innerHTML = `
        <img src="${img.url}" style="width: 100%; height: 120px; object-fit: cover; display: block;">
        <div style="padding: 0.5rem; display: flex; flex-direction: column; gap: 0.25rem; background: #fafafa;">
          ${!isPlaceholder ? `
            <div style="display: flex; justify-content: space-between;">
              <button type="button" class="admin-btn secondary btn-up" data-idx="${index}" ${index === 0 ? 'disabled' : ''} style="padding: 0.1rem 0.3rem;">◀</button>
              <button type="button" class="admin-btn secondary btn-down" data-idx="${index}" ${index === currentImages.length - 1 ? 'disabled' : ''} style="padding: 0.1rem 0.3rem;">▶</button>
            </div>
            <button type="button" class="admin-btn secondary btn-primary-toggle" data-id="${img.id}" style="width: 100%; padding: 0.25rem;">
              ${img.is_primary ? '★ Principal' : '☆ Marcar principal'}
            </button>
            <button type="button" class="admin-btn danger btn-delete-img" data-id="${img.id}" style="width: 100%; padding: 0.25rem;">Eliminar</button>
          ` : '<small>Placeholder</small>'}
        </div>
      `;
      gallery.appendChild(card);
    });

    attachGalleryEvents();
  }

  function attachGalleryEvents() {
    document.querySelectorAll('.btn-delete-img').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        if (!confirm('¿Eliminar esta imagen?')) return;
        const id = parseInt(e.currentTarget.dataset.id, 10);
        status.textContent = 'Eliminando...';
        const res = await removeImage(id);
        if (res.success) {
          currentImages = currentImages.filter(i => i.id !== id);
          status.textContent = '';
          drawGallery();
        } else {
          status.textContent = 'Error: ' + res.error;
        }
      });
    });

    document.querySelectorAll('.btn-primary-toggle').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        const id = parseInt(e.currentTarget.dataset.id, 10);
        status.textContent = 'Actualizando...';
        const res = await setPrimaryImage(productId, id);
        if (res.success) {
          currentImages.forEach(i => i.is_primary = (i.id === id ? 1 : 0));
          status.textContent = '';
          drawGallery();
        } else {
          status.textContent = 'Error: ' + res.error;
        }
      });
    });

    document.querySelectorAll('.btn-up, .btn-down').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        const idx = parseInt(e.currentTarget.dataset.idx, 10);
        const dir = e.currentTarget.classList.contains('btn-up') ? -1 : 1;
        const newIdx = idx + dir;

        // Swap
        const temp = currentImages[idx];
        currentImages[idx] = currentImages[newIdx];
        currentImages[newIdx] = temp;

        drawGallery();
        
        // Save order
        const orderedIds = currentImages.map(i => i.id);
        await reorderImages(productId, orderedIds);
      });
    });
  }

  btnAdd.addEventListener('click', () => {
    fileInput.click();
  });

  fileInput.addEventListener('change', async () => {
    const files = fileInput.files;
    if (files.length === 0) return;

    status.textContent = `Subiendo ${files.length} archivo(s)...`;

    for (let i = 0; i < files.length; i++) {
      try {
        const res = await uploadImage(productId, files[i]);
        if (res.success) {
          // Remove placeholder if it exists
          currentImages = currentImages.filter(img => img.id !== 0);
          
          currentImages.push({
            id: res.data.id,
            product_id: productId,
            url: res.data.url,
            is_primary: res.data.is_primary ? 1 : 0,
            sort_order: 999
          });
        } else {
          alert('Error al subir imagen: ' + res.error);
        }
      } catch (err) {
        alert('Error de red al subir imagen');
      }
    }

    fileInput.value = '';
    status.textContent = '';
    drawGallery();
  });

  drawGallery();
}
