@extends('layouts.master')

@section('styles')
 
        <!-- Prism CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/prismjs/themes/prism-coy.min.css')}}">

@endsection

@section('content')

                <!-- Page Header -->
                <div class="block justify-between page-header md:flex">
                    <div>
                        <h3
                            class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">
                            Badge</h3>
                    </div>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                        <li class="text-[0.813rem] ps-[0.5rem]">
                            <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate"
                                href="javascript:void(0);">
                                Ui Elements
                                <i
                                    class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                            </a>
                        </li>
                        <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 "
                            aria-current="page">
                            Badge
                        </li>
                    </ol>
                </div>
                <!-- Page Header Close -->

                <!-- Start::row-1 -->
                <div class="grid grid-cols-12 gap-x-6">
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Badges
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex flex-wrap gap-2">
                                <span class="badge bg-primary text-white">Primary</span>
                                <span class="badge bg-secondary text-white">Secondary</span>
                                <span class="badge bg-success text-white">Success</span>
                                <span class="badge bg-danger text-white">Danger</span>
                                <span class="badge bg-warning text-white">Warning</span>
                                <span class="badge bg-info text-white">Info</span>
                                <span class="badge bg-light text-dark">Light</span>
                                <span class="badge bg-black text-white">Dark</span>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">&lt;span class="badge bg-primary text-white"&gt;Primary&lt;/span&gt;
