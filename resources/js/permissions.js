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

/* --- collect checked permissions into nested {page:{resource:[actions...]}} --- */
function collectSelectedPermissions() {
  const selected = {};
  document.querySelectorAll('.permission-checkbox:checked').forEach(cb => {
    const page = cb.getAttribute('data-page');
    const action = cb.getAttribute('data-action');         // 'view' | 'modify' | 'delete'
    const resource = cb.getAttribute('data-permission');   // e.g., 'Users'
    if (!selected[page]) selected[page] = {};
    if (!selected[page][resource]) selected[page][resource] = [];
    selected[page][resource].push(action);
  });
  return selected;
}

/* ---------- STATUS TOGGLE (PATCH /users/{user}/status) ---------- */
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

    el.disabled = true;
    try {
      await apiJson(endpoint, { method: 'PATCH', body: { is_active: isActive } });
      toastSuccess(`User has been ${isActive ? 'activated' : 'deactivated'}.`);
    } catch (err) {
      el.checked = !isActive;
      toastError(err.message);
    } finally {
      el.disabled = false;
    }
  });
}

/* ---------- DELETE USER (DELETE /users/{user}) ---------- */
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

/* ---------- SAVE PERMISSIONS (PATCH /users/{user}/permissions) ---------- */
function bindSavePermissions() {
  const btn = document.getElementById('savePermissionsButton');
  if (!btn) return;

  btn.addEventListener('click', async () => {
    const endpoint = btn.dataset.endpoint;
    if (!endpoint) {
      toastError('Missing endpoint for saving permissions.');
      return;
    }

    const permissions = collectSelectedPermissions();
    const totalSelected = Object.values(permissions)
      .reduce((sum, group) => sum + Object.values(group).reduce((s, a) => s + a.length, 0), 0);

    const res = await Swal.fire({
      title: 'Apply changes?',
      text: totalSelected > 0
        ? `You are about to apply ${totalSelected} permission change(s).`
        : 'No permissions selected. This will clear all direct permissions. Continue?',
      icon: totalSelected > 0 ? 'question' : 'warning',
      showCancelButton: true,
      confirmButtonText: 'Save',
      cancelButtonText: 'Cancel',
    });
    if (!res.isConfirmed) return;

    btn.disabled = true;
    try {
      const data = await apiJson(endpoint, { method: 'PATCH', body: { permissions } });
      const count = Number(data.count ?? 0);
      toastSuccess(`Permissions updated${count ? ` (${count})` : ''}.`);
      Object.keys(permissions).forEach(page => {
        const span = document.getElementById(`feedback-${page.replace(/\s+/g, '-').toLowerCase()}`);
        if (span) {
          span.classList.remove('hidden');
          span.textContent = '✔ Saved';
          setTimeout(() => span.classList.add('hidden'), 2500);
        }
      });
    } catch (err) {
      toastError(err.message);
    } finally {
      btn.disabled = false;
    }
  });
}

/* ---------- CHANGE ROLE (PATCH /users/{user}/permissions with role) ---------- */
function bindRoleChange() {
  const ddl = document.getElementById('role');
  if (!ddl) return;

  let current = ddl.value;

  ddl.addEventListener('change', async () => {
    const endpoint = ddl.dataset.endpoint;
    const nextRole = ddl.value;

    if (!endpoint) {
      toastError('Missing endpoint for role change.');
      ddl.value = current;
      return;
    }

    const res = await Swal.fire({
      title: 'Change role?',
      text: 'Changing the role will reset permissions to the role defaults. Continue?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, change role',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#d33',
    });
    if (!res.isConfirmed) {
      ddl.value = current;
      return;
    }

    ddl.disabled = true;
    try {
      const data = await apiJson(endpoint, { method: 'PATCH', body: { role: nextRole, permissions: {} } });
      await Swal.fire({ icon: 'success', title: 'Role updated', timer: 1200, showConfirmButton: false });
      current = nextRole;
      location.reload();
    } catch (err) {
      ddl.value = current; // revert on failure
      toastError(err.message);
    } finally {
      ddl.disabled = false;
    }
  });
}

// --- Reset Password (POST /users/{user}/reset-password) ---
function bindResetPassword() {
  const btn = document.getElementById('resetPasswordButton');
  if (!btn) return;

  btn.addEventListener('click', async () => {
    const endpoint = btn.dataset.endpoint;
    if (!endpoint) {
      toastError('Missing endpoint for password reset.');
      return;
    }

    const res = await Swal.fire({
      title: 'Generate temporary password?',
      text: 'This will immediately replace the user’s current password.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, generate',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#d33',
    });
    if (!res.isConfirmed) return;

    btn.disabled = true;
    try {
      const data = await apiJson(endpoint, { method: 'POST' });

      const temp = data.temporary_password || '';
      if (!temp) {
        toastError('No temporary password returned.');
        return;
      }

      await Swal.fire({
        title: 'Temporary Password',
        html: `
          <div style="font-size:1.5rem; font-weight:700; letter-spacing:2px; margin-bottom:8px;">${temp}</div>
          <div class="text-sm text-gray-500">Copy this code and give it to the user. They should change it after login.</div>
        `,
        icon: 'success',
        showCancelButton: true,
        confirmButtonText: 'Copy',
        cancelButtonText: 'Close',
        didOpen: () => {
          // auto-copy for convenience
          try { navigator.clipboard.writeText(temp); } catch {}
        },
      }).then(async (choice) => {
        if (choice.isConfirmed) {
          try {
            await navigator.clipboard.writeText(temp);
            toastSuccess('Copied to clipboard.');
          } catch {
            toastError('Copy failed. Please copy manually.');
          }
        }
      });
    } catch (err) {
      toastError(err.message);
    } finally {
      btn.disabled = false;
    }
  });
}


document.addEventListener('DOMContentLoaded', () => {
  bindStatusToggles();
  bindDeleteButtons();
  bindSavePermissions();
  bindRoleChange();
  bindResetPassword(); 
});
