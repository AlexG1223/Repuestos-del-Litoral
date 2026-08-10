export function renderRawPreview(containerId, rawText, totalRows, onContinue) {
  const container = document.getElementById(containerId);
  if (!container) return;

  container.innerHTML = `
    <div class="import-step" id="step-raw">
      <h3>2. Vista Previa de Texto Crudo</h3>
      
      <div style="background: #fff3cd; color: #856404; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
        <strong>¡Atención!</strong> Revisá que esto tenga sentido antes de continuar. El sistema identificó <strong>${totalRows}</strong> posibles productos basándose en el patrón configurado. Si el texto se ve desordenado, incompleto o el total de productos no tiene sentido, avisale a Alex para ajustar el patrón de extracción en <code>import-pattern.php</code>.
      </div>

      <p>Primeras líneas del PDF extraído:</p>
      <pre class="raw-preview">${escapeHtml(rawText)}</pre>
      
      <div style="margin-top: 1.5rem;">
        <button id="btn-continue-preview" class="admin-btn admin-btn-primary">Continuar a vista de productos (${totalRows})</button>
      </div>
    </div>
  `;

  document.getElementById('btn-continue-preview').addEventListener('click', onContinue);
}

function escapeHtml(unsafe) {
    return (unsafe || '').replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}