&lt;span class="badge bg-secondary text-white"&gt;Secondary&lt;/span&gt;
&lt;span class="badge bg-success text-white"&gt;Success&lt;/span&gt;
&lt;span class="badge bg-danger text-white"&gt;Danger&lt;/span&gt;
&lt;span class="badge bg-warning text-white"&gt;Warning&lt;/span&gt;
&lt;span class="badge bg-info text-white"&gt;Info&lt;/span&gt;
&lt;span class="badge bg-light text-dark"&gt;Light&lt;/span&gt;
&lt;span class="badge bg-black text-white"&gt;Dark&lt;/span&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Pill badges
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex flex-wrap gap-2">
                                <span class="badge !rounded-full bg-primary text-white">Primary</span>
                                <span class="badge !rounded-full bg-secondary text-white">Secondary</span>
                                <span class="badge !rounded-full bg-success text-white">Success</span>
                                <span class="badge !rounded-full bg-danger text-white">Danger</span>
                                <span class="badge !rounded-full bg-warning text-white">Warning</span>
                                <span class="badge !rounded-full bg-info text-white">Info</span>
                                <span class="badge !rounded-full bg-light text-dark">Light</span>
                                <span class="badge !rounded-full bg-black text-white">Dark</span>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre
                                    class="language-html"><code class="language-html">&lt;span class="badge !rounded-full bg-primary text-white"&gt;Primary&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-secondary text-white"&gt;Secondary&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-success text-white"&gt;Success&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-danger text-white"&gt;Danger&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-warning text-white"&gt;Warning&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-info text-white"&gt;Info&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-light text-dark"&gt;Light&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-black text-white"&gt;Dark&lt;/span&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End::row-1 -->

                <!-- Start::row-2 -->
                <div class="grid grid-cols-12 gap-x-6">
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Light Badges
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex flex-wrap gap-2">
                                <span class="badge bg-primary/10 text-primary">Primary</span>
                                <span class="badge bg-secondary/10 text-secondary">Secondary</span>
                                <span class="badge bg-success/10 text-success">Success</span>
                                <span class="badge bg-danger/10 text-danger">Danger</span>
                                <span class="badge bg-warning/10 text-warning">Warning</span>
                                <span class="badge bg-info/10 text-info">Info</span>
                                <span class="badge bg-light/10 text-black dark:text-defaulttextcolor/70">Light</span>
                                <span class="badge bg-black/10 text-black dark:text-defaulttextcolor/70">Dark</span>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre
                                    class="language-html"><code class="language-html">&lt;span class="badge bg-primary/10 text-primary"&gt;Primary&lt;/span&gt;
                                    &lt;span class="badge bg-secondary/10 text-secondary"&gt;Secondary&lt;/span&gt;
                                    &lt;span class="badge bg-success/10 text-success"&gt;Success&lt;/span&gt;
                                    &lt;span class="badge bg-danger/10 text-danger"&gt;Danger&lt;/span&gt;
                                    &lt;span class="badge bg-warning/10 text-warning"&gt;Warning&lt;/span&gt;
                                    &lt;span class="badge bg-info/10 text-info"&gt;Info&lt;/span&gt;
                                    &lt;span class="badge bg-light/10 text-black dark:text-defaulttextcolor/70"&gt;Light&lt;/span&gt;
                                    &lt;span class="badge bg-black/10 text-black dark:text-defaulttextcolor/70"&gt;Dark&lt;/span&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Light Pill Badges
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex flex-wrap gap-2">
                                <span class="badge !rounded-full bg-primary/10 text-primary">Primary</span>
                                <span class="badge !rounded-full bg-secondary/10 text-secondary">Secondary</span>
                                <span class="badge !rounded-full bg-success/10 text-success">Success</span>
                                <span class="badge !rounded-full bg-danger/10 text-danger">Danger</span>
                                <span class="badge !rounded-full bg-warning/10 text-warning">Warning</span>
                                <span class="badge !rounded-full bg-info/10 text-info">Info</span>
                                <span class="badge !rounded-full bg-light/10 text-dark">Light</span>
                                <span class="badge !rounded-full bg-black/10">Dark</span>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">&lt;span class="badge !rounded-full bg-primary/10 text-primary"&gt;Primary&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-secondary/10 text-secondary"&gt;Secondary&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-success/10 text-success"&gt;Success&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-danger/10 text-danger"&gt;Danger&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-warning/10 text-warning"&gt;Warning&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-info/10 text-info"&gt;Info&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-light/10 text-dark"&gt;Light&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-black/10"&gt;Dark&lt;/span&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End::row-2 -->

                <!-- Start::row-3 -->
                <div class="grid grid-cols-12 gap-x-6">
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Gradient Badges
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex flex-wrap gap-2">
                                <span class="badge bg-primary-gradient">Primary</span>
                                <span class="badge bg-secondary-gradient">Secondary</span>
                                <span class="badge bg-success-gradient">Success</span>
                                <span class="badge bg-danger-gradient">Danger</span>
                                <span class="badge bg-warning-gradient">Warning</span>
                                <span class="badge bg-info-gradient">Info</span>
                                <span class="badge bg-orange-gradient">orange</span>
                                <span class="badge bg-purple-gradient">purple</span>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">
                                    &lt;span class="badge bg-primary-gradient"&gt;Primary&lt;/span&gt;
                                    &lt;span class="badge bg-secondary-gradient"&gt;Secondary&lt;/span&gt;
                                    &lt;span class="badge bg-success-gradient"&gt;Success&lt;/span&gt;
                                    &lt;span class="badge bg-danger-gradient"&gt;Danger&lt;/span&gt;
                                    &lt;span class="badge bg-warning-gradient"&gt;Warning&lt;/span&gt;
                                    &lt;span class="badge bg-info-gradient"&gt;Info&lt;/span&gt;
                                    &lt;span class="badge bg-orange-gradient"&gt;orange&lt;/span&gt;
                                    &lt;span class="badge bg-purple-gradient"&gt;purple&lt;/span&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Gradient Pill Badges
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex flex-wrap gap-2">
                                <span class="badge rounded-full bg-primary-gradient">Primary</span>
                                <span class="badge rounded-full bg-secondary-gradient">Secondary</span>
                                <span class="badge rounded-full bg-success-gradient">Success</span>
                                <span class="badge rounded-full bg-danger-gradient">Danger</span>
                                <span class="badge rounded-full bg-warning-gradient">Warning</span>
                                <span class="badge rounded-full bg-info-gradient">Info</span>
                                <span class="badge rounded-full bg-orange-gradient">orange</span>
                                <span class="badge rounded-full bg-purple-gradient">purple</span>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre
                                    class="language-html"><code class="language-html">
                                    &lt;span class="badge rounded-full bg-primary-gradient"&gt;Primary&lt;/span&gt;
                                    &lt;span class="badge rounded-full bg-secondary-gradient"&gt;Secondary&lt;/span&gt;
                                    &lt;span class="badge rounded-full bg-success-gradient"&gt;Success&lt;/span&gt;
                                    &lt;span class="badge rounded-full bg-danger-gradient"&gt;Danger&lt;/span&gt;
                                    &lt;span class="badge rounded-full bg-warning-gradient"&gt;Warning&lt;/span&gt;
                                    &lt;span class="badge rounded-full bg-info-gradient"&gt;Info&lt;/span&gt;
                                    &lt;span class="badge rounded-full bg-orange-gradient"&gt;orange&lt;/span&gt;
                                    &lt;span class="badge rounded-full bg-purple-gradient"&gt;purple&lt;/span&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End::row-3 -->

                <!-- Start::row-4 -->
                <div class="grid grid-cols-12 gap-x-6">
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Outline Badges
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex flex-wrap gap-2">
                                <span class="badge bg-outline-primary">Primary</span>
                                <span class="badge bg-outline-secondary">Secondary</span>
                                <span class="badge bg-outline-success">Success</span>
                                <span class="badge bg-outline-danger">Danger</span>
                                <span class="badge bg-outline-warning">Warning</span>
                                <span class="badge bg-outline-info">Info</span>
                                <span
                                    class="badge bg-outline-light !text-black dark:!text-defaulttextcolor/70">Light</span>
                                <span class="badge bg-outline-dark dark:!text-defaulttextcolor/70">Dark</span>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre
                                    class="language-html"><code class="language-html">
                                    &lt;span class="badge bg-outline-primary"&gt;Primary&lt;/span&gt;
                                    &lt;span class="badge bg-outline-secondary"&gt;Secondary&lt;/span&gt;
                                    &lt;span class="badge bg-outline-success"&gt;Success&lt;/span&gt;
                                    &lt;span class="badge bg-outline-danger"&gt;Danger&lt;/span&gt;
                                    &lt;span class="badge bg-outline-warning"&gt;Warning&lt;/span&gt;
                                    &lt;span class="badge bg-outline-info"&gt;Info&lt;/span&gt;
                                    &lt;span class="badge bg-outline-light !text-black dark:!text-defaulttextcolor/70"&gt;Light&lt;/span&gt;
                                    &lt;span class="badge bg-outline-dark dark:!text-defaulttextcolor/70"&gt;Dark&lt;/span&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Outline Pill Badges
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex flex-wrap gap-2">
                                <span class="badge !rounded-full bg-outline-primary">Primary</span>
                                <span class="badge !rounded-full bg-outline-secondary">Secondary</span>
                                <span class="badge !rounded-full bg-outline-success">Success</span>
                                <span class="badge !rounded-full bg-outline-danger">Danger</span>
                                <span class="badge !rounded-full bg-outline-warning">Warning</span>
                                <span class="badge !rounded-full bg-outline-info">Info</span>
                                <span
                                    class="badge !rounded-full bg-outline-light !text-black dark:!text-defaulttextcolor/70">Light</span>
                                <span
                                    class="badge !rounded-full bg-outline-dark  dark:!text-defaulttextcolor/70">Dark</span>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre
                                    class="language-html"><code class="language-html">
                                    &lt;span class="badge !rounded-full bg-outline-primary"&gt;Primary&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-outline-secondary"&gt;Secondary&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-outline-success"&gt;Success&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-outline-danger"&gt;Danger&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-outline-warning"&gt;Warning&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-outline-info"&gt;Info&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-outline-light !text-black dark:!text-defaulttextcolor/70"&gt;Light&lt;/span&gt;
                                    &lt;span class="badge !rounded-full bg-outline-dark  dark:!text-defaulttextcolor/70"&gt;Dark&lt;/span&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End::row-4 -->

                <!-- Start::row-5 -->
                <div class="grid grid-cols-12 gap-x-6">
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Max width Badges
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex flex-wrap gap-2">
                                <span
                                    class="max-w-40 truncate whitespace-nowrap inline-block py-1.5 px-3 rounded-lg text-xs font-medium bg-primary/10 text-primary/80">This
                                    content is a little bit longer.</span>
                                <span
                                    class="max-w-40 truncate whitespace-nowrap inline-block py-1.5 px-3 rounded-lg text-xs font-medium bg-secondary/10 text-secondary/80">This
                                    content is a little bit longer.</span>
                                <span
                                    class="max-w-40 truncate whitespace-nowrap inline-block py-1.5 px-3 rounded-lg text-xs font-medium bg-success/10 text-success/80">This
                                    content is a little bit longer.</span>
                                <span
                                    class="max-w-40 truncate whitespace-nowrap inline-block py-1.5 px-3 rounded-lg text-xs font-medium bg-danger/10 text-danger/80">This
                                    content is a little bit longer.</span>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
