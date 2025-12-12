
            <header class="app-header">
                <nav class="main-header !h-[3.75rem]" aria-label="Global">
                    <div class="main-header-container ps-[0.725rem] pe-[1rem] ">

                        <div class="header-content-left">
                            <!-- Start::header-element -->
                            <div class="header-element">
                            <div class="horizontal-logo">
                                <a href="{{url('index')}}" class="header-logo">
                                <img src="{{asset('build/assets/images/brand-logos/desktop-logo.png')}}" alt="logo" class="desktop-logo">
                                <img src="{{asset('build/assets/images/brand-logos/toggle-logo.png')}}" alt="logo" class="toggle-logo">
                                <img src="{{asset('build/assets/images/brand-logos/desktop-dark.png')}}" alt="logo" class="desktop-dark">
                                <img src="{{asset('build/assets/images/brand-logos/toggle-dark.png')}}" alt="logo" class="toggle-dark">
                                <img src="{{asset('build/assets/images/brand-logos/desktop-white.png')}}" alt="logo" class="desktop-white">
                                <img src="{{asset('build/assets/images/brand-logos/toggle-white.png')}}" alt="logo" class="toggle-white">
                                </a>
                            </div>
                            </div>
                            <!-- End::header-element -->
                            <!-- Start::header-element -->
                            <div class="header-element md:px-[0.325rem] !items-center">
                            <!-- Start::header-link -->
                            <a aria-label="Hide Sidebar"
                                class="sidemenu-toggle animated-arrow  hor-toggle horizontal-navtoggle inline-flex items-center" href="javascript:void(0);"><span></span></a>
                            <!-- End::header-link -->
                            </div>
                            <!-- End::header-element -->
                        </div>

                        <div class="header-content-right">

                            <div class="header-element py-[1rem] md:px-[0.65rem] px-2 header-search">
                            <button aria-label="button" type="button" data-hs-overlay="#search-modal"
                                class="inline-flex flex-shrink-0 justify-center items-center gap-2  rounded-full font-medium focus:ring-offset-0 focus:ring-offset-white transition-all text-xs dark:bg-bgdark dark:hover:bg-black/20 dark:text-[#8c9097] dark:text-white/50 dark:hover:text-white dark:focus:ring-white/10 dark:focus:ring-offset-white/10">
                                <i class="bx bx-search-alt-2 header-link-icon"></i>
                            </button>
                            </div>


                            <!-- Fullscreen -->
                            <div class="header-element header-fullscreen py-[1rem] md:px-[0.65rem] px-2">
                            <!-- Start::header-link -->
                            <a aria-label="anchor" onclick="openFullscreen();" href="javascript:void(0);"
                                class="inline-flex flex-shrink-0 justify-center items-center gap-2  !rounded-full font-medium dark:hover:bg-black/20 dark:text-[#8c9097] dark:text-white/50 dark:hover:text-white dark:focus:ring-white/10 dark:focus:ring-offset-white/10">
                                <i class="bx bx-fullscreen full-screen-open header-link-icon"></i>
                                <i class="bx bx-exit-fullscreen full-screen-close header-link-icon hidden"></i>
                            </a>
                            <!-- End::header-link -->
                            </div>
                            <!-- End Full screen -->

