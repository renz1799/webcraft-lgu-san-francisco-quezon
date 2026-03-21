import Swal from 'sweetalert2';

/* ---------- helpers ---------- */
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

/* ---------- Edit modal prefill ---------- */
document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-hs-overlay="#editRoleModal"]');
  if (!btn) return;

  const id    = btn.dataset.roleId;
  const name  = btn.dataset.roleName;
  let perms   = [];
  try { perms = JSON.parse(btn.dataset.rolePermissions || '[]'); } catch {}

  // id + name
  document.getElementById('editRoleId').value = id;
  document.getElementById('editRoleName').value = name;
  document.getElementById('editRoleForm').action = `/roles/${id}`;

  // uncheck all first then check those in perms
  document.querySelectorAll('#editRoleForm input[name="permissions[]"]').forEach(i => i.checked = false);
  const want = new Set(perms.map(String));
  document.querySelectorAll('#editRoleForm input[name="permissions[]"]').forEach(i => {
    if (want.has(String(i.value))) i.checked = true;
  });
});

document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-action="view-role-perms"]');
  if (!btn) return;

  const role = btn.dataset.role || 'Role';
  let perms = [];
  try { perms = JSON.parse(btn.dataset.perms || '[]'); } catch {}

  // Group by page
  const grouped = perms.reduce((acc, p) => {
    const page = p.page || 'Uncategorized';
    (acc[page] ||= []).push(p.name);
    return acc;
  }, {});

  const html = Object.entries(grouped)
    .sort((a,b) => a[0].localeCompare(b[0]))
    .map(([page, names]) => `
      <div>
        <h4 style="margin:0 0 6px">${page} <span style="font-size:12px;color:#6b7280">(${names.length})</span></h4>
        <ul style="margin:0 0 12px 1rem;padding:0">${names.sort().map(n => `<li>${n}</li>`).join('')}</ul>
      </div>
    `)
    .join('') || '<div>No permissions.</div>';

  Swal.fire({
    title: `Permissions for ${role}`,
    html: `<div style="text-align:left;max-height:420px;overflow:auto">${html}</div>`,
    width: 800,
    confirmButtonText: 'Close'
  });
});

/* ---------- Group "Select all" ---------- */
function toggleGroup(scope, ids, checked) {
  const form = scope === 'edit' ? '#editRoleForm' : '#addRoleForm';
  const idSet = new Set((ids || []).map(String));
  document.querySelectorAll(`${form} input[name="permissions[]"]`).forEach(i => {
    if (idSet.has(String(i.value))) i.checked = checked;
  });
}

document.addEventListener('change', (e) => {
  const t = e.target;
  if (t.matches('[data-toggle="group"]')) {
    const scope = t.dataset.scope;
    const ids   = JSON.parse(t.dataset.ids || '[]');
    toggleGroup(scope, ids, t.checked);
  }
});

/* ---------- Bulk all/none ---------- */
document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-bulk]');
  if (!btn) return;
  const scope = btn.dataset.scope;
  const check = btn.dataset.bulk === 'check-all';
  const form  = scope === 'edit' ? '#editRoleForm' : '#addRoleForm';
  document.querySelectorAll(`${form} input[name="permissions[]"]`).forEach(i => i.checked = check);
});

/* ---------- Per-group filter ---------- */
document.addEventListener('input', (e) => {
  const inp = e.target;
  if (!inp.matches('[data-filter="group"]')) return;
  const scope = inp.dataset.scope;
  const group = inp.dataset.group;
  const q = (inp.value || '').trim().toLowerCase();
  const box = document.querySelector(`[data-group-box="${group}"][data-scope="${scope}"]`);
  if (!box) return;
  box.querySelectorAll('.permission-chip').forEach(row => {
    row.style.display = q && !row.dataset.label.includes(q) ? 'none' : '';
  });
});

/* ---------- SweetAlert Delete ---------- */
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-action="delete-role"]');
  if (!btn) return;

  const name = btn.dataset.name || 'this role';
  const endpoint = btn.dataset.endpoint;

  const res = await Swal.fire({
    title: 'Delete role?',
    text: `You are about to delete "${name}". This cannot be undone.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#d33'
  });
  if (!res.isConfirmed) return;

  try {
    const r = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf(),
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ _method: 'DELETE' })
    });

    if (!r.ok) throw new Error('Delete failed');

    await Swal.fire({ icon: 'success', title: 'Role deleted', timer: 900, showConfirmButton: false });
    // remove row from table
    const row = btn.closest('tr');
    if (row) row.remove();
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Delete failed', text: err.message || 'Please try again.' });
  }
});

// Global search across all permissions within a modal (Add/Edit)
document.addEventListener('input', (e) => {
  const inp = e.target;
  if (!inp.matches('[data-filter="global"]')) return;
  const scope = inp.dataset.scope; // 'add' | 'edit'
  const q = (inp.value || '').trim().toLowerCase();
  const form = scope === 'edit' ? '#editRoleForm' : '#addRoleForm';
  document.querySelectorAll(`${form} .permission-chip`).forEach(el => {
    const label = el.dataset.label || '';
    el.style.display = q && !label.includes(q) ? 'none' : '';
  });
});