<pre class="language-html"><code class="language-html">
    &lt;span&gt;
class="max-w-40 truncate whitespace-nowrap inline-block py-1.5 px-3 rounded-lg text-xs font-medium bg-primary/10 text-primary/80"&gt;This
content is a little bit longer.&lt;/span&gt;
&lt;span&gt;
class="max-w-40 truncate whitespace-nowrap inline-block py-1.5 px-3 rounded-lg text-xs font-medium bg-secondary/10 text-secondary/80"&gt;This
content is a little bit longer.&lt;/span&gt;
&lt;span&gt;
class="max-w-40 truncate whitespace-nowrap inline-block py-1.5 px-3 rounded-lg text-xs font-medium bg-success/10 text-success/80"&gt;This
content is a little bit longer.&lt;/span&gt;
&lt;span&gt;
class="max-w-40 truncate whitespace-nowrap inline-block py-1.5 px-3 rounded-lg text-xs font-medium bg-danger/10 text-danger/80"&gt;This
content is a little bit longer.&lt;/span&gt;
    </code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Badges with indicators
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex flex-wrap gap-2">
                                <span
                                    class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                    <span class="size-1.5 inline-block rounded-full bg-primary/80"></span>
                                    Badge
                                </span>
                                <span
                                    class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-secondary/10 text-secondary">
                                    <span class="size-1.5 inline-block rounded-full bg-secondary/80"></span>
                                    Badge
                                </span>
                                <span
                                    class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-warning/10 text-warning">
                                    <span class="size-1.5 inline-block rounded-full bg-warning/80"></span>
                                    Badge
                                </span>
                                <span
                                    class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-success/10 text-success">
                                    <span class="size-1.5 inline-block rounded-full bg-success/80"></span>
                                    Badge
                                </span>
                                <span
                                    class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-info/10 text-info">
                                    <span class="size-1.5 inline-block rounded-full bg-info/80"></span>
                                    Badge
                                </span>
                                <span
                                    class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-danger/10 text-danger">
                                    <span class="size-1.5 inline-block rounded-full bg-danger/80"></span>
                                    Badge
                                </span>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">
    &lt;span class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-primary/10 text-primary"&gt;
    &lt;span class="size-1.5 inline-block rounded-full bg-primary/80"&gt;&lt;/span&gt;
    Badge
&lt;/span&gt;
&lt;span class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-secondary/10 text-secondary"&gt;
    &lt;span class="size-1.5 inline-block rounded-full bg-secondary/80"&gt;&lt;/span&gt;
    Badge
&lt;/span&gt;
&lt;span class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-warning/10 text-warning"&gt;
    &lt;span class="size-1.5 inline-block rounded-full bg-warning/80"&gt;&lt;/span&gt;
    Badge
&lt;/span&gt;
&lt;span class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-success/10 text-success"&gt;
    &lt;span class="size-1.5 inline-block rounded-full bg-success/80"&gt;&lt;/span&gt;
    Badge
&lt;/span&gt;
&lt;span class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-info/10 text-info"&gt;
    &lt;span class="size-1.5 inline-block rounded-full bg-info/80"&gt;&lt;/span&gt;
    Badge
&lt;/span&gt;
&lt;span class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-danger/10 text-danger"&gt;
    &lt;span class="size-1.5 inline-block rounded-full bg-danger/80"&gt;&lt;/span&gt;
    Badge
&lt;/span&gt;
    </code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Badges with icons
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex flex-wrap gap-2">
                                <span
                                    class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium bg-primary/10 text-primary/80 rounded-full">
                                    <svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
                                        <path d="m9 12 2 2 4-4" />
                                    </svg>
                                    Connected
                                </span>

                                <span
                                    class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs font-medium bg-secondary/10 text-secondary/80 rounded-full">
                                    <svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                                        <path d="M12 9v4" />
                                        <path d="M12 17h.01" />
                                    </svg>
                                    Attention
                                </span>

                                <span
                                    class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs font-medium bg-warning/10 text-warning/80 rounded-full">
                                    <svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" x2="12" y1="2" y2="6" />
                                        <line x1="12" x2="12" y1="18" y2="22" />
                                        <line x1="4.93" x2="7.76" y1="4.93" y2="7.76" />
                                        <line x1="16.24" x2="19.07" y1="16.24" y2="19.07" />
                                        <line x1="2" x2="6" y1="12" y2="12" />
                                        <line x1="18" x2="22" y1="12" y2="12" />
                                        <line x1="4.93" x2="7.76" y1="19.07" y2="16.24" />
                                        <line x1="16.24" x2="19.07" y1="7.76" y2="4.93" />
                                    </svg>
                                    Loading
                                </span>

                                <span
                                    class="py-1 px-2 inline-flex items-center gap-x-1 text-xs bg-info/10 text-info/80 rounded-full">
                                    <svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18.36 6.64a9 9 0 1 1-12.73 0" />
                                        <line x1="12" x2="12" y1="2" y2="12" />
                                    </svg>
                                    Disabled
                                </span>

                                <span
                                    class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs font-medium bg-success/10 text-success/80 rounded-full">
                                    <svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                                        <polyline points="16 7 22 7 22 13" />
                                    </svg>
                                    14.5%
                                </span>

                                <span
                                    class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs font-medium bg-danger/10 text-danger/80 rounded-full">
                                    <svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="22 17 13.5 8.5 8.5 13.5 2 7" />
                                        <polyline points="16 17 22 17 22 11" />
                                    </svg>
                                    2%
                                </span>

                                <span
                                    class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs bg-primary/10 text-primary/80 rounded-full">
                                    <svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                                        <polyline points="16 7 22 7 22 13" />
                                    </svg>
                                    37.3%
                                </span>

                                <span
                                    class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs bg-secondary/10 text-secondary/80 rounded-full">
                                    <svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="22 17 13.5 8.5 8.5 13.5 2 7" />
                                        <polyline points="16 17 22 17 22 11" />
                                    </svg>
                                    56%
                                </span>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">
    &lt;span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium bg-primary/10 text-primary/80 rounded-full"&gt;
    &lt;svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"&gt;
        &lt;path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" /&gt;
        &lt;path d="m9 12 2 2 4-4" /&gt;
    &lt;/svg&gt;
    Connected
