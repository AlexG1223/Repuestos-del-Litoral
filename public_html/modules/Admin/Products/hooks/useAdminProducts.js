import { fetchProducts, fetchProductById, deleteProduct, toggleProductStatus } from '../services/adminProductService.js';
import { ProductTable } from '../components/ProductTable.js';
import { openProductImageModal } from '../components/ProductImageModal.js';
import { downloadProductImage } from '../utils/downloadHelper.js';

export function useAdminProducts() {
  const tableRoot = document.getElementById('admin-product-table-root');
  const searchInput = document.getElementById('admin-product-search');
  
  let products = [];
  let currentFilters = { search: '', category_id: '', active: '' };

  async function loadProducts() {
    if (!tableRoot) return;
    tableRoot.innerHTML = '<p>Cargando productos...</p>';
    
    try {
      const res = await fetchProducts(currentFilters);
      if (res.success) {
        products = res.data;
        tableRoot.innerHTML = ProductTable(products);
        attachTableEvents();
      } else {
        tableRoot.innerHTML = `<p class="error">Error: ${res.error}</p>`;
      }
    } catch (e) {
      tableRoot.innerHTML = `<p class="error">Error de red.</p>`;
    }
  }

  function attachTableEvents() {
    // Abrir modal de imagenes para ver y descargar
    document.querySelectorAll('.btn-open-img-modal').forEach(el => {
      el.addEventListener('click', async (e) => {
        const id = parseInt(e.currentTarget.dataset.id, 10);
        const initialProd = products.find(p => p.id === id) || {
          id: id,
          name: e.currentTarget.dataset.name,
          code: e.currentTarget.dataset.code,
          primary_image: e.currentTarget.dataset.img
        };

        // Open modal with initial data immediately
        openProductImageModal(initialProd);

        // Fetch full product details including complete image gallery
        try {
          const res = await fetchProductById(id);
          if (res.success && res.data) {
            openProductImageModal(res.data);
          }
        } catch (err) {
          console.error("Error cargando galería completa", err);
        }
      });
    });

    // Descarga directa de la imagen principal
    document.querySelectorAll('.btn-download-img-direct').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const imgUrl = e.currentTarget.dataset.img;
        const name = e.currentTarget.dataset.name;
        const code = e.currentTarget.dataset.code;
        const suggestedName = `${code ? code + '_' : ''}${name}`;
        downloadProductImage(imgUrl, suggestedName);
      });
    });

    // Alternar estado activo / inactivo (Baja / Alta)
    document.querySelectorAll('.btn-toggle-status').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        const id = e.currentTarget.dataset.id;
        const targetActive = e.currentTarget.dataset.active === '1';
        const actionLabel = targetActive ? 'dar de alta' : 'dar de baja';

        if (confirm(`¿Estás seguro de que deseas ${actionLabel} este producto?`)) {
          const res = await toggleProductStatus(id, targetActive);
          if (res.success) {
            loadProducts();
          } else {
            alert('Error al cambiar estado: ' + res.error);
          }
        }
      });
    });

    // Eliminar permanentemente de la base de datos
    document.querySelectorAll('.btn-delete-product').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        const id = e.currentTarget.dataset.id;
        const name = e.currentTarget.dataset.name || 'este producto';
        if (confirm(`⚠️ ALERTA: ¿Estás seguro de que deseas ELIMINAR PERMANENTEMENTE el producto "${name}"?\n\nEsta acción eliminará el producto y sus imágenes de la base de datos de forma definitiva.`)) {
          const res = await deleteProduct(id);
          if (res.success) {
            loadProducts();
          } else {
            alert('Error al eliminar permanentemente: ' + res.error);
          }
        }
      });
    });
  }

  function initList() {
    if (searchInput) {
      let timeout = null;
      searchInput.addEventListener('input', (e) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
          currentFilters.search = e.target.value;
          loadProducts();
        }, 300);
      });
    }

    const catFilter = document.getElementById('admin-filter-category');
    if (catFilter) {
      catFilter.addEventListener('change', (e) => {
        currentFilters.category_id = e.target.value;
        loadProducts();
      });
    }

    const statusFilter = document.getElementById('admin-filter-status');
    if (statusFilter) {
      statusFilter.addEventListener('change', (e) => {
        currentFilters.active = e.target.value;
        loadProducts();
      });
    }

    loadProducts();
  }

  return { initList, loadProducts };
}
