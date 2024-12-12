@extends('layouts.custom-master')

@section('styles')
 
      
@endsection

@section('content')

@section('error-body')
<body class="bg-white dark:!bg-bodybg">
@endsection

          <div class="grid grid-cols-12 gap-0 w-full authentication under-maintenance mx-0 text-defaulttextcolor text-defaultsize">
              <div class="lg:col-span-7 col-span-12">
                  <div class="authentication-page md:h-full sm:py-16 w-full flex items-center justify-center">
                      <!-- ========== MAIN CONTENT ========== -->
                      <main id="content"  class="w-full lg:max-w-[37rem] p-6 ">
                          <div class="mt-7">
                              <div class="p-0 md:p-7">

                                  <div class="text-center">
                                      <div class="mb-2 flex justify-center">
                                          <a aria-label="anchor" href="{{url('index')}}">
                                              <img src="{{asset('build/assets/images/brand-logos/toggle-logo.png')}}" alt="" class="authentication-brand">
                                          </a>
                                      </div>
                                      <p class="font-semibold text-[0.75rem] mb-1 opacity-[0.4]">STAY TUNED</p>
                                      <h1 class="font-bold mb-4 text-[2.5rem] dark:text-defaulttextcolor/70">Under Maintenance</h1>
                                      <p class="mb-4">Our website is under maintenance, wait for some time.</p>
                                      <div class="grid grid-cols-12 mt-6 xxl:gap-y-0 gap-4 mb-[3rem]" id="timer">
                                      </div>
                                      <div class="mt-[3rem]">
                                          <div class="btn-list">
                                              <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-icon bg-primary font-bold me-[0.365rem] text-white">
                                                  <i class="ri-facebook-line font-bold"></i>
                                              </button>
                                              <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-icon bg-secondary font-bold me-[0.365rem] text-white">
                                                  <i class="ri-twitter-x-line font-bold"></i>
                                              </button>
                                              <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-icon bg-warning font-bold me-[0.365rem] text-white">
                                                  <i class="ri-instagram-line font-bold"></i>
                                              </button>
                                              <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-icon bg-success font-bold me-[0.365rem] text-white">
                                                  <i class="ri-github-line font-bold"></i>
                                              </button>
                                              <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-icon bg-danger font-bold text-white">
                                                  <i class="ri-youtube-line font-bold"></i>
                                              </button>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </main>
                      <!-- ========== END MAIN CONTENT ========== -->
                  </div>
              </div>
              <div class="lg:col-span-5 col-span-12 hidden lg:block relative px-0">
                  <div class="bg-light w-full h-full flex items-center justify-center under-maintenance-image-container">
                      <img src="{{asset('build/assets/images/media/media-88.svg')}}" alt="" class="imig-fluid">
                  </div>
              </div>

          </div>

@endsection

@section('scripts')

        <!-- Internal Under Maintenance JS -->
        <script src="{{asset('build/assets/under-maintenance.js')}}"></script>

@endsection