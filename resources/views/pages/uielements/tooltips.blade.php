@extends('layouts.master')

@section('styles')
 
        <!-- Prism CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/prismjs/themes/prism-coy.min.css')}}">

@endsection

@section('content')

                <!-- Page Header -->
                <div class="block justify-between page-header md:flex">
                    <div>
                        <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Tooltips</h3>
                    </div>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                        <li class="text-[0.813rem] ps-[0.5rem]">
                          <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                           Ui Elements
                            <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                          </a>
                        </li>
                        <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                            Tooltips
                        </li>
                    </ol>
                </div>
                <!-- Page Header Close -->

                <!-- Start:: row-1 -->
                <div class="grid grid-cols-12 gap-6">
                    <div class="xl:col-span-12 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Tooltip Directions
                                </div>
                                <div class="prism-toggle">
                                    <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !text-[0.75rem] !font-medium">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="ti-btn-list">
                                    <div class="hs-tooltip ti-main-tooltip">
                                        <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary-full">
                                            Tooltip on top
                                            <span
                                                class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                role="tooltip">
                                                Tooltip on top
                                            </span>
                                        </button>
                                    </div>
                                    <div class="hs-tooltip ti-main-tooltip ltr:[--placement:right] rtl:[--placement:left]">
                                        <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary-full">
                                            Tooltip on right
                                            <span
                                                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                                role="tooltip">
                                                Tooltip on right
                                            </span>
                                        </button>
                                    </div>
                                    <div class="hs-tooltip ti-main-tooltip [--placement:bottom]">
                                        <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary-full">
                                            Tooltip on bottom
                                            <span
                                                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                                role="tooltip">
                                                Tooltip on bottom
                                            </span>
                                        </button>
                                    </div>
                                    <div class="hs-tooltip ti-main-tooltip ltr:[--placement:left] rtl:[--placement:right]">
                                        <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary-full">
                                            Tooltip on left
                                            <span
                                                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                                role="tooltip">
                                                Tooltip on left
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer hidden !border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html"> &lt;div class="ti-btn-list"&gt;
&lt;div class="hs-tooltip ti-main-tooltip"&gt;
    &lt;button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary-full"&gt;
        Tooltip on top
        &lt;span class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm " role="tooltip"&gt;
        Tooltip on top
        &lt;/span&gt;
    &lt;/button&gt;
    &lt;/div&gt;
    &lt;div class="hs-tooltip ti-main-tooltip [--placement:right]"&gt;
    &lt;button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary-full"&gt;
        Tooltip on right
        &lt;span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700" role="tooltip"&gt;
        Tooltip on right
        &lt;/span&gt;
    &lt;/button&gt;
    &lt;/div&gt;
    &lt;div class="hs-tooltip ti-main-tooltip [--placement:bottom]"&gt;
    &lt;button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary-full"&gt;
        Tooltip on bottom
        &lt;span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700" role="tooltip"&gt;
        Tooltip on bottom
        &lt;/span&gt;
    &lt;/button&gt;
    &lt;/div&gt;
    &lt;div class="hs-tooltip ti-main-tooltip [--placement:left]"&gt;
    &lt;button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary-full"&gt;
        Tooltip on left
        &lt;span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700" role="tooltip"&gt;
        Tooltip on left
        &lt;/span&gt;
    &lt;/button&gt;
    &lt;/div&gt;
