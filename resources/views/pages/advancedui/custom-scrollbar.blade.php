@extends('layouts.master')

@section('styles')
 
        <!-- Prism CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/prismjs/themes/prism-coy.min.css')}}">

@endsection

@section('content')
 
                    <!-- Page Header -->
                    <div class="block justify-between page-header md:flex">
                        <div>
                            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Custom scrollbar</h3>
                        </div>
                        <ol class="flex items-center whitespace-nowrap min-w-0">
                            <li class="text-[0.813rem] ps-[0.5rem]">
                              <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                               Advanced Ui
                                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                              </a>
                            </li>
                            <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                Custom scrollbar
                            </li>
                        </ol>
                    </div>
                    <!-- Page Header Close -->

                    <!-- Start::row-1 -->
                    <div class="grid grid-cols-12 gap-6">
                        <div class="col-span-12 lg:col-span-6">
                          <div class="box">
                              <div class="box-header">
                                  <h5 class="box-title">Basic Custom Scrollbar</h5>
                              </div>
                            <div class="box-body">
                              <div class="max-h-[400px] space-y-4 pe-8 overflow-y-auto [&amp;::-webkit-scrollbar]:w-2 [&amp;::-webkit-scrollbar-track]:bg-gray-100 [&amp;::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&amp;::-webkit-scrollbar-track]:bg-slate-700 dark:[&amp;::-webkit-scrollbar-thumb]:bg-slate-500">
                                  <div>
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Item 1</h3>
                                    <p class="mt-1 text-sm leading-6 text-gray-700 dark:text-white/70">This is some placeholder content for the scrollspy page. Note that as you scroll down the page, the appropriate navigation link is highlighted. It's repeated throughout the component example. We keep adding some more example copy here to emphasize the scrolling and highlighting.</p>
                                  </div>
              
                                  <div>
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Item 1-1</h3>
                                    <p class="mt-1 text-sm leading-6 text-gray-700 dark:text-white/70">This is some placeholder content for the scrollspy page. Note that as you scroll down the page, the appropriate navigation link is highlighted. It's repeated throughout the component example. We keep adding some more example copy here to emphasize the scrolling and highlighting.</p>
                                  </div>
              
                                  <div>
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Item 1-2</h3>
                                    <p class="mt-1 text-sm leading-6 text-gray-700 dark:text-white/70">This is some placeholder content for the scrollspy page. Note that as you scroll down the page, the appropriate navigation link is highlighted. It's repeated throughout the component example. We keep adding some more example copy here to emphasize the scrolling and highlighting.</p>
                                  </div>
              
                                  <div>
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Item 2</h3>
                                    <p class="mt-1 text-sm leading-6 text-gray-700 dark:text-white/70">This is some placeholder content for the scrollspy page. Note that as you scroll down the page, the appropriate navigation link is highlighted. It's repeated throughout the component example. We keep adding some more example copy here to emphasize the scrolling and highlighting.</p>
                                  </div>
              
                                  <div>
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Item 3</h3>
                                    <p class="mt-1 text-sm leading-6 text-gray-700 dark:text-white/70">This is some placeholder content for the scrollspy page. Note that as you scroll down the page, the appropriate navigation link is highlighted. It's repeated throughout the component example. We keep adding some more example copy here to emphasize the scrolling and highlighting.</p>
                                  </div>
              
                                  <div>
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Item 3-1</h3>
                                    <p class="mt-1 text-sm leading-6 text-gray-700 dark:text-white/70">This is some placeholder content for the scrollspy page. Note that as you scroll down the page, the appropriate navigation link is highlighted. It's repeated throughout the component example. We keep adding some more example copy here to emphasize the scrolling and highlighting.</p>
                                  </div>
              
                                  <div>
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Item 3-2</h3>
                                    <p class="mt-1 text-sm leading-6 text-gray-700 dark:text-white/70">This is some placeholder content for the scrollspy page. Note that as you scroll down the page, the appropriate navigation link is highlighted. It's repeated throughout the component example. We keep adding some more example copy here to emphasize the scrolling and highlighting.</p>
                                  </div>
                                </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-span-12 lg:col-span-6">
                          <div class="box">
                              <div class="box-header">
                                  <h5 class="box-title">Rounded Custom Scrollbar</h5>
                              </div>
                            <div class="box-body">
                              <div class="max-h-[400px] space-y-4 pe-8 overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-track]:rounded-full [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-slate-700 dark:[&::-webkit-scrollbar-thumb]:bg-slate-500">
                                  <div>
                                  <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Item 1</h3>
                                  <p class="mt-1 text-sm leading-6 text-gray-700 dark:text-white/70">This is some placeholder content for the scrollspy page. Note that as you scroll down the page, the appropriate navigation link is highlighted. It's repeated throughout the component example. We keep adding some more example copy here to emphasize the scrolling and highlighting.</p>
                                  </div>
              
                                  <div>
                                  <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Item 1-1</h3>
                                  <p class="mt-1 text-sm leading-6 text-gray-700 dark:text-white/70">This is some placeholder content for the scrollspy page. Note that as you scroll down the page, the appropriate navigation link is highlighted. It's repeated throughout the component example. We keep adding some more example copy here to emphasize the scrolling and highlighting.</p>
                                  </div>
              
                                  <div>
                                  <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Item 1-2</h3>
                                  <p class="mt-1 text-sm leading-6 text-gray-700 dark:text-white/70">This is some placeholder content for the scrollspy page. Note that as you scroll down the page, the appropriate navigation link is highlighted. It's repeated throughout the component example. We keep adding some more example copy here to emphasize the scrolling and highlighting.</p>
                                  </div>
              
                                  <div>
                                  <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Item 2</h3>
                                  <p class="mt-1 text-sm leading-6 text-gray-700 dark:text-white/70">This is some placeholder content for the scrollspy page. Note that as you scroll down the page, the appropriate navigation link is highlighted. It's repeated throughout the component example. We keep adding some more example copy here to emphasize the scrolling and highlighting.</p>
                                  </div>
              
                                  <div>
                                  <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Item 3</h3>
                                  <p class="mt-1 text-sm leading-6 text-gray-700 dark:text-white/70">This is some placeholder content for the scrollspy page. Note that as you scroll down the page, the appropriate navigation link is highlighted. It's repeated throughout the component example. We keep adding some more example copy here to emphasize the scrolling and highlighting.</p>
                                  </div>
              
                                  <div>
                                  <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Item 3-1</h3>
                                  <p class="mt-1 text-sm leading-6 text-gray-700 dark:text-white/70">This is some placeholder content for the scrollspy page. Note that as you scroll down the page, the appropriate navigation link is highlighted. It's repeated throughout the component example. We keep adding some more example copy here to emphasize the scrolling and highlighting.</p>
                                  </div>
              
                                  <div>
                                  <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Item 3-2</h3>
                                  <p class="mt-1 text-sm leading-6 text-gray-700 dark:text-white/70">This is some placeholder content for the scrollspy page. Note that as you scroll down the page, the appropriate navigation link is highlighted. It's repeated throughout the component example. We keep adding some more example copy here to emphasize the scrolling and highlighting.</p>
                                  </div>
                              </div>
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