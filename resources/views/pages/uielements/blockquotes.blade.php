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
                                Blockquotes</h3>
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
                                Blockquotes
                            </li>
                        </ol>
                    </div>
                    <!-- Page Header Close -->

                    <!-- Start::row-1 -->
                    <div class="grid grid-cols-12 gap-x-6">
                    <div class="col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Basic Blockquote
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem] btn-wave">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <blockquote class="relative">
                                    <svg class="absolute top-0 ltr:left-0 rtl:right-0 transform -translate-x-6 -translate-y-8 h-16 w-16 text-gray-100 dark:text-white/10"
                                        width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path
                                            d="M7.39762 10.3C7.39762 11.0733 7.14888 11.7 6.6514 12.18C6.15392 12.6333 5.52552 12.86 4.76621 12.86C3.84979 12.86 3.09047 12.5533 2.48825 11.94C1.91222 11.3266 1.62421 10.4467 1.62421 9.29999C1.62421 8.07332 1.96459 6.87332 2.64535 5.69999C3.35231 4.49999 4.33418 3.55332 5.59098 2.85999L6.4943 4.25999C5.81354 4.73999 5.26369 5.27332 4.84476 5.85999C4.45201 6.44666 4.19017 7.12666 4.05926 7.89999C4.29491 7.79332 4.56983 7.73999 4.88403 7.73999C5.61716 7.73999 6.21938 7.97999 6.69067 8.45999C7.16197 8.93999 7.39762 9.55333 7.39762 10.3ZM14.6242 10.3C14.6242 11.0733 14.3755 11.7 13.878 12.18C13.3805 12.6333 12.7521 12.86 11.9928 12.86C11.0764 12.86 10.3171 12.5533 9.71484 11.94C9.13881 11.3266 8.85079 10.4467 8.85079 9.29999C8.85079 8.07332 9.19117 6.87332 9.87194 5.69999C10.5789 4.49999 11.5608 3.55332 12.8176 2.85999L13.7209 4.25999C13.0401 4.73999 12.4903 5.27332 12.0713 5.85999C11.6786 6.44666 11.4168 7.12666 11.2858 7.89999C11.5215 7.79332 11.7964 7.73999 12.1106 7.73999C12.8437 7.73999 13.446 7.97999 13.9173 8.45999C14.3886 8.93999 14.6242 9.55333 14.6242 10.3Z"
                                            fill="currentColor"></path>
                                    </svg>
                                    <div class="relative z-10">
                                        <p class="text-gray-800 sm:text-xl dark:text-white"><em>
                                                I just wanted to say that I'm very happy with my purchase so far. The
                                                documentation is outstanding - clear and
                                                detailed.</em>
                                        </p>
                                    </div>
                                </blockquote>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">
&lt;blockquote class="relative"&gt;
&lt;svg class="absolute top-0 ltr:left-0 rtl:right-0 transform -translate-x-6 -translate-y-8 h-16 w-16 text-gray-100 dark:text-white/10"
    width="16" height="16" viewBox="0 0 16 16" fill="none"
    xmlns="http://www.w3.org/2000/svg" aria-hidden="true"&gt;
    &lt;path&gt;
        d="M7.39762 10.3C7.39762 11.0733 7.14888 11.7 6.6514 12.18C6.15392 12.6333 5.52552 12.86 4.76621 12.86C3.84979 12.86 3.09047 12.5533 2.48825 11.94C1.91222 11.3266 1.62421 10.4467 1.62421 9.29999C1.62421 8.07332 1.96459 6.87332 2.64535 5.69999C3.35231 4.49999 4.33418 3.55332 5.59098 2.85999L6.4943 4.25999C5.81354 4.73999 5.26369 5.27332 4.84476 5.85999C4.45201 6.44666 4.19017 7.12666 4.05926 7.89999C4.29491 7.79332 4.56983 7.73999 4.88403 7.73999C5.61716 7.73999 6.21938 7.97999 6.69067 8.45999C7.16197 8.93999 7.39762 9.55333 7.39762 10.3ZM14.6242 10.3C14.6242 11.0733 14.3755 11.7 13.878 12.18C13.3805 12.6333 12.7521 12.86 11.9928 12.86C11.0764 12.86 10.3171 12.5533 9.71484 11.94C9.13881 11.3266 8.85079 10.4467 8.85079 9.29999C8.85079 8.07332 9.19117 6.87332 9.87194 5.69999C10.5789 4.49999 11.5608 3.55332 12.8176 2.85999L13.7209 4.25999C13.0401 4.73999 12.4903 5.27332 12.0713 5.85999C11.6786 6.44666 11.4168 7.12666 11.2858 7.89999C11.5215 7.79332 11.7964 7.73999 12.1106 7.73999C12.8437 7.73999 13.446 7.97999 13.9173 8.45999C14.3886 8.93999 14.6242 9.55333 14.6242 10.3Z"
        fill="currentColor"&gt;&lt;/path&gt;
&lt;/svg&gt;
&lt;div class="relative z-10"&gt;
    &lt;p&gt; class="text-gray-800 sm:text-xl dark:text-white"&gt;&lt;em&gt;
            I just wanted to say that I'm very happy with my purchase so far. The
            documentation is outstanding - clear and
            detailed.&lt;/em&gt;
    &lt;/p&gt;
