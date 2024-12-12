@extends('layouts.master')

@section('styles')

        <!-- Prism CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/prismjs/themes/prism-coy.min.css')}}">

        <!-- noui Slider -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/nouislider/nouislider.min.css')}}">
      
@endsection

@section('content')

                    <!-- Page Header -->
                    <div class="block justify-between page-header md:flex">
                        <div>
                            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">Range Slider</h3>
                        </div>
                        <ol class="flex items-center whitespace-nowrap min-w-0">
                            <li class="text-[0.813rem] ps-[0.5rem]">
                              <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                Form Elements
                                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                              </a>
                            </li>
                            <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                               Range Slider
                            </li>
                        </ol>
                    </div>
                    <!-- Page Header Close -->

                <!-- Start:: row-1 -->
                <div class="grid grid-cols-12 gap-6">
                    <div class="xl:col-span-3 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Default Range
                                </div>
                                <div class="prism-toggle">
                                    <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <input type="range" class="form-range" id="customRange1">
                            </div>
                            <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;input type="range" class="form-range" id="customRange1"&gt;</code></pre>
<!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-3 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Disabled Range
                                </div>
                                <div class="prism-toggle">
                                    <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <input type="range" class="form-range" id="disabledRange" disabled>
                            </div>
                            <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;input type="range" class="form-range" id="disabledRange" disabled&gt;</code></pre>
<!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-3 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Range With Min and Max Values
                                </div>
                                <div class="prism-toggle">
                                    <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <input type="range" class="form-range" min="0" max="5" id="customRange2">
                            </div>
                            <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;input type="range" class="form-range" min="0" max="5" id="customRange2"&gt;</code></pre>
<!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-3 col-span-12">
                        <div class="box">
                            <div class="box-header justify-between">
                                <div class="box-title">
                                    Range With Steps
                                </div>
                                <div class="prism-toggle">
                                    <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <input type="range" class="form-range" min="0" max="5" step="0.5" id="customRange3">
                            </div>
                            <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;input type="range" class="form-range" min="0" max="5" step="0.5" id="customRange3"&gt;</code></pre>
