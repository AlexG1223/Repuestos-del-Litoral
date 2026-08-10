export function useOrders() {
  let orders = [];
  let page = 1;
  let totalPages = 1;
  let isLoading = false;
  let errorMessage = '';

  const container = document.getElementById('orders-root');

  async function fetchOrders(p = 1) {
    isLoading = true;
    errorMessage = '';
    render();

    try {
      const res = await fetch(`/api/admin/orders.php?page=${p}`);
      const json = await res.json();
      if (json.success) {
        orders = json.data.items;
        page = json.data.page;
        totalPages = json.data.total_pages;
      } else {
        throw new Error(json.error || 'Error al cargar pedidos');
      }
    } catch (err) {
      errorMessage = err.message;
    } finally {
      isLoading = false;
      render();
    }
  }

  async function updateStatus(id, action) {
    if (!confirm('¿Seguro que deseas cambiar el estado del pedido?')) return;

    try {
      const res = await fetch('/api/admin/orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, action })
      });
      const json = await res.json();
      if (json.success) {
        await fetchOrders(page); // Reload current page
      } else {
        alert(json.error || 'Error al actualizar');
      }
    } catch (err) {
      alert(err.message);
    }
  }

  function render() {
    if (!container) return;

    if (isLoading) {
      container.innerHTML = '<div style="padding: 2rem; text-align: center;">Cargando pedidos...</div>';
      return;
    }

    if (errorMessage) {
      container.innerHTML = `<div class="alert-error">⚠️ ${errorMessage}</div>`;
      return;
    }

    let tbody = '';
    if (orders.length === 0) {
      tbody = '<tr><td colspan="7" style="text-align: center;">No hay pedidos registrados.</td></tr>';
    } else {
      tbody = orders.map(o => {
        let actionBtn = '';
        if (o.status === 'pendiente') {
          actionBtn = `<button class="action-btn btn-cancel" data-id="${o.id}" data-action="cancel">Cancelar</button>`;
        } else if (o.status === 'pagado') {
          actionBtn = `<button class="action-btn btn-finalize" data-id="${o.id}" data-action="finalize">Finalizar</button>`;
        }

        const dateStr = new Date(o.created_at).toLocaleString('es-UY');

        return `
          <tr>
            <td>#${o.id.toString().padStart(5, '0')}</td>
            <td>
              <strong>${o.customer_name}</strong><br>
              <small>${o.customer_phone}</small><br>
              <small>${o.customer_email || ''}</small>
            </td>
            <td>$U ${parseFloat(o.total).toLocaleString('es-UY')}</td>
            <td>${o.payment_method === 'mercado_pago' ? '💳 Mercado Pago' : '💬 WhatsApp'}</td>
            <td><span class="status-badge status-${o.status.replace(/_/g, '-')}">${o.status}</span></td>
            <td>${dateStr}</td>
            <td>${actionBtn}</td>
          </tr>
        `;
      }).join('');
    }

    let paginationHtml = '';
    if (totalPages > 1) {
      let btns = '';
      for (let i = 1; i <= totalPages; i++) {
        btns += `<button class="btn ${i === page ? 'btn-primary' : 'btn-outline'} page-btn" data-page="${i}" style="margin:0 0.25rem; padding: 0.25rem 0.75rem;">${i}</button>`;
      }
      paginationHtml = `<div style="margin-top: 1rem; display: flex; justify-content: center;">${btns}</div>`;
    }

    container.innerHTML = `
      <div class="orders-table-container">
        <table class="orders-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Cliente</th>
              <th>Total</th>
              <th>Método</th>
              <th>Estado</th>
              <th>Fecha</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            ${tbody}
          </tbody>
        </table>
      </div>
      ${paginationHtml}
    `;

    // Attach events
    container.querySelectorAll('.action-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        updateStatus(e.target.dataset.id, e.target.dataset.action);
      });
    });

    container.querySelectorAll('.page-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        fetchOrders(parseInt(e.target.dataset.page, 10));
      });
    });
  }

  return { init: () => fetchOrders(1) };
}
