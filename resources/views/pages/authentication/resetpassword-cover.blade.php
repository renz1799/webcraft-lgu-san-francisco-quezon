@extends('layouts.custom-master')

@section('styles')
 
        <!-- SWIPER CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/swiper/swiper-bundle.min.css')}}">
      
@endsection

@section('content')

@section('error-body')
<body class="bg-white dark:!bg-bodybg">
@endsection

        <div class="grid grid-cols-12 authentication mx-0">

            <div class="xxl:col-span-7 xl:col-span-7 lg:col-span-12 col-span-12">
                <div class="grid grid-cols-12 items-center h-full ">
                    <div class="xxl:col-span-3 xl:col-span-3 lg:col-span-3 md:col-span-3 sm:col-span-2"></div>
                    <div class="xxl:col-span-6 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-8 col-span-12">
                        <div class="p-[3rem]">
                            <div class="mb-4">
                            <a aria-label="anchor" href="{{url('index')}}">
                                <img src="{{asset('build/assets/images/brand-logos/desktop-logo.png')}}" alt=""
                                class="authentication-brand desktop-logo">
                                <img src="{{asset('build/assets/images/brand-logos/desktop-dark.png')}}" alt=""
                                class="authentication-brand desktop-dark">
                            </a>
                            </div>
                            <p class="h5 font-semibold mb-2">Reset Password</p>
                            <p class="mb-4 text-[#8c9097] dark:text-white/50 opacity-[0.7] font-normal">Hello Jhon !</p>
                            <div class="btn-list">
                            <button type="button" class="ti-btn ti-btn-lg ti-btn-light !font-medium me-[0.365rem] dark:border-defaultborder/10"><svg
                                class="google-svg" xmlns="http://www.w3.org/2000/svg" width="2443" height="2500"
                                preserveAspectRatio="xMidYMid" viewBox="0 0 256 262">
                                <path fill="#4285F4"
                                    d="M255.878 133.451c0-10.734-.871-18.567-2.756-26.69H130.55v48.448h71.947c-1.45 12.04-9.283 30.172-26.69 42.356l-.244 1.622 38.755 30.023 2.685.268c24.659-22.774 38.875-56.282 38.875-96.027" />
                                <path fill="#34A853"
                                    d="M130.55 261.1c35.248 0 64.839-11.605 86.453-31.622l-41.196-31.913c-11.024 7.688-25.82 13.055-45.257 13.055-34.523 0-63.824-22.773-74.269-54.25l-1.531.13-40.298 31.187-.527 1.465C35.393 231.798 79.49 261.1 130.55 261.1" />
                                <path fill="#FBBC05"
                                    d="M56.281 156.37c-2.756-8.123-4.351-16.827-4.351-25.82 0-8.994 1.595-17.697 4.206-25.82l-.073-1.73L15.26 71.312l-1.335.635C5.077 89.644 0 109.517 0 130.55s5.077 40.905 13.925 58.602l42.356-32.782" />
                                <path fill="#EB4335"
                                    d="M130.55 50.479c24.514 0 41.05 10.589 50.479 19.438l36.844-35.974C195.245 12.91 165.798 0 130.55 0 79.49 0 35.393 29.301 13.925 71.947l42.211 32.783c10.59-31.477 39.891-54.251 74.414-54.251" />
                                </svg>Reset Password with google</button>
                            <button aria-label="button" type="button" class="ti-btn ti-btn-icon   btn-wave ti-btn-primary me-[0.365rem]"><i class="ri-facebook-fill"></i></button>
                            <button aria-label="button" type="button" class="ti-btn ti-btn-icon  btn-wave ti-btn-info"><i class="ri-twitter-x-fill"></i></button>
                            </div>
                            <div class="text-center my-[3rem] authentication-barrier">
                            <span>OR</span>
                            </div>
                            <div class="grid grid-cols-12">
                                <div class="xl:col-span-12 col-span-12 mb-4">
                                    <label for="reset-password" class="form-label text-default">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg !border-s border-defaultborder dark:border-defaultborder/10 !rounded-e-none" id="reset-password" placeholder="current password">
                                        <button aria-label="button" class="ti-btn ti-btn-light !rounded-s-none !mb-0" onclick="createpassword('reset-password',this)" type="button" id="button-addon2"><i class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="xl:col-span-12 col-span-12 mb-4">
                                    <label for="reset-newpassword" class="form-label text-default">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg !border-s border-defaultborder dark:border-defaultborder/10 !rounded-e-none" id="reset-newpassword" placeholder="new password">
                                        <button aria-label="button" class="ti-btn ti-btn-light !mb-0 !rounded-s-none" onclick="createpassword('reset-newpassword',this)" type="button" id="button-addon21"><i class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="xl:col-span-12 col-span-12 mb-4">
                                    <label for="reset-confirmpassword" class="form-label text-default">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg !border-s border-defaultborder dark:border-defaultborder/10 !rounded-e-none" id="reset-confirmpassword" placeholder="confirm password">
                                        <button aria-label="button" class="ti-btn ti-btn-light !mb-0 !rounded-s-none" onclick="createpassword('reset-confirmpassword',this)" type="button" id="button-addon22"><i class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                    <div class=" mt-2">
                                        <div class="form-check !ps-0">
                                            <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                            <label class="form-check-label text-[#8c9097] dark:text-white/50 font-normal" for="defaultCheck1">
                                                Remember password ?
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="xl:col-span-12 col-span-12 grid">
                                    <a href="{{url('signin-cover')}}" class="ti-btn ti-btn-lg bg-primary text-white  btn-wave !font-medium dark:border-defaultborder/10">Create</a>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mt-4">Already have an account? <a href="{{url('signin-cover')}}" class="text-primary">Sign In</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="xxl:col-span-3 xl:col-span-3 lg:col-span-3 md:col-span-3 sm:col-span-2"></div>
                </div>
            </div>
            <div class="xxl:col-span-5 xl:col-span-5 lg:col-span-5 col-span-12 xl:block hidden px-0">
                <div class="authentication-cover">
                    <div class="aunthentication-cover-content rounded">
                        <div class="swiper keyboard-control">
                            <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="text-white text-center p-[3rem] flex items-center justify-center">
                                <div>
                                    <div class="mb-[3rem]">
                                    <img src="{{asset('build/assets/images/authentication/2.png')}}" class="authentication-image" alt="">
                                    </div>
                                    <h6 class="font-semibold text-[1rem]">Reset Password</h6>
                                    <p class="font-normal text-[0.875rem] opacity-[0.7]"> Lorem ipsum dolor sit amet, consectetur
                                    adipisicing elit. Ipsa eligendi expedita aliquam quaerat nulla voluptas facilis. Porro rem
                                    voluptates possimus, ad, autem quae culpa architecto, quam labore blanditiis at ratione.</p>
                                </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="text-white text-center p-[3rem] flex items-center justify-center">
                                <div>
                                    <div class="mb-[3rem]">
                                    <img src="{{asset('build/assets/images/authentication/3.png')}}" class="authentication-image" alt="">
                                    </div>
                                    <h6 class="font-semibold text-[1rem]">Reset Password</h6>
                                    <p class="font-normal text-[0.875rem] opacity-[0.7]"> Lorem ipsum dolor sit amet, consectetur
                                    adipisicing elit. Ipsa eligendi expedita aliquam quaerat nulla voluptas facilis. Porro rem
                                    voluptates possimus, ad, autem quae culpa architecto, quam labore blanditiis at ratione.</p>
                                </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="text-white text-center p-[3rem] flex items-center justify-center">
                                <div>
                                    <div class="mb-[3rem]">
                                    <img src="{{asset('build/assets/images/authentication/2.png')}}" class="authentication-image" alt="">
                                    </div>
                                    <h6 class="font-semibold text-[1rem]">Reset Password</h6>
                                    <p class="font-normal text-[0.875rem] opacity-[0.7]"> Lorem ipsum dolor sit amet, consectetur
                                    adipisicing elit. Ipsa eligendi expedita aliquam quaerat nulla voluptas facilis. Porro rem
                                    voluptates possimus, ad, autem quae culpa architecto, quam labore blanditiis at ratione.</p>
                                </div>
                                </div>
                            </div>
                            </div>
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-pagination"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

@endsection

@section('scripts')

        <!-- Swiper JS -->
        <script src="{{asset('build/assets/libs/swiper/swiper-bundle.min.js')}}"></script>

        <!-- Internal Sing-Up JS -->
        @vite('resources/assets/js/authentication.js')


        <!-- Show Password JS -->
        <script src="{{asset('build/assets/show-password.js')}}"></script>

@endsection