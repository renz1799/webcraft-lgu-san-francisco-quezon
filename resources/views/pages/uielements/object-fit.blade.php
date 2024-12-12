@extends('layouts.master')

@section('styles')
 
        <!-- Prism CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/prismjs/themes/prism-coy.min.css')}}">

@endsection

@section('content')

                        <!-- Page Header -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Object Fit</h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                  <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                  Ui Elements
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                  </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                    Object Fit
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start::row-1 -->
                        <div class="grid grid-cols-12 gap-x-6">
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">Object Fit Contain</div>
                                        <div class="prism-toggle">
                                            <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body object-fit-container">
                                        <img src="{{asset('build/assets/images/media/media-28.jpg')}}"
                                            class="object-contain border dark:border-defaultborder/10 !rounded-md"
                                            alt="...">
                                    </div>
                                    <div class="box-footer hidden !border-t-0">
                                        <!-- Prism Code -->
                                        <pre
                                            class="language-html">
<code class="language-html">
&lt;img src="{{asset('build/assets/images/media/media-28.jpg')}}" class="object-contain border dark:border-defaultborder/10 !rounded-md" alt="..."&gt;
</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">Object Fit Cover</div>
                                        <div class="prism-toggle">
                                            <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body object-fit-container">
                                        <img src="{{asset('build/assets/images/media/media-28.jpg')}}"
                                            class="object-cover border dark:border-defaultborder/10 md:!rounded-md" alt="...">
                                    </div>
                                    <div class="box-footer hidden !border-t-0">
                                        <!-- Prism Code -->
                                        <pre
                                            class="language-html">
<code class="language-html">&lt;img src="{{asset('build/assets/images/media/media-28.jpg')}}"
class="object-cover border dark:border-defaultborder/10 md:!rounded-md" alt="..."&gt;
</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">Object Fit Fill</div>
                                        <div class="prism-toggle">
                                            <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body object-fit-container">
                                        <img src="{{asset('build/assets/images/media/media-28.jpg')}}"
                                            class="object-fill border dark:border-defaultborder/10 !rounded-md" alt="...">
                                    </div>
                                    <div class="box-footer hidden !border-t-0">
                                        <!-- Prism Code -->
                                        <pre
                                            class="language-html">
<code class="language-html">
&lt;img src="{{asset('build/assets/images/media/media-28.jpg')}}"
class="object-fill border dark:border-defaultborder/10 !rounded-md" alt="..."&gt;
</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">Object Fit Scale Down</div>
                                        <div class="prism-toggle">
                                            <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body object-fit-container">
                                        <img src="{{asset('build/assets/images/media/media-28.jpg')}}"
                                            class="object-scale-down border dark:border-defaultborder/10 !rounded-md" alt="...">
                                    </div>
                                    <div class="box-footer hidden !border-t-0">
                                        <!-- Prism Code -->
                                        <pre
                                            class="language-html">
<code class="language-html">
&lt;img src="{{asset('build/assets/images/media/media-28.jpg')}}"
class="object-scale-down border dark:border-defaultborder/10 !rounded-md" alt="..."&gt;
</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">Object Fit None</div>
                                        <div class="prism-toggle">
                                            <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body object-fit-container">
                                        <img src="{{asset('build/assets/images/media/media-28.jpg')}}"
                                            class="object-none border dark:border-defaultborder/10 !rounded-md" alt="...">
                                    </div>
                                    <div class="box-footer hidden !border-t-0">
                                        <!-- Prism Code -->
                                        <pre
                                            class="language-html">
<code class="language-html">
&lt;img src="{{asset('build/assets/images/media/media-28.jpg')}}"
class="object-none border dark:border-defaultborder/10 !rounded-md" alt="..."&gt;
</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">Object Fit Contain (SM - responsive)</div>
                                        <div class="prism-toggle">
                                            <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body object-fit-container">
                                        <img src="{{asset('build/assets/images/media/media-28.jpg')}}"
                                            class="sm:object-contain border dark:border-defaultborder/10 !rounded-md" alt="...">
                                    </div>
                                    <div class="box-footer hidden !border-t-0">
                                        <!-- Prism Code -->
                                        <pre
                                            class="language-html">
<code class="language-html">
&lt;img src="{{asset('build/assets/images/media/media-28.jpg')}}"
class="sm:object-contain border dark:border-defaultborder/10 !rounded-md" alt="..."&gt;
</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">Object Fit Contain (MD - responsive)</div>
                                        <div class="prism-toggle">
                                            <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body object-fit-container">
                                        <img src="{{asset('build/assets/images/media/media-28.jpg')}}"
                                            class="md:object-contain border dark:border-defaultborder/10 !rounded-md" alt="...">
                                    </div>
                                    <div class="box-footer hidden !border-t-0">
                                        <!-- Prism Code -->
                                        <pre
                                            class="language-html">
<code class="language-html">
&lt;img src="{{asset('build/assets/images/media/media-28.jpg')}}"
class="md:object-contain border dark:border-defaultborder/10 !rounded-md" alt="..."&gt;
</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">Object Fit Contain (LG - responsive)</div>
                                        <div class="prism-toggle">
                                            <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body object-fit-container">
                                        <img src="{{asset('build/assets/images/media/media-28.jpg')}}"
                                            class="lg:object-contain border dark:border-defaultborder/10 !rounded-md" alt="...">
                                    </div>
                                    <div class="box-footer hidden !border-t-0">
                                        <!-- Prism Code -->
                                        <pre
                                            class="language-html">