&lt;/div&gt;
&lt;/blockquote&gt;
                                </code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Small Blockquote
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem] btn-wave">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <blockquote class="relative">
                                    <svg class="absolute top-0 ltr:left-0 rtl:right-0 transform -translate-x-6 -translate-y-8 h-16 w-16 text-gray-100 dark:text-white/10"
                                        width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path
                                            d="M7.39762 10.3C7.39762 11.0733 7.14888 11.7 6.6514 12.18C6.15392 12.6333 5.52552 12.86 4.76621 12.86C3.84979 12.86 3.09047 12.5533 2.48825 11.94C1.91222 11.3266 1.62421 10.4467 1.62421 9.29999C1.62421 8.07332 1.96459 6.87332 2.64535 5.69999C3.35231 4.49999 4.33418 3.55332 5.59098 2.85999L6.4943 4.25999C5.81354 4.73999 5.26369 5.27332 4.84476 5.85999C4.45201 6.44666 4.19017 7.12666 4.05926 7.89999C4.29491 7.79332 4.56983 7.73999 4.88403 7.73999C5.61716 7.73999 6.21938 7.97999 6.69067 8.45999C7.16197 8.93999 7.39762 9.55333 7.39762 10.3ZM14.6242 10.3C14.6242 11.0733 14.3755 11.7 13.878 12.18C13.3805 12.6333 12.7521 12.86 11.9928 12.86C11.0764 12.86 10.3171 12.5533 9.71484 11.94C9.13881 11.3266 8.85079 10.4467 8.85079 9.29999C8.85079 8.07332 9.19117 6.87332 9.87194 5.69999C10.5789 4.49999 11.5608 3.55332 12.8176 2.85999L13.7209 4.25999C13.0401 4.73999 12.4903 5.27332 12.0713 5.85999C11.6786 6.44666 11.4168 7.12666 11.2858 7.89999C11.5215 7.79332 11.7964 7.73999 12.1106 7.73999C12.8437 7.73999 13.446 7.97999 13.9173 8.45999C14.3886 8.93999 14.6242 9.55333 14.6242 10.3Z"
                                            fill="currentColor"></path>
                                    </svg>

                                    <div class="relative z-10">
                                        <p class="text-gray-800 dark:text-white">
                                            <em>
                                                I just wanted to say that I'm very happy with my purchase so far. The
                                                documentation is outstanding - clear and detailed.
                                            </em>
                                        </p>
                                    </div>
                                </blockquote>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">
&lt;blockquote class="relative"&gt;
&lt;svg class="absolute top-0 ltr:left-0 rtl:right-0 transform -translate-x-6 -translate-y-8 h-16 w-16 text-gray-100 dark:text-white/10" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"&gt;
&lt;path d="M7.39762 10.3C7.39762 11.0733 7.14888 11.7 6.6514 12.18C6.15392 12.6333 5.52552 12.86 4.76621 12.86C3.84979 12.86 3.09047 12.5533 2.48825 11.94C1.91222 11.3266 1.62421 10.4467 1.62421 9.29999C1.62421 8.07332 1.96459 6.87332 2.64535 5.69999C3.35231 4.49999 4.33418 3.55332 5.59098 2.85999L6.4943 4.25999C5.81354 4.73999 5.26369 5.27332 4.84476 5.85999C4.45201 6.44666 4.19017 7.12666 4.05926 7.89999C4.29491 7.79332 4.56983 7.73999 4.88403 7.73999C5.61716 7.73999 6.21938 7.97999 6.69067 8.45999C7.16197 8.93999 7.39762 9.55333 7.39762 10.3ZM14.6242 10.3C14.6242 11.0733 14.3755 11.7 13.878 12.18C13.3805 12.6333 12.7521 12.86 11.9928 12.86C11.0764 12.86 10.3171 12.5533 9.71484 11.94C9.13881 11.3266 8.85079 10.4467 8.85079 9.29999C8.85079 8.07332 9.19117 6.87332 9.87194 5.69999C10.5789 4.49999 11.5608 3.55332 12.8176 2.85999L13.7209 4.25999C13.0401 4.73999 12.4903 5.27332 12.0713 5.85999C11.6786 6.44666 11.4168 7.12666 11.2858 7.89999C11.5215 7.79332 11.7964 7.73999 12.1106 7.73999C12.8437 7.73999 13.446 7.97999 13.9173 8.45999C14.3886 8.93999 14.6242 9.55333 14.6242 10.3Z" fill="currentColor"&gt;&lt;/path&gt;
&lt;/svg&gt;

&lt;div class="relative z-10"&gt;
&lt;p class="text-gray-800 dark:text-white"&gt;
    &lt;em&gt;
    I just wanted to say that I'm very happy with my purchase so far. The documentation is outstanding - clear and detailed.
    &lt;/em&gt;
