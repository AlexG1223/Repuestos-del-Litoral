import { fetchWholesaleClients, approveClient, revokeClient } from '../services/adminUserService.js';
import { WholesaleTable } from '../components/WholesaleTable.js';

export function useAdminWholesale() {
  const tableRoot = document.getElementById('admin-wholesale-table-root');
  
  let currentFilter = 'all';

  async function loadClients() {
    if (!tableRoot) return;
    tableRoot.innerHTML = '<p>Cargando clientes...</p>';
    
    try {
      const res = await fetchWholesaleClients(currentFilter);
      if (res.success) {
        tableRoot.innerHTML = WholesaleTable(res.data);
        attachTableEvents();
      } else {
        tableRoot.innerHTML = `<p class="error">Error: ${res.error}</p>`;
      }
    } catch (e) {
      tableRoot.innerHTML = `<p class="error">Error de red.</p>`;
    }
  }

  function attachTableEvents() {
    document.querySelectorAll('.btn-approve').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        const id = parseInt(e.currentTarget.dataset.id, 10);
        if (confirm('¿Aprobar cliente mayorista?')) {
          const res = await approveClient(id);
          if (res.success) {
            loadClients();
          } else {
            alert('Error: ' + res.error);
          }
        }
      });
    });

    document.querySelectorAll('.btn-revoke').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        const id = parseInt(e.currentTarget.dataset.id, 10);
        if (confirm('¿Revocar permisos de mayorista a este cliente?')) {
          const res = await revokeClient(id);
          if (res.success) {
            loadClients();
          } else {
            alert('Error: ' + res.error);
          }
        }
      });
    });
  }

  function initList() {
    const filterSelect = document.getElementById('admin-filter-wholesale');
    if (filterSelect) {
      filterSelect.addEventListener('change', (e) => {
        currentFilter = e.target.value;
        loadClients();
      });
    }

    loadClients();
  }

  return { initList, loadClients };
}