<!-- Prism Code -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End:: row-1 -->

                <!-- Start:: row-2 -->
                <h6 class="mb-3">noUiSlider:</h6>
                <div class="grid grid-cols-12 gap-6">
                    <div class="xl:col-span-3 col-span-12">
                        <div class="box">
                            <div class="box-header">
                                <div class="box-title">
                                    Default-Styling
                                </div>
                            </div>
                            <div class="box-body">
                                <div id="slider"></div>
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-3 col-span-12">
                        <div class="box">
                            <div class="box-header">
                                <div class="box-title">
                                    Fit Handles
                                </div>
                            </div>
                            <div class="box-body">
                                <div id="slider-fit"></div>
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-3 col-span-12">
                        <div class="box">
                            <div class="box-header">
                                <div class="box-title">
                                    Rounded Styling
                                </div>
                            </div>
                            <div class="box-body">
                                <div id="slider-round"></div>
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-3 col-span-12">
                        <div class="box">
                            <div class="box-header">
                                <div class="box-title">
                                    Square Styling
                                </div>
                            </div>
                            <div class="box-body">
                                <div id="slider-square"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End:: row-2 -->

                <!-- Start:: row-3 -->
                <div class="grid grid-cols-12 gap-6">
                    <div class="xl:col-span-3 col-span-12">
                        <div class="box">
                            <div class="box-header">
                                <div class="box-title">Color Picker Slider</div>
                            </div>
                            <div class="box-body">
                                <div class="sliders" id="color1"></div>
                                <div class="sliders" id="color2"></div>
                                <div class="sliders" id="color3"></div>
                                <div id="result"></div>
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <div class="xl:col-span-12 col-span-12">
                            <div class="box">
                                <div class="box-header">
                                    <div class="box-title">Locking Sliders</div>
                                </div>
                                <div class="box-body">
                                    <div id="slider1"></div>
                                    <div id="slider1-span" class="my-1"></div>
                                    <div id="slider2"></div>
                                    <div id="slider2-span" class="my-1"></div>
                                    <div id="slider3"></div>
                                    <div id="slider3-span" class="my-1"></div>
                                    <button type="button" id="lockbutton" class="ti-btn btn-wave !py-1 !px-2 !text-[0.75rem] ti-btn-primary-full ltr:float-right rtl:float-left">Lock</button>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-12 col-span-12">
                            <div class="box">
                                <div class="box-header">
                                    <div class="box-title">Merging tooltips slider</div>
                                </div>
                                <div class="box-body">
                                    <div id="merging-tooltips"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-3 col-span-12">
                        <div class="grid grid-cols-12">
                            <div class="xl:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header">
                                        <div class="box-title">Non Linear Slider</div>
                                    </div>
                                    <div class="box-body">
                                        <div id="nonlinear"></div>
                                        <div id="lower-value" class="mt-5"></div>
                                        <div id="upper-value" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header">
                                        <div class="box-title">Sliding Handles Tooltips</div>
                                    </div>
                                    <div class="box-body">
                                        <div id="slider-hide"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End:: row-3 -->

                <!-- Start:: row-4 -->
                <div class="grid grid-cols-12 gap-x-6">
                    <div class="xl:col-span-10 col-span-12">
                        <div class="box">
                            <div class="box-header">
                                <div class="box-title">Colored Connect Elements</div>
                            </div>
                            <div class="box-body">
                                <div class="slider" id="color-slider"></div>
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-2 col-span-12">
                        <div class="box">
                            <div class="box-header">
                                <div class="box-title">Slider Toggle</div>
                            </div>
                            <div class="box-body">
                                <div id="slider-toggle"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End:: row-4 -->

                <!-- Start:: row-5 -->
                <div class="grid grid-cols-12 gap-6">
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header">
                                <div class="box-title">Toggle Movement By Clicking Pips</div>
                            </div>
                            <div class="box-body !pb-[3rem]">
                                <div id="slider-pips"></div>
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <div class="box">
                            <div class="box-header">
                                <div class="box-title">Soft Limits</div>
                            </div>
                            <div class="box-body !pb-[3rem]">
                                <div id="soft"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End:: row-5 -->

                <!-- Start:: row-6 -->
                <h6 class="mb-3">noUiSlider Colors:</h6>
                <div class="grid grid-cols-12 gap-6">
                    <div class="xl:col-span-4 col-span-12">
                        <div class="box">
                            <div class="box-header">
                                <div class="box-title">
                                    Primary
                                </div>
                            </div>
                            <div class="box-body">
                                <div id="primary-colored-slider"></div>
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-4 col-span-12">
                        <div class="box">
                            <div class="box-header">
                                <div class="box-title">
                                    Secondary
                                </div>
                            </div>
                            <div class="box-body">
                                <div id="secondary-colored-slider"></div>
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-4 col-span-12">
                        <div class="box">
                            <div class="box-header">
                                <div class="box-title">
                                    Warning
                                </div>
                            </div>
                            <div class="box-body">
                                <div id="warning-colored-slider"></div>
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-4 col-span-12">
                        <div class="box">
                            <div class="box-header">
                                <div class="box-title">
                                    Info
                                </div>
                            </div>
                            <div class="box-body">
                                <div id="info-colored-slider"></div>
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-4 col-span-12">
                        <div class="box">
                            <div class="box-header">
                                <div class="box-title">
                                    Success
                                </div>
                            </div>
                            <div class="box-body">
                                <div id="success-colored-slider"></div>
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-4 col-span-12">
                        <div class="box">
                            <div class="box-header">
                                <div class="box-title">
                                    Danger
                                </div>
                            </div>
                            <div class="box-body">
                                <div id="danger-colored-slider"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End:: row-6 -->

@endsection

@section('scripts')

        <!-- Prism JS -->
        <script src="{{asset('build/assets/libs/prismjs/prism.js')}}"></script>
        @vite('resources/assets/js/prism-custom.js')


        <!-- noUiSlider JS -->
        <script src="{{asset('build/assets/libs/nouislider/nouislider.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/wnumb/wNumb.min.js')}}"></script>
        @vite('resources/assets/js/nouislider.js')


@endsection