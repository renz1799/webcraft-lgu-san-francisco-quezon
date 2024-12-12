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
                                    <h1 class="font-bold mb-4 text-[2.5rem]">Coming Soon</h1>
                                    <p class="mb-6">Our website is currently under construction, enter your email id to get latest updates and notifications about the website.</p>
                                    <div class="input-group mb-[3em]">
                                        <input type="email" class="form-control w-full !border-s !rounded-s-md form-control-lg " placeholder="info@gmail.com" aria-label="info@gmail.com" aria-describedby="button-addon2">
                                        <button class="ti-btn btn-wave bg-primary text-white !rounded-s-none !mb-0" type="button" id="button-addon2">Subscribe</button>
                                    </div>
                                    <div class="grid grid-cols-12 mt-6 mb-[3rem] xxl:gap-y-0 gap-4 justify-center" id="timer">
                                    </div>
                                    <div class="mt-[3rem]">
                                        <div class="btn-list">
                                            <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-icon bg-primary text-white font-medium me-[0.365rem]">
                                                <i class="ri-facebook-line font-bold"></i>
                                            </button>
                                            <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-icon bg-secondary text-white font-medium me-[0.365rem]">
                                                <i class="ri-twitter-x-line font-bold"></i>
                                            </button>
                                            <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-icon bg-warning text-white font-medium me-[0.365rem]">
                                                <i class="ri-instagram-line font-bold"></i>
                                            </button>
                                            <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-icon bg-success text-white font-medium me-[0.365rem]">
                                                <i class="ri-github-line font-bold"></i>
                                            </button>
                                            <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-icon bg-danger text-white font-medium">
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
                    <img src="{{asset('build/assets/images/media/media-87.svg')}}" alt="" class="imig-fluid">
                </div>
            </div>
        </div>

@endsection

@section('scripts')

        <!-- Internal Coming Soon JS -->
        <script src="{{asset('build/assets/coming-soon.js')}}"></script>

@endsection