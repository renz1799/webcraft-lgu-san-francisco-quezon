@extends('layouts.master')

@section('styles')
 
        <!-- Prism CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/prismjs/themes/prism-coy.min.css')}}">

@endsection

@section('content')
 
                    <!-- Page Header -->
                    <div class="block justify-between page-header md:flex">
                        <div>
                            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Spinners</h3>
                        </div>
                        <ol class="flex items-center whitespace-nowrap min-w-0">
                            <li class="text-[0.813rem] ps-[0.5rem]">
                              <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                               Ui Elements
                                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                              </a>
                            </li>
                            <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                Spinners
                            </li>
                        </ol>
                    </div>
                    <!-- Page Header Close -->

                    <!-- Start:: row-1 -->
                    <div class="grid grid-cols-12  gap-6">
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">
                                        Border spinner
                                    </div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 !text-[0.75rem] ti-btn-primary">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="ti-spinner" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html"> &lt;div class="ti-spinner" role="status"&gt;
    &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
&lt;/div&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">
                                        Colors
                                    </div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 !text-[0.75rem] ti-btn-primary">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="ti-spinner text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <div class="ti-spinner text-secondary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <div class="ti-spinner text-success" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <div class="ti-spinner text-danger" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <div class="ti-spinner text-warning" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <div class="ti-spinner text-info" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <div class="ti-spinner text-light" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <div class="ti-spinner" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;div class="ti-spinner text-primary" role="status"&gt;
    &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
&lt;/div&gt;
&lt;div class="ti-spinner text-secondary" role="status"&gt;
    &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
&lt;/div&gt;
&lt;div class="ti-spinner text-success" role="status"&gt;
    &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
&lt;/div&gt;
&lt;div class="ti-spinner text-danger" role="status"&gt;
    &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
&lt;/div&gt;
&lt;div class="ti-spinner text-warning" role="status"&gt;
    &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
&lt;/div&gt;
&lt;div class="ti-spinner text-info" role="status"&gt;
    &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
&lt;/div&gt;
&lt;div class="ti-spinner text-light" role="status"&gt;
    &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
&lt;/div&gt;
&lt;div class="ti-spinner" role="status"&gt;
    &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
&lt;/div&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End:: row-1 -->

                    <!-- Start:: row-2 -->
                    <div class="grid grid-cols-12  gap-6">
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">
                                        Growing spinner
                                    </div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 !text-[0.75rem] ti-btn-primary">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="ti-spinner !bg-black dark:!bg-light !animate-ping !border-transparent ping-animation" role="status"
                                        aria-label="loading">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;div class="ti-spinner !bg-black dark:!bg-light !animate-ping !border-transparent ping-animation" role="status"
    aria-label="loading"&gt;
    &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
&lt;/div&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">
                                        Growing spinner
                                    </div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 !text-[0.75rem] ti-btn-primary">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="space-x-6 space-y-4 rtl:space-x-reverse">
                                        <div class="ti-spinner !animate-ping !border-transparent  bg-primary ping-animation" role="status"
                                            aria-label="loading">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <div class="ti-spinner !animate-ping !border-transparent  bg-secondary ping-animation" role="status"
                                            aria-label="loading">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <div class="ti-spinner !animate-ping !border-transparent  bg-warning ping-animation" role="status"
                                            aria-label="loading">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <div class="ti-spinner !animate-ping !border-transparent  bg-danger ping-animation" role="status"
                                            aria-label="loading">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <div class="ti-spinner !animate-ping !border-transparent  bg-success ping-animation" role="status"
                                            aria-label="loading">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <div class="ti-spinner !animate-ping !border-transparent  bg-info ping-animation" role="status"
                                            aria-label="loading">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <div class="ti-spinner !animate-ping !border-transparent  bg-black/20 dark:!bg-light dark:animate-ping ping-animation"
                                            role="status" aria-label="loading">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <div class="ti-spinner !animate-ping !border-transparent  bg-gray-400 ping-animation" role="status"
                                            aria-label="loading">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;div class="space-x-6 space-y-4 rtl:space-x-reverse"&gt;
    &lt;div class="ti-spinner !animate-ping !border-transparent  bg-primary ping-animation" role="status"
        aria-label="loading"&gt;
        &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
    &lt;/div&gt;
    &lt;div class="ti-spinner !animate-ping !border-transparent  bg-secondary ping-animation" role="status"
        aria-label="loading"&gt;
        &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
    &lt;/div&gt;
    &lt;div class="ti-spinner !animate-ping !border-transparent  bg-warning ping-animation" role="status"
        aria-label="loading"&gt;
        &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
    &lt;/div&gt;
    &lt;div class="ti-spinner !animate-ping !border-transparent  bg-danger ping-animation" role="status"
        aria-label="loading"&gt;
        &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
    &lt;/div&gt;
    &lt;div class="ti-spinner !animate-ping !border-transparent  bg-success ping-animation" role="status"
        aria-label="loading"&gt;
        &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
    &lt;/div&gt;
    &lt;div class="ti-spinner !animate-ping !border-transparent  bg-info ping-animation" role="status"
        aria-label="loading"&gt;
        &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
    &lt;/div&gt;
    &lt;div class="ti-spinner !animate-ping !border-transparent  bg-black/20 dark:!bg-light dark:animate-ping ping-animation"
        role="status" aria-label="loading"&gt;
        &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
    &lt;/div&gt;

    &lt;div class="ti-spinner !animate-ping !border-transparent  bg-gray-400 ping-animation" role="status"
        aria-label="loading"&gt;
        &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
    &lt;/div&gt;
