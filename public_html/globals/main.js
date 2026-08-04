/**
 * Funciones de utilidad globales para Repuestos del Litoral.
 */

/**
 * Formatea un monto numérico en formato de moneda uruguaya ($U 1.234).
 * @param {number|string} amount
 * @returns {string}
 */
export function formatCurrency(amount) {
  const num = typeof amount === 'string' ? parseFloat(amount) : amount;
  if (isNaN(num)) return '$U 0';

  const isInteger = num % 1 === 0;
  const formatted = new Intl.NumberFormat('es-UY', {
    minimumFractionDigits: isInteger ? 0 : 2,
    maximumFractionDigits: isInteger ? 0 : 2
  }).format(num);

  return `$U ${formatted}`;
}

/**
 * Normaliza la URL base de las imágenes.
 * @param {string} url 
 * @returns {string}
 */
export function getImageUrl(url) {
  if (!url) return '/assets/uploads/products/placeholder.jpg';
  if (url.startsWith('http://') || url.startsWith('https://')) return url;
  return url.startsWith('/') ? url : `/${url}`;
}
