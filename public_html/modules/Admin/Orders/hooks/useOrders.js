export function useOrders() {
  let orders = [];
  let page = 1;
  let totalPages = 1;
  let isLoading = false;
  let errorMessage = '';

  const container = document.getElementById('orders-root');

  // Asegurar root del modal en el body
  let modalBackdrop = document.getElementById('order-modal-backdrop');
  if (!modalBackdrop) {
    modalBackdrop = document.createElement('div');
    modalBackdrop.id = 'order-modal-backdrop';
    modalBackdrop.className = 'order-modal-backdrop';
    document.body.appendChild(modalBackdrop);
  }

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

  async function updateStatus(id, action, fromModal = false) {
    const isDelete = action === 'delete';
    const confirmMsg = isDelete
      ? '⚠️ ¿Estás seguro de que deseas ELIMINAR este pedido permanentemente? Esta acción no se puede deshacer.'
      : '¿Seguro que deseas cambiar el estado del pedido?';

    if (!confirm(confirmMsg)) return;

    try {
      const res = await fetch('/api/admin/orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, action })
      });
      const json = await res.json();
      if (json.success) {
        if (fromModal) {
          closeModal();
        }
        await fetchOrders(page);
      } else {
        alert(json.error || 'Error al actualizar');
      }
    } catch (err) {
      alert(err.message);
    }
  }

  function closeModal() {
    modalBackdrop.classList.remove('active');
    modalBackdrop.innerHTML = '';
  }

  async function openOrderDetail(orderId) {
    let order = orders.find(o => parseInt(o.id, 10) === parseInt(orderId, 10));

    if (!order || !order.items) {
      try {
        const res = await fetch(`/api/admin/orders.php?id=${orderId}`);
        const json = await res.json();
        if (json.success) {
          order = json.data;
        } else {
          alert(json.error || 'Error al cargar el detalle del pedido.');
          return;
        }
      } catch (err) {
        alert('Error de conexión al obtener el detalle: ' + err.message);
        return;
      }
    }

    const orderNum = `#${order.id.toString().padStart(5, '0')}`;
    const dateStr = new Date(order.created_at).toLocaleString('es-UY');
    const totalFormatted = parseFloat(order.total).toLocaleString('es-UY');

    const rawPhone = (order.customer_phone || '').replace(/\D/g, '');
    let waPhone = rawPhone;
    if (!waPhone.startsWith('598')) {
      if (waPhone.startsWith('0')) {
        waPhone = '598' + waPhone.substring(1);
      } else {
        waPhone = '598' + waPhone;
      }
    }
    const waText = encodeURIComponent(`Hola ${order.customer_name}, te contactamos de Repuestos del Litoral por tu pedido ${orderNum}.`);
    const waUrl = `https://wa.me/${waPhone}?text=${waText}`;

    // Construir lista de productos
    let itemsRows = '';
    if (!order.items || order.items.length === 0) {
      itemsRows = `<tr><td colspan="5" style="text-align: center; color: #777;">No se registraron productos en esta orden.</td></tr>`;
    } else {
      itemsRows = order.items.map(item => {
        const unitPrice = parseFloat(item.unit_price || 0);
        const qty = parseInt(item.quantity || 1, 10);
        const subtotal = unitPrice * qty;
        const prodName = item.product_name ? item.product_name : `Producto #${item.product_id}`;
        const prodCode = item.product_code || '';
        const imgPath = item.product_image ? item.product_image : '';
        
        let imgHtml = `<div class="order-item-img" style="display:flex;align-items:center;justify-content:center;font-size:1.2rem;">📦</div>`;
        if (imgPath) {
          const src = imgPath.startsWith('/') ? imgPath : '/' + imgPath;
          imgHtml = `<img src="${src}" alt="Producto" class="order-item-img">`;
        }

        const codeSpan = prodCode ? `<span class="order-item-code">Cód: ${prodCode}</span>` : '';

        return `
          <tr>
            <td>
              <div class="order-item-prod">
                ${imgHtml}
                <div class="order-item-details">
                  <span class="order-item-title">${prodName}</span>
                  ${codeSpan}
                </div>
              </div>
            </td>
            <td>${prodCode || '-'}</td>
            <td>$U ${unitPrice.toLocaleString('es-UY')}</td>
            <td><strong>${qty}</strong></td>
            <td><strong>$U ${subtotal.toLocaleString('es-UY')}</strong></td>
          </tr>
        `;
      }).join('');
    }

    // Botones de acción según el estado
    let modalStatusBtns = '';
    if (order.status === 'pendiente' || order.status === 'pagado') {
      modalStatusBtns = `
        <button class="action-btn btn-finalize modal-action-btn" data-id="${order.id}" data-action="finalize">✓ Finalizar Pedido</button>
        <button class="action-btn btn-cancel modal-action-btn" data-id="${order.id}" data-action="cancel">✕ Cancelar</button>
      `;
    } else if (order.status === 'finalizado') {
      modalStatusBtns = `
        <button class="action-btn btn-pending modal-action-btn" data-id="${order.id}" data-action="pending">↺ Reabrir Pedido</button>
        <button class="action-btn btn-cancel modal-action-btn" data-id="${order.id}" data-action="cancel">✕ Cancelar</button>
      `;
    } else if (order.status === 'cancelado') {
      modalStatusBtns = `
        <button class="action-btn btn-pending modal-action-btn" data-id="${order.id}" data-action="pending">↺ Restablecer Pedido</button>
      `;
    }

    const tierLabel = order.price_tier === 'wholesale' ? '🏷️ Mayorista' : '🛒 Minorista';
    const addressLabel = order.customer_address ? order.customer_address : '🏠 Retira en local';
    const paymentLabel = order.payment_method === 'mercado_pago' ? '💳 Mercado Pago' : '💬 WhatsApp';
    const extRefHtml = order.external_reference ? `<div class="order-info-item"><strong>Ref. Externa:</strong> <code>${order.external_reference}</code></div>` : '';
    const statusClass = order.status ? order.status.replace(/_/g, '-') : 'pendiente';
    const itemCount = order.items ? order.items.length : 0;

    modalBackdrop.innerHTML = `
      <div class="order-modal-container">
        <div class="order-modal-header">
          <h2>📦 Detalle de Pedido ${orderNum}</h2>
          <button class="modal-close-btn" id="btn-close-modal-x">&times;</button>
        </div>
        
        <div class="order-modal-body">
          <div class="order-info-grid">
            <div class="order-info-card">
              <h4>👤 Información del Cliente</h4>
              <div class="order-info-item"><strong>Nombre:</strong> ${order.customer_name}</div>
              <div class="order-info-item"><strong>Teléfono:</strong> <a href="tel:${order.customer_phone}" style="color:inherit;">${order.customer_phone}</a></div>
              <div class="order-info-item"><strong>Email:</strong> ${order.customer_email || 'No proporcionado'}</div>
              <div class="order-info-item"><strong>Tipo de Tarifa:</strong> ${tierLabel}</div>
            </div>

            <div class="order-info-card">
              <h4>📍 Entrega y Pago</h4>
              <div class="order-info-item"><strong>Dirección:</strong> ${addressLabel}</div>
              <div class="order-info-item"><strong>Método Pago:</strong> ${paymentLabel}</div>
              <div class="order-info-item"><strong>Estado:</strong> <span class="status-badge status-${statusClass}">${order.status}</span></div>
              <div class="order-info-item"><strong>Fecha:</strong> ${dateStr}</div>
              ${extRefHtml}
            </div>
          </div>

          <div>
            <div class="modal-section-title">🛍️ Productos Pedidos (${itemCount})</div>
            <table class="order-items-table">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Código</th>
                  <th>Precio U.</th>
                  <th>Cant.</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                ${itemsRows}
              </tbody>
            </table>
          </div>

          <div class="order-summary-box">
            <span class="order-summary-total-label">TOTAL DEL PEDIDO:</span>
            <span class="order-summary-total-val">$U ${totalFormatted}</span>
          </div>
        </div>

        <div class="order-modal-footer">
          <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
            <a href="${waUrl}" target="_blank" rel="noopener noreferrer" class="btn-whatsapp">
              💬 Enviar WhatsApp al Cliente
            </a>
            ${modalStatusBtns}
          </div>
          <button class="action-btn btn-close-modal" id="btn-close-modal-bottom">Cerrar</button>
        </div>
      </div>
    `;

    modalBackdrop.classList.add('active');

    modalBackdrop.querySelector('#btn-close-modal-x')?.addEventListener('click', closeModal);
    modalBackdrop.querySelector('#btn-close-modal-bottom')?.addEventListener('click', closeModal);

    modalBackdrop.querySelectorAll('.modal-action-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.currentTarget.dataset.id;
        const act = e.currentTarget.dataset.action;
        updateStatus(id, act, true);
      });
    });

    const backdropClickHandler = (e) => {
      if (e.target === modalBackdrop) {
        closeModal();
        modalBackdrop.removeEventListener('click', backdropClickHandler);
      }
    };
    modalBackdrop.addEventListener('click', backdropClickHandler);
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
        let actionBtn = `<button class="action-btn btn-view" data-id="${o.id}" title="Ver detalle de la orden">👁️ Detalle</button>`;

        if (o.status === 'pendiente' || o.status === 'pagado') {
          actionBtn += `
            <button class="action-btn btn-finalize" data-id="${o.id}" data-action="finalize">Finalizar</button>
            <button class="action-btn btn-cancel" data-id="${o.id}" data-action="cancel">Cancelar</button>
            <button class="action-btn btn-delete" data-id="${o.id}" data-action="delete" title="Eliminar pedido permanentemente">Eliminar</button>
          `;
        } else if (o.status === 'finalizado') {
          actionBtn += `
            <button class="action-btn btn-pending" data-id="${o.id}" data-action="pending">Reabrir</button>
            <button class="action-btn btn-cancel" data-id="${o.id}" data-action="cancel">Cancelar</button>
            <button class="action-btn btn-delete" data-id="${o.id}" data-action="delete" title="Eliminar pedido permanentemente">Eliminar</button>
          `;
        } else if (o.status === 'cancelado') {
          actionBtn += `
            <button class="action-btn btn-pending" data-id="${o.id}" data-action="pending">Restablecer</button>
            <button class="action-btn btn-delete" data-id="${o.id}" data-action="delete" title="Eliminar pedido permanentemente">Eliminar</button>
          `;
        }

        const dateStr = new Date(o.created_at).toLocaleString('es-UY');
        const formattedId = `#${o.id.toString().padStart(5, '0')}`;
        const totalVal = `$U ${parseFloat(o.total).toLocaleString('es-UY')}`;
        const payMethod = o.payment_method === 'mercado_pago' ? '💳 Mercado Pago' : '💬 WhatsApp';
        const statusClass = o.status ? o.status.replace(/_/g, '-') : 'pendiente';

        return `
          <tr>
            <td>
              <a href="#" class="order-id-link" data-id="${o.id}">${formattedId}</a>
            </td>
            <td>
              <strong>${o.customer_name}</strong><br>
              <small>${o.customer_phone}</small><br>
              <small>${o.customer_email || ''}</small>
            </td>
            <td><strong>${totalVal}</strong></td>
            <td>${payMethod}</td>
            <td><span class="status-badge status-${statusClass}">${o.status}</span></td>
            <td>${dateStr}</td>
            <td>
              <div class="actions-group">
                ${actionBtn}
              </div>
            </td>
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

    container.querySelectorAll('.action-btn[data-action]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        updateStatus(e.currentTarget.dataset.id, e.currentTarget.dataset.action);
      });
    });

    container.querySelectorAll('.btn-view, .order-id-link').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        openOrderDetail(e.currentTarget.dataset.id);
      });
    });

    container.querySelectorAll('.page-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        fetchOrders(parseInt(e.currentTarget.dataset.page, 10));
      });
    });
  }

  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modalBackdrop.classList.contains('active')) {
      closeModal();
    }
  });

  return { init: () => fetchOrders(1) };
}


