import { formatCurrency, getImageUrl } from '../../../globals/main.js';

/**
 * Componente que renderiza la vista detallada del producto.
 * @param {Object} product 
 * @returns {string} HTML String
 */
export function ProductDetailView(product) {
  const images = product.images && product.images.length > 0 ? product.images : [
    { url: '/assets/uploads/products/placeholder.jpg', is_primary: 1 }
  ];

  const primaryImage = images.find(img => img.is_primary) || images[0];
  const primaryUrl = getImageUrl(primaryImage.url);

  const thumbnailsHtml = images.length > 1 ? images.map((img, idx) => {
    const activeClass = img.url === primaryImage.url ? 'active' : '';
    const imgUrl = getImageUrl(img.url);
    return `
      <button class="thumb-btn ${activeClass}" data-img-url="${imgUrl}">
        <img src="${imgUrl}" alt="Miniatura ${idx + 1}" onerror="this.onerror=null; this.src='/assets/uploads/products/placeholder.jpg';" />
      </button>
    `;
  }).join('') : '';

  const inStock = product.stock > 0;
  const stockBadge = inStock 
    ? '<span class="badge badge-success">En Stock Disponible</span>'
    : '<span class="badge badge-warning">Consulte Disponibilidad</span>';

  const waMessage = encodeURIComponent(`Hola! Estoy interesado en el producto: ${product.name} (Cód: ${product.code || 'S/N'}).`);
  const waUrl = `https://wa.me/59899655283?text=${waMessage}`;

  const effectivePrice = product.display_price !== undefined 
    ? product.display_price 
    : (product.price !== undefined ? product.price : product.retail_price);

  const isWholesale = product.is_wholesale || product.price_tier === 'wholesale';
  const showOriginalPrice = isWholesale && product.retail_price && product.retail_price > effectivePrice;

  return `
    <article class="product-detail-container">
      <nav class="breadcrumb">
        <a href="/inicio.php">Inicio</a> &gt; 
        <a href="/index.php?category=${encodeURIComponent(product.category_slug || '')}#catalogo">${product.category_name || 'Tienda'}</a> &gt; 
        <span>${product.name}</span>
      </nav>

      <div class="product-detail-layout">
        <!-- Galería de Imágenes -->
        <div class="product-detail-gallery">
          <div class="main-image-wrapper" id="detail-main-img-wrapper" title="Haz clic para ver la imagen completa">
            <img id="detail-main-img" src="${primaryUrl}" alt="${product.name}" onerror="this.onerror=null; this.src='/assets/uploads/products/placeholder.jpg';" />
            <div class="image-zoom-overlay">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M11 8v6M8 11h6"/></svg>
              <span>Ampliar</span>
            </div>
          </div>
          ${thumbnailsHtml ? `<div class="thumbnails-grid">${thumbnailsHtml}</div>` : ''}
        </div>

        <!-- Lightbox Modal para ver la imagen a pantalla completa -->
        <div id="image-lightbox" class="image-lightbox" role="dialog" aria-hidden="true">
          <div class="lightbox-content">
            <button type="button" class="lightbox-close" id="lightbox-close-btn" title="Cerrar">&times;</button>
            <img id="lightbox-img" src="${primaryUrl}" alt="${product.name}" />
            <div class="lightbox-caption">${product.name}</div>
          </div>
        </div>

        <!-- Ficha Técnica e Información -->
        <div class="product-detail-info">
          <div class="detail-header-meta">
            ${product.category_name ? `<span class="detail-category">${product.category_name}</span>` : ''}
            ${product.code ? `<span class="detail-code">Código de Repuesto: <strong>${product.code}</strong></span>` : ''}
          </div>

          <h1 class="product-detail-title">${product.name}</h1>

          <div class="detail-stock-status">
            ${stockBadge}
          </div>

          <div class="detail-price-box">
            <span class="price-label">${isWholesale ? '🏷️ Precio mayorista:' : 'Precio al contado:'}</span>
            <div class="detail-price-value ${isWholesale ? 'wholesale-price' : ''}">${formatCurrency(effectivePrice)}</div>
            ${showOriginalPrice ? `<div class="price-original" style="text-decoration: line-through; color: #888; font-size: 0.95rem; margin-top: 0.2rem;">Minorista: ${formatCurrency(product.retail_price)}</div>` : ''}
            <span class="price-notice">IVA Incluido</span>
          </div>

          <!-- Selector de Cantidad y Botones de Acción -->
          <div class="detail-actions">
            <div class="quantity-selector">
              <label for="product-qty">Cantidad:</label>
              <div class="qty-controls">
                <button type="button" class="qty-btn" id="qty-minus">-</button>
                <input type="number" id="product-qty" value="1" min="1" max="${product.stock > 0 ? product.stock : 99}" readonly />
                <button type="button" class="qty-btn" id="qty-plus">+</button>
              </div>
            </div>

            <div class="detail-buttons-group">
              <button type="button" class="btn btn-primary btn-add-cart-detail" id="btn-add-to-cart-detail" ${!inStock ? 'disabled' : ''}>
                🛒 Agregar al Carrito
              </button>

              <a href="${waUrl}" target="_blank" rel="noopener" class="btn btn-outline btn-consult-wa">
                <svg viewBox="0 0 32 32" style="width:18px; height:18px; fill:currentColor;"><path d="M16 2a13 13 0 0 0-11 20L3 29l7.2-1.9A13 13 0 1 0 16 2zm0 24c-2.1 0-4.1-.6-5.8-1.7l-.4-.3-4.3 1.1 1.1-4.2-.3-.5A10.9 10.9 0 1 1 16 2zm6-8.2c-.3-.2-1.9-.9-2.2-1s-.5-.2-.7.2-.8 1-.9 1.2-.3.2-.6 0a8.2 8.2 0 0 1-2.5-1.5 9 9 0 0 1-1.7-2.1c-.2-.3 0-.5.1-.7l.5-.6.3-.5v-.5c0-.2-.7-1.6-1-2.2-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4s-1.2 1.2-1.2 2.8 1.2 3.2 1.4 3.4 2.4 3.7 5.8 5.1c.8.3 1.4.5 1.9.7.8.2 1.5.2 2.1.1.7-.1 1.9-.8 2.2-1.5.3-.7.3-1.3.2-1.5s-.3-.2-.6-.4z"/></svg>
                Consulta Directa
              </a>
            </div>
          </div>

          <!-- Descripción -->
          <div class="product-detail-description">
            <h3>Descripción del Producto</h3>
            <p>${product.description ? product.description.replace(/\n/g, '<br>') : 'Sin descripción detallada disponible.'}</p>
          </div>
        </div>
      </div>
    </article>
  `;
}