&lt;/p&gt;
&lt;/div&gt;
&lt;/blockquote&gt;
                                        </code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Medium Blockquote
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem] btn-wave">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <blockquote class="relative">
                                    <svg class="absolute top-0 ltr:left-0 rtl:right-0 transform -translate-x-6 -translate-y-8 h-16 w-16 text-gray-100 dark:text-white/10"
                                        width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path
                                            d="M7.39762 10.3C7.39762 11.0733 7.14888 11.7 6.6514 12.18C6.15392 12.6333 5.52552 12.86 4.76621 12.86C3.84979 12.86 3.09047 12.5533 2.48825 11.94C1.91222 11.3266 1.62421 10.4467 1.62421 9.29999C1.62421 8.07332 1.96459 6.87332 2.64535 5.69999C3.35231 4.49999 4.33418 3.55332 5.59098 2.85999L6.4943 4.25999C5.81354 4.73999 5.26369 5.27332 4.84476 5.85999C4.45201 6.44666 4.19017 7.12666 4.05926 7.89999C4.29491 7.79332 4.56983 7.73999 4.88403 7.73999C5.61716 7.73999 6.21938 7.97999 6.69067 8.45999C7.16197 8.93999 7.39762 9.55333 7.39762 10.3ZM14.6242 10.3C14.6242 11.0733 14.3755 11.7 13.878 12.18C13.3805 12.6333 12.7521 12.86 11.9928 12.86C11.0764 12.86 10.3171 12.5533 9.71484 11.94C9.13881 11.3266 8.85079 10.4467 8.85079 9.29999C8.85079 8.07332 9.19117 6.87332 9.87194 5.69999C10.5789 4.49999 11.5608 3.55332 12.8176 2.85999L13.7209 4.25999C13.0401 4.73999 12.4903 5.27332 12.0713 5.85999C11.6786 6.44666 11.4168 7.12666 11.2858 7.89999C11.5215 7.79332 11.7964 7.73999 12.1106 7.73999C12.8437 7.73999 13.446 7.97999 13.9173 8.45999C14.3886 8.93999 14.6242 9.55333 14.6242 10.3Z"
                                            fill="currentColor"></path>
                                    </svg>

                                    <div class="relative z-10">
                                        <p class="text-gray-800 sm:text-xl dark:text-white"><em>
                                                I just wanted to say that I'm very happy with my purchase so far. The
                                                documentation is outstanding - clear and detailed.</em>
                                        </p>
                                    </div>
                                </blockquote>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">
&lt;blockquote class="relative"&gt;
&lt;svg class="absolute top-0 ltr:left-0 rtl:right-0 transform -translate-x-6 -translate-y-8 h-16 w-16 text-gray-100 dark:text-white/10" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"&gt;
&lt;path&gt; d="M7.39762 10.3C7.39762 11.0733 7.14888 11.7 6.6514 12.18C6.15392 12.6333 5.52552 12.86 4.76621 12.86C3.84979 12.86 3.09047 12.5533 2.48825 11.94C1.91222 11.3266 1.62421 10.4467 1.62421 9.29999C1.62421 8.07332 1.96459 6.87332 2.64535 5.69999C3.35231 4.49999 4.33418 3.55332 5.59098 2.85999L6.4943 4.25999C5.81354 4.73999 5.26369 5.27332 4.84476 5.85999C4.45201 6.44666 4.19017 7.12666 4.05926 7.89999C4.29491 7.79332 4.56983 7.73999 4.88403 7.73999C5.61716 7.73999 6.21938 7.97999 6.69067 8.45999C7.16197 8.93999 7.39762 9.55333 7.39762 10.3ZM14.6242 10.3C14.6242 11.0733 14.3755 11.7 13.878 12.18C13.3805 12.6333 12.7521 12.86 11.9928 12.86C11.0764 12.86 10.3171 12.5533 9.71484 11.94C9.13881 11.3266 8.85079 10.4467 8.85079 9.29999C8.85079 8.07332 9.19117 6.87332 9.87194 5.69999C10.5789 4.49999 11.5608 3.55332 12.8176 2.85999L13.7209 4.25999C13.0401 4.73999 12.4903 5.27332 12.0713 5.85999C11.6786 6.44666 11.4168 7.12666 11.2858 7.89999C11.5215 7.79332 11.7964 7.73999 12.1106 7.73999C12.8437 7.73999 13.446 7.97999 13.9173 8.45999C14.3886 8.93999 14.6242 9.55333 14.6242 10.3Z" fill="currentColor"&gt;&lt;/path&gt;
&lt;/svg&gt;

&lt;div class="relative z-10"&gt;
&lt;p&gt; class="text-gray-800 sm:text-xl dark:text-white"&gt;&lt;em&gt;
I just wanted to say that I'm very happy with my purchase so far. The documentation is outstanding - clear and detailed.&lt;/em&gt;
&lt;/p&gt;
&lt;/div&gt;
&lt;/blockquote&gt;
                                        </code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Large Blockquote
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem] btn-wave">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <blockquote class="relative">
                                    <svg class="absolute top-0 ltr:left-0 rtl:right-0 transform -translate-x-6 -translate-y-8 h-16 w-16 text-gray-100 dark:text-white/10"
                                        width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path
                                            d="M7.39762 10.3C7.39762 11.0733 7.14888 11.7 6.6514 12.18C6.15392 12.6333 5.52552 12.86 4.76621 12.86C3.84979 12.86 3.09047 12.5533 2.48825 11.94C1.91222 11.3266 1.62421 10.4467 1.62421 9.29999C1.62421 8.07332 1.96459 6.87332 2.64535 5.69999C3.35231 4.49999 4.33418 3.55332 5.59098 2.85999L6.4943 4.25999C5.81354 4.73999 5.26369 5.27332 4.84476 5.85999C4.45201 6.44666 4.19017 7.12666 4.05926 7.89999C4.29491 7.79332 4.56983 7.73999 4.88403 7.73999C5.61716 7.73999 6.21938 7.97999 6.69067 8.45999C7.16197 8.93999 7.39762 9.55333 7.39762 10.3ZM14.6242 10.3C14.6242 11.0733 14.3755 11.7 13.878 12.18C13.3805 12.6333 12.7521 12.86 11.9928 12.86C11.0764 12.86 10.3171 12.5533 9.71484 11.94C9.13881 11.3266 8.85079 10.4467 8.85079 9.29999C8.85079 8.07332 9.19117 6.87332 9.87194 5.69999C10.5789 4.49999 11.5608 3.55332 12.8176 2.85999L13.7209 4.25999C13.0401 4.73999 12.4903 5.27332 12.0713 5.85999C11.6786 6.44666 11.4168 7.12666 11.2858 7.89999C11.5215 7.79332 11.7964 7.73999 12.1106 7.73999C12.8437 7.73999 13.446 7.97999 13.9173 8.45999C14.3886 8.93999 14.6242 9.55333 14.6242 10.3Z"
                                            fill="currentColor"></path>
                                    </svg>

                                    <div class="relative z-10">
                                        <p class="text-xl text-gray-800 md:text-3xl md:leading-normal dark:text-white">
                                            <em>
                                                I just wanted to say that I'm very happy with my purchase so far. The
                                                documentation is outstanding - clear and detailed.</em>
                                        </p>
                                    </div>
                                </blockquote>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">