&lt;/span&gt;
&lt;span
    class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs font-medium bg-secondary/10 text-secondary/80 rounded-full"&gt;
    &lt;svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"&gt;
        &lt;path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" /&gt;
        &lt;path d="M12 9v4" /&gt;
        &lt;path d="M12 17h.01" /&gt;
    &lt;/svg&gt;
    Attention
&lt;/span&gt;
&lt;span
    class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs font-medium bg-warning/10 text-warning/80 rounded-full"&gt;
    &lt;svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"&gt;
        &lt;line x1="12" x2="12" y1="2" y2="6" /&gt;
        &lt;line x1="12" x2="12" y1="18" y2="22" /&gt;
        &lt;line x1="4.93" x2="7.76" y1="4.93" y2="7.76" /&gt;
        &lt;line x1="16.24" x2="19.07" y1="16.24" y2="19.07" /&gt;
        &lt;line x1="2" x2="6" y1="12" y2="12" /&gt;
        &lt;line x1="18" x2="22" y1="12" y2="12" /&gt;
        &lt;line x1="4.93" x2="7.76" y1="19.07" y2="16.24" /&gt;
        &lt;line x1="16.24" x2="19.07" y1="7.76" y2="4.93" /&gt;
    &lt;/svg&gt;
    Loading
&lt;/span&gt;
&lt;span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs bg-info/10 text-info/80 rounded-full"&gt;
    &lt;svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"&gt;
        &lt;path d="M18.36 6.64a9 9 0 1 1-12.73 0" /&gt;
        &lt;line x1="12" x2="12" y1="2" y2="12" /&gt;
    &lt;/svg&gt;
    Disabled
&lt;/span&gt;
&lt;span
    class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs font-medium bg-success/10 text-success/80 rounded-full"&gt;
    &lt;svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"&gt;
        &lt;polyline points="22 7 13.5 15.5 8.5 10.5 2 17" /&gt;
        &lt;polyline points="16 7 22 7 22 13" /&gt;
    &lt;/svg&gt;
    14.5%
&lt;/span&gt;
&lt;span class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs font-medium bg-danger/10 text-danger/80 rounded-full"&gt;
    &lt;svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"&gt;
        &lt;polyline points="22 17 13.5 8.5 8.5 13.5 2 7" /&gt;
        &lt;polyline points="16 17 22 17 22 11" /&gt;
    &lt;/svg&gt;
    2%
&lt;/span&gt;
&lt;span class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs bg-primary/10 text-primary/80 rounded-full"&gt;
    &lt;svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"&gt;
        &lt;polyline points="22 7 13.5 15.5 8.5 10.5 2 17" /&gt;
        &lt;polyline points="16 7 22 7 22 13" /&gt;
    &lt;/svg&gt;
    37.3%
&lt;/span&gt;
&lt;span class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs bg-secondary/10 text-secondary/80 rounded-full"&gt;
    &lt;svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"&gt;
        &lt;polyline points="22 17 13.5 8.5 8.5 13.5 2 7" /&gt;
        &lt;polyline points="16 17 22 17 22 11" /&gt;
    &lt;/svg&gt;
    56%
&lt;/span&gt;
    </code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Badges with remove button
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex flex-wrap gap-2">
                                <span
                                    class="inline-flex items-center gap-x-1.5 py-1.5 ps-3 pe-2 rounded-full text-xs font-medium bg-primary/10 text-primary/80">
                                    Badge
                                    <button type="button"
                                        class="flex-shrink-0 size-4 inline-flex items-center justify-center rounded-full hover:bg-primary/20 focus:outline-none focus:bg-primary/20 focus:text-primary/50 dark:hover:bg-primary/90">
                                        <span class="sr-only">Remove badge</span>
                                        <i class="ti ti-x size-3"></i>
                                    </button>
                                </span>
                                <span
                                    class="inline-flex items-center gap-x-1.5 py-1.5 ps-3 pe-2 rounded-full text-xs font-medium bg-secondary/10 text-secondary/80">
                                    Badge
                                    <button type="button"
                                        class="flex-shrink-0 size-4 inline-flex items-center justify-center rounded-full hover:bg-secondary/20 focus:outline-none focus:bg-secondary/20 focus:text-secondary/50 dark:hover:bg-secondary/90">
                                        <span class="sr-only">Remove badge</span>
                                        <i class="ti ti-x size-3"></i>
                                    </button>
                                </span>
                                <span
                                    class="inline-flex items-center gap-x-1.5 py-1.5 ps-3 pe-2 rounded-full text-xs font-medium bg-success/10 text-success/80">
                                    Badge
                                    <button type="button"
                                        class="flex-shrink-0 size-4 inline-flex items-center justify-center rounded-full hover:bg-success/20 focus:outline-none focus:bg-success/20 focus:text-success/50 dark:hover:bg-success/90">
                                        <span class="sr-only">Remove badge</span>
                                        <i class="ti ti-x size-3"></i>
                                    </button>
                                </span>
                                <span
                                    class="inline-flex items-center gap-x-1.5 py-1.5 ps-3 pe-2 rounded-full text-xs font-medium bg-warning/10 text-warning/80">
                                    Badge
                                    <button type="button"
                                        class="flex-shrink-0 size-4 inline-flex items-center justify-center rounded-full hover:bg-warning/20 focus:outline-none focus:bg-warning/20 focus:text-warning/50 dark:hover:bg-warning/90">
                                        <span class="sr-only">Remove badge</span>
                                        <i class="ti ti-x size-3"></i>
                                    </button>
                                </span>
                                <span
                                    class="inline-flex items-center gap-x-1.5 py-1.5 ps-3 pe-2 rounded-full text-xs font-medium bg-info/10 text-info/80">
                                    Badge
                                    <button type="button"
                                        class="flex-shrink-0 size-4 inline-flex items-center justify-center rounded-full hover:bg-info/20 focus:outline-none focus:bg-info/20 focus:text-info/50 dark:hover:bg-info/90">
                                        <span class="sr-only">Remove badge</span>
                                        <i class="ti ti-x size-3"></i>
                                    </button>
                                </span>
                                <span
                                    class="inline-flex items-center gap-x-1.5 py-1.5 ps-3 pe-2 rounded-full text-xs font-medium bg-danger/10 text-danger/80">
                                    Badge
                                    <button type="button"
                                        class="flex-shrink-0 size-4 inline-flex items-center justify-center rounded-full hover:bg-danger/20 focus:outline-none focus:bg-danger/20 focus:text-danger/50 dark:hover:bg-danger/90">
                                        <span class="sr-only">Remove badge</span>
                                        <i class="ti ti-x size-3"></i>
                                    </button>
                                </span>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">
                                    &lt;span class="inline-flex items-center gap-x-1.5 py-1.5 ps-3 pe-2 rounded-full text-xs font-medium bg-primary/10 text-primary/80"&gt;
    Badge
    &lt;button type="button" class="flex-shrink-0 size-4 inline-flex items-center justify-center rounded-full hover:bg-primary/20 focus:outline-none focus:bg-primary/20 focus:text-primary/50 dark:hover:bg-primary/90"&gt;
      &lt;span class="sr-only"&gt;Remove badge&lt;/span&gt;
      &lt;i class="ti ti-x size-3"&gt;&lt;/i&gt;
    &lt;/button&gt;
