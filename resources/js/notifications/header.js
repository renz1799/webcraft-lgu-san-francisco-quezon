console.log('[Notifications] Header script loaded');

(function () {
  const endpoints = window.__notifEndpoints || {};
  const els = {
    badgeWrap: document.getElementById('notification-badge-wrap'),
    badgePing: document.getElementById('notification-badge-ping'),
    badge: document.getElementById('notification-icon-badge'),
    unreadText: document.getElementById('notification-unread-text'),
    list: document.getElementById('header-notification-scroll'),
    empty: document.getElementById('header-notification-empty'),
    dropdownBtn: document.getElementById('dropdown-notification'),
    markAllBtn: document.getElementById('notification-mark-all'),
  };

  if (!els.badge || !els.list) return;

  function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  function setUnread(count) {
    const n = Number(count || 0);
    els.badge.textContent = n;
    els.unreadText.textContent = `${n} Unread`;

    if (n <= 0) {
      els.badgeWrap?.classList.add('hidden');
      els.badgePing?.classList.add('hidden');
    } else {
      els.badgeWrap?.classList.remove('hidden');
      els.badgePing?.classList.remove('hidden');
    }
  }

  function showEmptyState(show) {
    if (!els.empty) return;
    if (show) els.empty.classList.remove('hidden');
    else els.empty.classList.add('hidden');
  }

  function formatRelativeTime(iso) {
    if (!iso) return '';
    const date = new Date(iso);
    if (isNaN(date.getTime())) return '';

    const diffMs = date.getTime() - Date.now();
    const diffSec = Math.round(diffMs / 1000);

    const rtf = new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' });

    const abs = Math.abs(diffSec);
    if (abs < 60) return rtf.format(diffSec, 'second');

    const diffMin = Math.round(diffSec / 60);
    if (Math.abs(diffMin) < 60) return rtf.format(diffMin, 'minute');

    const diffHr = Math.round(diffMin / 60);
    if (Math.abs(diffHr) < 24) return rtf.format(diffHr, 'hour');

    const diffDay = Math.round(diffHr / 24);
    return rtf.format(diffDay, 'day');
  }

  function buildItem(n) {
    const id = escapeHtml(n.id);
    const title = escapeHtml(n.title);
    const message = escapeHtml(n.message);
    const url = escapeHtml(n.url || '#');
    const isRead = !!n.read_at;
    const unreadClass = isRead ? '' : 'bg-secondary/5';
    const timeText = escapeHtml(formatRelativeTime(n.created_at));

    return `
      <li class="ti-dropdown-item dropdown-item !block ${unreadClass}" data-notification-id="${id}">
        <div class="flex items-start">
          <div class="pe-2">
            <span class="inline-flex text-secondary justify-center items-center !w-[2.5rem] !h-[2.5rem] !leading-[2.5rem] !text-[0.8rem] bg-secondary/10 rounded-[50%]">
              <i class="ti ti-bell text-[1.125rem]"></i>
            </span>
          </div>

          <div class="grow flex items-start justify-between gap-2">
            <div class="min-w-0">
              <p class="mb-0 text-defaulttextcolor dark:text-white text-[0.8125rem] font-semibold truncate">
                <a href="${url}" class="header-notification-link" data-notification-id="${id}">
                  ${title}
                </a>
              </p>

              <span class="block text-[#8c9097] dark:text-white/50 font-normal text-[0.75rem] header-notification-text">
                ${message}
              </span>

              ${timeText ? `<span class="block mt-1 text-[0.70rem] text-[#8c9097] dark:text-white/50">${timeText}</span>` : ''}
            </div>

            <div class="shrink-0">
              <a aria-label="anchor" href="javascript:void(0);" class="min-w-fit text-[#8c9097] dark:text-white/50 me-1 header-notification-close" data-notification-id="${id}">
                <i class="ti ti-x text-[1rem]"></i>
              </a>
            </div>
          </div>
        </div>
      </li>
    `;
  }

  async function fetchHeaderNotifications() {
    const res = await fetch(endpoints.header, {
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin',
    });
    if (!res.ok) throw new Error('Failed to load notifications');
    return await res.json();
  }

  async function markAsRead(notificationId) {
    const token = getCsrfToken();
    if (!token) return;

    await fetch(`${endpoints.markRead}/${notificationId}/read`, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
      credentials: 'same-origin',
    });
  }

  async function markAllAsRead() {
    const token = getCsrfToken();
    if (!token) return;

    await fetch(endpoints.markAllRead, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
      credentials: 'same-origin',
    });
  }

  async function refreshHeaderNotifications() {
    try {
      const data = await fetchHeaderNotifications();
      const unread = data.unread_count ?? 0;
      const list = Array.isArray(data.notifications) ? data.notifications : [];

      setUnread(unread);
      els.list.innerHTML = '';

      if (list.length === 0) {
        showEmptyState(true);
        return;
      }

      showEmptyState(false);
      for (const n of list) {
        els.list.insertAdjacentHTML('beforeend', buildItem(n));
      }
    } catch (e) {
      // fail silently
    }
  }

  document.addEventListener('DOMContentLoaded', refreshHeaderNotifications);

  els.dropdownBtn?.addEventListener('click', () => {
    refreshHeaderNotifications();
  });

  // mark as read when clicking link (don’t block navigation)
  document.addEventListener('click', async (e) => {
    const link = e.target.closest('.header-notification-link');
    if (!link) return;

    const id = link.getAttribute('data-notification-id');
    if (!id) return;

    markAsRead(id).finally(() => {
      refreshHeaderNotifications();
    });
  });

  // close icon: mark as read + refresh
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.header-notification-close');
    if (!btn) return;

    e.preventDefault();
    const id = btn.getAttribute('data-notification-id');
    if (!id) return;

    await markAsRead(id);
    await refreshHeaderNotifications();
  });

  // ✅ mark all
  els.markAllBtn?.addEventListener('click', async () => {
    await markAllAsRead();
    await refreshHeaderNotifications();
  });
})();
