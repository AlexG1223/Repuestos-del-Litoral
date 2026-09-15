<?php
require_once __DIR__ . '/../../private/services/SessionService.php';
require_once __DIR__ . '/../../private/services/AdminGuard.php';

\RepuestosDelLitoral\Services\AdminGuard::requirePage();

$pageTitle = "Generador de Links UTM - Repuestos del Litoral";
$preloadProductId = $_GET['product_id'] ?? $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  
  <link rel="icon" href="/assets/img/logo.png" type="image/x-icon">
  <link rel="stylesheet" href="/globals/main.css">
  <link rel="stylesheet" href="/modules/Admin/shared/styles/admin-layout.css">
  <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
  
  <style>
    .utm-container {
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 1.5rem;
    }
    @media (max-width: 992px) {
      .utm-container {
        grid-template-columns: 1fr;
      }
    }
    .preset-chips {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-bottom: 1.5rem;
    }
    .preset-chip {
      background: #eef2f7;
      border: 1px solid #cbd5e1;
      padding: 0.4rem 0.8rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      color: #334155;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .preset-chip:hover, .preset-chip.active {
      background: var(--color-primary, #f39c12);
      color: #000;
      border-color: var(--color-primary, #f39c12);
    }
    .result-box {
      background: #0f172a;
      color: #f8fafc;
      padding: 1.25rem;
      border-radius: 8px;
      margin-top: 1.5rem;
      position: relative;
    }
    .result-url {
      font-family: monospace;
      font-size: 0.95rem;
      word-break: break-all;
      background: #1e293b;
      padding: 0.75rem;
      border-radius: 6px;
      border: 1px solid #334155;
      color: #38bdf8;
      margin-bottom: 1rem;
    }
    .utm-breakdown {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 0.5rem;
      margin-top: 1rem;
      font-size: 0.82rem;
    }
    .utm-item {
      background: rgba(255,255,255,0.05);
      padding: 0.5rem;
      border-radius: 4px;
      border-left: 3px solid var(--color-primary, #f39c12);
    }
    .utm-item span {
      display: block;
      color: #94a3b8;
      font-weight: 700;
      font-size: 0.75rem;
    }
    .copy-toast {
      display: none;
      position: absolute;
      top: 10px;
      right: 15px;
      background: #10b981;
      color: white;
      padding: 0.25rem 0.6rem;
      border-radius: 4px;
      font-size: 0.8rem;
      font-weight: bold;
    }
    .cheatsheet-card {
      background: #fff;
      border-radius: 8px;
      padding: 1.25rem;
      border: 1px solid #e2e8f0;
    }
    .cheatsheet-card h3 {
      margin-top: 0;
      font-size: 1.05rem;
      color: #0f172a;
      border-bottom: 2px solid var(--color-primary, #f39c12);
      padding-bottom: 0.4rem;
    }
    .cheatsheet-list {
      list-style: none;
      padding: 0;
      margin: 0;
      font-size: 0.85rem;
    }
    .cheatsheet-list li {
      margin-bottom: 0.75rem;
    }
    .cheatsheet-list code {
      background: #f1f5f9;
      color: #0f172a;
      padding: 0.1rem 0.3rem;
      border-radius: 3px;
      font-weight: bold;
    }
    .product-preview-card {
      display: flex;
      align-items: center;
      gap: 1rem;
      background: #ffffff;
      border: 1px solid #94a3b8;
      padding: 0.75rem;
      border-radius: 6px;
      margin-top: 0.75rem;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .product-preview-card img {
      width: 50px;
      height: 50px;
      object-fit: cover;
      border-radius: 4px;
    }
    .product-preview-info {
      font-size: 0.85rem;
    }
    .product-preview-info strong {
      display: block;
      color: #0f172a;
    }
    .product-search-results {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: #ffffff;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
      max-height: 280px;
      overflow-y: auto;
      z-index: 100;
      margin-top: 4px;
    }
    .product-search-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.6rem 0.8rem;
      border-bottom: 1px solid #f1f5f9;
      cursor: pointer;
      transition: background 0.15s ease;
    }
    .product-search-item:last-child {
      border-bottom: none;
    }
    .product-search-item:hover {
      background: #f8fafc;
    }
    .product-search-item img {
      width: 38px;
      height: 38px;
      object-fit: cover;
      border-radius: 4px;
    }
    .product-search-item-info {
      font-size: 0.85rem;
    }
    .product-search-item-info strong {
      display: block;
      color: #0f172a;
    }
    .product-search-item-info span {
      color: #64748b;
      font-size: 0.78rem;
    }
  </style>
</head>
<body class="admin-body">

  <div id="admin-sidebar-root"></div>

  <main class="admin-main">
    <header class="admin-header">
      <h1>🔗 Generador de Links UTM (Atribución O1-KR2, O2-KR2)</h1>
    </header>

    <div class="admin-content">
      <div class="utm-container">
        
        <!-- Formulario Principal -->
        <div class="admin-card">
          <h2>Construir Enlace para Compartir</h2>
          <p class="text-muted">Busca un producto del catálogo o ingresa una URL. Elige el canal (Instagram Bio, Historia, Meta Ads, WhatsApp) y obtén tu link de atribución listo.</p>

          <!-- Buscador Interactivo de Productos -->
          <div class="admin-form-group" style="background: #f1f5f9; padding: 1.25rem; border-radius: 8px; border: 1px solid #cbd5e1; position: relative;">
            <label for="product_search_input" style="color:#0f172a; font-weight:700; display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
              <span>🔍 Buscador de Productos en el Catálogo:</span>
            </label>
            
            <div style="position: relative;">
              <input type="text" id="product_search_input" class="admin-form-control" placeholder="Escribe para buscar por código o nombre (ej: Motosierra, Stihl, Cadena)..." autocomplete="off" style="padding-left: 2.2rem;">
              <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #64748b;">🔍</span>
              
              <!-- Autocomplete Results Dropdown -->
              <div id="product-search-results" class="product-search-results" style="display:none;"></div>
            </div>

            <!-- Desplegable Tradicional Opcional -->
            <details style="margin-top: 0.75rem;">
              <summary style="font-size: 0.8rem; color: #64748b; cursor: pointer;">O seleccionar de la lista completa desplegable</summary>
              <select id="product_select" class="admin-form-control" style="margin-top:0.5rem;">
                <option value="">-- Cargar producto específico --</option>
              </select>
            </details>

            <!-- Tarjeta de Producto Seleccionado -->
            <div id="product-preview" class="product-preview-card" style="display:none;">
              <img id="product-img" src="" alt="Producto">
              <div class="product-preview-info" style="flex:1;">
                <strong id="product-title">-</strong>
                <span id="product-meta" style="color:#64748b;">-</span>
              </div>
              <button type="button" class="admin-btn secondary" style="padding:0.25rem 0.5rem; font-size:0.75rem; background:#e2e8f0; color:#334155;" onclick="clearProductSelection()">✕ Quitar</button>
            </div>
          </div>

          <label class="admin-form-group" style="display:block; margin-top:1.5rem;">
            <span style="font-weight:700; font-size:0.9rem; color:#475569;">Presets de Canal Rápido:</span>
          </label>
          <div class="preset-chips">
            <button type="button" class="preset-chip active" onclick="applyPreset('ig-story')">📲 Instagram Historia</button>
            <button type="button" class="preset-chip" onclick="applyPreset('ig-bio')">📸 Instagram Bio</button>
            <button type="button" class="preset-chip" onclick="applyPreset('ig-post')">🖼️ Instagram Post</button>
            <button type="button" class="preset-chip" onclick="applyPreset('meta-ad')">🎯 Meta Ads (Facebook/IG)</button>
            <button type="button" class="preset-chip" onclick="applyPreset('whatsapp')">💬 WhatsApp Chat</button>
            <button type="button" class="preset-chip" onclick="applyPreset('email')">✉️ Email Newsletter</button>
          </div>

          <form id="utm-form" onsubmit="event.preventDefault();">
            <div class="admin-form-group">
              <label for="target_url">URL Destino *</label>
              <input type="url" id="target_url" class="admin-form-control" value="https://repuestosdellitoral.com/" required placeholder="https://repuestosdellitoral.com/producto.php?slug=...">
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
              <div class="admin-form-group">
                <label for="utm_source">UTM Source (Fuente) *</label>
                <select id="utm_source" class="admin-form-control" required>
                  <option value="instagram">instagram</option>
                  <option value="facebook">facebook</option>
                  <option value="whatsapp">whatsapp</option>
                  <option value="google">google</option>
                  <option value="email">email</option>
                  <option value="tiktok">tiktok</option>
                  <option value="mercadolibre">mercadolibre</option>
                </select>
              </div>

              <div class="admin-form-group">
                <label for="utm_medium">UTM Medium (Medio) *</label>
                <select id="utm_medium" class="admin-form-control" required>
                  <option value="organic-social">organic-social</option>
                  <option value="paid-social">paid-social</option>
                  <option value="bio-link">bio-link</option>
                  <option value="chat-organic">chat-organic</option>
                  <option value="chat-automated">chat-automated</option>
                  <option value="email-newsletter">email-newsletter</option>
                  <option value="cpc">cpc</option>
                </select>
              </div>
            </div>

            <div class="admin-form-group">
              <label for="utm_campaign">UTM Campaign (Campaña) * <small style="color:#64748b; font-weight:normal;">(Formato: mes_nombre)</small></label>
              <input type="text" id="utm_campaign" class="admin-form-control" value="setiembre_arranque" required placeholder="ej: setiembre_arranque">
            </div>

            <div class="admin-form-group">
              <label for="utm_content">UTM Content (Detalle de Producto / Pieza) <small style="color:#64748b; font-weight:normal;">(Opcional)</small></label>
              <input type="text" id="utm_content" class="admin-form-control" placeholder="ej: historia_motosierra_stihl_170">
            </div>

            <div class="admin-form-group">
              <label for="utm_term">UTM Term (Palabra Clave) <small style="color:#64748b; font-weight:normal;">(Opcional para Ads)</small></label>
              <input type="text" id="utm_term" class="admin-form-control" placeholder="ej: repuestos-motosierra">
            </div>
          </form>

          <!-- Resultado Generado -->
          <div class="result-box">
            <span id="copy-toast" class="copy-toast">✓ Copiado al portapapeles</span>
            <div style="font-weight:700; margin-bottom:0.5rem; color:#94a3b8; font-size:0.85rem;">LINK FORMATEADO PARA COMPARTIR:</div>
            <div id="result-url" class="result-url">https://repuestosdellitoral.com/</div>
            
            <div style="display:flex; gap:0.5rem;">
              <button type="button" class="admin-btn" onclick="copyUtmUrl()">📋 Copiar Link</button>
              <a id="test-url-btn" href="#" target="_blank" class="admin-btn secondary" style="background:#334155; color:#fff;">🔗 Probar Link</a>
            </div>

            <div class="utm-breakdown">
              <div class="utm-item"><span>SOURCE</span><strong id="bd-source">instagram</strong></div>
              <div class="utm-item"><span>MEDIUM</span><strong id="bd-medium">organic-social</strong></div>
              <div class="utm-item"><span>CAMPAIGN</span><strong id="bd-campaign">setiembre_arranque</strong></div>
              <div class="utm-item"><span>CONTENT</span><strong id="bd-content">-</strong></div>
            </div>
          </div>
        </div>

        <!-- Sidebar Informativo / Guía -->
        <div>
          <div class="cheatsheet-card">
            <h3>📲 Compartir Productos en Historias</h3>
            <ul class="cheatsheet-list">
              <li><b>1. Busca el producto:</b> Escribe su nombre o código en el buscador interactivo.</li>
              <li><b>2. Elige el preset:</b> Si es para Historia de Instagram, presiona <code>📲 Instagram Historia</code>.</li>
              <li><b>3. Copia el link:</b> Presiona <code>📋 Copiar Link</code>.</li>
              <li><b>4. En Instagram:</b> Agrega el sticker de <b>Enlace</b> en tu Historia, pega la URL y pon un texto atractivo como <i>"Ver Producto"</i> o <i>"Comprar Ahora"</i>.</li>
            </ul>

            <div style="margin-top:1.5rem; padding:0.8rem; background:#f8fafc; border-left:3px solid #0284c7; border-radius:4px; font-size:0.82rem;">
              <strong>🎯 Atribución Garantizada:</strong><br>
              Cada compra que resulte de este enlace se registrará en GA4 indicando exactamente qué producto y campaña generó la venta.
            </div>
          </div>

          <!-- Dónde ver Estadísticas Card -->
          <div class="cheatsheet-card" style="margin-top:1.5rem;">
            <h3>📊 ¿Dónde ver las Estadísticas?</h3>
            <p style="font-size:0.85rem; color:#475569; margin-bottom:0.75rem;">
              Toda la analítica de tus enlaces UTM se rastrea automáticamente en <b>Google Analytics 4 (GA4)</b> y redes sociales:
            </p>
            
            <ul class="cheatsheet-list" style="font-size:0.82rem;">
              <li><b>1. Tráfico y Clics (CTR) por Campaña:</b><br>
              En GA4 -> <i>Informes -> Adquisición -> Adquisición de Tráfico</i>.<br>
              Selecciona la dimensión <code>Campaña de la sesión</code> o <code>Fuente / medio de la sesión</code>.</li>
              
              <li><b>2. Ventas y Conversiones por Canal (O2-KR2):</b><br>
              En GA4 -> <i>Publicidad -> Rutas de conversión</i> o en <i>Explorar -> Compras por Campaña</i>.</li>

              <li><b>3. Clics en Historias de Instagram:</b><br>
              En Instagram -> Abre la Historia -> Desliza hacia arriba para ver las métricas del sticker <i>"Clics en el enlace"</i>.</li>
            </ul>

            <div style="margin-top:1rem;">
              <a href="https://analytics.google.com/" target="_blank" class="admin-btn secondary" style="display:flex; width:100%; text-align:center; justify-content:center; font-size:0.8rem; background:#f1f5f9; color:#0f172a; text-decoration:none;">↗️ Abrir Google Analytics 4</a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>

  <script type="module">
    import { renderAdminSidebar } from '/modules/Admin/shared/components/AdminSidebar.js';
    renderAdminSidebar(window.location.pathname);
  </script>

  <script>
    const PRELOAD_PRODUCT_ID = <?= json_encode($preloadProductId) ?>;
    let productsList = [];

    function sanitizeParam(str, replaceSpaceWith = '_') {
      if (!str) return '';
      return str.trim()
        .toLowerCase()
        .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9\-_]/g, replaceSpaceWith)
        .replace(new RegExp(`\\${replaceSpaceWith}+`, 'g'), replaceSpaceWith);
    }

    async function loadProductsCatalog() {
      try {
        const res = await fetch('/api/products.php?per_page=200');
        const json = await res.json();
        if (json.success && Array.isArray(json.data)) {
          productsList = json.data;
          populateProductSelect();
          if (PRELOAD_PRODUCT_ID) {
            selectProductById(PRELOAD_PRODUCT_ID);
          }
        }
      } catch (e) {
        console.error('Error cargando productos:', e);
      }
    }

    function populateProductSelect() {
      const select = document.getElementById('product_select');
      select.innerHTML = '<option value="">-- Cargar producto específico --</option>';

      productsList.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = `${p.code ? '[' + p.code + '] ' : ''}${p.name} - $${p.retail_price}`;
        select.appendChild(opt);
      });
    }

    function selectProductById(id) {
      const select = document.getElementById('product_select');
      select.value = id;
      onProductSelect(id);
    }

    function onProductSelect(productId) {
      if (!productId) {
        clearProductSelection();
        return;
      }

      const p = productsList.find(item => String(item.id) === String(productId));
      if (!p) return;

      // Actualizar buscador input con nombre
      document.getElementById('product_search_input').value = `${p.code ? '[' + p.code + '] ' : ''}${p.name}`;
      document.getElementById('product-search-results').style.display = 'none';

      // Actualizar tarjeta de previsualización
      document.getElementById('product-preview').style.display = 'flex';
      document.getElementById('product-img').src = p.primary_image || '/assets/img/logo.png';
      document.getElementById('product-title').textContent = p.name;
      document.getElementById('product-meta').textContent = `Código: ${p.code || 'N/A'} | Categoría: ${p.category_name || 'General'} | Precio: $${p.retail_price}`;

      // Construir URL del producto
      const origin = window.location.origin;
      const productSlug = p.slug ? p.slug : '';
      const productUrl = productSlug 
        ? `${origin}/producto.php?slug=${encodeURIComponent(productSlug)}`
        : `${origin}/producto.php?id=${p.id}`;

      document.getElementById('target_url').value = productUrl;

      // Sugerir utm_content automático basado en el producto
      const currentPreset = document.querySelector('.preset-chip.active')?.textContent || '';
      let prefix = 'historia';
      if (currentPreset.includes('Bio')) prefix = 'bio';
      else if (currentPreset.includes('Post')) prefix = 'post';
      else if (currentPreset.includes('Ads')) prefix = 'ad';
      else if (currentPreset.includes('WhatsApp')) prefix = 'chat';

      const cleanName = sanitizeParam(p.name, '_');
      document.getElementById('utm_content').value = `${prefix}_${cleanName.slice(0, 30)}`;

      updateUtmUrl();
    }

    function clearProductSelection() {
      document.getElementById('product_select').value = '';
      document.getElementById('product_search_input').value = '';
      document.getElementById('product-preview').style.display = 'none';
      document.getElementById('target_url').value = window.location.origin + '/';
      document.getElementById('utm_content').value = '';
      updateUtmUrl();
    }

    // Buscador interactivo en tiempo real
    const searchInput = document.getElementById('product_search_input');
    const resultsContainer = document.getElementById('product-search-results');

    searchInput.addEventListener('input', (e) => {
      const query = e.target.value.toLowerCase().trim();
      if (!query) {
        resultsContainer.style.display = 'none';
        return;
      }

      const matches = productsList.filter(p => {
        const nameMatch = (p.name || '').toLowerCase().includes(query);
        const codeMatch = (p.code || '').toLowerCase().includes(query);
        const catMatch = (p.category_name || '').toLowerCase().includes(query);
        return nameMatch || codeMatch || catMatch;
      });

      if (matches.length === 0) {
        resultsContainer.innerHTML = '<div style="padding:0.75rem; color:#64748b; font-size:0.85rem;">No se encontraron productos que coincidan.</div>';
        resultsContainer.style.display = 'block';
        return;
      }

      resultsContainer.innerHTML = matches.slice(0, 10).map(p => `
        <div class="product-search-item" data-id="${p.id}">
          <img src="${p.primary_image || '/assets/img/logo.png'}" alt="${p.name}">
          <div class="product-search-item-info">
            <strong>${p.name}</strong>
            <span>Código: ${p.code || 'N/A'} | Categoría: ${p.category_name || 'General'} | Precio: $${p.retail_price}</span>
          </div>
        </div>
      `).join('');

      resultsContainer.style.display = 'block';

      // Event listeners para items
      resultsContainer.querySelectorAll('.product-search-item').forEach(item => {
        item.addEventListener('click', () => {
          const id = item.getAttribute('data-id');
          selectProductById(id);
        });
      });
    });

    document.addEventListener('click', (e) => {
      if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
        resultsContainer.style.display = 'none';
      }
    });

    function updateUtmUrl() {
      const baseUrlInput = document.getElementById('target_url').value.trim() || window.location.origin + '/';
      const source = sanitizeParam(document.getElementById('utm_source').value, '-');
      const medium = sanitizeParam(document.getElementById('utm_medium').value, '-');
      const campaign = sanitizeParam(document.getElementById('utm_campaign').value, '_');
      const content = sanitizeParam(document.getElementById('utm_content').value, '_');
      const term = sanitizeParam(document.getElementById('utm_term').value, '-');

      try {
        const urlObj = new URL(baseUrlInput);
        if (source) urlObj.searchParams.set('utm_source', source);
        if (medium) urlObj.searchParams.set('utm_medium', medium);
        if (campaign) urlObj.searchParams.set('utm_campaign', campaign);
        if (content) urlObj.searchParams.set('utm_content', content); else urlObj.searchParams.delete('utm_content');
        if (term) urlObj.searchParams.set('utm_term', term); else urlObj.searchParams.delete('utm_term');

        const finalUrl = urlObj.toString();
        document.getElementById('result-url').textContent = finalUrl;
        document.getElementById('test-url-btn').href = finalUrl;

        document.getElementById('bd-source').textContent = source || '-';
        document.getElementById('bd-medium').textContent = medium || '-';
        document.getElementById('bd-campaign').textContent = campaign || '-';
        document.getElementById('bd-content').textContent = content || '-';
      } catch (e) {
        document.getElementById('result-url').textContent = 'URL base inválida';
      }
    }

    function applyPreset(type) {
      document.querySelectorAll('.preset-chip').forEach(btn => btn.classList.remove('active'));
      event.target.classList.add('active');

      const sourceEl = document.getElementById('utm_source');
      const mediumEl = document.getElementById('utm_medium');

      if (type === 'ig-bio') {
        sourceEl.value = 'instagram';
        mediumEl.value = 'bio-link';
      } else if (type === 'ig-story') {
        sourceEl.value = 'instagram';
        mediumEl.value = 'organic-social';
      } else if (type === 'ig-post') {
        sourceEl.value = 'instagram';
        mediumEl.value = 'organic-social';
      } else if (type === 'meta-ad') {
        sourceEl.value = 'facebook';
        mediumEl.value = 'paid-social';
      } else if (type === 'whatsapp') {
        sourceEl.value = 'whatsapp';
        mediumEl.value = 'chat-organic';
      } else if (type === 'email') {
        sourceEl.value = 'email';
        mediumEl.value = 'email-newsletter';
      }

      const selectedProductId = document.getElementById('product_select').value;
      if (selectedProductId) {
        onProductSelect(selectedProductId);
      } else {
        updateUtmUrl();
      }
    }

    function copyUtmUrl() {
      const urlText = document.getElementById('result-url').textContent;
      if (!urlText || urlText.includes('inválida')) return;

      navigator.clipboard.writeText(urlText).then(() => {
        const toast = document.getElementById('copy-toast');
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 2000);
      });
    }

    document.getElementById('product_select').addEventListener('change', (e) => {
      onProductSelect(e.target.value);
    });

    document.querySelectorAll('#utm-form input, #utm-form select').forEach(el => {
      el.addEventListener('input', updateUtmUrl);
      el.addEventListener('change', updateUtmUrl);
    });

    window.clearProductSelection = clearProductSelection;

    // Inicializar catálogo al cargar
    loadProductsCatalog();
  </script>
</body>
</html>
