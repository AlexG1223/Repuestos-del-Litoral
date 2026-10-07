import { downloadProductImage } from '../utils/downloadHelper.js';

let activeModal = null;

export function openProductImageModal(product) {
  closeProductImageModal();

  const backdrop = document.createElement('div');
  backdrop.id = 'admin-image-modal-backdrop';
  backdrop.className = 'admin-modal-backdrop active';

  const images = (product.images && product.images.length > 0) 
    ? product.images 
    : [{ id: 0, url: product.primary_image || '/assets/uploads/products/placeholder.jpg', is_primary: 1 }];

  let selectedIndex = 0;

  function renderModalContent() {
    const currentImg = images[selectedIndex] || images[0];
    const isPlaceholder = !currentImg.url || currentImg.url.includes('placeholder.jpg');
    const totalImages = images.length;

    const codeBadge = product.code ? `<span class="admin-badge secondary" style="font-size:0.85rem;">Código: ${product.code}</span>` : '';

    const thumbnailsHtml = totalImages > 1 ? `
      <div class="admin-modal-thumbnails">
        ${images.map((img, idx) => `
          <div class="admin-modal-thumb-item ${idx === selectedIndex ? 'selected' : ''}" data-idx="${idx}">
            <img src="${img.url}" alt="Thumbnail ${idx + 1}">
            ${img.is_primary ? '<span class="primary-tag">★</span>' : ''}
          </div>
        `).join('')}
      </div>
    ` : '';

    const downloadAllBtn = totalImages > 1 ? `
      <button type="button" class="admin-btn secondary" id="btn-download-all-modal">
        📥 Descargar Todas (${totalImages})
      </button>
    ` : '';

    backdrop.innerHTML = `
      <div class="admin-modal-container">
        <div class="admin-modal-header">
          <div>
            <h3 style="margin: 0; font-size: 1.2rem; color: #111;">${product.name}</h3>
            <div style="margin-top: 0.25rem;">${codeBadge}</div>
          </div>
          <button type="button" class="admin-modal-close" id="btn-close-img-modal">&times;</button>
        </div>

        <div class="admin-modal-body">
          <div class="admin-modal-main-img-container">
            <img src="${currentImg.url}" alt="${product.name}" id="modal-main-img" class="admin-modal-main-img">
            ${totalImages > 1 ? `<div class="admin-modal-img-counter">Imagen ${selectedIndex + 1} de ${totalImages}</div>` : ''}
          </div>

          ${thumbnailsHtml}
        </div>

        <div class="admin-modal-footer">
          <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <button type="button" class="admin-btn" id="btn-download-current-modal" style="background: #10b981; color: #fff;" ${isPlaceholder ? 'disabled title="Sin imagen real"' : ''}>
              📥 Descargar Imagen ${totalImages > 1 ? `(${selectedIndex + 1})` : ''}
            </button>
            ${downloadAllBtn}
          </div>
          <button type="button" class="admin-btn secondary" id="btn-close-img-modal-bottom">Cerrar</button>
        </div>
      </div>
    `;

    // Attach events inside modal
    backdrop.querySelector('#btn-close-img-modal')?.addEventListener('click', closeProductImageModal);
    backdrop.querySelector('#btn-close-img-modal-bottom')?.addEventListener('click', closeProductImageModal);

    const downloadCurrentBtn = backdrop.querySelector('#btn-download-current-modal');
    if (downloadCurrentBtn && !isPlaceholder) {
      downloadCurrentBtn.addEventListener('click', () => {
        const fileSuffix = totalImages > 1 ? `_img${selectedIndex + 1}` : '';
        const suggestedName = `${product.code ? product.code + '_' : ''}${product.name}${fileSuffix}`;
        downloadProductImage(currentImg.url, suggestedName);
      });
    }

    const downloadAllElement = backdrop.querySelector('#btn-download-all-modal');
    if (downloadAllElement) {
      downloadAllElement.addEventListener('click', async () => {
        downloadAllElement.disabled = true;
        downloadAllElement.textContent = 'Descargando...';
        for (let i = 0; i < images.length; i++) {
          if (images[i].url && !images[i].url.includes('placeholder.jpg')) {
            const suggestedName = `${product.code ? product.code + '_' : ''}${product.name}_img${i + 1}`;
            await downloadProductImage(images[i].url, suggestedName);
            // Small delay between downloads so browser handles them smoothly
            await new Promise(r => setTimeout(r, 400));
          }
        }
        downloadAllElement.disabled = false;
        downloadAllElement.textContent = `📥 Descargar Todas (${totalImages})`;
      });
    }

    backdrop.querySelectorAll('.admin-modal-thumb-item').forEach(thumb => {
      thumb.addEventListener('click', (e) => {
        const idx = parseInt(e.currentTarget.dataset.idx, 10);
        if (!isNaN(idx)) {
          selectedIndex = idx;
          renderModalContent();
        }
      });
    });
  }

  renderModalContent();
  document.body.appendChild(backdrop);
  activeModal = backdrop;

  // Backdrop click to close
  backdrop.addEventListener('click', (e) => {
    if (e.target === backdrop) {
      closeProductImageModal();
    }
  });

  // ESC key to close
  const escHandler = (e) => {
    if (e.key === 'Escape') {
      closeProductImageModal();
      document.removeEventListener('keydown', escHandler);
    }
  };
  document.addEventListener('keydown', escHandler);
}

export function closeProductImageModal() {
  if (activeModal) {
    activeModal.remove();
    activeModal = null;
  }
  const existing = document.getElementById('admin-image-modal-backdrop');
  if (existing) existing.remove();
}