&lt;/span&gt;
&lt;span class="inline-flex items-center gap-x-1.5 py-1.5 ps-3 pe-2 rounded-full text-xs font-medium bg-secondary/10 text-secondary/80"&gt;
    Badge
    &lt;button type="button" class="flex-shrink-0 size-4 inline-flex items-center justify-center rounded-full hover:bg-secondary/20 focus:outline-none focus:bg-secondary/20 focus:text-secondary/50 dark:hover:bg-secondary/90"&gt;
      &lt;span class="sr-only"&gt;Remove badge&lt;/span&gt;
      &lt;i class="ti ti-x size-3"&gt;&lt;/i&gt;
    &lt;/button&gt;
&lt;/span&gt;
&lt;span class="inline-flex items-center gap-x-1.5 py-1.5 ps-3 pe-2 rounded-full text-xs font-medium bg-success/10 text-success/80"&gt;
    Badge
    &lt;button type="button" class="flex-shrink-0 size-4 inline-flex items-center justify-center rounded-full hover:bg-success/20 focus:outline-none focus:bg-success/20 focus:text-success/50 dark:hover:bg-success/90"&gt;
      &lt;span class="sr-only"&gt;Remove badge&lt;/span&gt;
      &lt;i class="ti ti-x size-3"&gt;&lt;/i&gt;
    &lt;/button&gt;
&lt;/span&gt;
&lt;span class="inline-flex items-center gap-x-1.5 py-1.5 ps-3 pe-2 rounded-full text-xs font-medium bg-warning/10 text-warning/80"&gt;
    Badge
    &lt;button type="button" class="flex-shrink-0 size-4 inline-flex items-center justify-center rounded-full hover:bg-warning/20 focus:outline-none focus:bg-warning/20 focus:text-warning/50 dark:hover:bg-warning/90"&gt;
      &lt;span class="sr-only"&gt;Remove badge&lt;/span&gt;
      &lt;i class="ti ti-x size-3"&gt;&lt;/i&gt;
    &lt;/button&gt;
&lt;/span&gt;
&lt;span class="inline-flex items-center gap-x-1.5 py-1.5 ps-3 pe-2 rounded-full text-xs font-medium bg-info/10 text-info/80"&gt;
    Badge
    &lt;button type="button" class="flex-shrink-0 size-4 inline-flex items-center justify-center rounded-full hover:bg-info/20 focus:outline-none focus:bg-info/20 focus:text-info/50 dark:hover:bg-info/90"&gt;
      &lt;span class="sr-only"&gt;Remove badge&lt;/span&gt;
      &lt;i class="ti ti-x size-3"&gt;&lt;/i&gt;
    &lt;/button&gt;
&lt;/span&gt;
&lt;span class="inline-flex items-center gap-x-1.5 py-1.5 ps-3 pe-2 rounded-full text-xs font-medium bg-danger/10 text-danger/80"&gt;
    Badge
    &lt;button type="button" class="flex-shrink-0 size-4 inline-flex items-center justify-center rounded-full hover:bg-danger/20 focus:outline-none focus:bg-danger/20 focus:text-danger/50 dark:hover:bg-danger/90"&gt;
      &lt;span class="sr-only"&gt;Remove badge&lt;/span&gt;
      &lt;i class="ti ti-x size-3"&gt;&lt;/i&gt;
    &lt;/button&gt;
&lt;/span&gt;
                                   </code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Badges with avatars
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex flex-wrap gap-2">
                                <div
                                    class="inline-flex flex-nowrap items-center border border-defaultborder rounded-full p-1.5 pe-3 dark:border-white/10">
                                    <img class="me-1.5 mb-0 inline-block avatar avatar-xs avatar-rounded"
                                        src="{{asset('build/assets/images/faces/10.jpg')}}" alt="Image Description">
                                    <div
                                        class="whitespace-nowrap text-sm font-medium text-defaulttextcolor dark:text-white">
                                        Christina
                                    </div>
                                </div>
                                <div
                                    class="inline-flex flex-nowrap items-center border border-defaultborder rounded-full p-1.5 dark:border-white/10">
                                    <img class="me-1.5 mb-0 inline-block avatar avatar-xs avatar-rounded"
                                        src="{{asset('build/assets/images/faces/11.jpg')}}" alt="Image Description">
                                    <div
                                        class="whitespace-nowrap text-sm font-medium text-defaulttextcolor dark:text-white">
                                        Mark
                                    </div>
                                </div>
                                <div
                                    class="inline-flex flex-nowrap items-center border border-defaultborder rounded-full p-1.5 dark:border-white/10">
                                    <img class="me-1.5 mb-0 inline-block avatar avatar-xs avatar-rounded"
                                        src="{{asset('build/assets/images/faces/12.jpg')}}" alt="Image Description">
                                    <div
                                        class="whitespace-nowrap text-sm font-medium text-defaulttextcolor dark:text-white">
                                        Bhamako
                                    </div>
                                </div>
                                <div
                                    class="inline-flex flex-nowrap items-center border border-defaultborder rounded-full p-1.5 dark:border-white/10">
                                    <img class="me-1.5 mb-0 inline-block avatar avatar-xs avatar-rounded"
                                        src="{{asset('build/assets/images/faces/13.jpg')}}" alt="Image Description">
                                    <div
                                        class="whitespace-nowrap text-sm font-medium text-defaulttextcolor dark:text-white">
                                        Wicky cross
                                    </div>
                                </div>
                                <div
                                    class="inline-flex flex-nowrap items-center border border-defaultborder rounded-full p-1.5 dark:border-white/10">
                                    <img class="me-1.5 mb-0 inline-block avatar avatar-xs avatar-rounded"
                                        src="{{asset('build/assets/images/faces/14.jpg')}}" alt="Image Description">
                                    <div
                                        class="whitespace-nowrap text-sm font-medium text-defaulttextcolor dark:text-white">
                                        Brodus
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">
    &lt;div class="inline-flex flex-nowrap items-center border border-defaultborder rounded-full p-1.5 pe-3 dark:border-white/10"&gt;
    &lt;img class="me-1.5 mb-0 inline-block avatar avatar-xs avatar-rounded" src="{{asset('build/assets/images/faces/10.jpg')}}" alt="Image Description"&gt;
    &lt;div&gt; class="whitespace-nowrap text-sm font-medium text-defaulttextcolor dark:text-white"&gt;
        Christina
    &lt;/div&gt;
