@extends('layouts.custom-master')

@section('styles')
 
      
@endsection

@section('content')

@section('error-body')
<body>
@endsection

<div class="container">
        <div class="flex justify-center authentication authentication-basic items-center h-full text-defaultsize text-defaulttextcolor">
            <div class="grid grid-cols-12">
                <div class="xxl:col-span-4 xl:col-span-4 lg:col-span-4 md:col-span-3 sm:col-span-2"></div>
                <div class="xxl:col-span-4 xl:col-span-4 lg:col-span-4 md:col-span-6 sm:col-span-8 col-span-12">
                    <div class="my-[2.5rem] flex justify-center">
                        <a href="{{url('index')}}">
                            <img src="{{asset('build/assets/images/brand-logos/desktop-logo.png')}}" alt="logo" class="desktop-logo">
                            <img src="{{asset('build/assets/images/brand-logos/desktop-dark.png')}}" alt="logo" class="desktop-dark">
                        </a>
                    </div>
                    <div class="box">
                        <div class="box-body !p-[3rem]">
                            <p class="h5 font-semibold mb-2 text-center">Sign Up</p>
                            <p class="mb-4 text-[#8c9097] dark:text-white/50 opacity-[0.7] font-normal text-center">Welcome &amp; Join us by
                                creating a free account !</p>
                            <div class="grid grid-cols-12 gap-y-4">
                                <div class="xl:col-span-12 col-span-12">
                                    <label for="signup-firstname" class="form-label text-default">First Name</label>
                                    <input type="text" class="form-control form-control-lg w-full !rounded-md"
                                        id="signup-firstname" placeholder="first name">
                                </div>
                                <div class="xl:col-span-12 col-span-12">
                                    <label for="signup-lastname" class="form-label text-default">Last Name</label>
                                    <input type="text" class="form-control form-control-lg w-full !rounded-md"
                                        id="signup-lastname" placeholder="last name">
                                </div>
                                <div class="xl:col-span-12 col-span-12">
                                    <label for="signup-password" class="form-label text-default">Password</label>
                                    <div class="input-group">
                                        <input type="password"
                                            class="form-control !border-s border-defaultborder dark:border-defaultborder/10 form-control-lg !rounded-e-none"
                                            id="signup-password" placeholder="password">
                                        <button aria-label="button" type="button" class="ti-btn ti-btn-light !rounded-s-none !mb-0"
                                            onclick="createpassword('signup-password',this)"
                                            id="button-addon2"><i class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="xl:col-span-12 col-span-12 mb-2">
                                    <label for="signup-confirmpassword" class="form-label text-default">Confirm
                                        Password</label>
                                    <div class="input-group">
                                        <input type="password"
                                            class="form-control !border-s border-defaultborder dark:border-defaultborder/10 form-control-lg !rounded-e-none"
                                            id="signup-confirmpassword" placeholder="confirm password">
                                        <button aria-label="button" type="button" class="ti-btn ti-btn-light !rounded-s-none !mb-0"
                                            onclick="createpassword('signup-confirmpassword',this)"
                                            id="button-addon21"><i class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                    <div class="mt-4">
                                        <div class="form-check !flex !ps-0">
                                            <input class="form-check-input me-1" type="checkbox" value="" id="defaultCheck1">
                                            <label class="ps-2 form-check-label text-[#8c9097] dark:text-white/50 font-normal block" for="defaultCheck1">
                                                By creating a account you agree to our <a href="{{url('terms')}}"
                                                class="text-success"><u>Terms &amp; Conditions</u></a> and <a
                                                class="text-success"><u>Privacy Policy</u></a>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="xl:col-span-12 col-span-12 grid mt-2">
                                    <button type="button" class="ti-btn ti-btn-lg bg-primary  btn-wave text-white !font-medium dark:border-defaultborder/10">Create
                                        Account</button>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mt-4">Already have an account? <a
                                        href="{{url('signin-basic')}}" class="text-primary">Sign In</a></p>
                            </div>
                            <div class="text-center my-4 authentication-barrier">
                                <span>OR</span>
                            </div>
                            <div class="btn-list text-center">
                                <button aria-label="button" type="button" class="ti-btn ti-btn-icon ti-btn-light me-[0.365rem]">
                                    <i class="ri-facebook-line font-bold text-dark opacity-[0.7]"></i>
                                </button>
                                <button aria-label="button" type="button" class="ti-btn ti-btn-icon ti-btn-light me-[0.365rem]">
                                    <i class="ri-google-line font-bold text-dark opacity-[0.7]"></i>
                                </button>
                                <button aria-label="button" type="button" class="ti-btn ti-btn-icon ti-btn-light">
                                    <i class="ri-twitter-x-line font-bold text-dark opacity-[0.7]"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="xxl:col-span-4 xl:col-span-4 lg:col-span-4 md:col-span-3 sm:col-span-2"></div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')

        <!-- Show Password JS -->
        <script src="{{asset('build/assets/show-password.js')}}"></script>

@endsection