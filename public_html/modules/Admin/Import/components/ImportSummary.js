export function renderSummary(containerId, results, onReset) {
  const container = document.getElementById(containerId);
  if (!container) return;

  container.innerHTML = `
    <div class="import-step" id="step-summary">
      <h3>🎉 Importación Completada</h3>
      
      <p>El catálogo fue actualizado exitosamente con los siguientes resultados:</p>

      <div class="summary-stats">
        <div class="stat-box success">
          <span class="num">${results.created}</span>
          Nuevos creados
        </div>
        <div class="stat-box warning">
          <span class="num">${results.updated}</span>
          Actualizados
        </div>
        <div class="stat-box danger">
          <span class="num">${results.failed}</span>
          Errores
        </div>
      </div>
      
      <div style="text-align: center; margin-top: 2rem;">
        <button id="btn-reset-import" class="admin-btn admin-btn-primary">Volver al inicio</button>
      </div>
    </div>
  `;

  document.getElementById('btn-reset-import').addEventListener('click', onReset);
}