<code class="language-html">
&lt;img src="{{asset('build/assets/images/media/media-28.jpg')}}"
class="lg:object-contain border dark:border-defaultborder/10 !rounded-md" alt="..."&gt;
</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">Object Fit Contain (XL - responsive)</div>
                                        <div class="prism-toggle">
                                            <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body object-fit-container">
                                        <img src="{{asset('build/assets/images/media/media-28.jpg')}}"
                                            class="xl:object-contain border dark:border-defaultborder/10 !rounded-md" alt="...">
                                    </div>
                                    <div class="box-footer hidden !border-t-0">
                                        <!-- Prism Code -->
                                        <pre
                                            class="language-html">
<code class="language-html">
&lt;img src="{{asset('build/assets/images/media/media-28.jpg')}}"
class="xl:object-contain border dark:border-defaultborder/10 !rounded-md" alt="..."&gt;
</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">Object Fit Contain (XXL - responsive)</div>
                                        <div class="prism-toggle">
                                            <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body object-fit-container">
                                        <img src="{{asset('build/assets/images/media/media-28.jpg')}}"
                                            class="xxl:object-contain border dark:border-defaultborder/10 !rounded-md"
                                            alt="...">
                                    </div>
                                    <div class="box-footer hidden !border-t-0">
                                        <!-- Prism Code -->
                                        <pre
                                            class="language-html">
<code class="language-html">
&lt;img src="{{asset('build/assets/images/media/media-28.jpg')}}"
class="xxl:object-contain border dark:border-defaultborder/10 !rounded-md"
alt="..."&gt;
</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">
                                            Object Fit Contain Video
                                        </div>
                                        <div class="prism-toggle">
                                            <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body object-fit-container">
                                        <video src="{{asset('build/assets/video/1.mp4')}}"
                                            class="object-contain !rounded-md border dark:border-defaultborder/10"
                                            autoplay loop muted></video>
                                    </div>
                                    <div class="box-footer hidden !border-t-0">
                                        <!-- Prism Code -->
                                        <pre
                                            class="language-html">
<code class="language-html">
&lt;video src="{{asset('build/assets/video/1.mp4')}}"
class="object-contain !rounded-md border dark:border-defaultborder/10"
autoplay loop muted&gt;&lt;/video&gt;
</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">
                                            Object Fit Cover Video
                                        </div>
                                        <div class="prism-toggle">
                                            <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body object-fit-container">
                                        <video src="{{asset('build/assets/video/1.mp4')}}"
                                            class="object-cover !rounded-md border dark:border-defaultborder/10"
                                            autoplay loop muted></video>
                                    </div>
                                    <div class="box-footer hidden !border-t-0">
                                        <!-- Prism Code -->
                                        <pre
                                            class="language-html">
<code class="language-html">
&lt;video src="{{asset('build/assets/video/1.mp4')}}"
class="object-cover !rounded-md border dark:border-defaultborder/10"
autoplay loop muted&gt;&lt;/video&gt;
</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">
                                            Object Fit Fill Video
                                        </div>
                                        <div class="prism-toggle">
                                            <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body object-fit-container">
                                        <video src="{{asset('build/assets/video/1.mp4')}}"
                                            class="object-fill !rounded-md border dark:border-defaultborder/10"
                                            autoplay loop muted></video>
                                    </div>
                                    <div class="box-footer hidden !border-t-0">
                                        <!-- Prism Code -->
                                        <pre
                                            class="language-html">
<code class="language-html">
&lt;video src="{{asset('build/assets/video/1.mp4')}}"
class="object-fill !rounded-md border dark:border-defaultborder/10"
autoplay loop muted&gt;&lt;/video&gt;
</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">
                                            Object Fit Scale Video
                                        </div>
                                        <div class="prism-toggle">
                                            <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body object-fit-container">
                                        <video src="{{asset('build/assets/video/1.mp4')}}"
                                            class="object-scale-down !rounded-md border dark:border-defaultborder/10"
                                            autoplay loop muted></video>
                                    </div>
                                    <div class="box-footer hidden !border-t-0">
                                        <!-- Prism Code -->
                                        <pre
                                            class="language-html">
<code class="language-html">
&lt;video src="{{asset('build/assets/video/1.mp4')}}"
class="object-scale-down !rounded-md border dark:border-defaultborder/10"
autoplay loop muted&gt;&lt;/video&gt;
</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">
                                            Object Fit None Video
                                        </div>
                                        <div class="prism-toggle">
                                            <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !font-medium !text-[0.75rem]">Show
                                                Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body object-fit-container">
                                        <video src="{{asset('build/assets/video/1.mp4')}}"
                                            class="object-none !rounded-md border dark:border-defaultborder/10"
                                            autoplay loop muted></video>
                                    </div>
                                    <div class="box-footer hidden !border-t-0">
                                        <!-- Prism Code -->
                                        <pre
                                            class="language-html">
<code class="language-html">
&lt;video src="{{asset('build/assets/video/1.mp4')}}"
class="object-none !rounded-md border dark:border-defaultborder/10"
autoplay loop muted&gt;&lt;/video&gt;
</code></pre>
                                        <!-- Prism Code -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End::row-1 -->

@endsection

@section('scripts')

        <!-- Prism JS -->
        <script src="{{asset('build/assets/libs/prismjs/prism.js')}}"></script>
        @vite('resources/assets/js/prism-custom.js')


@endsection