&lt;/div&gt;
&lt;div class="inline-flex flex-nowrap items-center border border-defaultborder rounded-full p-1.5 dark:border-white/10"&gt;
    &lt;img class="me-1.5 mb-0 inline-block avatar avatar-xs avatar-rounded" src="{{asset('build/assets/images/faces/11.jpg')}}" alt="Image Description"&gt;
    &lt;div&gt; class="whitespace-nowrap text-sm font-medium text-defaulttextcolor dark:text-white"&gt;
        Mark
    &lt;/div&gt;
&lt;/div&gt;
&lt;div class="inline-flex flex-nowrap items-center border border-defaultborder rounded-full p-1.5 dark:border-white/10"&gt;
    &lt;img class="me-1.5 mb-0 inline-block avatar avatar-xs avatar-rounded" src="{{asset('build/assets/images/faces/12.jpg')}}" alt="Image Description"&gt;
    &lt;div&gt; class="whitespace-nowrap text-sm font-medium text-defaulttextcolor dark:text-white"&gt;
        Bhamako
    &lt;/div&gt;
&lt;/div&gt;
&lt;div class="inline-flex flex-nowrap items-center border border-defaultborder rounded-full p-1.5 dark:border-white/10"&gt;
    &lt;img class="me-1.5 mb-0 inline-block avatar avatar-xs avatar-rounded" src="{{asset('build/assets/images/faces/13.jpg')}}" alt="Image Description"&gt;
    &lt;div&gt; class="whitespace-nowrap text-sm font-medium text-defaulttextcolor dark:text-white"&gt;
        Wicky cross
    &lt;/div&gt;
&lt;/div&gt;
&lt;div class="inline-flex flex-nowrap items-center border border-defaultborder rounded-full p-1.5 dark:border-white/10"&gt;
    &lt;img class="me-1.5 mb-0 inline-block avatar avatar-xs avatar-rounded" src="{{asset('build/assets/images/faces/14.jpg')}}" alt="Image Description"&gt;
    &lt;div&gt; class="whitespace-nowrap text-sm font-medium text-defaulttextcolor dark:text-white"&gt;
        Brodus
    &lt;/div&gt;
&lt;/div&gt;
    </code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Badges with animation ping
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex flex-wrap gap-2">
                                <button type="button"
                                    class="m-1 ms-0 relative flex justify-center items-center size-[35px] text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-bodybg dark:border-white/10 dark:text-white dark:hover:bg-gray-800 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                    <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m5 11 4-7" />
                                        <path d="m19 11-4-7" />
                                        <path d="M2 11h20" />
                                        <path d="m3.5 11 1.6 7.4a2 2 0 0 0 2 1.6h9.8c.9 0 1.8-.7 2-1.6l1.7-7.4" />
                                        <path d="m9 11 1 9" />
                                        <path d="M4.5 15.5h15" />
                                        <path d="m15 11-1 9" />
                                    </svg>
                                    <span class="flex absolute top-0 end-0 size-3 -mt-1.5 -me-1.5">
                                        <span
                                            class="animate-ping absolute inline-flex size-full rounded-full bg-danger/40 opacity-75 dark:bg-danger/60"></span>
                                        <span class="relative inline-flex rounded-full size-3 bg-danger/50"></span>
                                    </span>
                                </button>
                                <button type="button"
                                    class="m-1 ms-0 relative py-1 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 dark:bg-bodybg dark:border-white/10 dark:text-white dark:hover:bg-gray-800 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                    Notification
                                    <span class="flex absolute top-0 end-0 -mt-2 -me-2">
                                        <span
                                            class="animate-ping absolute inline-flex size-full rounded-full bg-danger/40 opacity-75"></span>
                                        <span
                                            class="relative inline-flex text-xs bg-danger/50 text-white rounded-full py-0.5 px-1.5">
                                            9+
                                        </span>
                                    </span>
                                </button>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">
    &lt;button type="button"
    class="m-1 ms-0 relative flex justify-center items-center size-[35px] text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-bodybg dark:border-white/10 dark:text-white dark:hover:bg-gray-800 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600"&gt;
    &lt;svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"&gt;
        &lt;path d="m5 11 4-7" /&gt;
        &lt;path d="m19 11-4-7" /&gt;
        &lt;path d="M2 11h20" /&gt;
        &lt;path d="m3.5 11 1.6 7.4a2 2 0 0 0 2 1.6h9.8c.9 0 1.8-.7 2-1.6l1.7-7.4" /&gt;
        &lt;path d="m9 11 1 9" /&gt;
        &lt;path d="M4.5 15.5h15" /&gt;
        &lt;path d="m15 11-1 9" /&gt;
    &lt;/svg&gt;
    &lt;span class="flex absolute top-0 end-0 size-3 -mt-1.5 -me-1.5"&gt;
        &lt;span
            class="animate-ping absolute inline-flex size-full rounded-full bg-danger/40 opacity-75 dark:bg-danger/60"&gt;&lt;/span&gt;
        &lt;span class="relative inline-flex rounded-full size-3 bg-danger/50"&gt;&lt;/span&gt;
    &lt;/span&gt;
&lt;/button&gt;
&lt;button type="button"
    class="m-1 ms-0 relative py-1 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 dark:bg-bodybg dark:border-white/10 dark:text-white dark:hover:bg-gray-800 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600"&gt;
    Notification
    &lt;span class="flex absolute top-0 end-0 -mt-2 -me-2"&gt;
        &lt;span class="animate-ping absolute inline-flex size-full rounded-full bg-danger/40 opacity-75"&gt;&lt;/span&gt;
        &lt;span class="relative inline-flex text-xs bg-danger/50 text-white rounded-full py-0.5 px-1.5"&gt;
            9+
        &lt;/span&gt;
    &lt;/span&gt;