&lt;/div&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End:: row-1 -->

                <!-- Start:: row-2 -->
                <div class="grid grid-cols-12 gap-6">
                    <div class="xl:col-span-12 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Colored Tooltips
                                </div>
                                <div class="prism-toggle">
                                    <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !text-[0.75rem] !font-medium">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="ti-btn-list">
                                    <div class="hs-tooltip ti-main-tooltip [--placement:top]">
                                        <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary-full">
                                            Primary Tooltip
                                            <span
                                                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-primary !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                                role="tooltip">
                                                Primary Tooltip
                                            </span>
                                        </button>
                                    </div>
                                    <div class="hs-tooltip ti-main-tooltip ltr:[--placement:right] rtl:[--placement:left]">
                                        <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-secondary-full">
                                            Secondary Tooltip
                                            <span
                                                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-secondary !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                                role="tooltip">
                                                Secondary Tooltip
                                            </span>
                                        </button>
                                    </div>
                                    <div class="hs-tooltip ti-main-tooltip [--placement:bottom]">
                                        <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-warning-full">
                                            Warning Tooltip
                                            <span
                                                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-warning !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                                role="tooltip">
                                                Warning Tooltip
                                            </span>
                                        </button>
                                    </div>
                                    <div class="hs-tooltip ti-main-tooltip ltr:[--placement:left] rtl:[--placement:right]">
                                        <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-info-full">
                                            Info Tooltip
                                            <span
                                                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-info !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                                role="tooltip">
                                                Info Tooltip
                                            </span>
                                        </button>
                                    </div>
                                    <div class="hs-tooltip ti-main-tooltip [--placement:top]">
                                        <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-success-full">
                                            Success Tooltip
                                            <span
                                                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-success !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                                role="tooltip">
                                                Success Tooltip
                                            </span>
                                        </button>
                                    </div>
                                    <div class="hs-tooltip ti-main-tooltip [--placement:bottom]">
                                        <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger-full">
                                            Danger Tooltip
                                            <span
                                                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-danger !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                                role="tooltip">
                                                Danger Tooltip
                                            </span>
                                        </button>
                                    </div>
                                    <div class="hs-tooltip ti-main-tooltip [--placement:bottom]">
                                        <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-light">
                                            Light Tooltip
                                            <span
                                                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-light !text-xs !font-medium !text-defaulttextcolor shadow-sm dark:bg-slate-700"
                                                role="tooltip">
                                                Light Tooltip</span>
                                        </button>
                                    </div>
                                    <div class="hs-tooltip ti-main-tooltip [--placement:bottom]">
                                        <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-dark">
                                            Dark Tooltip
                                            <span
                                                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                                role="tooltip">
                                                Dark Tooltip
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer hidden !border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">&lt;div class="ti-btn-list"&gt;
    &lt;div class="hs-tooltip ti-main-tooltip [--placement:top]"&gt;
        &lt;button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary-full"&gt;
            Primary Tooltip
            &lt;span
                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-primary !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                role="tooltip"&gt;
                Primary Tooltip
            &lt;/span&gt;
        &lt;/button&gt;
    &lt;/div&gt;
    &lt;div class="hs-tooltip ti-main-tooltip [--placement:right]"&gt;
        &lt;button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-secondary-full"&gt;
            Secondary Tooltip
            &lt;span
                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-secondary !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                role="tooltip"&gt;
                Secondary Tooltip
            &lt;/span&gt;
        &lt;/button&gt;
    &lt;/div&gt;
    &lt;div class="hs-tooltip ti-main-tooltip [--placement:bottom]"&gt;
        &lt;button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-warning-full"&gt;
            Warning Tooltip
            &lt;span
                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-warning !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                role="tooltip"&gt;
                Warning Tooltip
            &lt;/span&gt;
        &lt;/button&gt;
    &lt;/div&gt;
    &lt;div class="hs-tooltip ti-main-tooltip [--placement:left]"&gt;
        &lt;button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-info-full"&gt;
            Info Tooltip
            &lt;span
                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-info !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                role="tooltip"&gt;
                Info Tooltip
            &lt;/span&gt;
        &lt;/button&gt;
    &lt;/div&gt;
    &lt;div class="hs-tooltip ti-main-tooltip [--placement:top]"&gt;
        &lt;button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-success-full"&gt;
            Success Tooltip
            &lt;span
                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-success !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                role="tooltip"&gt;
                Success Tooltip
            &lt;/span&gt;
        &lt;/button&gt;
    &lt;/div&gt;
    &lt;div class="hs-tooltip ti-main-tooltip [--placement:bottom]"&gt;
        &lt;button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger-full"&gt;
            Danger Tooltip
            &lt;span
                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-danger !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                role="tooltip"&gt;
                Danger Tooltip
            &lt;/span&gt;
        &lt;/button&gt;
    &lt;/div&gt;
    &lt;div class="hs-tooltip ti-main-tooltip [--placement:bottom]"&gt;
        &lt;button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-light"&gt;
            Light Tooltip
            &lt;span
                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-light !text-xs !font-medium !text-defaulttextcolor shadow-sm dark:bg-slate-700"
                role="tooltip"&gt;
                Light Tooltip
        &lt;/button&gt;
    &lt;/div&gt;
    &lt;div class="hs-tooltip ti-main-tooltip [--placement:bottom]"&gt;
        &lt;button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-dark"&gt;
            Dark Tooltip
            &lt;span
                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                role="tooltip"&gt;
                Dark Tooltip
            &lt;/span&gt;
        &lt;/button&gt;
    &lt;/div&gt;