&lt;blockquote class="relative"&gt;
&lt;svg class="absolute top-0 ltr:left-0 rtl:right-0 transform -translate-x-6 -translate-y-8 h-16 w-16 text-gray-100 dark:text-white/10" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"&gt;
&lt;path d="M7.39762 10.3C7.39762 11.0733 7.14888 11.7 6.6514 12.18C6.15392 12.6333 5.52552 12.86 4.76621 12.86C3.84979 12.86 3.09047 12.5533 2.48825 11.94C1.91222 11.3266 1.62421 10.4467 1.62421 9.29999C1.62421 8.07332 1.96459 6.87332 2.64535 5.69999C3.35231 4.49999 4.33418 3.55332 5.59098 2.85999L6.4943 4.25999C5.81354 4.73999 5.26369 5.27332 4.84476 5.85999C4.45201 6.44666 4.19017 7.12666 4.05926 7.89999C4.29491 7.79332 4.56983 7.73999 4.88403 7.73999C5.61716 7.73999 6.21938 7.97999 6.69067 8.45999C7.16197 8.93999 7.39762 9.55333 7.39762 10.3ZM14.6242 10.3C14.6242 11.0733 14.3755 11.7 13.878 12.18C13.3805 12.6333 12.7521 12.86 11.9928 12.86C11.0764 12.86 10.3171 12.5533 9.71484 11.94C9.13881 11.3266 8.85079 10.4467 8.85079 9.29999C8.85079 8.07332 9.19117 6.87332 9.87194 5.69999C10.5789 4.49999 11.5608 3.55332 12.8176 2.85999L13.7209 4.25999C13.0401 4.73999 12.4903 5.27332 12.0713 5.85999C11.6786 6.44666 11.4168 7.12666 11.2858 7.89999C11.5215 7.79332 11.7964 7.73999 12.1106 7.73999C12.8437 7.73999 13.446 7.97999 13.9173 8.45999C14.3886 8.93999 14.6242 9.55333 14.6242 10.3Z" fill="currentColor"&gt;&lt;/path&gt;
&lt;/svg&gt;

&lt;div class="relative z-10"&gt;
&lt;p class="text-xl text-gray-800 md:text-3xl md:leading-normal dark:text-white"&gt;&lt;em&gt;
I just wanted to say that I'm very happy with my purchase so far. The documentation is outstanding - clear and detailed.&lt;/em&gt;
&lt;/p&gt;
&lt;/div&gt;
&lt;/blockquote&gt;
                                        </code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Center Aligned Blockquote
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem] btn-wave">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <blockquote class="relative text-center max-w-lg mx-auto">
                                    <div class="relative z-10">
                                        <p class="text-xl text-gray-800">
                                            <em class="relative">
                                                <svg class="absolute top-0 ltr:left-0 rtl:right-0 transform -translate-x-8 -translate-y-8 h-16 w-16 text-gray-100 sm:h-24 sm:w-24 dark:text-white/10"
                                                    width="16" height="16" viewBox="0 0 16 16" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path
                                                        d="M7.39762 10.3C7.39762 11.0733 7.14888 11.7 6.6514 12.18C6.15392 12.6333 5.52552 12.86 4.76621 12.86C3.84979 12.86 3.09047 12.5533 2.48825 11.94C1.91222 11.3266 1.62421 10.4467 1.62421 9.29999C1.62421 8.07332 1.96459 6.87332 2.64535 5.69999C3.35231 4.49999 4.33418 3.55332 5.59098 2.85999L6.4943 4.25999C5.81354 4.73999 5.26369 5.27332 4.84476 5.85999C4.45201 6.44666 4.19017 7.12666 4.05926 7.89999C4.29491 7.79332 4.56983 7.73999 4.88403 7.73999C5.61716 7.73999 6.21938 7.97999 6.69067 8.45999C7.16197 8.93999 7.39762 9.55333 7.39762 10.3ZM14.6242 10.3C14.6242 11.0733 14.3755 11.7 13.878 12.18C13.3805 12.6333 12.7521 12.86 11.9928 12.86C11.0764 12.86 10.3171 12.5533 9.71484 11.94C9.13881 11.3266 8.85079 10.4467 8.85079 9.29999C8.85079 8.07332 9.19117 6.87332 9.87194 5.69999C10.5789 4.49999 11.5608 3.55332 12.8176 2.85999L13.7209 4.25999C13.0401 4.73999 12.4903 5.27332 12.0713 5.85999C11.6786 6.44666 11.4168 7.12666 11.2858 7.89999C11.5215 7.79332 11.7964 7.73999 12.1106 7.73999C12.8437 7.73999 13.446 7.97999 13.9173 8.45999C14.3886 8.93999 14.6242 9.55333 14.6242 10.3Z"
                                                        fill="currentColor"></path>
                                                </svg>
                                                <span class="relative z-10 dark:text-white">I just wanted to say that
                                                    I'm very happy with my purchase so far. The documentation is
                                                    outstanding - clear and detailed.</span>
                                            </em>
                                        </p>
                                    </div>
                                </blockquote>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">
