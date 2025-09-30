
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