&lt;/div&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End:: row-2 -->

                    <!-- Start:: row-3 -->
                    <div class="grid grid-cols-12  gap-6">
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">
                                        Alignment Flex
                                    </div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 !text-[0.75rem] ti-btn-primary">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="flex justify-center mb-6">
                                        <div class="ti-spinner" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <strong>Loading...</strong>
                                        <div class="ti-spinner" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;div class="flex justify-center mb-6"&gt;
    &lt;div class="ti-spinner" role="status"&gt;
        &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
    &lt;/div&gt;
&lt;/div&gt;
&lt;div class="flex items-center justify-between"&gt;
    &lt;strong&gt;Loading...&lt;/strong&gt;
    &lt;div class="ti-spinner" role="status"&gt;
        &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
    &lt;/div&gt;
&lt;/div&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">
                                        Alignment Float
                                    </div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 !text-[0.75rem] ti-btn-primary">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="clearfix mb-6">
                                        <div class="ti-spinner ltr:float-right rtl:float-left" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                    <div class="clearfix">
                                        <div class="ti-spinner ltr:float-left rtl:float-right" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;div class="clearfix mb-6"&gt;
    &lt;div class="ti-spinner ltr:float-right rtl:float-left" role="status"&gt;
        &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
    &lt;/div&gt;
&lt;/div&gt;
&lt;div class="clearfix"&gt;
    &lt;div class="ti-spinner ltr:float-left rtl:float-right" role="status"&gt;
        &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
    &lt;/div&gt;
&lt;/div&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End:: row-3 -->

                    <!-- Start:: row-4 -->
                    <div class="grid grid-cols-12  gap-6">
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">
                                        Alignment Text center
                                    </div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 !text-[0.75rem] ti-btn-primary">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="text-center">
                                        <div class="ti-spinner" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;div class="text-center"&gt;
    &lt;div class="ti-spinner" role="status"&gt;
        &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
    &lt;/div&gt;
&lt;/div&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">
                                        Spinner Sizes
                                    </div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 !text-[0.75rem] ti-btn-primary">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body flex items-center">
                                    <div class="ti-spinner !w-[1rem] !h-[1rem] me-6" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <div class="ti-spinner !w-[1rem] !h-[1rem] !bg-black dark:!bg-white !animate-ping !border-transparent me-6 ping-animation" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <div class="ti-spinner me-6 w-12 h-12"
                                        role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <div class="ti-spinner !bg-black dark:!bg-white !animate-ping !border-transparent w-8 h-8 ping-animation" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">
    &lt;div class="flex items-center"&gt;
        &lt;div class="ti-spinner !w-[1rem] !h-[1rem] me-6" role="status"&gt;
            &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
        &lt;/div&gt;
        &lt;div class="ti-spinner !w-[1rem] !h-[1rem] !bg-black dark:!bg-white !animate-ping !border-transparent me-6" role="status"&gt;
            &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
        &lt;/div&gt;
        &lt;div class="ti-spinner me-6 w-12 h-12"
            role="status"&gt;
            &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
        &lt;/div&gt;
        &lt;div class="ti-spinner !bg-black dark:!bg-white !animate-ping !border-transparent w-8 h-8" role="status"&gt;
            &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
        &lt;/div&gt;
    &lt;/div&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">
                                        Alignment Margin
                                    </div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 !text-[0.75rem] ti-btn-primary">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="ti-spinner m-[3rem]" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;div class="ti-spinner m-[3rem]" role="status"&gt;
    &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
