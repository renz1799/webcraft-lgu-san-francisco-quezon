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
                                <p class="h5 font-semibold mb-2">Lockscreen</p>
                                <p class="mb-4 text-[#8c9097] dark:text-white/50 opacity-[0.7] font-normal">Hello Jhon !</p>
                                <div class="flex items-center mb-6">
                                    <div class="leading-none">
                                        <span class="avatar avatar-md avatar-rounded">
                                            <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="">
                                        </span>
                                    </div>
                                    <div class="ms-4">
                                        <p class="mb-0 font-semibold text-dark">jhonslicer21@gmail.com</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12">
                                    <div class="xl:col-span-12 col-span-12">
                                        <label for="lockscreen-password" class="form-label text-default">Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control !border-s border-defaultborder dark:border-defaultborder/10 form-control-lg !rounded-e-none" id="lockscreen-password" placeholder="password">
                                            <button class="ti-btn ti-btn-light !mb-0 !rounded-s-none" aria-label="button" type="button" onclick="createpassword('lockscreen-password',this)" id="button-addon2"><i class="ri-eye-off-line align-middle"></i></button>
                                        </div>
                                        <div class="mt-2">
                                            <div class="form-check !ps-0">
                                                <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                                <label class="form-check-label text-[#8c9097] dark:text-white/50 font-normal" for="defaultCheck1">
                                                    Remember password ?
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="xl:col-span-12 col-span-12 grid mt-4">
                                        <a href="{{url('index')}}" class="ti-btn ti-btn-lg bg-primary text-white  btn-wave !font-medium dark:border-defaultborder/10">Unlock</a>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mt-4">Try unlock with different methods <a class="text-success" href="javascript:void(0);"><u>Finger print</u></a> / <a class="text-success" href="javascript:void(0);"><u>Face Id</u></a></p>
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
                                        <h6 class="font-semibold text-[1rem]">Lockscreen</h6>
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
                                        <h6 class="font-semibold text-[1rem]">Lockscreen</h6>
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
                                        <h6 class="font-semibold text-[1rem]">Lockscreen</h6>
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