&lt;/button&gt;
                                   </code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End::row-5 -->

                <!-- Start::row-6 -->
                <div class="grid grid-cols-12 gap-x-6">
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Buttons With Badges
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex flex-wrap gap-2">
                                <button type="button" class="ti-btn btn-wave bg-primary text-white my-1 me-2">
                                    Notifications <span class="badge ms-2 bg-secondary">4</span>
                                </button>
                                <button type="button" class="ti-btn btn-wave bg-secondary text-white my-1 me-2">
                                    Notifications <span class="badge ms-2 bg-primary">7</span>
                                </button>
                                <button type="button" class="ti-btn btn-wave bg-success text-white my-1 me-2">
                                    Notifications <span class="badge ms-2 bg-danger">12</span>
                                </button>
                                <button type="button" class="ti-btn btn-wave bg-info text-white my-1 me-2">
                                    Notifications <span class="badge ms-2 bg-warning">32</span>
                                </button>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">&lt;button type="button" class="ti-btn btn-wave bg-primary text-white my-1 me-2"&gt;
                                    Notifications &lt;span class="badge ms-2 bg-secondary"&gt;4&lt;/span&gt;
                                    &lt;/button&gt;
                                    &lt;button type="button" class="ti-btn btn-wave bg-secondary text-white my-1 me-2"&gt;
                                    Notifications &lt;span class="badge ms-2 bg-primary"&gt;7&lt;/span&gt;
                                    &lt;/button&gt;
                                    &lt;button type="button" class="ti-btn btn-wave bg-success text-white my-1 me-2"&gt;
                                    Notifications &lt;span class="badge ms-2 bg-danger"&gt;12&lt;/span&gt;
                                    &lt;/button&gt;
                                    &lt;button type="button" class="ti-btn btn-wave bg-info text-white my-1 me-2"&gt;
                                    Notifications &lt;span class="badge ms-2 bg-warning"&gt;32&lt;/span&gt;
                                    &lt;/button&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Outline Button Badges
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body flex flex-wrap gap-2">
                                <button type="button" class="ti-btn btn-wave ti-btn-outline-primary my-1 me-2">
                                    Notifications <span class="badge bg-primary ms-2 text-white">4</span>
                                </button>
                                <button type="button" class="ti-btn btn-wave ti-btn-outline-secondary my-1 me-2">
                                    Notifications <span class="badge bg-secondary ms-2 text-white">7</span>
                                </button>
                                <button type="button" class="ti-btn btn-wave ti-btn-outline-success my-1 me-2">
                                    Notifications <span class="badge bg-success ms-2 text-white">12</span>
                                </button>
                                <button type="button" class="ti-btn btn-wave ti-btn-outline-info my-1 me-2">
                                    Notifications <span class="badge bg-info ms-2 text-white">32</span>
                                </button>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">&lt;button type="button" class="ti-btn btn-wave ti-btn-outline-primary my-1 me-2"&gt;
                                    Notifications &lt;span class="badge bg-primary ms-2 text-white"&gt;4&lt;/span&gt;
                                &lt;/button&gt;
                                &lt;button type="button" class="ti-btn btn-wave ti-btn-outline-secondary my-1 me-2"&gt;
                                    Notifications &lt;span class="badge bg-secondary ms-2 text-white"&gt;7&lt;/span&gt;
                                &lt;/button&gt;
                                &lt;button type="button" class="ti-btn btn-wave ti-btn-outline-success my-1 me-2"&gt;
                                    Notifications &lt;span class="badge bg-success ms-2 text-white"&gt;12&lt;/span&gt;
                                &lt;/button&gt;
                                &lt;button type="button" class="ti-btn btn-wave ti-btn-outline-info my-1 me-2"&gt;
                                    Notifications &lt;span class="badge bg-info ms-2 text-white"&gt;32&lt;/span&gt;
                                &lt;/button&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End::row-6 -->

                <!-- Start::row-7 -->
                <div class="grid grid-cols-12 gap-x-6">
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Headings
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <h1 class="text-[2.5rem]">Example heading <span
                                        class="badge bg-primary text-white">New</span></h1>
                                <h2 class="text-[2rem]">Example heading <span
                                        class="badge bg-primary text-white">New</span></h2>
                                <h3 class="text-[1.75rem]">Example heading <span
                                        class="badge bg-primary text-white">New</span></h3>
                                <h4 class="text-[1.5rem]">Example heading <span
                                        class="badge bg-primary text-white">New</span></h4>
                                <h5 class="text-[1.25rem]">Example heading <span
                                        class="badge bg-primary text-white">New</span></h5>
                                <h6 class="text-[1rem]">Example heading <span
                                        class="badge bg-primary text-white">New</span></h6>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre
                                    class="language-html"><code class="language-html">&lt;h1 class="text-[2.5rem]"&gt;Example heading &lt;span class="badge bg-primary text-white"&gt;New&lt;/span&gt;&lt;/h1&gt;
                                    &lt;h2 class="text-[2rem]"&gt;Example heading &lt;span class="badge bg-primary text-white"&gt;New&lt;/span&gt;&lt;/h2&gt;
                                    &lt;h3 class="text-[1.75rem]"&gt;Example heading &lt;span class="badge bg-primary text-white"&gt;New&lt;/span&gt;&lt;/h3&gt;
                                    &lt;h4 class="text-[1.5rem]"&gt;Example heading &lt;span class="badge bg-primary text-white"&gt;New&lt;/span&gt;&lt;/h4&gt;
                                    &lt;h5 class="text-[1.25rem]"&gt;Example heading &lt;span class="badge bg-primary text-white"&gt;New&lt;/span&gt;&lt;/h5&gt;
                                    &lt;h6 class="text-[1rem]"&gt;Example heading &lt;span class="badge bg-primary text-white"&gt;New&lt;/span&gt;&lt;/h6&gt;</code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <div class="grid grid-cols-12 gap-6">
                            <div class="xl:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">Positioned Badges</div>
                                        <div class="prism-toggle">
                                            <button type="button"
                                                class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body flex flex-wrap gap-4">
                                        <button type="button" class="ti-btn bg-primary text-white relative">
                                            Inbox
                                            <span
                                                class="absolute -top-2 start-[60%] translate-middle  badge !rounded-full bg-danger">
                                                99+
                                                <span class="hidden">unread messages</span>
                                            </span>
                                        </button>
                                        <button type="button" class="ti-btn bg-secondary text-white relative">
                                            Profile
                                            <span
                                                class="absolute -top-2 start-[80%] translate-middle p-2 bg-success border border-light !rounded-full">
                                                <span class="hidden">New alerts</span>
                                            </span>
                                        </button>
                                        <span class="avatar relative">
                                            <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img" class="!rounded-md">
                                            <span
                                                class="absolute -top-1 start-[80%] translate-middle p-1 bg-success border border-light !rounded-full">
                                                <span class="hidden">New alerts</span>
                                            </span>
                                        </span>
                                        <span class="avatar avatar-rounded relative">
                                            <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="img">
                                            <span
                                                class="absolute top-0 start-[80%] translate-middle p-1 bg-success border border-light !rounded-full">
                                                <span class="hidden">New alerts</span>
                                            </span>
                                        </span>
                                        <span class="avatar avatar-rounded relative">
                                            <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="img">
                                            <span
                                                class="absolute -top-2 start-[60%] translate-middle badge bg-secondary !rounded-full shadow-lg text-white">1000+
                                                <span class="hidden">New alerts</span>
                                            </span>
                                        </span>
                                    </div>
                                    <div class="box-footer hidden border-t-0">
                                        <!-- Prism Code -->
                                        <pre class="language-html"><code class="language-html">&lt;button type="button" class="ti-btn btn-wave bg-primary text-white relative"&gt;
    Inbox
    &lt;span
        class="absolute -top-2 start-[60%] translate-middle  badge !rounded-full bg-danger"&gt;
        99+
        &lt;span class="hidden"&gt;unread messages&lt;/span&gt;
    &lt;/span&gt;
