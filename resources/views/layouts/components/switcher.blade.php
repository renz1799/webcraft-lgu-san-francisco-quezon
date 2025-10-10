
            <div id="hs-overlay-switcher"
                data-theme-style='@json($themeStyle)'
                data-update-style-url="{{ route('theme.style.update') }}"
                data-update-colors-url="{{ route('theme.colors.update') }}"
                class="hs-overlay hidden ti-offcanvas ti-offcanvas-right"
                tabindex="-1">
                <div class="ti-offcanvas-header z-10 relative">
                    <h5 class="ti-offcanvas-title">
                    Switcher
                    </h5>
                    <button type="button"
                    class="ti-btn flex-shrink-0 p-0  transition-none text-defaulttextcolor dark:text-defaulttextcolor/70 hover:text-gray-700 focus:ring-gray-400 focus:ring-offset-white  dark:hover:text-white/80 dark:focus:ring-white/10 dark:focus:ring-offset-white/10"
                    data-hs-overlay="#hs-overlay-switcher">
                    <span class="sr-only">Close modal</span>
                    <i class="ri-close-circle-line leading-none text-lg"></i>
                    </button>
                </div>
                <div class="ti-offcanvas-body !p-0 !border-b dark:border-white/10 z-10 relative !h-auto">
                    <div class="flex rtl:space-x-reverse" aria-label="Tabs" role="tablist">
                    <button type="button"
                        class="hs-tab-active:bg-success/20 w-full !py-2 !px-4 hs-tab-active:border-b-transparent text-defaultsize border-0 hs-tab-active:text-success dark:hs-tab-active:bg-success/20 dark:hs-tab-active:border-b-white/10 dark:hs-tab-active:text-success -mb-px bg-white font-semibold text-center  text-defaulttextcolor dark:text-defaulttextcolor/70 rounded-none hover:text-gray-700 dark:bg-bodybg dark:border-white/10  active"
                        id="switcher-item-1" data-hs-tab="#switcher-1" aria-controls="switcher-1" role="tab">
                        Theme Style
                    </button>
                    <button type="button"
                        class="hs-tab-active:bg-success/20 w-full !py-2 !px-4 hs-tab-active:border-b-transparent text-defaultsize border-0 hs-tab-active:text-success dark:hs-tab-active:bg-success/20 dark:hs-tab-active:border-b-white/10 dark:hs-tab-active:text-success -mb-px  bg-white font-semibold text-center  text-defaulttextcolor dark:text-defaulttextcolor/70 rounded-none hover:text-gray-700 dark:bg-bodybg dark:border-white/10  dark:hover:text-gray-300"
                        id="switcher-item-2" data-hs-tab="#switcher-2" aria-controls="switcher-2" role="tab">
                        Theme Colors
                    </button>
                    </div>
                </div>
                <div class="ti-offcanvas-body" id="switcher-body">
                    <div id="switcher-1" role="tabpanel" aria-labelledby="switcher-item-1" class="">
                    <div class="">
                        <p class="switcher-style-head">Theme Color Mode:</p>
                        <div class="grid grid-cols-3 switcher-style">
                         <div class="flex items-center">
                            <input type="radio" name="theme_mode" value="light" class="ti-form-radio" id="switcher-light-theme"
                                @checked(($themeStyle['mode'] ?? 'light') === 'light')>
                            <label for="switcher-light-theme" class="ms-2 font-semibold">Light</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" name="theme_mode" value="dark" class="ti-form-radio" id="switcher-dark-theme"
                                @checked(($themeStyle['mode'] ?? 'light') === 'dark')>
                            <label for="switcher-dark-theme" class="ms-2 font-semibold">Dark</label>
                        </div>
                        </div>
                    </div>
                    <div>
                        <p class="switcher-style-head">Directions:</p>
                        <div class="grid grid-cols-3  switcher-style">
                        <div class="flex items-center">
                            <input type="radio" name="direction" value="ltr" class="ti-form-radio" id="switcher-ltr"
                                @checked(($themeStyle['dir'] ?? 'ltr') === 'ltr')>
                            <label for="switcher-ltr" class="ms-2 font-semibold">LTR</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" name="direction" value="rtl" class="ti-form-radio" id="switcher-rtl"
                                @checked(($themeStyle['dir'] ?? 'ltr') === 'rtl')>
                            <label for="switcher-rtl" class="ms-2 font-semibold">RTL</label>
                        </div>
                        </div>
                    </div>
                    <div>
                        <p class="switcher-style-head">Navigation Styles:</p>
                        <div class="grid grid-cols-3  switcher-style">
                          <div class="flex items-center">
                            <input type="radio" name="nav_style" value="vertical" class="ti-form-radio" id="switcher-vertical"
                                @checked(($themeStyle['nav'] ?? 'vertical') === 'vertical')>
                            <label for="switcher-vertical" class="ms-2 font-semibold">Vertical</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" name="nav_style" value="horizontal" class="ti-form-radio" id="switcher-horizontal"
                                @checked(($themeStyle['nav'] ?? 'vertical') === 'horizontal')>
                            <label for="switcher-horizontal" class="ms-2 font-semibold">Horizontal</label>
                        </div>
                        </div>
                    </div>
                        <div>
                        <p class="switcher-style-head">Navigation Menu Style:</p>
                        <div class="grid grid-cols-2 gap-2 switcher-style">
                            <div class="flex">
                            <input type="radio" name="menu_style" value="menu-click" class="ti-form-radio"
                                    id="switcher-menu-click"
                                    @checked(($themeStyle['menuStyle'] ?? 'menu-click') === 'menu-click')>
                            <label for="switcher-menu-click" class="ms-2 font-semibold">Menu Click</label>
                            </div>

                            <div class="flex">
                            <input type="radio" name="menu_style" value="menu-hover" class="ti-form-radio"
                                    id="switcher-menu-hover"
                                    @checked(($themeStyle['menuStyle'] ?? 'menu-click') === 'menu-hover')>
                            <label for="switcher-menu-hover" class="ms-2 font-semibold">Menu Hover</label>
                            </div>

                            <div class="flex">
                            <input type="radio" name="menu_style" value="icon-click" class="ti-form-radio"
                                    id="switcher-icon-click"
                                    @checked(($themeStyle['menuStyle'] ?? 'menu-click') === 'icon-click')>
                            <label for="switcher-icon-click" class="ms-2 font-semibold">Icon Click</label>
                            </div>

                            <div class="flex">
                            <input type="radio" name="menu_style" value="icon-hover" class="ti-form-radio"
                                    id="switcher-icon-hover"
                                    @checked(($themeStyle['menuStyle'] ?? 'menu-click') === 'icon-hover')>
                            <label for="switcher-icon-hover" class="ms-2 font-semibold">Icon Hover</label>
                            </div>
                        </div>
                        <div class="px-4 text-secondary text-xs"><b class="me-2">Note:</b>Works same for both Vertical and Horizontal</div>
                        </div>
                    {{-- Sidemenu Layout Styles (vertical only) --}}
                    <div class="sidemenu-layout-styles">
                    <p class="switcher-style-head">Sidemenu Layout Styles:</p>
                    <div class="grid grid-cols-2 gap-2 switcher-style">

                        @php $side = $themeStyle['sideMenuLayout'] ?? 'default'; @endphp

                        <div class="flex">
                        <input type="radio" name="sidemenu_layout" class="ti-form-radio"
                                id="switcher-default-menu" value="default"
                                @checked($side === 'default')>
                        <label for="switcher-default-menu" class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2 font-semibold">
                            Default Menu
                        </label>
                        </div>

                        <div class="flex">
                        <input type="radio" name="sidemenu_layout" class="ti-form-radio"
                                id="switcher-closed-menu" value="closed"
                                @checked($side === 'closed')>
                        <label for="switcher-closed-menu" class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2 font-semibold">
                            Closed Menu
                        </label>
                        </div>

                        <div class="flex">
                        <input type="radio" name="sidemenu_layout" class="ti-form-radio"
                                id="switcher-icontext-menu" value="icontext"
                                @checked($side === 'icontext')>
                        <label for="switcher-icontext-menu" class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2 font-semibold">
                            Icon Text
                        </label>
                        </div>

                        <div class="flex">
                        <input type="radio" name="sidemenu_layout" class="ti-form-radio"
                                id="switcher-icon-overlay" value="icon-overlay"
                                @checked($side === 'icon-overlay')>
                        <label for="switcher-icon-overlay" class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2 font-semibold">
                            Icon Overlay
                        </label>
                        </div>

                        <div class="flex">
                        <input type="radio" name="sidemenu_layout" class="ti-form-radio"
                                id="switcher-detached" value="detached"
                                @checked($side === 'detached')>
                        <label for="switcher-detached" class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2 font-semibold">
                            Detached
                        </label>
                        </div>

                        <div class="flex">
                        <input type="radio" name="sidemenu_layout" class="ti-form-radio"
                                id="switcher-double-menu" value="doublemenu"
                                @checked($side === 'doublemenu')>
                        <label for="switcher-double-menu" class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2 font-semibold">
                            Double Menu
                        </label>
                        </div>
                    </div>

                    <div class="px-4 text-secondary text-xs">
                        <b class="me-2">Note:</b>Navigation menu styles won't work here.
                    </div>
                    </div>

                    <div>
                        <p class="switcher-style-head">Page Styles:</p>
                        <div class="grid grid-cols-3 switcher-style" id="page-style-group">
                            <div class="flex">
                            <input
                                type="radio"
                                name="data-page-styles"
                                value="regular"
                                id="switcher-regular"
                                class="ti-form-radio"
                                @checked(($themeStyle['pageStyle'] ?? 'regular') === 'regular')
                            >
                            <label for="switcher-regular"
                                    class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2 font-semibold">
                                Regular
                            </label>
                            </div>

                            <div class="flex">
                            <input
                                type="radio"
                                name="data-page-styles"
                                value="classic"
                                id="switcher-classic"
                                class="ti-form-radio"
                                @checked(($themeStyle['pageStyle'] ?? 'regular') === 'classic')
                            >
                            <label for="switcher-classic"
                                    class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2 font-semibold">
                                Classic
                            </label>
                            </div>

                            <div class="flex">
                            <input
                                type="radio"
                                name="data-page-styles"
                                value="modern"
                                id="switcher-modern"
                                class="ti-form-radio"
                                @checked(($themeStyle['pageStyle'] ?? 'regular') === 'modern')
                            >
                            <label for="switcher-modern"
                                    class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2 font-semibold">
                                Modern
                            </label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <p class="switcher-style-head">Layout Width Styles:</p>
                        <div class="grid grid-cols-3 switcher-style">
                        <div class="flex">
                            <input type="radio" name="layout-width" class="ti-form-radio" id="switcher-full-width" checked>
                            <label for="switcher-full-width"
                            class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2  font-semibold">FullWidth</label>
                        </div>
                        <div class="flex">
                            <input type="radio" name="layout-width" class="ti-form-radio" id="switcher-boxed">
                            <label for="switcher-boxed" class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2  font-semibold">Boxed</label>
                        </div>
                        </div>
                    </div>
                    <div>
                        <p class="switcher-style-head">Menu Positions:</p>
                        <div class="grid grid-cols-3  switcher-style">
                        <div class="flex">
                            <input type="radio" name="data-menu-positions" class="ti-form-radio" id="switcher-menu-fixed" checked>
                            <label for="switcher-menu-fixed"
                            class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2  font-semibold">Fixed</label>
                        </div>
                        <div class="flex">
                            <input type="radio" name="data-menu-positions" class="ti-form-radio" id="switcher-menu-scroll">
                            <label for="switcher-menu-scroll"
                            class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2  font-semibold">Scrollable </label>
                        </div>
                        </div>
                    </div>
                    <div>
                        <p class="switcher-style-head">Header Positions:</p>
                        <div class="grid grid-cols-3 switcher-style">
                        <div class="flex">
                            <input type="radio" name="data-header-positions" class="ti-form-radio" id="switcher-header-fixed" checked>
                            <label for="switcher-header-fixed" class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2  font-semibold">
                            Fixed</label>
                        </div>
                        <div class="flex">
                            <input type="radio" name="data-header-positions" class="ti-form-radio" id="switcher-header-scroll">
                            <label for="switcher-header-scroll"
                            class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2  font-semibold">Scrollable
                            </label>
                        </div>
                        </div>
                    </div>
                    <div class="">
                        <p class="switcher-style-head">Loader:</p>
                        <div class="grid grid-cols-3 switcher-style">
                        <div class="flex">
                            <input type="radio" name="page-loader" class="ti-form-radio" id="switcher-loader-enable" checked>
                            <label for="switcher-loader-enable" class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2  font-semibold">
                            Enable</label>
                        </div>
                        <div class="flex">
                            <input type="radio" name="page-loader" class="ti-form-radio" id="switcher-loader-disable">
                            <label for="switcher-loader-disable"
                            class="text-defaultsize text-defaulttextcolor dark:text-defaulttextcolor/70 ms-2  font-semibold">Disable
                            </label>
                        </div>
                        </div>
                    </div>
                    </div>
                    <div id="switcher-2" class="hidden" role="tabpanel" aria-labelledby="switcher-item-2">
                    <div class="theme-colors">
                        <p class="switcher-style-head">Menu Colors:</p>
                        <div class="flex switcher-style space-x-3 rtl:space-x-reverse">
                        <div class="hs-tooltip ti-main-tooltip ti-form-radio switch-select ">
                            <input class="hs-tooltip-toggle ti-form-radio color-input color-white" type="radio" name="menu-colors"
                            id="switcher-menu-light" checked>
                            <span
                            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-black text-xs font-medium !text-white shadow-sm dark:!bg-black"
                            role="tooltip">
                            Light Menu
                            </span>
                        </div>
                        <div class="hs-tooltip ti-main-tooltip ti-form-radio switch-select ">
                            <input class="hs-tooltip-toggle ti-form-radio color-input color-dark" type="radio" name="menu-colors"
                            id="switcher-menu-dark" checked>
                            <span
                            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-black text-xs font-medium !text-white shadow-sm dark:!bg-black"
                            role="tooltip">
                            Dark Menu
                            </span>
                        </div>
                        <div class="hs-tooltip ti-main-tooltip ti-form-radio switch-select ">
                            <input class="hs-tooltip-toggle ti-form-radio color-input color-primary" type="radio" name="menu-colors"
                            id="switcher-menu-primary">
                            <span
                            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-black text-xs font-medium !text-white shadow-sm dark:!bg-black"
                            role="tooltip">
                            Color Menu
                            </span>
                        </div>
                        <div class="hs-tooltip ti-main-tooltip ti-form-radio switch-select ">
                            <input class="hs-tooltip-toggle ti-form-radio color-input color-gradient" type="radio" name="menu-colors"
                            id="switcher-menu-gradient">
                            <span
                            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-black text-xs font-medium !text-white shadow-sm dark:!bg-black"
                            role="tooltip">
                            Gradient Menu
                            </span>
                        </div>
                        <div class="hs-tooltip ti-main-tooltip ti-form-radio switch-select ">
                            <input class="hs-tooltip-toggle ti-form-radio color-input color-transparent" type="radio" name="menu-colors"
                            id="switcher-menu-transparent">
                            <span
                            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-black text-xs font-medium !text-white shadow-sm dark:!bg-black"
                            role="tooltip">
                            Transparent Menu
                            </span>
                        </div>
                        </div>
                        <div class="px-4 text-[#8c9097] dark:text-white/50 text-[.6875rem]"><b class="me-2">Note:</b>If you want to change color Menu
                        dynamically
                        change from below Theme Primary color picker.</div>
                    </div>
                    <div class="theme-colors">
                        <p class="switcher-style-head">Header Colors:</p>
                        <div class="flex switcher-style space-x-3 rtl:space-x-reverse">
                        <div class="hs-tooltip ti-main-tooltip ti-form-radio switch-select ">
                            <input class="hs-tooltip-toggle ti-form-radio color-input color-white !border" type="radio" name="header-colors"
                            id="switcher-header-light" checked>
                            <span
                            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-black text-xs font-medium !text-white shadow-sm dark:!bg-black"
                            role="tooltip">
                            Light Header
                            </span>
                        </div>
                        <div class="hs-tooltip ti-main-tooltip ti-form-radio switch-select ">
                            <input class="hs-tooltip-toggle ti-form-radio color-input color-dark" type="radio" name="header-colors"
                            id="switcher-header-dark">
                            <span
                            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-black text-xs font-medium !text-white shadow-sm dark:!bg-black"
                            role="tooltip">
                            Dark Header
                            </span>
                        </div>
                        <div class="hs-tooltip ti-main-tooltip ti-form-radio switch-select ">
                            <input class="hs-tooltip-toggle ti-form-radio color-input color-primary" type="radio" name="header-colors"
                            id="switcher-header-primary">
                            <span
                            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-black text-xs font-medium !text-white shadow-sm dark:!bg-black"
                            role="tooltip">
                            Color Header
                            </span>
                        </div>
                        <div class="hs-tooltip ti-main-tooltip ti-form-radio switch-select ">
                            <input class="hs-tooltip-toggle ti-form-radio color-input color-gradient" type="radio" name="header-colors"
                            id="switcher-header-gradient">
                            <span
                            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-black text-xs font-medium !text-white shadow-sm dark:!bg-black"
                            role="tooltip">
                            Gradient Header
                            </span>
                        </div>
                        <div class="hs-tooltip ti-main-tooltip ti-form-radio switch-select ">
                            <input class="hs-tooltip-toggle ti-form-radio color-input color-transparent" type="radio"
                            name="header-colors" id="switcher-header-transparent">
                            <span
                            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-black text-xs font-medium !text-white shadow-sm dark:!bg-black"
                            role="tooltip">
                            Transparent Header
                            </span>
                        </div>
                        </div>
                        <div class="px-4 text-[#8c9097] dark:text-white/50 text-[.6875rem]"><b class="me-2">Note:</b>If you want to change color
                        Header dynamically
                        change from below Theme Primary color picker.</div>
                    </div>
                    <div class="theme-colors">
                        <p class="switcher-style-head">Theme Primary:</p>
                        <div class="flex switcher-style space-x-3 rtl:space-x-reverse">
                        <div class="ti-form-radio switch-select">
                            <input class="ti-form-radio color-input color-primary-1" type="radio" name="theme-primary"
                            id="switcher-primary" checked>
                        </div>
                        <div class="ti-form-radio switch-select">
                            <input class="ti-form-radio color-input color-primary-2" type="radio" name="theme-primary"
                            id="switcher-primary1">
                        </div>
                        <div class="ti-form-radio switch-select">
                            <input class="ti-form-radio color-input color-primary-3" type="radio" name="theme-primary"
                            id="switcher-primary2">
                        </div>
                        <div class="ti-form-radio switch-select">
                            <input class="ti-form-radio color-input color-primary-4" type="radio" name="theme-primary"
                            id="switcher-primary3">
                        </div>
                        <div class="ti-form-radio switch-select">
                            <input class="ti-form-radio color-input color-primary-5" type="radio" name="theme-primary"
                            id="switcher-primary4">
                        </div>
                        <div class="ti-form-radio switch-select ps-0 mt-1 color-primary-light">
                            <div class="theme-container-primary"></div>
                            <div class="pickr-container-primary"></div>
                        </div>
                        </div>
                    </div>
                    <div class="theme-colors">
                        <p class="switcher-style-head">Theme Background:</p>
                        <div class="flex switcher-style space-x-3 rtl:space-x-reverse">
                        <div class="ti-form-radio switch-select">
                            <input class="ti-form-radio color-input color-bg-1" type="radio" name="theme-background"
                            id="switcher-background" checked>
                        </div>
                        <div class="ti-form-radio switch-select">
                            <input class="ti-form-radio color-input color-bg-2" type="radio" name="theme-background"
                            id="switcher-background1">
                        </div>
                        <div class="ti-form-radio switch-select">
                            <input class="ti-form-radio color-input color-bg-3" type="radio" name="theme-background"
                            id="switcher-background2">
                        </div>
                        <div class="ti-form-radio switch-select">
                            <input class="ti-form-radio color-input color-bg-4" type="radio" name="theme-background"
                            id="switcher-background3">
                        </div>
                        <div class="ti-form-radio switch-select">
                            <input class="ti-form-radio color-input color-bg-5" type="radio" name="theme-background"
                            id="switcher-background4">
                        </div>
                        <div class="ti-form-radio switch-select ps-0 mt-1 color-bg-transparent">
                            <div class="theme-container-background hidden"></div>
                            <div class="pickr-container-background"></div>
                        </div>
                        </div>
                    </div>
                    <div class="menu-image theme-colors">
                        <p class="switcher-style-head">Menu With Background Image:</p>
                        <div class="flex switcher-style space-x-3 rtl:space-x-reverse flex-wrap gap-3">
                        <div class="ti-form-radio switch-select">
                            <input class="ti-form-radio bgimage-input bg-img1" type="radio" name="theme-images" id="switcher-bg-img">
                        </div>
                        <div class="ti-form-radio switch-select">
                            <input class="ti-form-radio bgimage-input bg-img2" type="radio" name="theme-images" id="switcher-bg-img1">
                        </div>
                        <div class="ti-form-radio switch-select">
                            <input class="ti-form-radio bgimage-input bg-img3" type="radio" name="theme-images" id="switcher-bg-img2">
                        </div>
                        <div class="ti-form-radio switch-select">
                            <input class="ti-form-radio bgimage-input bg-img4" type="radio" name="theme-images" id="switcher-bg-img3">
                        </div>
                        <div class="ti-form-radio switch-select">
                            <input class="ti-form-radio bgimage-input bg-img5" type="radio" name="theme-images" id="switcher-bg-img4">
                        </div>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="ti-offcanvas-footer sm:flex justify-between">
                    <a href="javascript:void(0);" id="reset-all" class="w-full ti-btn btn-wave ti-btn-danger-full m-1">Reset</a>
                </div>
            </div>


