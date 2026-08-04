/**
 * Servicio HTTP para la autenticación de usuarios.
 */

let currentUserCache = null;

export async function getCurrentUser() {
  try {
    const response = await fetch('/api/auth/me.php');
    const data = await response.json();
    if (response.ok && data.success) {
      currentUserCache = data.data;
      return currentUserCache;
    }
  } catch (e) {
    console.error('Error al obtener usuario de sesión:', e);
  }
  currentUserCache = null;
  return null;
}

export function getCachedUser() {
  return currentUserCache;
}

export async function login({ email, password }) {
  const response = await fetch('/api/auth/login.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });

  const data = await response.json();

  if (!response.ok || !data.success) {
    throw new Error(data.error || 'Error al iniciar sesión.');
  }

  currentUserCache = data.data;
  document.dispatchEvent(new CustomEvent('auth:changed', { detail: currentUserCache }));
  return currentUserCache;
}

export async function register(formData) {
  const response = await fetch('/api/auth/register.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(formData)
  });

  const data = await response.json();

  if (response.status === 422 && data.errors) {
    return { success: false, errors: data.errors };
  }

  if (!response.ok || !data.success) {
    throw new Error(data.error || 'Error al registrar la cuenta.');
  }

  currentUserCache = data.data;
  document.dispatchEvent(new CustomEvent('auth:changed', { detail: currentUserCache }));
  return { success: true, user: currentUserCache };
}

export async function logout() {
  try {
    await fetch('/api/auth/logout.php', { method: 'POST' });
  } catch (e) {
    console.error('Error en logout:', e);
  }
  currentUserCache = null;
  document.dispatchEvent(new CustomEvent('auth:changed', { detail: null }));
}
