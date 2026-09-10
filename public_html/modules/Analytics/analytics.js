/**
 * Módulo Centralizado de Tracking de Analítica (Google Tag Manager & dataLayer)
 * Sigue la especificación oficial de GA4 Enhanced Ecommerce y Meta Pixel vía GTM.
 */

window.dataLayer = window.dataLayer || [];

/**
 * Resetea el objeto ecommerce en el dataLayer para evitar combinar objetos de eventos anteriores.
 * Recomendación oficial de Google Analytics 4.
 */
export function resetEcommerce() {
  window.dataLayer.push({ ecommerce: null });
}

/**
 * Evento view_item: Se dispara al cargar la ficha de un producto.
 * @param {Object} product Datos del producto (id, name, display_price/retail_price, category_name)
 */
export function trackViewItem(product) {
  if (!product) return;

  const rawPrice = product.display_price !== undefined 
    ? product.display_price 
    : (product.unitPrice !== undefined ? product.unitPrice : (product.price !== undefined ? product.price : (product.retail_price || 0)));
  const price = parseFloat(rawPrice) || 0;

  resetEcommerce();
  window.dataLayer.push({
    event: 'view_item',
    ecommerce: {
      currency: 'UYU',
      value: price,
      items: [
        {
          item_id: String(product.id || product.productId || product.code || ''),
          item_name: String(product.name || 'Producto'),
          price: price,
          item_category: String(product.category_name || product.category || 'General'),
          quantity: 1
        }
      ]
    }
  });
}

/**
 * Evento add_to_cart: Se dispara al hacer clic en "Agregar al carrito".
 * @param {Object} product Datos del producto o ítem
 * @param {number} qty Cantidad agregada
 */
export function trackAddToCart(product, qty = 1) {
  if (!product) return;

  const rawPrice = product.unitPrice !== undefined 
    ? product.unitPrice 
    : (product.display_price !== undefined ? product.display_price : (product.price !== undefined ? product.price : (product.retail_price || 0)));
  const price = parseFloat(rawPrice) || 0;
  const quantity = parseInt(qty, 10) || 1;

  resetEcommerce();
  window.dataLayer.push({
    event: 'add_to_cart',
    ecommerce: {
      currency: 'UYU',
      value: price * quantity,
      items: [
        {
          item_id: String(product.productId || product.id || product.code || ''),
          item_name: String(product.name || 'Producto'),
          price: price,
          quantity: quantity
        }
      ]
    }
  });
}

/**
 * Evento begin_checkout: Se dispara al iniciar la pantalla de Checkout.
 * @param {Array} cartItems Lista de ítems en el carrito
 * @param {number} total Valor total del pedido
 */
export function trackBeginCheckout(cartItems = [], total = 0) {
  if (!Array.isArray(cartItems) || cartItems.length === 0) return;

  const formattedItems = cartItems.map(item => {
    const rawPrice = item.unitPrice !== undefined ? item.unitPrice : (item.retail_price || 0);
    return {
      item_id: String(item.productId || item.id || item.code || ''),
      item_name: String(item.name || 'Producto'),
      price: parseFloat(rawPrice) || 0,
      quantity: parseInt(item.quantity, 10) || 1
    };
  });

  resetEcommerce();
  window.dataLayer.push({
    event: 'begin_checkout',
    ecommerce: {
      currency: 'UYU',
      value: parseFloat(total) || 0,
      items: formattedItems
    }
  });
}

/**
 * Evento purchase: Se dispara al confirmar una compra exitosa.
 * @param {number|string} orderId ID de la orden / transacción
 * @param {number} total Valor total asignado a la transacción
 * @param {Array} items Lista de productos comprados
 */
export function trackPurchase(orderId, total, items = []) {
  if (!orderId) return;

  const formattedItems = (Array.isArray(items) ? items : []).map(item => {
    const rawPrice = item.unit_price !== undefined ? item.unit_price : (item.unitPrice !== undefined ? item.unitPrice : (item.retail_price || 0));
    return {
      item_id: String(item.product_id || item.productId || item.id || ''),
      item_name: String(item.name || 'Producto'),
      price: parseFloat(rawPrice) || 0,
      quantity: parseInt(item.quantity, 10) || 1
    };
  });

  resetEcommerce();
  window.dataLayer.push({
    event: 'purchase',
    ecommerce: {
      transaction_id: String(orderId),
      value: parseFloat(total) || 0,
      currency: 'UYU',
      items: formattedItems
    }
  });
}
