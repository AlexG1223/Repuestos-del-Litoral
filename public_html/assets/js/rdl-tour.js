/**
 * Guía interactiva de bienvenida (onboarding) para repuestosdellitoral.com
 * Archivo: /assets/js/rdl-tour.js
 * JavaScript puro (vanilla), sin librerías externas, encapsulado en Shadow DOM.
 * 
 * NOTA: La guía se ejecuta EXCLUSIVAMENTE en la página de Tienda / Catálogo (/index.php)
 * para asegurar que resalte el catálogo real, sus filtros y sus productos.
 */
(function () {
  'use strict';

  try {
    // Configuración de los pasos de la guía
    const STORAGE_KEY = 'rdl_tour_v1';
    const DISPLAY_DELAY_MS = 1200;

    const TOUR_STEPS_CONFIG = [
      {
        selector: null,
        title: '¡Bienvenido a Repuestos del Litoral!',
        text: 'Te mostramos en 30 segundos cómo encontrar lo que necesitás. Si ya sabés, podés omitirlo.'
      },
      {
        selector: '#catalog-search-input',
        title: 'Buscá por nombre o modelo',
        text: '¿Sabés qué repuesto necesitás? Escribilo acá y lo encontrás al toque.'
      },
      {
        selector: '#catalog-root .category-pills-wrapper, #catalog-root .category-pills',
        title: 'Navegá por categoría',
        text: 'Motosierras, desmalezadoras, repuestos y más, todo ordenado.'
      },
      {
        selector: '#catalog-root .product-card',
        title: 'Mirá el detalle',
        text: 'Tocá cualquier producto para ver fotos, precio y características.'
      },
      {
        selector: '#btn-header-cart',
        title: 'Tu carrito',
        text: 'Acá quedan tus productos. Pagás seguro con Mercado Pago y te lo enviamos a todo Uruguay.'
      },
      {
        selector: '.whatsapp-float',
        title: '¿Dudas? Escribinos',
        text: 'Si no sabés qué modelo te sirve, consultanos por WhatsApp y te asesoramos.'
      }
    ];

    // Variables de estado del Tour
    let activeSteps = [];
    let currentActiveIndex = 0;
    let tourHost = null;
    let shadowRoot = null;
    let spotlightEl = null;
    let backdropEl = null;
    let popoverEl = null;
    let arrowEl = null;
    let titleEl = null;
    let descEl = null;
    let counterEl = null;
    let dotsContainerEl = null;
    let btnNextEl = null;
    let btnPrevEl = null;
    let btnSkipEl = null;
    let btnCloseEl = null;
    let previouslyFocusedElement = null;
    let isTourRunning = false;
    let resizeObserver = null;
    let rafId = null;
    let savedBodyOverflow = '';
    let savedBodyPaddingRight = '';

    // Manejo seguro de Storage (localStorage con fallback a sessionStorage)
    function getStorage() {
      try {
        if (typeof window !== 'undefined' && window.localStorage) {
          const testKey = '__rdl_test__';
          window.localStorage.setItem(testKey, '1');
          window.localStorage.removeItem(testKey);
          return window.localStorage;
        }
      } catch (e) {
        // Ignorar error de localStorage bloqueado
      }
      try {
        if (typeof window !== 'undefined' && window.sessionStorage) {
          const testKey = '__rdl_test__';
          window.sessionStorage.setItem(testKey, '1');
          window.sessionStorage.removeItem(testKey);
          return window.sessionStorage;
        }
      } catch (e) {
        // Ignorar error
      }
      return null;
    }

    function isTourDoneOrSkipped() {
      const storage = getStorage();
      if (!storage) return true; // Si no hay storage disponible, preferimos no mostrar
      try {
        const val = storage.getItem(STORAGE_KEY);
        return val === 'done' || val === 'skipped';
      } catch (e) {
        return true;
      }
    }

    function markTourState(state) {
      const storage = getStorage();
      if (!storage) return;
      try {
        storage.setItem(STORAGE_KEY, state);
      } catch (e) {
        // Silencioso
      }
    }

    // Analítica a dataLayer
    function trackEvent(eventName, stepNum) {
      try {
        if (window.dataLayer && typeof window.dataLayer.push === 'function') {
          const payload = { event: eventName };
          if (typeof stepNum === 'number') payload.tour_step = stepNum;
          window.dataLayer.push(payload);
        }
      } catch (e) {
        // Silencioso
      }
    }

    // Verificación de exclusiones (bot, checkout, Mercado Pago, modales abiertos)
    function isExcludedPage() {
      if (navigator.webdriver) return true;

      const path = (window.location.pathname || '').toLowerCase();
      if (path.includes('/checkout')) return true;

      const search = (window.location.search || '').toLowerCase();
      if (
        search.includes('payment_status') ||
        search.includes('collection_status') ||
        search.includes('status=')
      ) {
        return true;
      }

      return false;
    }

    function isAllowedPage() {
      const path = (window.location.pathname || '').toLowerCase();
      // La guía se muestra EXCLUSIVAMENTE en la página de Tienda / Catálogo (/index.php)
      return path.endsWith('/index.php');
    }

    function hasOtherModalOpen() {
      try {
        const drawer = document.getElementById('cart-drawer');
        if (drawer && drawer.classList.contains('is-open')) return true;

        const modals = document.querySelectorAll('.modal, [role="dialog"]');
        for (let i = 0; i < modals.length; i++) {
          const m = modals[i];
          if (m.id === 'rdl-tour-host') continue;
          const style = window.getComputedStyle(m);
          if (style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0') {
            return true;
          }
        }
      } catch (e) {
        // Ignorar
      }
      return false;
    }

    // Búsqueda de elementos visibles
    function findVisibleElement(selectorCandidate) {
      if (!selectorCandidate) return null;
      const selectors = selectorCandidate.split(',').map(s => s.trim());
      for (let i = 0; i < selectors.length; i++) {
        try {
          const el = document.querySelector(selectors[i]);
          if (el) {
            const rect = el.getBoundingClientRect();
            const style = window.getComputedStyle(el);
            if (
              rect.width > 0 &&
              rect.height > 0 &&
              style.display !== 'none' &&
              style.visibility !== 'hidden' &&
              style.opacity !== '0'
            ) {
              return el;
            }
          }
        } catch (e) {
          // Selector no válido
        }
      }
      return null;
    }

    function computeActiveSteps() {
      const result = [];
      for (let i = 0; i < TOUR_STEPS_CONFIG.length; i++) {
        const step = TOUR_STEPS_CONFIG[i];
        if (step.selector === null) {
          result.push({
            ...step,
            originalIndex: i,
            targetElement: null
          });
        } else {
          const el = findVisibleElement(step.selector);
          if (el) {
            result.push({
              ...step,
              originalIndex: i,
              targetElement: el
            });
          }
        }
      }
      return result;
    }

    // Creación del DOM en Shadow DOM
    function createShadowUI() {
      if (tourHost) return;

      tourHost = document.createElement('div');
      tourHost.id = 'rdl-tour-host';
      tourHost.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 0; z-index: 2147483000; pointer-events: none;';
      document.body.appendChild(tourHost);

      shadowRoot = tourHost.attachShadow({ mode: 'open' });

      const style = document.createElement('style');
      style.textContent = `
        :host {
          all: initial;
          display: block;
        }

        *, *::before, *::after {
          box-sizing: border-box;
          margin: 0;
          padding: 0;
        }

        .rdl-tour-backdrop {
          position: fixed;
          top: 0;
          left: 0;
          width: 100vw;
          height: 100vh;
          background-color: rgba(13, 13, 15, 0.6);
          z-index: 2147483001;
          pointer-events: auto;
          opacity: 0;
          transition: opacity 0.2s ease-out;
          display: none;
        }

        .rdl-tour-backdrop.is-visible {
          display: block;
          opacity: 1;
        }

        .rdl-tour-spotlight {
          position: absolute;
          z-index: 2147483002;
          border: 2px solid #F27A0F;
          border-radius: 8px;
          box-shadow: 0 0 0 9999px rgba(13, 13, 15, 0.6);
          pointer-events: none;
          transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
          display: none;
        }

        .rdl-tour-spotlight.is-visible {
          display: block;
        }

        .rdl-tour-popover {
          position: absolute;
          z-index: 2147483003;
          width: 340px;
          max-width: calc(100vw - 32px);
          background-color: #0D0D0F;
          border: 1px solid #58585A;
          border-radius: 12px;
          padding: 1.25rem;
          color: #F2F2F1;
          box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5);
          pointer-events: auto;
          font-family: 'Montserrat', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
          opacity: 0;
          transform: translateY(8px);
          transition: opacity 0.2s ease-out, transform 0.2s ease-out;
          outline: none;
          display: none;
        }

        .rdl-tour-popover.is-visible {
          display: block;
          opacity: 1;
          transform: translateY(0);
        }

        .rdl-tour-header {
          display: flex;
          align-items: center;
          justify-content: space-between;
          margin-bottom: 0.65rem;
        }

        .rdl-tour-badge {
          font-size: 0.7rem;
          font-weight: 800;
          color: #F27A0F;
          background: rgba(242, 122, 15, 0.15);
          border: 1px solid rgba(242, 122, 15, 0.3);
          padding: 0.2rem 0.5rem;
          border-radius: 4px;
          text-transform: uppercase;
          letter-spacing: 0.5px;
        }

        .rdl-tour-close-btn {
          background: transparent;
          border: none;
          color: #C5C2C1;
          font-size: 1.4rem;
          line-height: 1;
          cursor: pointer;
          width: 32px;
          height: 32px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          border-radius: 50%;
          transition: background-color 0.2s, color 0.2s;
        }

        .rdl-tour-close-btn:hover {
          background-color: rgba(255, 255, 255, 0.1);
          color: #FFFFFF;
        }

        .rdl-tour-title {
          font-family: 'Montserrat', 'Bebas Neue', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
          font-size: 1.15rem;
          font-weight: 800;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          color: #F2F2F1;
          margin-bottom: 0.5rem;
          line-height: 1.25;
        }

        .rdl-tour-desc {
          font-size: 0.92rem;
          line-height: 1.5;
          color: #C5C2C1;
          margin-bottom: 1.25rem;
        }

        .rdl-tour-arrow {
          position: absolute;
          width: 12px;
          height: 12px;
          background-color: #0D0D0F;
          border: 1px solid #58585A;
          transform: rotate(45deg);
          z-index: 1;
        }

        .rdl-tour-footer {
          display: flex;
          flex-direction: column;
          gap: 0.9rem;
        }

        .rdl-tour-progress {
          display: flex;
          align-items: center;
          justify-content: space-between;
        }

        .rdl-tour-counter {
          font-size: 0.8rem;
          font-weight: 600;
          color: #C5C2C1;
        }

        .rdl-tour-dots {
          display: flex;
          align-items: center;
          gap: 6px;
        }

        .rdl-tour-dot {
          width: 8px;
          height: 8px;
          border-radius: 50%;
          background-color: #58585A;
          transition: background-color 0.2s, transform 0.2s;
        }

        .rdl-tour-dot.is-active {
          background-color: #F27A0F;
          transform: scale(1.25);
        }

        .rdl-tour-actions {
          display: flex;
          align-items: center;
          justify-content: space-between;
          gap: 0.5rem;
        }

        .rdl-tour-btn {
          font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
          font-size: 0.85rem;
          font-weight: 700;
          height: 44px;
          min-width: 44px;
          padding: 0 1rem;
          border-radius: 8px;
          cursor: pointer;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          text-decoration: none;
          transition: all 0.2s ease;
          border: none;
          outline: none;
          box-sizing: border-box;
        }

        .rdl-tour-btn-skip {
          background: transparent;
          color: #C5C2C1;
          padding: 0 0.5rem;
        }

        .rdl-tour-btn-skip:hover {
          color: #FFFFFF;
          text-decoration: underline;
        }

        .rdl-tour-btn-prev {
          background: transparent;
          color: #F2F2F1;
          border: 1px solid #58585A;
        }

        .rdl-tour-btn-prev:hover {
          background: rgba(255, 255, 255, 0.08);
          border-color: #C5C2C1;
        }

        .rdl-tour-btn-next {
          background-color: #F27A0F;
          color: #0D0D0F;
        }

        .rdl-tour-btn-next:hover {
          background-color: #933F0C;
          color: #F2F2F1;
        }

        .rdl-tour-btn:focus-visible,
        .rdl-tour-close-btn:focus-visible {
          outline: 2px solid #F27A0F;
          outline-offset: 2px;
        }

        @media (max-width: 599.98px) {
          .rdl-tour-popover {
            position: fixed !important;
            top: auto !important;
            left: 16px !important;
            right: 16px !important;
            bottom: max(16px, env(safe-area-inset-bottom, 16px)) !important;
            width: auto !important;
            max-width: none !important;
            transform: translateY(16px);
          }
          .rdl-tour-popover.is-visible {
            transform: translateY(0);
          }
          .rdl-tour-arrow {
            display: none !important;
          }
        }

        @media (prefers-reduced-motion: reduce) {
          .rdl-tour-backdrop,
          .rdl-tour-spotlight,
          .rdl-tour-popover,
          .rdl-tour-dot {
            transition: none !important;
            animation: none !important;
          }
        }
      `;

      shadowRoot.appendChild(style);

      backdropEl = document.createElement('div');
      backdropEl.className = 'rdl-tour-backdrop';
      shadowRoot.appendChild(backdropEl);

      spotlightEl = document.createElement('div');
      spotlightEl.className = 'rdl-tour-spotlight';
      shadowRoot.appendChild(spotlightEl);

      popoverEl = document.createElement('div');
      popoverEl.className = 'rdl-tour-popover';
      popoverEl.setAttribute('role', 'dialog');
      popoverEl.setAttribute('aria-modal', 'true');
      popoverEl.setAttribute('tabindex', '-1');

      popoverEl.innerHTML = `
        <div class="rdl-tour-arrow"></div>
        <div class="rdl-tour-header">
          <span class="rdl-tour-badge">GUÍA RÁPIDA</span>
          <button type="button" class="rdl-tour-close-btn" aria-label="Cerrar guía">&times;</button>
        </div>
        <h2 class="rdl-tour-title"></h2>
        <p class="rdl-tour-desc"></p>
        <div class="rdl-tour-footer">
          <div class="rdl-tour-progress">
            <span class="rdl-tour-counter"></span>
            <div class="rdl-tour-dots"></div>
          </div>
          <div class="rdl-tour-actions">
            <button type="button" class="rdl-tour-btn rdl-tour-btn-skip">Omitir</button>
            <div style="display:flex; gap:0.5rem;">
              <button type="button" class="rdl-tour-btn rdl-tour-btn-prev">Anterior</button>
              <button type="button" class="rdl-tour-btn rdl-tour-btn-next">Siguiente</button>
            </div>
          </div>
        </div>
      `;

      shadowRoot.appendChild(popoverEl);

      arrowEl = popoverEl.querySelector('.rdl-tour-arrow');
      titleEl = popoverEl.querySelector('.rdl-tour-title');
      descEl = popoverEl.querySelector('.rdl-tour-desc');
      counterEl = popoverEl.querySelector('.rdl-tour-counter');
      dotsContainerEl = popoverEl.querySelector('.rdl-tour-dots');
      btnNextEl = popoverEl.querySelector('.rdl-tour-btn-next');
      btnPrevEl = popoverEl.querySelector('.rdl-tour-btn-prev');
      btnSkipEl = popoverEl.querySelector('.rdl-tour-btn-skip');
      btnCloseEl = popoverEl.querySelector('.rdl-tour-close-btn');

      // Bind events inside shadow root
      btnNextEl.addEventListener('click', () => nextStep());
      btnPrevEl.addEventListener('click', () => prevStep());
      btnSkipEl.addEventListener('click', () => skipTour());
      btnCloseEl.addEventListener('click', () => skipTour());

      // Manejo de foco atrapado (Focus Trap) y teclado
      shadowRoot.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          e.preventDefault();
          skipTour();
          return;
        }
        if (e.key === 'ArrowRight') {
          e.preventDefault();
          nextStep();
          return;
        }
        if (e.key === 'ArrowLeft') {
          e.preventDefault();
          prevStep();
          return;
        }
        if (e.key === 'Tab') {
          const focusable = [btnCloseEl, btnSkipEl, btnPrevEl, btnNextEl].filter(
            el => el && el.style.display !== 'none'
          );
          if (focusable.length === 0) return;
          const first = focusable[0];
          const last = focusable[focusable.length - 1];

          if (e.shiftKey && shadowRoot.activeElement === first) {
            e.preventDefault();
            last.focus();
          } else if (!e.shiftKey && shadowRoot.activeElement === last) {
            e.preventDefault();
            first.focus();
          }
        }
      });
    }

    // Bloqueo y desbloqueo de scroll sin salto de layout
    function lockBodyScroll() {
      savedBodyOverflow = document.body.style.overflow;
      savedBodyPaddingRight = document.body.style.paddingRight;

      const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
      document.body.style.overflow = 'hidden';
      if (scrollbarWidth > 0) {
        document.body.style.paddingRight = `${scrollbarWidth}px`;
      }
    }

    function unlockBodyScroll() {
      document.body.style.overflow = savedBodyOverflow;
      document.body.style.paddingRight = savedBodyPaddingRight;
    }

    // Posicionamiento dinámico y responsivo
    function updatePosition() {
      if (!isTourRunning || activeSteps.length === 0) return;

      const step = activeSteps[currentActiveIndex];
      const isMobile = window.innerWidth < 600;

      if (!step || !step.targetElement) {
        // Paso 1 centrado
        spotlightEl.classList.remove('is-visible');
        backdropEl.classList.add('is-visible');

        popoverEl.style.position = isMobile ? 'fixed' : 'fixed';
        popoverEl.style.top = isMobile ? '' : '50%';
        popoverEl.style.left = isMobile ? '' : '50%';
        popoverEl.style.transform = isMobile ? '' : 'translate(-50%, -50%)';
        arrowEl.style.display = 'none';
        popoverEl.classList.add('is-visible');
        return;
      }

      // Paso con elemento objetivo
      backdropEl.classList.remove('is-visible');
      const target = step.targetElement;
      const rect = target.getBoundingClientRect();

      // Ajustar Spotlight
      const offset = 6;
      const spotLeft = rect.left + window.scrollX - offset;
      const spotTop = rect.top + window.scrollY - offset;
      const spotWidth = rect.width + offset * 2;
      const spotHeight = rect.height + offset * 2;

      spotlightEl.style.left = `${spotLeft}px`;
      spotlightEl.style.top = `${spotTop}px`;
      spotlightEl.style.width = `${spotWidth}px`;
      spotlightEl.style.height = `${spotHeight}px`;
      spotlightEl.classList.add('is-visible');

      if (isMobile) {
        // En mobile se posiciona con CSS como bottom sheet
        popoverEl.style.position = 'fixed';
        popoverEl.style.top = '';
        popoverEl.style.left = '';
        popoverEl.style.transform = '';
        arrowEl.style.display = 'none';
        popoverEl.classList.add('is-visible');
        return;
      }

      // Posicionamiento en Desktop
      popoverEl.style.position = 'absolute';
      popoverEl.style.transform = '';
      arrowEl.style.display = 'block';

      const vw = window.innerWidth;
      const vh = window.innerHeight;

      // Medidas del popover
      const pw = 340;
      const ph = popoverEl.offsetHeight || 220;

      // Determinar el lado con más espacio libre
      const spaceBelow = vh - rect.bottom;
      const spaceAbove = rect.top;
      const spaceRight = vw - rect.right;
      const spaceLeft = rect.left;

      let placement = 'bottom';
      if (spaceBelow >= ph + 20) {
        placement = 'bottom';
      } else if (spaceAbove >= ph + 20) {
        placement = 'top';
      } else if (spaceRight >= pw + 20) {
        placement = 'right';
      } else if (spaceLeft >= pw + 20) {
        placement = 'left';
      } else {
        placement = spaceBelow > spaceAbove ? 'bottom' : 'top';
      }

      let popLeft = 0;
      let popTop = 0;

      const targetCenterX = rect.left + rect.width / 2;
      const targetCenterY = rect.top + rect.height / 2;

      if (placement === 'bottom') {
        popTop = rect.bottom + window.scrollY + 14;
        popLeft = targetCenterX + window.scrollX - pw / 2;
      } else if (placement === 'top') {
        popTop = rect.top + window.scrollY - ph - 14;
        popLeft = targetCenterX + window.scrollX - pw / 2;
      } else if (placement === 'right') {
        popLeft = rect.right + window.scrollX + 14;
        popTop = targetCenterY + window.scrollY - ph / 2;
      } else if (placement === 'left') {
        popLeft = rect.left + window.scrollX - pw - 14;
        popTop = targetCenterY + window.scrollY - ph / 2;
      }

      // Clampear para mantener en viewport
      const minLeft = window.scrollX + 16;
      const maxLeft = window.scrollX + vw - pw - 16;
      popLeft = Math.max(minLeft, Math.min(popLeft, maxLeft));

      const minTop = window.scrollY + 16;
      popTop = Math.max(minTop, popTop);

      popoverEl.style.left = `${popLeft}px`;
      popoverEl.style.top = `${popTop}px`;

      // Posicionar flecha
      arrowEl.style.top = '';
      arrowEl.style.bottom = '';
      arrowEl.style.left = '';
      arrowEl.style.right = '';

      if (placement === 'bottom') {
        arrowEl.style.top = '-6px';
        const arrowLeft = targetCenterX + window.scrollX - popLeft - 6;
        arrowEl.style.left = `${Math.max(16, Math.min(arrowLeft, pw - 28))}px`;
        arrowEl.style.borderRightColor = 'transparent';
        arrowEl.style.borderBottomColor = 'transparent';
      } else if (placement === 'top') {
        arrowEl.style.bottom = '-6px';
        const arrowLeft = targetCenterX + window.scrollX - popLeft - 6;
        arrowEl.style.left = `${Math.max(16, Math.min(arrowLeft, pw - 28))}px`;
        arrowEl.style.borderLeftColor = 'transparent';
        arrowEl.style.borderTopColor = 'transparent';
      } else if (placement === 'right') {
        arrowEl.style.left = '-6px';
        const arrowTop = targetCenterY + window.scrollY - popTop - 6;
        arrowEl.style.top = `${Math.max(16, Math.min(arrowTop, ph - 28))}px`;
        arrowEl.style.borderTopColor = 'transparent';
        arrowEl.style.borderRightColor = 'transparent';
      } else if (placement === 'left') {
        arrowEl.style.right = '-6px';
        const arrowTop = targetCenterY + window.scrollY - popTop - 6;
        arrowEl.style.top = `${Math.max(16, Math.min(arrowTop, ph - 28))}px`;
        arrowEl.style.borderBottomColor = 'transparent';
        arrowEl.style.borderLeftColor = 'transparent';
      }

      popoverEl.classList.add('is-visible');
    }

    function scheduleUpdatePosition() {
      if (rafId) cancelAnimationFrame(rafId);
      rafId = requestAnimationFrame(() => {
        updatePosition();
      });
    }

    // Mostrar un paso
    function renderStep(index) {
      if (index < 0 || index >= activeSteps.length) return;

      currentActiveIndex = index;
      const step = activeSteps[currentActiveIndex];

      titleEl.textContent = step.title;
      descEl.textContent = step.text;

      // Progreso
      counterEl.textContent = `${currentActiveIndex + 1} de ${activeSteps.length}`;

      // Puntos de progreso
      dotsContainerEl.innerHTML = '';
      for (let i = 0; i < activeSteps.length; i++) {
        const dot = document.createElement('span');
        dot.className = `rdl-tour-dot ${i === currentActiveIndex ? 'is-active' : ''}`;
        dotsContainerEl.appendChild(dot);
      }

      // Botón anterior
      if (currentActiveIndex === 0) {
        btnPrevEl.style.display = 'none';
      } else {
        btnPrevEl.style.display = 'inline-flex';
      }

      // Botón siguiente / listo
      if (currentActiveIndex === activeSteps.length - 1) {
        btnNextEl.textContent = '¡Listo!';
      } else {
        btnNextEl.textContent = 'Siguiente';
      }

      trackEvent('rdl_tour_step', currentActiveIndex + 1);

      // Si tiene objetivo, scrollear suavemente antes de posicionar
      if (step.targetElement) {
        try {
          step.targetElement.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
            inline: 'nearest'
          });
        } catch (e) {
          // Fallback silencioso
        }

        setTimeout(() => {
          scheduleUpdatePosition();
          btnNextEl.focus();
        }, 250);
      } else {
        scheduleUpdatePosition();
        btnNextEl.focus();
      }

      // ResizeObserver sobre el objetivo
      if (resizeObserver) {
        resizeObserver.disconnect();
        resizeObserver = null;
      }

      if (step.targetElement && typeof ResizeObserver !== 'undefined') {
        resizeObserver = new ResizeObserver(() => {
          scheduleUpdatePosition();
        });
        resizeObserver.observe(step.targetElement);
      }
    }

    // Acciones del tour
    function nextStep() {
      if (currentActiveIndex < activeSteps.length - 1) {
        renderStep(currentActiveIndex + 1);
      } else {
        completeTour();
      }
    }

    function prevStep() {
      if (currentActiveIndex > 0) {
        renderStep(currentActiveIndex - 1);
      }
    }

    function skipTour() {
      markTourState('skipped');
      trackEvent('rdl_tour_skip', currentActiveIndex + 1);
      closeTourUI();
    }

    function completeTour() {
      markTourState('done');
      trackEvent('rdl_tour_complete');
      closeTourUI();
    }

    function closeTourUI() {
      isTourRunning = false;

      if (resizeObserver) {
        resizeObserver.disconnect();
        resizeObserver = null;
      }

      window.removeEventListener('resize', scheduleUpdatePosition);
      window.removeEventListener('scroll', scheduleUpdatePosition, true);
      window.removeEventListener('orientationchange', scheduleUpdatePosition);

      if (spotlightEl) spotlightEl.classList.remove('is-visible');
      if (backdropEl) backdropEl.classList.remove('is-visible');
      if (popoverEl) popoverEl.classList.remove('is-visible');

      unlockBodyScroll();

      if (previouslyFocusedElement && typeof previouslyFocusedElement.focus === 'function') {
        try {
          previouslyFocusedElement.focus();
        } catch (e) {
          // Ignorar
        }
      }

      if (tourHost && tourHost.parentNode) {
        setTimeout(() => {
          if (tourHost && tourHost.parentNode) {
            tourHost.parentNode.removeChild(tourHost);
            tourHost = null;
            shadowRoot = null;
          }
        }, 200);
      }
    }

    function startTour(force = false) {
      if (isTourRunning) return;

      const urlParams = new URLSearchParams(window.location.search);
      const isForced = force || urlParams.get('rdl_tour') === '1' || urlParams.get('rdl_tour') === 'true';

      if (!isForced) {
        if (isTourDoneOrSkipped()) return;
        if (isExcludedPage()) return;
        if (!isAllowedPage()) return;
        if (hasOtherModalOpen()) return;
      }

      activeSteps = computeActiveSteps();
      if (activeSteps.length === 0) return;

      // Marcar de entrada para asegurar que si cierra la pestaña no vuelva a salir
      markTourState('done');

      previouslyFocusedElement = document.activeElement;
      createShadowUI();
      lockBodyScroll();

      isTourRunning = true;

      window.addEventListener('resize', scheduleUpdatePosition, { passive: true });
      window.addEventListener('scroll', scheduleUpdatePosition, { passive: true, capture: true });
      window.addEventListener('orientationchange', scheduleUpdatePosition, { passive: true });

      trackEvent('rdl_tour_start');
      renderStep(0);
    }

    // Esperar a que el catálogo JS renderice sus elementos en /index.php antes de arrancar
    function waitForCatalogAndStart(isForced) {
      let attempts = 0;
      const maxAttempts = 30; // Max 6 segundos (30 x 200ms)

      const checkInterval = setInterval(() => {
        attempts++;
        const hasCatalogReady = document.querySelector('#catalog-search-input') || document.querySelector('#catalog-root .product-card');

        if (hasCatalogReady || attempts >= maxAttempts) {
          clearInterval(checkInterval);
          setTimeout(() => {
            startTour(isForced);
          }, DISPLAY_DELAY_MS);
        }
      }, 200);
    }

    function init() {
      const urlParams = new URLSearchParams(window.location.search);
      const isForced = urlParams.get('rdl_tour') === '1' || urlParams.get('rdl_tour') === 'true';

      if (!isForced) {
        if (isTourDoneOrSkipped()) return;
        if (isExcludedPage()) return;
        if (!isAllowedPage()) return;
      }

      const launch = () => {
        waitForCatalogAndStart(isForced);
      };

      if (document.readyState === 'complete') {
        launch();
      } else {
        window.addEventListener('load', launch, { once: true });
      }
    }

    // Exponer API global window.RDLTour
    window.RDLTour = {
      init: init,
      start: function () { startTour(true); },
      reset: function () {
        const storage = getStorage();
        if (storage) {
          try {
            storage.removeItem(STORAGE_KEY);
          } catch (e) {
            // Ignorar
          }
        }
      },
      next: nextStep,
      prev: prevStep,
      skip: skipTour
    };

    // Inicializar automáticamente
    init();

  } catch (err) {
    // Si la guía falla, se desactiva en silencio sin romper nada del sitio
    if (typeof console !== 'undefined' && console.warn) {
      console.warn('RDLTour auto-disabled due to runtime catch:', err);
    }
  }
})();
