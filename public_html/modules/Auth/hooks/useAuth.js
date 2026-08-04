import { getCurrentUser, getCachedUser, login, register, logout } from '../services/authService.js';
import { AccountStatus } from '../components/AccountStatus.js';
import { LoginForm } from '../components/LoginForm.js';
import { RegisterForm } from '../components/RegisterForm.js';

export function useAuth() {

  async function init() {
    // 1. Obtener usuario de sesión y renderizar estado en el header
    const user = await getCurrentUser();
    renderHeaderStatus(user);

    // Escuchar eventos globales de cambio de autenticación
    document.addEventListener('auth:changed', (e) => {
      const updatedUser = e.detail;
      renderHeaderStatus(updatedUser);
    });

    // 2. Inicializar vista de Login si existe
    if (document.getElementById('login-root')) {
      initLoginView();
    }

    // 3. Inicializar vista de Registro si existe
    if (document.getElementById('register-root')) {
      initRegisterView();
    }

    // 4. Inicializar vista de Mi Cuenta si existe
    if (document.getElementById('account-root')) {
      initAccountView(user);
    }
  }

  function renderHeaderStatus(user) {
    const root = document.getElementById('header-auth-root');
    if (!root) return;

    root.innerHTML = AccountStatus(user);

    const btnLogout = root.querySelector('#btn-header-logout');
    if (btnLogout) {
      btnLogout.onclick = async () => {
        await logout();
        window.location.href = '/index.php';
      };
    }
  }

  /* --- VISTA DE LOGIN --- */
  function initLoginView() {
    const container = document.getElementById('login-root');
    if (!container) return;

    let state = {
      formData: { email: '' },
      error: null,
      isSubmitting: false
    };

    function render() {
      container.innerHTML = `
        <div class="auth-page-container">
          ${LoginForm(state.formData, state.error, state.isSubmitting)}
        </div>
      `;

      const form = container.querySelector('#login-form');
      if (form) {
        form.onsubmit = async (e) => {
          e.preventDefault();
          if (state.isSubmitting) return;

          const formData = new FormData(form);
          state.formData.email = (formData.get('email') || '').toString().trim();
          const password = (formData.get('password') || '').toString();

          state.isSubmitting = true;
          state.error = null;
          render();

          try {
            await login({ email: state.formData.email, password });
            window.location.href = '/index.php#catalogo';
          } catch (err) {
            state.error = err.message || 'Error al iniciar sesión.';
            state.isSubmitting = false;
            render();
          }
        };
      }
    }

    render();
  }

  /* --- VISTA DE REGISTRO --- */
  function initRegisterView() {
    const container = document.getElementById('register-root');
    if (!container) return;

    let state = {
      formData: { name: '', email: '', phone: '', wantsWholesale: false, businessName: '' },
      errors: {},
      isSubmitting: false
    };

    function render() {
      container.innerHTML = `
        <div class="auth-page-container">
          ${RegisterForm(state.formData, state.errors, state.isSubmitting)}
        </div>
      `;

      const form = container.querySelector('#register-form');
      const checkbox = container.querySelector('#reg-wants-wholesale');
      const businessGroup = container.querySelector('#business-name-group');

      if (checkbox && businessGroup) {
        checkbox.onchange = () => {
          state.formData.wantsWholesale = checkbox.checked;
          businessGroup.style.display = checkbox.checked ? 'flex' : 'none';
        };
      }

      if (form) {
        form.onsubmit = async (e) => {
          e.preventDefault();
          if (state.isSubmitting) return;

          const formData = new FormData(form);
          state.formData = {
            name: (formData.get('name') || '').toString().trim(),
            email: (formData.get('email') || '').toString().trim(),
            phone: (formData.get('phone') || '').toString().trim(),
            password: (formData.get('password') || '').toString(),
            confirmPassword: (formData.get('confirmPassword') || '').toString(),
            wantsWholesale: checkbox ? checkbox.checked : false,
            businessName: (formData.get('businessName') || '').toString().trim()
          };

          // Validación preliminar en cliente
          if (state.formData.password !== state.formData.confirmPassword) {
            state.errors = { confirmPassword: 'Las contraseñas no coinciden.' };
            render();
            return;
          }

          state.isSubmitting = true;
          state.errors = {};
          render();

          try {
            const res = await register(state.formData);

            if (!res.success) {
              state.errors = res.errors || { general: 'Error al procesar el registro.' };
              state.isSubmitting = false;
              render();
              return;
            }

            // Redirigir a Mi Cuenta tras registro
            window.location.href = '/mi-cuenta.php';

          } catch (err) {
            state.errors = { general: err.message || 'Error inesperado al conectar con el servidor.' };
            state.isSubmitting = false;
            render();
          }
        };
      }
    }

    render();
  }

  /* --- VISTA DE MI CUENTA --- */
  function initAccountView(initialUser) {
    const container = document.getElementById('account-root');
    if (!container) return;

    function render(user) {
      if (!user) {
        window.location.href = '/login.php';
        return;
      }

      const isWholesalePending = user.role === 'wholesale' && user.approved !== 1;
      const isWholesaleApproved = user.role === 'wholesale' && user.approved === 1;

      let roleNoticeHtml = '';
      if (isWholesalePending) {
        roleNoticeHtml = `
          <div class="alert-warning-box">
            <h4>⏳ Cuenta Mayorista en Revisión</h4>
            <p>Tu solicitud para acceder a precios mayoristas fue recibida. Un administrador debe revisar y aprobar tu cuenta (Empresa: <strong>${user.business_name || 'No especificada'}</strong>). Mientras tanto, puedes realizar compras con precios minoristas.</p>
          </div>
        `;
      } else if (isWholesaleApproved) {
        roleNoticeHtml = `
          <div class="alert-success-box">
            <h4>✅ Cuenta Mayorista Aprobada</h4>
            <p>¡Bienvenido! Estás navegando con tu tarifa de <strong>Cliente Mayorista</strong> (${user.business_name || 'Empresa registrada'}). Todos los precios del catálogo reflejan tu descuento especial.</p>
          </div>
        `;
      } else {
        roleNoticeHtml = `
          <div class="alert-info-box">
            <h4>👤 Cuenta Minorista</h4>
            <p>Estás navegando como cliente minorista. Si eres revendedor o taller mecánico y deseas solicitar precios mayoristas, puedes contactar al soporte.</p>
          </div>
        `;
      }

      container.innerHTML = `
        <section class="account-section">
          <h1 class="account-title">Mi Cuenta</h1>

          ${roleNoticeHtml}

          <div class="account-card-box">
            <h3>Información de Perfil</h3>
            <div class="account-details-grid">
              <div class="detail-row">
                <span class="detail-label">Nombre:</span>
                <span class="detail-val">${user.name}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-val">${user.email}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Teléfono:</span>
                <span class="detail-val">${user.phone || 'No especificado'}</span>
              </div>
              ${user.business_name ? `
                <div class="detail-row">
                  <span class="detail-label">Empresa / Negocio:</span>
                  <span class="detail-val">${user.business_name}</span>
                </div>
              ` : ''}
              <div class="detail-row">
                <span class="detail-label">Tipo de Usuario:</span>
                <span class="detail-val">${user.role === 'wholesale' ? 'Cliente Mayorista' : 'Cliente Minorista'}</span>
              </div>
            </div>

            <div class="account-actions" style="margin-top:2rem;">
              <a href="/index.php#catalogo" class="btn btn-primary">Ir al Catálogo de Productos</a>
              <button type="button" id="btn-page-logout" class="btn btn-outline">Cerrar Sesión</button>
            </div>
          </div>
        </section>
      `;

      const btnLogout = container.querySelector('#btn-page-logout');
      if (btnLogout) {
        btnLogout.onclick = async () => {
          await logout();
          window.location.href = '/login.php';
        };
      }
    }

    render(initialUser || getCachedUser());
  }

  return { init };
}
