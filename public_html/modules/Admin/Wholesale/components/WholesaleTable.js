export function WholesaleTable(clients) {
  if (!clients || clients.length === 0) {
    return '<p>No se encontraron clientes.</p>';
  }

  const rows = clients.map(c => {
    const isApproved = c.approved === 1;
    const statusBadge = isApproved
      ? '<span class="admin-badge success">Aprobado</span>'
      : '<span class="admin-badge warning">Pendiente</span>';

    return `
      <tr>
        <td><strong>${c.business_name}</strong></td>
        <td>${c.name}</td>
        <td>${c.email}</td>
        <td>${c.phone || '-'}</td>
        <td>${statusBadge}</td>
        <td>${c.created_at ? new Date(c.created_at.includes(' ') ? c.created_at.replace(' ', 'T') : c.created_at).toLocaleDateString('es-UY', { timeZone: 'America/Montevideo' }) : '-'}</td>
        <td>
          ${isApproved 
            ? `<button type="button" class="admin-btn danger btn-revoke" data-id="${c.id}" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Revocar</button>`
            : `<button type="button" class="admin-btn btn-approve" data-id="${c.id}" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Aprobar</button>`
          }
        </td>
      </tr>
    `;
  }).join('');

  return `
    <div class="admin-table-wrapper">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Empresa / Negocio</th>
            <th>Contacto</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Estado</th>
            <th>Registro</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          ${rows}
        </tbody>
      </table>
    </div>
  `;
}
