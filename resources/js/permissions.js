// resources/js/permissions.js
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
      'X-CSRF-TOKEN': getCsrfToken(),
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: body ? JSON.stringify(body) : undefined,
  });

  const isJson = (res.headers.get('content-type') || '').includes('application/json');
  const data = isJson ? await res.json().catch(() => ({})) : {};

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
    timer: 1400,
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

// --- Status toggle (PATCH /users/{user}/status) ---
function bindStatusToggles() {
  document.addEventListener('change', async (e) => {
    const el = e.target;
    if (!el.classList.contains('toggle-status')) return;

    const endpoint = el.dataset.endpoint || (el.dataset.userId ? `/users/${el.dataset.userId}/status` : null);
    const isActive = el.checked;
    if (!endpoint) {
      toastError('Missing endpoint for status update.');
      el.checked = !isActive;
      return;
    }

    // Prevent double-clicks while request in flight
    el.disabled = true;
    try {
      await apiJson(endpoint, { method: 'PATCH', body: { is_active: isActive } });
      toastSuccess(`User has been ${isActive ? 'activated' : 'deactivated'}.`);
    } catch (err) {
      // revert UI on failure
      el.checked = !isActive;
      toastError(err.message);
    } finally {
      el.disabled = false;
    }
  });
}

// --- Delete user (DELETE /users/{user}) ---
function bindDeleteButtons() {
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-action="delete-user"]');
    if (!btn) return;

    const endpoint = btn.dataset.endpoint || (btn.dataset.userId ? `/users/${btn.dataset.userId}` : null);
    const username = btn.dataset.username || 'this user';
    if (!endpoint) {
      toastError('Missing endpoint for delete.');
      return;
    }

    const res = await Swal.fire({
      title: 'Delete user?',
      text: `You are about to delete ${username}. This action cannot be undone.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#d33',
    });

    if (!res.isConfirmed) return;

    try {
      await apiJson(endpoint, { method: 'DELETE' });
      await Swal.fire({ icon: 'success', title: 'User deleted', timer: 1200, showConfirmButton: false });
      location.reload();
    } catch (err) {
      toastError(err.message);
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  bindStatusToggles();
  bindDeleteButtons();
});
