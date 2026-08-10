export function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

export async function fetchProducts(filters = {}) {
  const params = new URLSearchParams(filters).toString();
  const res = await fetch(`/api/admin/products.php?${params}`);
  return res.json();
}

export async function fetchProductById(id) {
  const res = await fetch(`/api/admin/products.php?id=${id}`);
  return res.json();
}

export async function saveProduct(data) {
  const res = await fetch('/api/admin/product-save.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': getCsrfToken()
    },
    body: JSON.stringify(data)
  });
  return res.json();
}

export async function deleteProduct(id) {
  const res = await fetch(`/api/admin/product-delete.php?id=${id}`, {
    method: 'DELETE',
    headers: {
      'X-CSRF-Token': getCsrfToken()
    }
  });
  return res.json();
}

export async function toggleProductStatus(id, active) {
  const res = await fetch('/api/admin/product-delete.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': getCsrfToken()
    },
    body: JSON.stringify({ id, active })
  });
  return res.json();
}

export async function uploadImage(productId, file) {
  const formData = new FormData();
  formData.append('product_id', productId);
  formData.append('image', file);

  const res = await fetch('/api/admin/product-images.php', {
    method: 'POST',
    headers: {
      'X-CSRF-Token': getCsrfToken()
    },
    body: formData
  });
  return res.json();
}

export async function removeImage(imageId) {
  const res = await fetch(`/api/admin/product-images.php?id=${imageId}`, {
    method: 'DELETE',
    headers: {
      'X-CSRF-Token': getCsrfToken()
    }
  });
  return res.json();
}

export async function setPrimaryImage(productId, imageId) {
  const res = await fetch('/api/admin/product-images.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': getCsrfToken()
    },
    body: JSON.stringify({ action: 'set_primary', product_id: productId, image_id: imageId })
  });
  return res.json();
}

export async function reorderImages(productId, orderedIds) {
  const res = await fetch('/api/admin/product-images.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': getCsrfToken()
    },
    body: JSON.stringify({ action: 'reorder', product_id: productId, ordered_ids: orderedIds })
  });
  return res.json();
}

export async function fetchCategories() {
  const res = await fetch('/api/admin/categories.php');
  return res.json();
}

export async function createCategory(name) {
  const res = await fetch('/api/admin/categories.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': getCsrfToken()
    },
    body: JSON.stringify({ name })
  });
  return res.json();
}
