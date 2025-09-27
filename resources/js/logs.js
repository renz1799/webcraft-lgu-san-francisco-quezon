// resources/js/logs.js
import Swal from 'sweetalert2';

/* ---------------- helpers ---------------- */
const getCsrf = () => {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
};

const escapeHtml = (s) =>
  String(s ?? '')
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

const safeParse = (str, fallback = {}) => {
  if (typeof str !== 'string' || !str.length) return fallback;
  try { return JSON.parse(str); } catch { return fallback; }
};

const pretty = (obj) =>
  `<pre style="text-align:left;max-height:320px;overflow:auto;margin:0">${escapeHtml(
    JSON.stringify(obj ?? {}, null, 2)
  )}</pre>`;

const toast = (icon, title, text) =>
  Swal.fire({
    icon, title, text,
    timer: icon === 'success' ? 1300 : undefined,
    showConfirmButton: icon !== 'success',
    position: icon === 'success' ? 'top-end' : 'center'
  });

/* --------------- “view changes” modal --------------- */
document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-action="view-log"]');
  if (!btn) return;

  const payloadRaw = btn.dataset.payload;
  let data;
  if (payloadRaw) {
    data = safeParse(payloadRaw, {});
  } else {
    data = {
      message: btn.dataset.message || 'Change details',
      old: safeParse(btn.dataset.old, {}),
      new: safeParse(btn.dataset.new, {}),
      meta: safeParse(btn.dataset.meta, {}),
      agent: btn.dataset.agent || '—',
    };
  }

  Swal.fire({
    title: escapeHtml(data.message || 'Change details'),
    html: `
      <div style="text-align:left;display:grid;gap:12px">
        <div><h4 style="margin:0 0 6px">New</h4>${pretty(data.new)}</div>
        <div><h4 style="margin:0 0 6px">Old</h4>${pretty(data.old)}</div>
        <div><h4 style="margin:0 0 6px">Meta</h4>${pretty(data.meta)}</div>
        <div><h4 style="margin:0 0 6px">User-Agent</h4>
          <div style="font-size:12px">${escapeHtml(data.agent || '—')}</div>
        </div>
      </div>
    `,
    width: 820,
    confirmButtonText: 'Close',
  });
});

/* --------------- copy UUID buttons --------------- */
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-action="copy"]');
  if (!btn) return;

  const value = btn.dataset.copy || '';
  if (!value) return;

  try {
    await navigator.clipboard.writeText(value);
    toast('success', 'Copied to clipboard');
  } catch {
    const ta = document.createElement('textarea');
    ta.value = value;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
      document.execCommand('copy');
      toast('success', 'Copied to clipboard');
    } catch {
      toast('error', 'Copy failed');
    } finally {
      document.body.removeChild(ta);
    }
  }
});

/* --------------- restore soft-deleted subject --------------- */
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-action="restore-subject"]');
  if (!btn) return;

  // avoid double submits
  if (btn.dataset.restored === '1' || btn.disabled) return;

  const endpoint = btn.dataset.endpoint;
  const type = btn.dataset.type;
  const id = btn.dataset.id;

  if (!endpoint || !type || !id) {
    return toast('error', 'Restore failed', 'Missing endpoint or identifiers.');
  }

  const confirm = await Swal.fire({
    title: 'Restore this record?',
    text: 'This will un-delete the record.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, restore',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#16a34a', // green
  });

  if (!confirm.isConfirmed) return;

  btn.disabled = true;

  try {
    const res = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': getCsrf(),
      },
      body: JSON.stringify({ type, id }),
    });

    const data = (await res.json().catch(() => ({}))) || {};
    if (!res.ok || data.ok !== true) {
      throw new Error(data.message || 'Restore failed');
    }

    // Update UI in-place (no reload)
    const td = btn.closest('td') || btn.parentElement;

    // Remove the (deleted) badge if present
    const deletedFlag = td.querySelector('.text-red-500');
    if (deletedFlag) deletedFlag.remove();

    // Flip the button to "restored" state (green, check icon, disabled)
    btn.classList.remove('ti-btn-warning');
    btn.classList.add('ti-btn-success');
    btn.innerHTML = '<i class="ri-check-line"></i>';
    btn.title = 'Restored';
    btn.dataset.restored = '1';
    btn.disabled = true;

    toast('success', 'Restored');
  } catch (err) {
    btn.disabled = false;
    toast('error', 'Restore failed', err.message);
  }
});
