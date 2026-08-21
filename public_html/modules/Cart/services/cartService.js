/**
 * Servicio de gestión del Carrito de compras (localStorage).
 * Única fuente de verdad del estado del carrito en el cliente.
 */

const STORAGE_KEY = 'rdl_cart';

function save(cart) {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
  } catch (e) {
    console.error('Error al guardar en localStorage:', e);
  }
  document.dispatchEvent(new CustomEvent('cart:updated', { detail: cart }));
}

export function getCart() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch (e) {
    console.error('Error al leer de localStorage:', e);
    return [];
  }
}

/**
 * Agrega un producto al carrito o incrementa su cantidad si ya existe.
 * @param {Object} product { id/productId, name, retail_price/unitPrice, primary_image/image, stock }
 * @param {number} qty 
 */
export function addItem(product, qty = 1) {
  const cart = getCart();
  const id = product.productId || product.id;
  const price = product.unitPrice !== undefined 
    ? product.unitPrice 
    : (product.display_price !== undefined 
      ? product.display_price 
      : (product.price !== undefined ? product.price : (product.retail_price || 0)));
  const image = product.image || product.primary_image || '/assets/uploads/products/placeholder.jpg';
  const stock = product.stock !== undefined ? product.stock : 99;

  const existingIndex = cart.findIndex(item => item.productId === id);

  if (existingIndex > -1) {
    const currentQty = cart[existingIndex].quantity;
    const newQty = Math.min(currentQty + qty, stock);
    cart[existingIndex].quantity = newQty;
  } else {
    const finalQty = Math.min(qty, stock);
    cart.push({
      productId: id,
      name: product.name,
      unitPrice: price,
      quantity: finalQty,
      image: image,
      stock: stock
    });
  }

  save(cart);
}

export function updateQuantity(productId, qty) {
  let cart = getCart();
  const item = cart.find(i => i.productId === productId);

  if (item) {
    if (qty <= 0) {
      cart = cart.filter(i => i.productId !== productId);
    } else {
      const maxStock = item.stock || 99;
      item.quantity = Math.min(qty, maxStock);
    }
    save(cart);
  }
}

export function removeItem(productId) {
  const cart = getCart().filter(i => i.productId !== productId);
  save(cart);
}

export function clear() {
  save([]);
}

export function getTotal() {
  return getCart().reduce((sum, item) => sum + (item.unitPrice * item.quantity), 0);
}

export function getItemCount() {
  return getCart().reduce((count, item) => count + item.quantity, 0);
}
