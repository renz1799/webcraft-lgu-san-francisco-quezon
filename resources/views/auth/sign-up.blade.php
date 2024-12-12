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
                    <a href="{{ url('index') }}">
                        <img src="{{ asset('build/assets/images/brand-logos/desktop-logo.png') }}" alt="logo" class="desktop-logo">
                        <img src="{{ asset('build/assets/images/brand-logos/desktop-dark.png') }}" alt="logo" class="desktop-dark">
                    </a>
                </div>
                <div class="box">
                    <div class="box-body !p-[3rem]">
                        <p class="h5 font-semibold mb-2 text-center">Sign Up</p>
                        <p class="mb-4 text-[#8c9097] dark:text-white/50 opacity-[0.7] font-normal text-center">Welcome &amp; Join us by creating a free account!</p>

                        {{-- Display errors or success messages --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        {{-- Start of the form --}}
                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="grid grid-cols-12 gap-y-4">
                                <div class="xl:col-span-12 col-span-12">
                                    <label for="signup-username" class="form-label text-default">Username</label>
                                    <input type="text" name="username" class="form-control form-control-lg w-full !rounded-md" id="signup-username" placeholder="Username" value="{{ old('username') }}">
                                    @error('username')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="xl:col-span-12 col-span-12">
                                    <label for="signup-email" class="form-label text-default">Email</label>
                                    <input type="email" name="email" class="form-control form-control-lg w-full !rounded-md" id="signup-email" placeholder="Email" value="{{ old('email') }}">
                                    @error('email')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="xl:col-span-12 col-span-12">
                                    <label for="signup-usertype" class="form-label text-default">User Type</label>
                                    <select name="user_type" class="form-control form-control-lg w-full !rounded-md" id="signup-usertype">
                                        <option value="">Select User Type</option>
                                        <option value="Administrator" {{ old('user_type') == 'Administrator' ? 'selected' : '' }}>Administrator</option>
                                        <option value="Staff" {{ old('user_type') == 'Staff' ? 'selected' : '' }}>Staff</option>
                                        <option value="Guest" {{ old('user_type') == 'Guest' ? 'selected' : '' }}>Guest</option>
                                    </select>
                                    @error('user_type')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="xl:col-span-12 col-span-12">
                                    <label for="signup-password" class="form-label text-default">Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password" class="form-control form-control-lg w-full !rounded-md" id="signup-password" placeholder="Password">
                                    </div>
                                    @error('password')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="xl:col-span-12 col-span-12 mb-2">
                                    <label for="signup-confirmpassword" class="form-label text-default">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" class="form-control form-control-lg w-full !rounded-md" id="signup-confirmpassword" placeholder="Confirm password">
                                    </div>
                                </div>

                                <div class="xl:col-span-12 col-span-12 grid mt-2">
                                    <button type="submit" class="ti-btn ti-btn-lg bg-primary btn-wave text-white !font-medium dark:border-defaultborder/10">Create Account</button>
                                </div>
                            </div>
                        </form>
                        {{-- End of the form --}}

                        <div class="text-center">
                            <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mt-4">Already have an account? <a href="{{ url('signin-basic') }}" class="text-primary">Sign In</a></p>
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
    <script src="{{ asset('build/assets/show-password.js') }}"></script>
@endsection