&lt;blockquote class="relative text-center max-w-lg mx-auto"&gt;
&lt;div class="relative z-10"&gt;
&lt;p class="text-xl text-gray-800"&gt;
&lt;em class="relative"&gt;
&lt;svg class="absolute top-0 ltr:left-0 rtl:right-0 transform -translate-x-8 -translate-y-8 h-16 w-16 text-gray-100 sm:h-24 sm:w-24 dark:text-white/10" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"&gt;
&lt;path&gt; d="M7.39762 10.3C7.39762 11.0733 7.14888 11.7 6.6514 12.18C6.15392 12.6333 5.52552 12.86 4.76621 12.86C3.84979 12.86 3.09047 12.5533 2.48825 11.94C1.91222 11.3266 1.62421 10.4467 1.62421 9.29999C1.62421 8.07332 1.96459 6.87332 2.64535 5.69999C3.35231 4.49999 4.33418 3.55332 5.59098 2.85999L6.4943 4.25999C5.81354 4.73999 5.26369 5.27332 4.84476 5.85999C4.45201 6.44666 4.19017 7.12666 4.05926 7.89999C4.29491 7.79332 4.56983 7.73999 4.88403 7.73999C5.61716 7.73999 6.21938 7.97999 6.69067 8.45999C7.16197 8.93999 7.39762 9.55333 7.39762 10.3ZM14.6242 10.3C14.6242 11.0733 14.3755 11.7 13.878 12.18C13.3805 12.6333 12.7521 12.86 11.9928 12.86C11.0764 12.86 10.3171 12.5533 9.71484 11.94C9.13881 11.3266 8.85079 10.4467 8.85079 9.29999C8.85079 8.07332 9.19117 6.87332 9.87194 5.69999C10.5789 4.49999 11.5608 3.55332 12.8176 2.85999L13.7209 4.25999C13.0401 4.73999 12.4903 5.27332 12.0713 5.85999C11.6786 6.44666 11.4168 7.12666 11.2858 7.89999C11.5215 7.79332 11.7964 7.73999 12.1106 7.73999C12.8437 7.73999 13.446 7.97999 13.9173 8.45999C14.3886 8.93999 14.6242 9.55333 14.6242 10.3Z" fill="currentColor"&gt;&lt;/path&gt;
&lt;/svg&gt;
&lt;span&gt; class="relative z-10 dark:text-white"&gt;I just wanted to say that I'm very happy with my purchase so far. The documentation is outstanding - clear and detailed.&lt;/span&gt;
&lt;/em&gt;
&lt;/p&gt;
&lt;/div&gt;
&lt;/blockquote&gt;
                                        </code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Right Aligned Blockquote
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem] btn-wave">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <blockquote class="relative text-end">
                                    <div class="relative z-10">
                                        <p class="text-xl text-gray-800">
                                            <em class="relative">
                                                <svg class="absolute top-0 ltr:left-0 rtl:right-0 transform -translate-x-8 -translate-y-8 h-16 w-16 text-gray-100 sm:h-24 sm:w-24 dark:text-white/10"
                                                    width="16" height="16" viewBox="0 0 16 16" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path
                                                        d="M7.39762 10.3C7.39762 11.0733 7.14888 11.7 6.6514 12.18C6.15392 12.6333 5.52552 12.86 4.76621 12.86C3.84979 12.86 3.09047 12.5533 2.48825 11.94C1.91222 11.3266 1.62421 10.4467 1.62421 9.29999C1.62421 8.07332 1.96459 6.87332 2.64535 5.69999C3.35231 4.49999 4.33418 3.55332 5.59098 2.85999L6.4943 4.25999C5.81354 4.73999 5.26369 5.27332 4.84476 5.85999C4.45201 6.44666 4.19017 7.12666 4.05926 7.89999C4.29491 7.79332 4.56983 7.73999 4.88403 7.73999C5.61716 7.73999 6.21938 7.97999 6.69067 8.45999C7.16197 8.93999 7.39762 9.55333 7.39762 10.3ZM14.6242 10.3C14.6242 11.0733 14.3755 11.7 13.878 12.18C13.3805 12.6333 12.7521 12.86 11.9928 12.86C11.0764 12.86 10.3171 12.5533 9.71484 11.94C9.13881 11.3266 8.85079 10.4467 8.85079 9.29999C8.85079 8.07332 9.19117 6.87332 9.87194 5.69999C10.5789 4.49999 11.5608 3.55332 12.8176 2.85999L13.7209 4.25999C13.0401 4.73999 12.4903 5.27332 12.0713 5.85999C11.6786 6.44666 11.4168 7.12666 11.2858 7.89999C11.5215 7.79332 11.7964 7.73999 12.1106 7.73999C12.8437 7.73999 13.446 7.97999 13.9173 8.45999C14.3886 8.93999 14.6242 9.55333 14.6242 10.3Z"
                                                        fill="currentColor"></path>
                                                </svg>
                                                <span class="relative z-10 dark:text-white">I just wanted to say that
                                                    I'm very happy with my purchase so far. The documentation is
                                                    outstanding - clear and detailed.</span>
                                            </em>
                                        </p>
                                    </div>
                                </blockquote>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">