&lt;/div&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End:: row-2 -->

                <!-- Start:: row-3 -->
                <div class="grid grid-cols-12 gap-6">
                    <div class="xl:col-span-4 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Tooltips on links
                                </div>
                                <div class="prism-toggle">
                                    <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !text-[0.75rem] !font-medium">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="hs-tooltip ti-main-tooltip [--placement:top]">
                                    <p class="text-muted mb-0">
                                        Hover on the link to view the <a aria-label="anchor" href="javascript:void(0);"
                                             
                                            title="Link Tooltip"></a>
                                        <span class="hs-tooltip-toggle !text-primary">
                                            Tooltip
                                            <span
                                                class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-1 !bg-primary !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                                role="tooltip">
                                                Link Tooltip
                                            </span>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="box-footer hidden !border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">&lt;div class="hs-tooltip ti-main-tooltip [--placement:top]"&gt;
&lt;p class="text-muted mb-0"&gt;
    Hover on the link to view the &lt;a href="javascript:void(0);"
        title="Link Tooltip"&gt;&lt;/a&gt;
    &lt;span class="hs-tooltip-toggle !text-primary"&gt;
        Tooltip
        &lt;span
            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-1 !bg-primary !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
            role="tooltip"&gt;
            Link Tooltip
        &lt;/span&gt;
    &lt;/span&gt;
