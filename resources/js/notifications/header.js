console.log('[Notifications] header.js loaded');

(function () {
  const endpoints = {
    header: window.__notifEndpoints?.header,
    markRead: window.__notifEndpoints?.markRead, // base: /notifications
  };

  if (!endpoints.header || !endpoints.markRead) return;

  const els = {
    badgeWrap: document.getElementById('notification-badge-wrap'),
    badgePing: document.getElementById('notification-badge-ping'),
    badge: document.getElementById('notification-icon-badge'),
    unreadText: document.getElementById('notification-unread-text'),
    list: document.getElementById('header-notification-scroll'),
    empty: document.getElementById('header-notification-empty'),
    dropdownBtn: document.getElementById('dropdown-notification'),
  };

  if (!els.badge || !els.list) return;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
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
    els.empty.classList.toggle('hidden', !show);
  }

  function buildItem(n) {
    const id = escapeHtml(n.id);
    const title = escapeHtml(n.title);
    const message = escapeHtml(n.message);
    const url = escapeHtml(n.url || '#');
    const isRead = !!n.read_at;
    const unreadClass = isRead ? '' : 'bg-secondary/5';

    return `
      <li class="ti-dropdown-item dropdown-item !block ${unreadClass}" data-notification-id="${id}">
        <div class="flex items-start">
          <div class="pe-2">
            <span class="inline-flex text-secondary justify-center items-center !w-[2.5rem] !h-[2.5rem] !leading-[2.5rem] !text-[0.8rem] bg-secondary/10 rounded-[50%]">
              <i class="ti ti-bell text-[1.125rem]"></i>
            </span>
          </div>

          <div class="grow flex items-center justify-between">
            <div class="min-w-0">
              <p class="mb-0 text-defaulttextcolor dark:text-white text-[0.8125rem] font-semibold truncate">
                <a href="${url}" class="header-notification-link" data-notification-id="${id}">
                  ${title}
                </a>
              </p>
              <span class="text-[#8c9097] dark:text-white/50 font-normal text-[0.75rem] header-notification-text">
                ${message}
              </span>
            </div>

            <div>
              <a href="javascript:void(0);" class="min-w-fit text-[#8c9097] dark:text-white/50 me-1 header-notification-close" data-notification-id="${id}">
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

  // ✅ IMPORTANT: use sendBeacon so it still sends even when the page navigates away
  function markAsReadBeacon(notificationId) {
    if (!csrf) return;

    const url = `${endpoints.markRead}/${notificationId}/read`;
    const fd = new FormData();
    fd.append('_token', csrf);

    // sendBeacon returns true/false, but we don't need to await it
    navigator.sendBeacon(url, fd);
  }

  // For the close button (no navigation), normal fetch is fine
  async function markAsReadFetch(notificationId) {
    if (!csrf) return;
    await fetch(`${endpoints.markRead}/${notificationId}/read`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
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
      // keep header stable
      // console.error(e);
    }
  }

  document.addEventListener('DOMContentLoaded', refreshHeaderNotifications);
  els.dropdownBtn?.addEventListener('click', refreshHeaderNotifications);

  // ✅ Clicking the notification link: mark read via beacon, then let navigation happen normally
  document.addEventListener('click', (e) => {
    const link = e.target.closest('.header-notification-link');
    if (!link) return;

    const id = link.getAttribute('data-notification-id');
    if (!id) return;

    markAsReadBeacon(id);
  });

  // Close icon: mark as read + remove from UI
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.header-notification-close');
    if (!btn) return;

    e.preventDefault();

    const id = btn.getAttribute('data-notification-id');
    if (!id) return;

    await markAsReadFetch(id);

    const li = document.querySelector(`li[data-notification-id="${CSS.escape(id)}"]`);
    li?.remove();

    const current = Number(els.badge.textContent || 0);
    setUnread(Math.max(0, current - 1));

    if (els.list.children.length === 0) showEmptyState(true);
  });
})();
