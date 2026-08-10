function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

export async function fetchWholesaleClients(filter = 'all') {
  const res = await fetch(`/api/admin/wholesale-clients.php?filter=${filter}`);
  return res.json();
}

export async function approveClient(userId) {
  const res = await fetch('/api/admin/wholesale-approve.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': getCsrfToken()
    },
    body: JSON.stringify({ action: 'approve', user_id: userId })
  });
  return res.json();
}

export async function revokeClient(userId) {
  const res = await fetch('/api/admin/wholesale-approve.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': getCsrfToken()
    },
    body: JSON.stringify({ action: 'revoke', user_id: userId })
  });
  return res.json();
}

export async function createClient(data) {
  const res = await fetch('/api/admin/wholesale-create.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': getCsrfToken()
    },
    body: JSON.stringify(data)
  });
  return res.json();
}
