
            <header class="app-header">
            <!--      <div style="position:fixed; top:70px; right:20px; z-index:999999; background:#fff; padding:6px 10px; border:1px solid #ddd; border-radius:8px; font-size:12px;">
    composer: {{ $user?->cache_busted_photo_url ? 'yes' : 'no' }}
    @if($user?->profile?->profile_photo_path)
      <div>path: {{ $user->profile->profile_photo_path }}</div>
    @endif
  </div>  For debugging if image wont appear --> 
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

        <div class="ti-dropdown-header !m-0 !p-4 !bg-transparent flex justify-between items-center gap-3">
            <div class="min-w-0">
                <p class="mb-0 text-[1.0625rem] text-defaulttextcolor font-semibold dark:text-[#8c9097] dark:text-white/50">
                    Notifications
                </p>
                <span class="text-[0.75em] py-[0.25rem/2] px-[0.45rem] font-[600] rounded-sm bg-secondary/10 text-secondary"
                    id="notification-unread-text">0 Unread</span>
            </div>

            <div class="shrink-0 flex items-center gap-2">
                <button
                    type="button"
                    class="ti-btn ti-btn-xs ti-btn-light !rounded-full"
                    id="notification-mark-all"
                    title="Mark all as read">
                    <i class="ri-check-double-line"></i>
                    <span class="hidden sm:inline">Mark all</span>
                </button>
            </div>
        </div>

        <div class="dropdown-divider"></div>

        {{-- dynamic list --}}
        <div class="max-h-[360px] overflow-y-auto overscroll-contain" id="header-notification-scroll-wrap">
            <ul class="list-none !m-0 !p-0 end-0" id="header-notification-scroll"></ul>
        </div>

        <div class="p-4 empty-header-item1 border-t mt-2">
            <div class="grid">
                {{-- NOTE: make sure this route exists later. If not yet, temporarily use url('/notifications') --}}
                <a href="{{ url('/notifications') }}" class="ti-btn ti-btn-primary-full !m-0 w-full p-2">View All</a>
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
  window.__notifEndpoints = {
    header: "{{ url('/notifications/header') }}",
    markRead: "{{ url('/notifications') }}",      // POST /notifications/{id}/read
    markAllRead: "{{ url('/notifications/read-all') }}", // POST /notifications/read-all
  };
</script>

@vite('resources/core/js/notifications/header.js')
@endpush




                           <!-- Header Profile -->
    <div class="header-element md:!px-[0.65rem] px-2 hs-dropdown !items-center ti-dropdown [--placement:bottom-left]">
        @php
        // extra safety: if composer fails, still fallback cleanly
        $photo = $user?->cache_busted_photo_url ?: asset('build/assets/images/faces/9.jpg');
        @endphp

        <button id="dropdown-profile" type="button"
        class="hs-dropdown-toggle ti-dropdown-toggle !gap-2 !p-0 flex-shrink-0 sm:me-2 me-0 !rounded-full !shadow-none text-xs align-middle !border-0 !shadow-transparent">
        <img class="inline-block rounded-full"
            src="{{ $photo }}"
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
                <li><a class="w-full ti-dropdown-item !text-[0.8125rem] !gap-x-0 !p-[0.65rem] !inline-flex" href="{{ $profileRoutes['index'] ?? route('profile.index') }}">
                        <i class="ti ti-user-circle text-[1.125rem] me-2 opacity-[0.7]"></i>Profile
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
                <li><a class="w-full ti-dropdown-item !text-[0.8125rem] !gap-x-0 !p-[0.65rem] !inline-flex" href="{{ route('login') }}">
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