&lt;/p&gt;
&lt;/div&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-4 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    With an SVG's
                                </div>
                                <div class="prism-toggle">
                                    <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !text-[0.75rem] !font-medium">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex">
                                <div class="hs-tooltip ti-main-tooltip [--placement:top]">
                                    <button type="button" class="hs-tooltip-toggle me-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="fill-primary" height="24px"
                                            viewBox="0 0 24 24" width="24px" fill="#000000">
                                            <path d="M0 0h24v24H0V0z" fill="none" />
                                            <path
                                                d="M12 5.69l5 4.5V18h-2v-6H9v6H7v-7.81l5-4.5M12 3L2 12h3v8h6v-6h2v6h6v-8h3L12 3z" />
                                        </svg>
                                        <span
                                            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-primary !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                            role="tooltip">
                                            Home
                                        </span>
                                    </button>
                                </div>
                                <div class="hs-tooltip ti-main-tooltip [--placement:top]">
                                    <button type="button" class="hs-tooltip-toggle me-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="fill-secondary" height="24px"
                                            viewBox="0 0 24 24" width="24px" fill="#000000">
                                            <path d="M0 0h24v24H0V0z" fill="none" />
                                            <path
                                                d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6zm-2 0l-8 5-8-5h16zm0 12H4V8l8 5 8-5v10z" />
                                        </svg>
                                        <span
                                            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-secondary !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                            role="tooltip">
                                            Message
                                        </span>
                                    </button>
                                </div>
                                <div class="hs-tooltip ti-main-tooltip [--placement:top]">
                                    <button type="button" class="hs-tooltip-toggle me-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="fill-warning"
                                            enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24"
                                            width="24px" fill="#000000">
                                            <g>
                                                <rect fill="none" height="24" width="24" />
                                            </g>
                                            <g>
                                                <path
                                                    d="M20,9V6h-2v3h-3v2h3v3h2v-3h3V9H20z M9,12c2.21,0,4-1.79,4-4c0-2.21-1.79-4-4-4S5,5.79,5,8C5,10.21,6.79,12,9,12z M9,6 c1.1,0,2,0.9,2,2c0,1.1-0.9,2-2,2S7,9.1,7,8C7,6.9,7.9,6,9,6z M15.39,14.56C13.71,13.7,11.53,13,9,13c-2.53,0-4.71,0.7-6.39,1.56 C1.61,15.07,1,16.1,1,17.22V20h16v-2.78C17,16.1,16.39,15.07,15.39,14.56z M15,18H3v-0.78c0-0.38,0.2-0.72,0.52-0.88 C4.71,15.73,6.63,15,9,15c2.37,0,4.29,0.73,5.48,1.34C14.8,16.5,15,16.84,15,17.22V18z" />
                                            </g>
                                        </svg>
                                        <span
                                            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-warning !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                            role="tooltip">
                                            Add User
                                        </span>
                                    </button>
                                </div>
                                <div class="hs-tooltip ti-main-tooltip [--placement:top]">
                                    <button type="button" class="hs-tooltip-toggle me-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="fill-info" height="24px"
                                            viewBox="0 0 24 24" width="24px" fill="#000000">
                                            <path d="M0 0h24v24H0V0z" fill="none" />
                                            <path
                                                d="M4.01 6.03l7.51 3.22-7.52-1 .01-2.22m7.5 8.72L4 17.97v-2.22l7.51-1M2.01 3L2 10l15 2-15 2 .01 7L23 12 2.01 3z" />
                                        </svg>
                                        <span
                                            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-info !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                            role="tooltip">
                                            Send File
                                        </span>
                                    </button>
                                </div>
                                <div class="hs-tooltip ti-main-tooltip [--placement:top]">
                                    <button type="button" class="hs-tooltip-toggle me-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="fill-success" height="24px"
                                            viewBox="0 0 24 24" width="24px" fill="#000000">
                                            <path d="M0 0h24v24H0V0z" fill="none" />
                                            <path
                                                d="M6 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm12 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-6 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                        </svg>
                                        <span
                                            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-success !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                            role="tooltip">
                                            Action
                                        </span>
                                    </button>
                                </div>
                            </div>
                            <div class="box-footer hidden !border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html"> &lt;div class="hs-tooltip ti-main-tooltip [--placement:top]"&gt;
    &lt;button type="button" class="hs-tooltip-toggle me-4"&gt;
        &lt;svg xmlns="http://www.w3.org/2000/svg" class="fill-primary" height="24px"
            viewBox="0 0 24 24" width="24px" fill="#000000"&gt;
            &lt;path d="M0 0h24v24H0V0z" fill="none" /&gt;
            &lt;path
                d="M12 5.69l5 4.5V18h-2v-6H9v6H7v-7.81l5-4.5M12 3L2 12h3v8h6v-6h2v6h6v-8h3L12 3z" /&gt;
        &lt;/svg&gt;
        &lt;span
            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-primary !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
            role="tooltip"&gt;
            Home
        &lt;/span&gt;
    &lt;/button&gt;
&lt;/div&gt;
&lt;div class="hs-tooltip ti-main-tooltip [--placement:top]"&gt;
    &lt;button type="button" class="hs-tooltip-toggle me-4"&gt;
        &lt;svg xmlns="http://www.w3.org/2000/svg" class="fill-secondary" height="24px"
            viewBox="0 0 24 24" width="24px" fill="#000000"&gt;
            &lt;path d="M0 0h24v24H0V0z" fill="none" /&gt;
            &lt;path
                d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6zm-2 0l-8 5-8-5h16zm0 12H4V8l8 5 8-5v10z" /&gt;
        &lt;/svg&gt;
        &lt;span
            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-secondary !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
            role="tooltip"&gt;
            Message
        &lt;/span&gt;
    &lt;/button&gt;