<!--Header Notifictaion -->
<div class="header-element py-[1rem] md:px-[0.65rem] px-2 notifications-dropdown header-notification hs-dropdown ti-dropdown !hidden md:!block [--placement:bottom-left]">
    <button id="dropdown-notification" type="button"
        class="hs-dropdown-toggle relative ti-dropdown-toggle !p-0 !border-0 flex-shrink-0 !rounded-full !shadow-none align-middle text-xs">
        <i class="bx bx-bell header-link-icon text-[1.125rem]"></i>

        {{-- badge --}}
        <span class="flex absolute h-5 w-5 -top-[0.25rem] end-0 -me-[0.6rem]" id="notification-badge-wrap">
            <span
                class="animate-slow-ping absolute inline-flex -top-[2px] -start-[2px] h-full w-full rounded-full bg-secondary/40 opacity-75"
                id="notification-badge-ping"></span>

            <span
                class="relative inline-flex justify-center items-center rounded-full h-[14.7px] w-[14px] bg-secondary text-[0.625rem] text-white"
                id="notification-icon-badge">0</span>
        </span>
    </button>

    <div class="main-header-dropdown !-mt-3 !p-0 hs-dropdown-menu ti-dropdown-menu bg-white !w-[22rem] border-0 border-defaultborder hidden !m-0"
        aria-labelledby="dropdown-notification">

        <div class="ti-dropdown-header !m-0 !p-4 !bg-transparent flex justify-between items-center">
            <p class="mb-0 text-[1.0625rem] text-defaulttextcolor font-semibold dark:text-[#8c9097] dark:text-white/50">
                Notifications
            </p>

            <span class="text-[0.75em] py-[0.25rem/2] px-[0.45rem] font-[600] rounded-sm bg-secondary/10 text-secondary"
                id="notification-unread-text">0 Unread</span>
        </div>

        <div class="dropdown-divider"></div>

        {{-- dynamic list --}}
        <ul class="list-none !m-0 !p-0 end-0" id="header-notification-scroll"></ul>

        <div class="p-4 empty-header-item1 border-t mt-2">
            <div class="grid">
                <a href="{{ url('notifications') }}" class="ti-btn ti-btn-primary-full !m-0 w-full p-2">View All</a>
            </div>
        </div>

        {{-- empty state --}}
        <div class="p-[3rem] empty-item1 hidden" id="header-notification-empty">
            <div class="text-center">
                <span class="!h-[4rem] !w-[4rem] avatar !leading-[4rem] !rounded-full !bg-secondary/10 !text-secondary">
                    <i class="ri-notification-off-line text-[2rem]"></i>
                </span>
                <h6 class="font-semibold mt-3 text-defaulttextcolor dark:text-white text-[1rem]">No New Notifications</h6>
            </div>
        </div>
    </div>
</div>
<!--End Header Notifictaion -->

@push('scripts')
<script>
    console.log('[Notifications] Header script loaded');

