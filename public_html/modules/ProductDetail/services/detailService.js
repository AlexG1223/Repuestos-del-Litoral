/**
 * Servicio de peticiones HTTP para el módulo de Detalle de Producto.
 */

export async function fetchProductDetail(slug) {
  if (!slug) {
    throw new Error('Identificador de producto no especificado.');
  }

  const response = await fetch(`/api/products.php?slug=${encodeURIComponent(slug)}`);
  const data = await response.json();

  if (!response.ok || !data.success) {
    throw new Error(data.error || 'Producto no encontrado');
  }

  return data.data;
}