&lt;/div&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End:: row-4 -->

                    <!-- Start:: row-5 -->
                    <div class="grid grid-cols-12  gap-6">
                        <div class="xl:col-span-12 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">Buttons</div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 !text-[0.75rem] ti-btn-primary">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="">
                                        <button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-primary-full" disabled>
                                            <span class="ti-spinner text-white" role="status" aria-label="loading">
                                                <span class="sr-only">Loading...</span>
                                            </span>
                                        </button>
                                        <button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-primary-full" disabled>
                                            <span class="ti-spinner text-white" role="status" aria-label="loading"></span>
                                            <span>Loading...</span>
                                        </button>
                                        <button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-secondary-full" disabled>
                                            <span class="ti-spinner text-white" role="status" aria-label="loading">
                                                <span class="sr-only">Loading...</span>
                                            </span>
                                        </button>
                                        <button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-secondary-full" disabled>
                                            <span class="ti-spinner text-white" role="status" aria-label="loading"></span>
                                            <span>Loading...</span>
                                        </button>
                                        <button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-warning-full" disabled>
                                            <span class="ti-spinner text-white" role="status" aria-label="loading">
                                                <span class="sr-only">Loading...</span>
                                            </span>
                                        </button>
                                        <button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-warning-full" disabled>
                                            <span class="ti-spinner text-white" role="status" aria-label="loading"></span>
                                            <span>Loading...</span>
                                        </button>
                                        <button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-danger-full" disabled>
                                            <span class="ti-spinner text-white" role="status" aria-label="loading">
                                                <span class="sr-only">Loading...</span>
                                            </span>
                                        </button>
                                        <button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-danger-full" disabled>
                                            <span class="ti-spinner text-white" role="status" aria-label="loading"></span>
                                            <span>Loading...</span>
                                        </button>
                                        <button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-info-full" disabled>
                                            <span class="ti-spinner text-white" role="status" aria-label="loading">
                                                <span class="sr-only">Loading...</span>
                                            </span>
                                        </button>
                                        <button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-info-full" disabled>
                                            <span class="ti-spinner text-white" role="status" aria-label="loading"></span>
                                            <span>Loading...</span>
                                        </button>
                                        <button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-success-full" disabled>
                                            <span class="ti-spinner text-white" role="status" aria-label="loading">
                                                <span class="sr-only">Loading...</span>
                                            </span>
                                        </button>
                                        <button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-success-full" disabled>
                                            <span class="ti-spinner text-white" role="status" aria-label="loading"></span>
                                            <span>Loading...</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html"> &lt;div class=""&gt;
    &lt;button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-primary-full" disabled&gt;
        &lt;span class="ti-spinner text-white" role="status" aria-label="loading"&gt;
            &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
        &lt;/span&gt;
    &lt;/button&gt;
    &lt;button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-primary-full" disabled&gt;
        &lt;span class="ti-spinner text-white" role="status" aria-label="loading"&gt;&lt;/span&gt;
        &lt;span&gt;Loading...&lt;/span&gt;
    &lt;/button&gt;
    &lt;button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-secondary-full" disabled&gt;
        &lt;span class="ti-spinner text-white" role="status" aria-label="loading"&gt;
            &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
        &lt;/span&gt;
    &lt;/button&gt;
    &lt;button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-secondary-full" disabled&gt;
        &lt;span class="ti-spinner text-white" role="status" aria-label="loading"&gt;&lt;/span&gt;
        &lt;span&gt;Loading...&lt;/span&gt;
    &lt;/button&gt;
    &lt;button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-warning-full" disabled&gt;
        &lt;span class="ti-spinner text-white" role="status" aria-label="loading"&gt;
            &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
        &lt;/span&gt;
    &lt;/button&gt;
    &lt;button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-warning-full" disabled&gt;
        &lt;span class="ti-spinner text-white" role="status" aria-label="loading"&gt;&lt;/span&gt;
        &lt;span&gt;Loading...&lt;/span&gt;
    &lt;/button&gt;
    &lt;button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-danger-full" disabled&gt;
        &lt;span class="ti-spinner text-white" role="status" aria-label="loading"&gt;
            &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
        &lt;/span&gt;
    &lt;/button&gt;
    &lt;button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-danger-full" disabled&gt;
        &lt;span class="ti-spinner text-white" role="status" aria-label="loading"&gt;&lt;/span&gt;
        &lt;span&gt;Loading...&lt;/span&gt;
    &lt;/button&gt;
    &lt;button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-info-full" disabled&gt;
        &lt;span class="ti-spinner text-white" role="status" aria-label="loading"&gt;
            &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
        &lt;/span&gt;
    &lt;/button&gt;
    &lt;button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-info-full" disabled&gt;
        &lt;span class="ti-spinner text-white" role="status" aria-label="loading"&gt;&lt;/span&gt;
        &lt;span&gt;Loading...&lt;/span&gt;
    &lt;/button&gt;
    &lt;button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-success-full" disabled&gt;
        &lt;span class="ti-spinner text-white" role="status" aria-label="loading"&gt;
            &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
        &lt;/span&gt;
    &lt;/button&gt;
    &lt;button type="button" class="ti-btn btn-wave ti-btn-disabled ti-btn-success-full" disabled&gt;
        &lt;span class="ti-spinner text-white" role="status" aria-label="loading"&gt;&lt;/span&gt;
        &lt;span&gt;Loading...&lt;/span&gt;
    &lt;/button&gt;
&lt;/div&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End:: row-5 -->

@endsection

@section('scripts')

        <!-- Prism JS -->
        <script src="{{asset('build/assets/libs/prismjs/prism.js')}}"></script>
        @vite('resources/assets/js/prism-custom.js')

@endsection