&lt;blockquote class="relative text-end"&gt;
&lt;div class="relative z-10"&gt;
&lt;p class="text-xl text-gray-800"&gt;
&lt;em class="relative"&gt;
&lt;svg class="absolute top-0 ltr:left-0 rtl:right-0 transform -translate-x-8 -translate-y-8 h-16 w-16 text-gray-100 sm:h-24 sm:w-24 dark:text-white/10" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"&gt;
&lt;path d="M7.39762 10.3C7.39762 11.0733 7.14888 11.7 6.6514 12.18C6.15392 12.6333 5.52552 12.86 4.76621 12.86C3.84979 12.86 3.09047 12.5533 2.48825 11.94C1.91222 11.3266 1.62421 10.4467 1.62421 9.29999C1.62421 8.07332 1.96459 6.87332 2.64535 5.69999C3.35231 4.49999 4.33418 3.55332 5.59098 2.85999L6.4943 4.25999C5.81354 4.73999 5.26369 5.27332 4.84476 5.85999C4.45201 6.44666 4.19017 7.12666 4.05926 7.89999C4.29491 7.79332 4.56983 7.73999 4.88403 7.73999C5.61716 7.73999 6.21938 7.97999 6.69067 8.45999C7.16197 8.93999 7.39762 9.55333 7.39762 10.3ZM14.6242 10.3C14.6242 11.0733 14.3755 11.7 13.878 12.18C13.3805 12.6333 12.7521 12.86 11.9928 12.86C11.0764 12.86 10.3171 12.5533 9.71484 11.94C9.13881 11.3266 8.85079 10.4467 8.85079 9.29999C8.85079 8.07332 9.19117 6.87332 9.87194 5.69999C10.5789 4.49999 11.5608 3.55332 12.8176 2.85999L13.7209 4.25999C13.0401 4.73999 12.4903 5.27332 12.0713 5.85999C11.6786 6.44666 11.4168 7.12666 11.2858 7.89999C11.5215 7.79332 11.7964 7.73999 12.1106 7.73999C12.8437 7.73999 13.446 7.97999 13.9173 8.45999C14.3886 8.93999 14.6242 9.55333 14.6242 10.3Z" fill="currentColor"&gt;&lt;/path&gt;
&lt;/svg&gt;
&lt;span class="relative z-10 dark:text-white"&gt;I just wanted to say that I'm very happy with my purchase so far. The documentation is outstanding - clear and detailed.&lt;/span&gt;
&lt;/em&gt;
&lt;/p&gt;
&lt;/div&gt;
&lt;/blockquote&gt;
                                        </code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Naming A Source
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem] btn-wave">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <blockquote class="relative">
                                    <svg class="absolute top-0 ltr:left-0 rtl:right-0 transform -translate-x-6 -translate-y-8 h-16 w-16 text-gray-100 dark:text-white/10"
                                        width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path
                                            d="M7.39762 10.3C7.39762 11.0733 7.14888 11.7 6.6514 12.18C6.15392 12.6333 5.52552 12.86 4.76621 12.86C3.84979 12.86 3.09047 12.5533 2.48825 11.94C1.91222 11.3266 1.62421 10.4467 1.62421 9.29999C1.62421 8.07332 1.96459 6.87332 2.64535 5.69999C3.35231 4.49999 4.33418 3.55332 5.59098 2.85999L6.4943 4.25999C5.81354 4.73999 5.26369 5.27332 4.84476 5.85999C4.45201 6.44666 4.19017 7.12666 4.05926 7.89999C4.29491 7.79332 4.56983 7.73999 4.88403 7.73999C5.61716 7.73999 6.21938 7.97999 6.69067 8.45999C7.16197 8.93999 7.39762 9.55333 7.39762 10.3ZM14.6242 10.3C14.6242 11.0733 14.3755 11.7 13.878 12.18C13.3805 12.6333 12.7521 12.86 11.9928 12.86C11.0764 12.86 10.3171 12.5533 9.71484 11.94C9.13881 11.3266 8.85079 10.4467 8.85079 9.29999C8.85079 8.07332 9.19117 6.87332 9.87194 5.69999C10.5789 4.49999 11.5608 3.55332 12.8176 2.85999L13.7209 4.25999C13.0401 4.73999 12.4903 5.27332 12.0713 5.85999C11.6786 6.44666 11.4168 7.12666 11.2858 7.89999C11.5215 7.79332 11.7964 7.73999 12.1106 7.73999C12.8437 7.73999 13.446 7.97999 13.9173 8.45999C14.3886 8.93999 14.6242 9.55333 14.6242 10.3Z"
                                            fill="currentColor"></path>
                                    </svg>

                                    <div class="relative z-10">
                                        <p class="text-gray-800 sm:text-xl dark:text-white"><em>
                                                I just wanted to say that I'm very happy with my purchase so far. The
                                                documentation is outstanding - clear and detailed.</em>
                                        </p>
                                    </div>

                                    <footer class="mt-6">
                                        <div class="text-base font-semibold text-gray-800 dark:text-white/70">Josh
                                            Grazioso</div>
                                    </footer>
                                </blockquote>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">
