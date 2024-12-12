@extends('layouts.master')

@section('styles')

        <!-- Prism CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/prismjs/themes/prism-coy.min.css')}}">
      
@endsection

@section('content')
 
                    <!-- Page Header -->
                    <div class="block justify-between page-header md:flex">
                        <div>
                            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Images &amp; Figures</h3>
                        </div>
                        <ol class="flex items-center whitespace-nowrap min-w-0">
                            <li class="text-[0.813rem] ps-[0.5rem]">
                              <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                               Ui Elements
                                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                              </a>
                            </li>
                            <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                Images &amp; Figures
                            </li>
                        </ol>
                    </div>
                    <!-- Page Header Close -->

                    <!-- Start:: row-1 -->
                    <div class="grid grid-cols-12 gap-6">
                        <div class="xl:col-span-4 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">
                                        Responsive image
                                    </div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !text-[0.75rem] !font-medium">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <p class="box-title mb-4 !text-[0.813rem] !font-normal">Use <code> .img-fluid </code>class to the img tag to get responsive image.</p>
                                    <div class="text-center">
                                        <img src="{{asset('build/assets/images/media/media-48.jpg')}}" class="img-fluid !inline-flex" alt="...">
                                    </div>
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;div class="text-center"&gt;
    &lt;img src="{{asset('build/assets/images/media/media-48.jpg')}}" class="img-fluid !inline-flex" alt="..."&gt;
&lt;/div&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-4 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">
                                        Image With Radius
                                    </div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !text-[0.75rem] !font-medium">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <p class="box-title mb-4 !text-[0.813rem] !font-normal">Use <code>.rounded-md</code> class along with <code>.img-fluid</code> to get border radius.</p>
                                    <div class="text-center">
                                        <img src="{{asset('build/assets/images/media/media-49.jpg')}}" class="img-fluid !rounded-md !inline-flex" alt="...">
                                    </div>
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;div class="text-center"&gt;
    &lt;img src="{{asset('build/assets/images/media/media-49.jpg')}}" class="img-fluid !rounded-md !inline-flex" alt="..."&gt;
&lt;/div&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-4 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">
                                        Rounded Image
                                    </div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !text-[0.75rem] !font-medium">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <p class="box-title mb-4 !text-[0.813rem] !font-normal">Use <code>.rounded-full</code> class to <code>img</code> tag to get rounded image.</p>
                                    <div class="text-center">
                                        <img src="{{asset('build/assets/images/media/media-50.jpg')}}" class="img-fluid !rounded-full !inline-flex" alt="...">
                                    </div>
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;div class="text-center"&gt;
    &lt;img src="{{asset('build/assets/images/media/media-50.jpg')}}" class="img-fluid !rounded-full !inline-flex" alt="..."&gt;
&lt;/div&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                           </div>
                        </div>
                    </div>
                    <!-- End:: row-1 -->

                    <!-- Start:: row-2 -->
                    <div class="grid grid-cols-12 gap-6">
                        <div class="xl:col-span-4 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">Image Left Align</div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !text-[0.75rem] !font-medium">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <img class="!rounded-md float-start" src="{{asset('build/assets/images/media/media-53.jpg')}}" alt="...">
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;img class="!rounded-md float-start" src="{{asset('build/assets/images/media/media-53.jpg')}}" alt="..."&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-4 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">Image Center Align</div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !text-[0.75rem] !font-medium">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <img class="!rounded-md mx-auto block" src="{{asset('build/assets/images/media/media-55.jpg')}}" alt="...">
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;img class="!rounded-md mx-auto block" src="{{asset('build/assets/images/media/media-55.jpg')}}" alt="..."&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-4 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">Image Right Align</div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !text-[0.75rem] !font-medium">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <img class="!rounded-md ltr:float-right rtl:float-left" src="{{asset('build/assets/images/media/media-54.jpg')}}" alt="...">
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;img class="!rounded-md ltr:float-right rtl:float-left" src="{{asset('build/assets/images/media/media-54.jpg')}}" alt="..."&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End:: row-2 -->

                    <!-- Start:: row-2 -->
                    <div class="grid grid-cols-12 gap-6">
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">Figures</div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !text-[0.75rem] !font-medium">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="flex justify-between gap-2">
                                        <figure class="figure inline-block mb-4">
                                            <img class="m-[0.125rem] leading-none img-fluid rounded-md w-full" src="{{asset('build/assets/images/media/media-56.jpg')}}" alt="...">
                                            <figcaption class="text-[0.875em] text-textmuted dark:text-white/70 mt-2">A caption for the above image.
                                            </figcaption>
                                        </figure>
                                        <figure class="figure ltr:float-right rtl:float-left">
                                            <img class="m-[0.125rem] leading-none img-fluid rounded-md w-full" src="{{asset('build/assets/images/media/media-57.jpg')}}" alt="...">
                                            <figcaption class="text-[0.875em] text-textmuted dark:text-white/70 text-end mt-2">A caption for the above image.
                                            </figcaption>
                                        </figure>
                                    </div>
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">
    &lt;div class="flex justify-between gap-2"&gt;
        &lt;figure class="figure inline-block mb-4"&gt;
            &lt;img class="m-[0.125rem] leading-none img-fluid rounded-md w-full" src="{{asset('build/assets/images/media/media-56.jpg')}}" alt="..."&gt;
            &lt;figcaption class="text-[0.875em] text-textmuted dark:text-white/70 mt-2"&gt;A caption for the above image.
            &lt;/figcaption&gt;
        &lt;/figure&gt;
        &lt;figure class="figure ltr:float-right rtl:float-left"&gt;
            &lt;img class="m-[0.125rem] leading-none img-fluid rounded-md w-full" src="{{asset('build/assets/images/media/media-57.jpg')}}" alt="..."&gt;
            &lt;figcaption class="text-[0.875em] text-textmuted dark:text-white/70 text-end mt-2"&gt;A caption for the above image.
            &lt;/figcaption&gt;
        &lt;/figure&gt;
    &lt;/div&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-3 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">Image Thumbnail</div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !text-[0.75rem] !font-medium">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <p class="mb-3">Use <code>ti-img-thumbnail</code> to give an image a rounded 1px border.</p>
                                    <div class="text-center">
                                        <img src="{{asset('build/assets/images/media/media-51.jpg')}}" class="ti-img-thumbnail" alt="...">
                                    </div>
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;div class="text-center"&gt;
    &lt;img src="{{asset('build/assets/images/media/media-51.jpg')}}" class="ti-img-thumbnail" alt="..."&gt;
&lt;/div&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-3 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">Image Thumbnail</div>
                                    <div class="prism-toggle">
                                        <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-primary !text-[0.75rem] !font-medium">Show Code<i class="ri-code-line ms-2 inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <p class="mb-3">Use <code>ti-img-thumbnail-rounded</code> to give an image a rounded 1px border.</p>
                                    <div class="text-center">
                                        <img src="{{asset('build/assets/images/media/media-52.jpg')}}" class="ti-img-thumbnail-rounded" alt="...">
                                    </div>
                                </div>
                                <div class="box-footer hidden !border-t-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;div class="text-center"&gt;
    &lt;img src="{{asset('build/assets/images/media/media-52.jpg')}}" class="ti-img-thumbnail-rounded" alt="..."&gt;
&lt;/div&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End:: row-2 -->

@endsection

@section('scripts')

        <!-- Prism JS -->
        <script src="{{asset('build/assets/libs/prismjs/prism.js')}}"></script>
        @vite('resources/assets/js/prism-custom.js')
        

@endsection