&lt;/button&gt;
&lt;button type="button" class="ti-btn btn-wave bg-secondary text-white relative"&gt;
    Profile
    &lt;span
        class="absolute -top-2 start-[80%] translate-middle p-2 bg-success border border-light !rounded-full"&gt;
        &lt;span class="hidden"&gt;New alerts&lt;/span&gt;
    &lt;/span&gt;
&lt;/button&gt;
&lt;span class="avatar relative"&gt;
    &lt;img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img" class="!rounded-md"&gt;
    &lt;span
        class="absolute -top-2 start-[80%] translate-middle p-1 bg-success border border-light !rounded-full"&gt;
        &lt;span class="hidden"&gt;New alerts&lt;/span&gt;
    &lt;/span&gt;
&lt;/span&gt;
&lt;span class="avatar avatar-rounded relative"&gt;
    &lt;img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="img"&gt;
    &lt;span
        class="absolute -top-2 start-[80%] translate-middle p-1 bg-success border border-light !rounded-full"&gt;
        &lt;span class="hidden"&gt;New alerts&lt;/span&gt;
    &lt;/span&gt;
&lt;/span&gt;
&lt;span class="avatar avatar-rounded relative"&gt;
    &lt;img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="img"&gt;
    &lt;span
        class="absolute -top-2 start-[60%] translate-middle badge bg-secondary !rounded-full shadow-lg text-white"&gt;1000+
        &lt;span class="hidden"&gt;New alerts&lt;/span&gt;
    &lt;/span&gt;
&lt;/span&gt;</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">Positioned Badges</div>
                                        <div class="prism-toggle">
                                            <button type="button"
                                                class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="flex items-center gap-5 flex-wrap">
                                            <div>
                                                <span
                                                    class="badge bg-outline-secondary !font-semibold !text-[.9375rem] inline-flex items-center">
                                                    <i class="ti ti-flame me-1"></i>
                                                    Hot
                                                </span>
                                            </div>
                                            <div>
                                                <span class="relative">
                                                    <svg class="fill-textmuted dark:fill-textmuted/50 w-8 h-8 text-[2rem]"
                                                        xmlns="http://www.w3.org/2000/svg" height="24px"
                                                        viewBox="0 0 24 24" width="24px" fill="#000000">
                                                        <path
                                                            d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z">
                                                        </path>
                                                    </svg>
                                                    <span
                                                        class="badge !rounded-full bg-success text-white absolute -end-2 top-0">14</span>
                                                </span>
                                            </div>
                                            <div>
                                                <span
                                                    class="badge border dark:border-light bg-light text-defaulttextcolor font-semibold font-[.7rem]"><i
                                                        class="fe fe-eye me-2 inline-block"></i>13.2k</span>
                                            </div>
                                            <div>
                                                <span class="text-badge relative">
                                                    <span class="text text-lg">Inbox</span>
                                                    <span class="badge !rounded-full bg-success text-white">32</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-footer hidden border-t-0">
                                        <!-- Prism Code -->
                                        <pre class="language-html"><code class="language-html">&lt;div class="flex items-center gap-5 flex-wrap"&gt;
                                            &lt;div&gt;
                                                &lt;span class="badge bg-outline-secondary !font-semibold !text-[.9375rem] inline-flex items-center"&gt;
                                                    &lt;i class="ti ti-flame me-1"&gt;&lt;/i&gt;
                                                    Hot
                                                &lt;/span&gt;
                                            &lt;/div&gt;
                                            &lt;div&gt;
                                                &lt;span class="relative"&gt;
                                                    &lt;svg class="fill-textmuted dark:fill-textmuted/50 w-8 h-8 text-[2rem]" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"&gt;&lt;path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"&gt;&lt;/path&gt;&lt;/svg&gt;
                                                    &lt;span class="badge !rounded-full bg-success text-white absolute -end-2 top-0"&gt;14&lt;/span&gt;
                                                &lt;/span&gt;
                                            &lt;/div&gt;
                                            &lt;div&gt;
                                                &lt;span class="badge border bg-light text-defaulttextcolor font-semibold font-[.7rem]"&gt;&lt;i class="fe fe-eye me-2 inline-block"&gt;&lt;/i&gt;13.2k&lt;/span&gt;
                                            &lt;/div&gt;
                                            &lt;div&gt;
                                                &lt;span class="text-badge relative"&gt;
                                                    &lt;span class="text text-lg"&gt;Inbox&lt;/span&gt;
                                                    &lt;span class="badge !rounded-full bg-success text-white"&gt;32&lt;/span&gt;
                                                &lt;/span&gt;
                                            &lt;/div&gt;
                                        &lt;/div&gt;</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End::row-7 -->
                
@endsection

@section('scripts')

        <!-- Prism JS -->
        <script src="{{asset('build/assets/libs/prismjs/prism.js')}}"></script>
        @vite('resources/assets/js/prism-custom.js')

        
@endsection