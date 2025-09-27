import Swal from 'sweetalert2';

function getCsrfToken() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

async function apiJson(url, { method = 'GET', body } = {}) {
  const res = await fetch(url, {
    method,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': getCsrfToken(),
    },
    body: body ? JSON.stringify(body) : undefined,
  });

  let data = {};
  const isJson = (res.headers.get('content-type') || '').includes('application/json');
  if (isJson) {
    try { data = await res.json(); } catch { data = {}; }
  }

  if (!res.ok) {
    const msg = data.message || res.statusText || 'Request failed';
    throw new Error(msg);
  }
  return data;
}

function toastSuccess(message) {
  Swal.fire({
    icon: 'success',
    title: message || 'Success',
    timer: 1300,
    showConfirmButton: false,
    position: 'top-end',
  });
}

function toastError(message) {
  Swal.fire({
    icon: 'error',
    title: 'Oops…',
    text: message || 'Something went wrong',
  });
}

function bindDeletePermission() {
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-action="delete-permission"]');
    if (!btn) return;

    const endpoint = btn.dataset.endpoint;
    const rowId    = btn.dataset.row;
    const name     = btn.dataset.name || 'this permission';

    if (!endpoint) {
      toastError('Missing delete endpoint.');
      return;
    }

    const result = await Swal.fire({
      title: 'Delete permission?',
      text: `You are about to delete "${name}". This action cannot be undone.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#d33',
    });

    if (!result.isConfirmed) return;

    btn.disabled = true;
    try {
      await apiJson(endpoint, { method: 'DELETE' });
      // Remove the row (or fallback to reload)
      if (rowId) {
        const row = document.getElementById(rowId);
        if (row) row.remove();
      }
      toastSuccess('Permission deleted');
    } catch (err) {
      toastError(err.message);
    } finally {
      btn.disabled = false;
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  bindDeletePermission();
});
