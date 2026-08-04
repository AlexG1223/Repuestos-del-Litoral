/**
 * Servicio de peticiones HTTP para el módulo de Catálogo.
 */

export async function fetchProducts({ page = 1, category = '', search = '' } = {}) {
  const params = new URLSearchParams();
  if (page) params.append('page', page);
  if (category) params.append('category', category);
  if (search) params.append('search', search);

  const response = await fetch(`/api/products.php?${params.toString()}`);
  const data = await response.json();

  if (!response.ok || !data.success) {
    throw new Error(data.error || 'Error al cargar los productos');
  }

  return data;
}

export async function fetchCategories() {
  const response = await fetch('/api/categories.php');
  const data = await response.json();

  if (!response.ok || !data.success) {
    throw new Error(data.error || 'Error al cargar las categorías');
  }

  return data.data;
}
