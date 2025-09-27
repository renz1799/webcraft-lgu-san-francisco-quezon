// resources/js/logs.js
import Swal from 'sweetalert2';

// --- tiny helpers ---
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
  Swal.fire({ icon, title, text, timer: icon === 'success' ? 900 : undefined, showConfirmButton: icon !== 'success', position: icon === 'success' ? 'top-end' : 'center' });

// --- view log modal ---
document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-action="view-log"]');
  if (!btn) return;

  // Back-compat: if data-payload exists, parse it; else read individual attributes
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

// --- copy-to-clipboard for UUIDs (user/subject) ---
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-action="copy"]');
  if (!btn) return;

  const value = btn.dataset.copy || '';
  if (!value) return;

  try {
    await navigator.clipboard.writeText(value);
    toast('success', 'Copied to clipboard');
  } catch {
    // fallback
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
