import { formatCurrency, getImageUrl } from '../../../globals/main.js';

/**
 * Componente JS puro para renderizar la tarjeta de un producto en el catálogo.
 * @param {Object} product 
 * @returns {string} HTML String
 */
export function ProductCard(product) {
  const imageUrl = getImageUrl(product.primary_image);
  const detailUrl = `/producto.php?slug=${encodeURIComponent(product.slug)}`;
  const codeHtml = product.code ? `<span class="product-card-code">Cód: ${product.code}</span>` : '';
  const categoryHtml = product.category_name ? `<span class="product-card-category">${product.category_name}</span>` : '';
  
  const inStock = product.stock > 0;
  const stockBadge = inStock 
    ? '<span class="badge badge-success">En Stock</span>'
    : '<span class="badge badge-warning">Consulte Stock</span>';

  // Determinar precio efectivo según sesión (mayorista o minorista)
  const effectivePrice = product.display_price !== undefined 
    ? product.display_price 
    : (product.price !== undefined ? product.price : product.retail_price);
  
  const isWholesale = product.is_wholesale || product.price_tier === 'wholesale';
  const showOriginalPrice = isWholesale && product.retail_price && product.retail_price > effectivePrice;

  // Objeto serializado de forma segura para pasar al botón de agregar al carrito
  const productData = {
    id: product.id,
    name: product.name,
    unitPrice: effectivePrice,
    retail_price: effectivePrice,
    primary_image: product.primary_image,
    stock: product.stock
  };

  return `
    <article class="product-card" data-slug="${product.slug}">
      <a href="${detailUrl}" class="product-card-image-wrapper">
        <img src="${imageUrl}" alt="${product.name}" loading="lazy" onerror="this.onerror=null; this.src='/assets/uploads/products/placeholder.jpg';" />
      </a>
      <div class="product-card-body">
        <div class="product-card-meta">
          ${categoryHtml}
          ${codeHtml}
        </div>
        <h3 class="product-card-title">
          <a href="${detailUrl}">${product.name}</a>
        </h3>
        <div class="product-card-footer">
          <div class="product-card-price-wrapper">
            <span class="product-card-price-label ${isWholesale ? 'label-wholesale' : ''}">
              ${isWholesale ? '🏷️ Precio mayorista:' : 'Precio contado:'}
            </span>
            <span class="product-card-price ${isWholesale ? 'wholesale-price' : ''}">${formatCurrency(effectivePrice)}</span>
            ${showOriginalPrice ? `<span class="product-card-original-price" style="text-decoration: line-through; color: #888; font-size: 0.8rem; margin-top: 0.1rem;">Minorista: ${formatCurrency(product.retail_price)}</span>` : ''}
          </div>
          <div class="product-card-stock">
            ${stockBadge}
          </div>
        </div>
        <div class="product-card-actions">
          <button type="button" 
                  class="btn btn-primary btn-add-cart" 
                  data-product='${JSON.stringify(productData).replace(/'/g, "&apos;")}'
                  ${!inStock ? 'disabled' : ''}>
            🛒 Agregar al carrito
          </button>
          <a href="${detailUrl}" class="btn btn-outline btn-card-detail">
            Ver detalle
          </a>
        </div>
      </div>
    </article>
  `;
}