&lt;/div&gt;
&lt;div class="hs-tooltip ti-main-tooltip [--placement:top]"&gt;
    &lt;button type="button" class="hs-tooltip-toggle me-4"&gt;
        &lt;svg xmlns="http://www.w3.org/2000/svg" class="fill-warning"
            enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24"
            width="24px" fill="#000000"&gt;
            &lt;g&gt;
                &lt;rect fill="none" height="24" width="24" /&gt;
            &lt;/g&gt;
            &lt;g&gt;
                &lt;path
                    d="M20,9V6h-2v3h-3v2h3v3h2v-3h3V9H20z M9,12c2.21,0,4-1.79,4-4c0-2.21-1.79-4-4-4S5,5.79,5,8C5,10.21,6.79,12,9,12z M9,6 c1.1,0,2,0.9,2,2c0,1.1-0.9,2-2,2S7,9.1,7,8C7,6.9,7.9,6,9,6z M15.39,14.56C13.71,13.7,11.53,13,9,13c-2.53,0-4.71,0.7-6.39,1.56 C1.61,15.07,1,16.1,1,17.22V20h16v-2.78C17,16.1,16.39,15.07,15.39,14.56z M15,18H3v-0.78c0-0.38,0.2-0.72,0.52-0.88 C4.71,15.73,6.63,15,9,15c2.37,0,4.29,0.73,5.48,1.34C14.8,16.5,15,16.84,15,17.22V18z" /&gt;
            &lt;/g&gt;
        &lt;/svg&gt;
        &lt;span
            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-warning !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
            role="tooltip"&gt;
            Add User
        &lt;/span&gt;
    &lt;/button&gt;
&lt;/div&gt;
&lt;div class="hs-tooltip ti-main-tooltip [--placement:top]"&gt;
    &lt;button type="button" class="hs-tooltip-toggle me-4"&gt;
        &lt;svg xmlns="http://www.w3.org/2000/svg" class="fill-info" height="24px"
            viewBox="0 0 24 24" width="24px" fill="#000000"&gt;
            &lt;path d="M0 0h24v24H0V0z" fill="none" /&gt;
            &lt;path
                d="M4.01 6.03l7.51 3.22-7.52-1 .01-2.22m7.5 8.72L4 17.97v-2.22l7.51-1M2.01 3L2 10l15 2-15 2 .01 7L23 12 2.01 3z" /&gt;
        &lt;/svg&gt;
        &lt;span
            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-info !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
            role="tooltip"&gt;
            Send File
        &lt;/span&gt;
    &lt;/button&gt;
&lt;/div&gt;
&lt;div class="hs-tooltip ti-main-tooltip [--placement:top]"&gt;
    &lt;button type="button" class="hs-tooltip-toggle me-4"&gt;
        &lt;svg xmlns="http://www.w3.org/2000/svg" class="fill-success" height="24px"
            viewBox="0 0 24 24" width="24px" fill="#000000"&gt;
            &lt;path d="M0 0h24v24H0V0z" fill="none" /&gt;
            &lt;path
                d="M6 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm12 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-6 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" /&gt;
        &lt;/svg&gt;
        &lt;span
            class="hs-tooltip-content ti-main-tooltip-content !py-1 !px-2 !bg-success !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
            role="tooltip"&gt;
            Action
        &lt;/span&gt;
    &lt;/button&gt;
&lt;/div&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-4 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Disabled elements
                                </div>
                                <div class="prism-toggle">
                                    <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !text-[0.75rem] !font-medium">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="hs-tooltip ti-main-tooltip">
                                    <button type="button"
                                        class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary-full opacity-[0.6]">
                                        Disabled button
                                        <span
                                            class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                            role="tooltip">
                                            Disabled Tooltip
                                        </span>
                                    </button>
                                </div>
                            </div>
                            <div class="box-footer hidden !border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">&lt;div class="hs-tooltip ti-main-tooltip"&gt;
