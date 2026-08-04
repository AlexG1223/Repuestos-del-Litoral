import { useCatalog } from './modules/Catalog/hooks/useCatalog.js';
import { useProductDetail } from './modules/ProductDetail/hooks/useProductDetail.js';

document.addEventListener('DOMContentLoaded', () => {
  // Inicialización de módulo según contenedor en el DOM
  if (document.getElementById('catalog-root')) {
    const catalog = useCatalog();
    catalog.init();
  }

  if (document.getElementById('detail-root')) {
    const detail = useProductDetail();
    detail.init();
  }
});