&lt;blockquote class="relative"&gt;
&lt;svg class="absolute top-0 ltr:left-0 rtl:right-0 transform -translate-x-6 -translate-y-8 h-16 w-16 text-gray-100 dark:text-white/10" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"&gt;
&lt;path&gt; d="M7.39762 10.3C7.39762 11.0733 7.14888 11.7 6.6514 12.18C6.15392 12.6333 5.52552 12.86 4.76621 12.86C3.84979 12.86 3.09047 12.5533 2.48825 11.94C1.91222 11.3266 1.62421 10.4467 1.62421 9.29999C1.62421 8.07332 1.96459 6.87332 2.64535 5.69999C3.35231 4.49999 4.33418 3.55332 5.59098 2.85999L6.4943 4.25999C5.81354 4.73999 5.26369 5.27332 4.84476 5.85999C4.45201 6.44666 4.19017 7.12666 4.05926 7.89999C4.29491 7.79332 4.56983 7.73999 4.88403 7.73999C5.61716 7.73999 6.21938 7.97999 6.69067 8.45999C7.16197 8.93999 7.39762 9.55333 7.39762 10.3ZM14.6242 10.3C14.6242 11.0733 14.3755 11.7 13.878 12.18C13.3805 12.6333 12.7521 12.86 11.9928 12.86C11.0764 12.86 10.3171 12.5533 9.71484 11.94C9.13881 11.3266 8.85079 10.4467 8.85079 9.29999C8.85079 8.07332 9.19117 6.87332 9.87194 5.69999C10.5789 4.49999 11.5608 3.55332 12.8176 2.85999L13.7209 4.25999C13.0401 4.73999 12.4903 5.27332 12.0713 5.85999C11.6786 6.44666 11.4168 7.12666 11.2858 7.89999C11.5215 7.79332 11.7964 7.73999 12.1106 7.73999C12.8437 7.73999 13.446 7.97999 13.9173 8.45999C14.3886 8.93999 14.6242 9.55333 14.6242 10.3Z" fill="currentColor"&gt;&lt;/path&gt;
&lt;/svg&gt;

&lt;div class="relative z-10"&gt;
&lt;p&gt; class="text-gray-800 sm:text-xl dark:text-white"&gt;&lt;em&gt;
I just wanted to say that I'm very happy with my purchase so far. The documentation is outstanding - clear and detailed.&lt;/em&gt;
&lt;/p&gt;
&lt;/div&gt;

&lt;footer class="mt-6"&gt;
&lt;div&gt; class="text-base font-semibold text-gray-800 dark:text-white/70"&gt;Josh Grazioso&lt;/div&gt;
&lt;/footer&gt;
&lt;/blockquote&gt;
                                        </code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Blockquote With Avatar
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem] btn-wave">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <blockquote class="relative">
                                    <svg class="absolute top-0 ltr:left-0 rtl:right-0 transform -translate-x-6 -translate-y-8 h-16 w-16 text-gray-100 dark:text-white/10"
                                        width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path
                                            d="M7.39762 10.3C7.39762 11.0733 7.14888 11.7 6.6514 12.18C6.15392 12.6333 5.52552 12.86 4.76621 12.86C3.84979 12.86 3.09047 12.5533 2.48825 11.94C1.91222 11.3266 1.62421 10.4467 1.62421 9.29999C1.62421 8.07332 1.96459 6.87332 2.64535 5.69999C3.35231 4.49999 4.33418 3.55332 5.59098 2.85999L6.4943 4.25999C5.81354 4.73999 5.26369 5.27332 4.84476 5.85999C4.45201 6.44666 4.19017 7.12666 4.05926 7.89999C4.29491 7.79332 4.56983 7.73999 4.88403 7.73999C5.61716 7.73999 6.21938 7.97999 6.69067 8.45999C7.16197 8.93999 7.39762 9.55333 7.39762 10.3ZM14.6242 10.3C14.6242 11.0733 14.3755 11.7 13.878 12.18C13.3805 12.6333 12.7521 12.86 11.9928 12.86C11.0764 12.86 10.3171 12.5533 9.71484 11.94C9.13881 11.3266 8.85079 10.4467 8.85079 9.29999C8.85079 8.07332 9.19117 6.87332 9.87194 5.69999C10.5789 4.49999 11.5608 3.55332 12.8176 2.85999L13.7209 4.25999C13.0401 4.73999 12.4903 5.27332 12.0713 5.85999C11.6786 6.44666 11.4168 7.12666 11.2858 7.89999C11.5215 7.79332 11.7964 7.73999 12.1106 7.73999C12.8437 7.73999 13.446 7.97999 13.9173 8.45999C14.3886 8.93999 14.6242 9.55333 14.6242 10.3Z"
                                            fill="currentColor"></path>
                                    </svg>

                                    <div class="relative z-10">
                                        <p class="text-gray-800 sm:text-xl dark:text-white"><em>
                                                I just wanted to say that I'm very happy with my purchase so far. The
                                                documentation is outstanding - clear and detailed.</em>
                                        </p>
                                    </div>

                                    <footer class="mt-6">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <img class="h-10 w-10 rounded-full" src="{{asset('build/assets/images/faces/1.jpg')}}"
                                                    alt="Image Description">
                                            </div>
                                            <div class="ltr:ml-4 rtl:mr-4">
                                                <div class="text-base font-semibold text-gray-800 dark:text-white/70">
                                                    Josh Grazioso</div>
                                                <div class="text-xs text-gray-500 dark:text-white/70">Source title</div>
                                            </div>
                                        </div>
                                    </footer>
                                </blockquote>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">
