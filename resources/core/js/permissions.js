import Swal from 'sweetalert2';

function onReady(fn) {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fn);
    return;
  }

  fn();
}

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
    title: 'Oops...',
    text: message || 'Something went wrong',
  });
}

function getPermissionsSavedBadge() {
  return document.getElementById('permissionsSavedBadge');
}

function hidePermissionsSavedBadge() {
  const badge = getPermissionsSavedBadge();
  if (!badge) return;

  if (badge._hideTimer) {
    clearTimeout(badge._hideTimer);
  }

  badge.style.display = 'none';
}

function showPermissionsSavedBadge(message = 'Saved just now') {
  const badge = getPermissionsSavedBadge();
  if (!badge) return;

  if (badge._hideTimer) {
    clearTimeout(badge._hideTimer);
  }

  badge.textContent = message;
  badge.style.display = 'inline-flex';
  badge._hideTimer = setTimeout(() => {
    badge.style.display = 'none';
  }, 2500);
}

/* --- collect checked permissions into nested {page:{resource:[actions...]}} --- */
function collectSelectedPermissions() {
  const selected = {};
  document.querySelectorAll('.permission-checkbox:checked').forEach(cb => {
    const page = cb.getAttribute('data-page');
    const action = cb.getAttribute('data-action');
    const resource = cb.getAttribute('data-permission');
    if (!selected[page]) selected[page] = {};
    if (!selected[page][resource]) selected[page][resource] = [];
    selected[page][resource].push(action);
  });
  return selected;
}

function getRoleDefaults() {
  const el = document.getElementById('roleDefaultsJson');
  if (!el) return {};

  try {
    return JSON.parse(el.textContent || '{}');
  } catch {
    return {};
  }
}

function setSelectedPermissions(nested = {}) {
  const selected = new Set();

  Object.entries(nested).forEach(([page, resources]) => {
    Object.entries(resources || {}).forEach(([resource, actions]) => {
      (actions || []).forEach((action) => {
        selected.add(`${page}::${resource}::${action}`);
      });
    });
  });

  document.querySelectorAll('.permission-checkbox').forEach((checkbox) => {
    const key = `${checkbox.getAttribute('data-page')}::${checkbox.getAttribute('data-permission')}::${checkbox.getAttribute('data-action')}`;
    checkbox.checked = selected.has(key);
  });
}

function bindRestoreDefaults() {
  const btn = document.getElementById('restoreDefaultsButton');
  const ddl = document.getElementById('role');
  if (!btn || !ddl) return;

  btn.addEventListener('click', () => {
    const roleName = ddl.value;
    const roleDefaults = getRoleDefaults();
    const defaults = roleDefaults[roleName];

    if (!roleName) {
      toastError('Select a role first.');
      return;
    }

    if (typeof defaults === 'undefined') {
      toastError('Role defaults are unavailable for the selected role.');
      return;
    }

    setSelectedPermissions(defaults || {});
    hidePermissionsSavedBadge();
    toastSuccess(`Restored ${roleName} defaults. Click Save Changes to apply.`);
  });
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
      showPermissionsSavedBadge();
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
      text: "This will immediately replace the user's current password.",
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

function bindPermissionEditorState() {
  document.addEventListener('change', (event) => {
    if (event.target.classList.contains('permission-checkbox')) {
      hidePermissionsSavedBadge();
    }
  });
}

function bindPermissionConcernTabs() {
  const buttons = Array.from(document.querySelectorAll('[data-permission-concern-tab]'));
  const panels = Array.from(document.querySelectorAll('[data-permission-concern-panel]'));

  if (!buttons.length || !panels.length) {
    return;
  }

  const activate = (key) => {
    buttons.forEach((button) => {
      const isActive = button.getAttribute('data-permission-concern-tab') === key;
      button.classList.toggle('is-active', isActive);
      button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });

    panels.forEach((panel) => {
      const isActive = panel.getAttribute('data-permission-concern-panel') === key;
      panel.style.display = isActive ? '' : 'none';

      if (isActive && !panel.querySelector('details[open]')) {
        const firstDetails = panel.querySelector('details[data-permission-accordion]');
        if (firstDetails) {
          firstDetails.open = true;
        }
      }
    });
  };

  buttons.forEach((button) => {
    button.addEventListener('click', () => activate(button.getAttribute('data-permission-concern-tab')));
  });

  activate(buttons[0].getAttribute('data-permission-concern-tab'));
}

function bindPermissionAccordions() {
  const sections = Array.from(document.querySelectorAll('details[data-permission-accordion]'));
  if (!sections.length) {
    return;
  }

  sections.forEach((section) => {
    section.addEventListener('toggle', () => {
      if (!section.open) {
        return;
      }

      const group = section.getAttribute('data-permission-accordion');
      sections.forEach((other) => {
        if (other !== section && other.getAttribute('data-permission-accordion') === group) {
          other.open = false;
        }
      });
    });
  });
}


onReady(() => {
  const hasPermissionsEditor = !!document.getElementById('savePermissionsButton');
  const hasRoleSelect = !!document.getElementById('role');
  const hasPermissionCheckbox = !!document.querySelector('.permission-checkbox');
  const hasResetBtn = !!document.getElementById('resetPasswordButton');

  // Safety guard: this module is for user-permission edit page only.
  if (!hasPermissionsEditor && !hasRoleSelect && !hasPermissionCheckbox && !hasResetBtn) {
    return;
  }

  bindStatusToggles();
  bindDeleteButtons();
  bindSavePermissions();
  bindRestoreDefaults();
  bindRoleChange();
  bindResetPassword();
  bindPermissionEditorState();
  bindPermissionConcernTabs();
  bindPermissionAccordions();
});