(function () {
    const endpoints = {
        header: "{{ url('/notifications/header') }}",
        markRead: "{{ url('/notifications') }}", // + /{id}/read
    };

    const els = {
        badgeWrap: document.getElementById('notification-badge-wrap'),
        badgePing: document.getElementById('notification-badge-ping'),
        badge: document.getElementById('notification-icon-badge'),
        unreadText: document.getElementById('notification-unread-text'),
        list: document.getElementById('header-notification-scroll'),
        empty: document.getElementById('header-notification-empty'),
        dropdownBtn: document.getElementById('dropdown-notification'),
    };

    // If header is not present on the page, do nothing.
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

    function setUnread(count) {
        const n = Number(count || 0);

        els.badge.textContent = n;
        els.unreadText.textContent = `${n} Unread`;

        // Hide badge & ping when 0 to match most UI expectations
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

    function buildItem(n) {
        const id = escapeHtml(n.id);
        const title = escapeHtml(n.title);
        const message = escapeHtml(n.message);
        const url = escapeHtml(n.url || '#');
        const isRead = !!n.read_at;

        // Optional: subtle styling for unread items
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
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // If no token, just skip (prevents breaking pages missing the meta tag)
        if (!token) return;

        await fetch(`${endpoints.markRead}/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
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
            // Fail silently to avoid breaking header UI
            // console.error(e);
        }
    }

    // Load on page ready
    document.addEventListener('DOMContentLoaded', refreshHeaderNotifications);

    // Refresh on dropdown open click (so it's always fresh)
    els.dropdownBtn?.addEventListener('click', () => {
        refreshHeaderNotifications();
    });

    // Event delegation: mark as read when clicking link
    document.addEventListener('click', async (e) => {
        const link = e.target.closest('.header-notification-link');
        if (!link) return;

        const id = link.getAttribute('data-notification-id');
        if (!id) return;

        // fire and forget (do not block navigation)
        markAsRead(id);
    });

    // Optional: close icon -> mark as read + remove from list UI
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.header-notification-close');
        if (!btn) return;

        e.preventDefault();

        const id = btn.getAttribute('data-notification-id');
        if (!id) return;

        await markAsRead(id);

        const li = document.querySelector(`li[data-notification-id="${CSS.escape(id)}"]`);
        li?.remove();

        // reduce unread count visually (best effort)
        const current = Number(els.badge.textContent || 0);
        setUnread(Math.max(0, current - 1));

        if (els.list.children.length === 0) {
            showEmptyState(true);
        }
    });

    // Optional: poll unread count every 30s (comment out if you don't want polling)
    // setInterval(refreshHeaderNotifications, 30000);
})();
</script>
@endpush


                           <!-- Header Profile -->
<div class="header-element md:!px-[0.65rem] px-2 hs-dropdown !items-center ti-dropdown [--placement:bottom-left]">
    <button id="dropdown-profile" type="button"
        class="hs-dropdown-toggle ti-dropdown-toggle !gap-2 !p-0 flex-shrink-0 sm:me-2 me-0 !rounded-full !shadow-none text-xs align-middle !border-0 !shadow-transparent">
        <img class="inline-block rounded-full"
            src="{{ $user?->cache_busted_photo_url ?? asset('build/assets/images/default-profile.png') }}"
            width="32" height="32" alt="{{ $user?->username ?? 'Guest' }}">
    </button>
    <div class="md:block hidden dropdown-profile">
        <p class="font-semibold mb-0 leading-none text-[#536485] text-[0.813rem]">
            {{ $user?->username ?? 'Guest' }}
        </p>
        <span class="opacity-[0.7] font-normal text-[#536485] block text-[0.6875rem]">
            {{ $user?->user_type ?? 'N/A' }}
        </span>
    </div>

    <div
        class="hs-dropdown-menu ti-dropdown-menu !-mt-3 border-0 w-[11rem] !p-0 border-defaultborder hidden main-header-dropdown  pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end"
        aria-labelledby="dropdown-profile">

        <ul class="text-defaulttextcolor font-medium dark:text-[#8c9097] dark:text-white/50">
            @if($user)
                <li><a class="w-full ti-dropdown-item !text-[0.8125rem] !gap-x-0 !p-[0.65rem] !inline-flex" href="{{ url('mail-settings') }}">
                        <i class="ti ti-adjustments-horizontal text-[1.125rem] me-2 opacity-[0.7]"></i>Settings
                    </a>
                </li>

                <li>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full ti-dropdown-item !text-[0.8125rem] !gap-x-0 !p-[0.65rem] !inline-flex text-left">
                            <i class="ti ti-logout text-[1.125rem] me-2 opacity-[0.7]"></i>Log Out
                        </button>
                    </form>
                </li>
            @else
                <li><a class="w-full ti-dropdown-item !text-[0.8125rem] !gap-x-0 !p-[0.65rem] !inline-flex" href="{{ url('login') }}">
                        <i class="ti ti-login text-[1.125rem] me-2 opacity-[0.7]"></i>Log In
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
<!-- End Header Profile -->

                            <!-- Switcher Icon -->
                            <div class="header-element md:px-[0.48rem]">
                            <button aria-label="button" type="button"
                                class="hs-dropdown-toggle switcher-icon inline-flex flex-shrink-0 justify-center items-center gap-2  rounded-full font-medium  align-middle transition-all text-xs dark:text-[#8c9097] dark:text-white/50 dark:hover:text-white dark:focus:ring-white/10 dark:focus:ring-offset-white/10"
                                data-hs-overlay="#hs-overlay-switcher">
                                <i class="bx bx-cog header-link-icon animate-spin-slow"></i>
                            </button>
                            </div>
                            <!-- Switcher Icon -->

                            <!-- End::header-element -->
                        </div>
                    </div>
                </nav>
            </header>