&lt;blockquote class="relative"&gt;
&lt;svg class="absolute top-0 ltr:left-0 rtl:right-0 transform -translate-x-6 -translate-y-8 h-16 w-16 text-gray-100 dark:text-white/10" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"&gt;
&lt;path d="M7.39762 10.3C7.39762 11.0733 7.14888 11.7 6.6514 12.18C6.15392 12.6333 5.52552 12.86 4.76621 12.86C3.84979 12.86 3.09047 12.5533 2.48825 11.94C1.91222 11.3266 1.62421 10.4467 1.62421 9.29999C1.62421 8.07332 1.96459 6.87332 2.64535 5.69999C3.35231 4.49999 4.33418 3.55332 5.59098 2.85999L6.4943 4.25999C5.81354 4.73999 5.26369 5.27332 4.84476 5.85999C4.45201 6.44666 4.19017 7.12666 4.05926 7.89999C4.29491 7.79332 4.56983 7.73999 4.88403 7.73999C5.61716 7.73999 6.21938 7.97999 6.69067 8.45999C7.16197 8.93999 7.39762 9.55333 7.39762 10.3ZM14.6242 10.3C14.6242 11.0733 14.3755 11.7 13.878 12.18C13.3805 12.6333 12.7521 12.86 11.9928 12.86C11.0764 12.86 10.3171 12.5533 9.71484 11.94C9.13881 11.3266 8.85079 10.4467 8.85079 9.29999C8.85079 8.07332 9.19117 6.87332 9.87194 5.69999C10.5789 4.49999 11.5608 3.55332 12.8176 2.85999L13.7209 4.25999C13.0401 4.73999 12.4903 5.27332 12.0713 5.85999C11.6786 6.44666 11.4168 7.12666 11.2858 7.89999C11.5215 7.79332 11.7964 7.73999 12.1106 7.73999C12.8437 7.73999 13.446 7.97999 13.9173 8.45999C14.3886 8.93999 14.6242 9.55333 14.6242 10.3Z" fill="currentColor"&gt;&lt;/path&gt;
&lt;/svg&gt;

&lt;div class="relative z-10"&gt;
&lt;p class="text-gray-800 sm:text-xl dark:text-white"&gt;&lt;em&gt;
I just wanted to say that I'm very happy with my purchase so far. The documentation is outstanding - clear and detailed.&lt;/em&gt;
&lt;/p&gt;
&lt;/div&gt;

&lt;footer class="mt-6"&gt;
&lt;div class="flex items-center"&gt;
&lt;div class="flex-shrink-0"&gt;
&lt;img class="h-10 w-10 rounded-full" src="{{asset('build/assets/images/faces/1.jpg')}}" alt="Image Description"&gt;
&lt;/div&gt;
&lt;div class="ltr:ml-4 rtl:mr-4"&gt;
&lt;div class="text-base font-semibold text-gray-800 dark:text-white/70"&gt;Josh Grazioso&lt;/div&gt;
&lt;div class="text-xs text-gray-500 dark:text-white/70"&gt;Source title&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/footer&gt;
&lt;/blockquote&gt;
                                        </code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Left Bordered Blockquote
                                </div>
                                <div class="prism-toggle">
                                    <button type="button"
                                        class="ti-btn !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem] btn-wave">Show
                                        Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <blockquote
                                    class="relative ltr:border-l-4 rtl:border-r-4 ltr:pl-4 rtl:pr-4 ltr:sm:pl-6 rtl:sm:pr-6 dark:border-white/10">
                                    <p class="text-gray-800 sm:text-xl dark:text-white"><em>
                                            I just wanted to say that I'm very happy with my purchase so far. The
                                            documentation is outstanding - clear and detailed.</em>
                                    </p>

                                    <footer class="mt-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <img class="h-10 w-10 rounded-full" src="{{asset('build/assets/images/faces/1.jpg')}}"
                                                    alt="Image Description">
                                            </div>
                                            <div class="ltr:ml-4 rtl:mr-4">
                                                <div class="text-base font-semibold text-gray-800 dark:text-white/70">
                                                    Josh Grazioso</div>
                                                <div class="text-xs text-gray-500 dark:text-white/70">Source title</div>
                                            </div>
                                        </div>
                                    </footer>
                                </blockquote>
                            </div>
                            <div class="box-footer hidden border-t-0">
                                <!-- Prism Code -->
                                <pre class="language-html"><code class="language-html">
&lt;blockquote class="relative ltr:border-l-4 rtl:border-r-4 ltr:pl-4 rtl:pr-4 ltr:sm:pl-6 rtl:sm:pr-6 dark:border-white/10"&gt;
&lt;p class="text-gray-800 sm:text-xl dark:text-white"&gt;&lt;em&gt;
I just wanted to say that I'm very happy with my purchase so far. The documentation is outstanding - clear and detailed.&lt;/em&gt;
&lt;/p&gt;

&lt;footer class="mt-4"&gt;
&lt;div class="flex items-center"&gt;
&lt;div&gt; class="flex-shrink-0"&gt;
&lt;img class="h-10 w-10 rounded-full" src="{{asset('build/assets/images/faces/1.jpg')}}" alt="Image Description"&gt;
&lt;/div&gt;
&lt;div class="ltr:ml-4 rtl:mr-4"&gt;
&lt;div&gt; class="text-base font-semibold text-gray-800 dark:text-white/70"&gt;Josh Grazioso&lt;/div&gt;
&lt;div&gt; class="text-xs text-gray-500 dark:text-white/70"&gt;Source title&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/footer&gt;
&lt;/blockquote&gt;
                                        </code></pre>
                                <!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Start::row-1 -->

@endsection

@section('scripts')

        <!-- Prism JS -->
        <script src="{{asset('build/assets/libs/prismjs/prism.js')}}"></script>
        @vite('resources/assets/js/prism-custom.js')

        
@endsection