&lt;button type="button"
    class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary-full opacity-[0.6]"&gt;
    Disabled button
    &lt;span
        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
        role="tooltip"&gt;
        Default Tooltip
    &lt;/span&gt;
&lt;/button&gt;
&lt;/div&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End:: row-3 -->

                <!-- Start:: row-4 -->
                <div class="grid grid-cols-12 gap-6">
                    <div class="xl:col-span-4 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Tooltip For Images
                                </div>
                                <div class="prism-toggle">
                                    <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !text-[0.75rem] !font-medium">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="hs-tooltip ti-main-tooltip">
                                    <button type="button"
                                        class="hs-tooltip-toggle avatar avatar-md me-2 online avatar-rounded">
                                        <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="img">
                                    </button>
                                    <span
                                        class="hs-tooltip-content  ti-main-tooltip-content !py-1 !px-2 !bg-primary !text-xs !font-medium !text-white shadow-sm "
                                        role="tooltip">
                                        Alex Carey
                                    </span>
                                </div>
                                <div class="hs-tooltip ti-main-tooltip">
                                    <button type="button"
                                        class="hs-tooltip-toggle avatar avatar-lg me-2 online avatar-rounded">
                                        <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="img">
                                    </button>
                                    <span
                                        class="hs-tooltip-content  ti-main-tooltip-content !py-1 !px-1 !bg-primary !text-xs !font-medium !text-white shadow-sm "
                                        role="tooltip">
                                        Marina Kai
                                    </span>
                                </div>
                                <div class="hs-tooltip ti-main-tooltip">
                                    <button type="button"
                                        class="hs-tooltip-toggle avatar avatar-xl me-2 offline avatar-rounded">
                                        <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="img">
                                    </button>
                                    <span
                                        class="hs-tooltip-content  ti-main-tooltip-content !py-1 !px-1 !bg-primary !text-xs !font-medium !text-white shadow-sm "
                                        role="tooltip">
                                        Marina Kai
                                    </span>
                                </div>
                            </div>
                            <div class="box-footer hidden !border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">&lt;div class="hs-tooltip ti-main-tooltip"&gt;
&lt;button type="button"
    class="hs-tooltip-toggle avatar avatar-md me-2 online avatar-rounded"&gt;
    &lt;img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="img"&gt;
&lt;/button&gt;
&lt;span
    class="hs-tooltip-content  ti-main-tooltip-content !py-1 !px-2 !bg-primary !text-xs !font-medium !text-white shadow-sm "
    role="tooltip"&gt;
    Alex Carey
&lt;/span&gt;
&lt;/div&gt;
&lt;div class="hs-tooltip ti-main-tooltip"&gt;
&lt;button type="button"
    class="hs-tooltip-toggle avatar avatar-lg me-2 online avatar-rounded"&gt;
    &lt;img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="img"&gt;
&lt;/button&gt;
&lt;span
    class="hs-tooltip-content  ti-main-tooltip-content !py-1 !px-1 !bg-primary !text-xs !font-medium !text-white shadow-sm "
    role="tooltip"&gt;
    Marina Kai
&lt;/span&gt;
&lt;/div&gt;
&lt;div class="hs-tooltip ti-main-tooltip"&gt;
&lt;button type="button"
    class="hs-tooltip-toggle avatar avatar-xl me-2 offline avatar-rounded"&gt;
    &lt;img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="img"&gt;
&lt;/button&gt;
&lt;span
    class="hs-tooltip-content  ti-main-tooltip-content !py-1 !px-1 !bg-primary !text-xs !font-medium !text-white shadow-sm "
    role="tooltip"&gt;
    Marina Kai
&lt;/span&gt;
&lt;/div&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End:: row-4 -->
                
@endsection

@section('scripts')

        <!-- Prism JS -->
        <script src="{{asset('build/assets/libs/prismjs/prism.js')}}"></script>
        @vite('resources/assets/js/prism-custom.js')
        

@endsection