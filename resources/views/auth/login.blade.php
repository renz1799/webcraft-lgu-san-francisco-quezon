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
                        <p class="h5 font-semibold mb-2 text-center">Sign In</p>
                        <p class="mb-4 text-[#8c9097] dark:text-white/50 opacity-[0.7] font-normal text-center">Welcome back!</p>

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

                        {{-- Start of login form --}}
                        <form method="POST" action="{{ route('login') }}" onsubmit="return captureLocation(event);">
    @csrf
    <div class="grid grid-cols-12">
        <div class="xl:col-span-12 col-span-12 mb-3">
            <label for="signin-username" class="form-label text-default">Email</label>
            <input type="email" name="email" class="form-control form-control-lg w-full !rounded-md" id="signin-username" placeholder="Enter your email" value="{{ old('email') }}">
            @error('email')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div class="xl:col-span-12 col-span-12 mb-3">
            <label for="signin-password" class="form-label text-default block">Password
            </label>
            <div class="input-group">
                <input type="password" name="password" class="form-control !border-s border-defaultborder dark:border-defaultborder/10 form-control-lg !rounded-s-md" id="signin-password" placeholder="Enter your password">
                <button aria-label="button" class="ti-btn ti-btn-light !rounded-s-none !mb-0" type="button" onclick="createpassword('signin-password',this)" id="button-addon2"><i class="ri-eye-off-line align-middle"></i></button>
            </div>
            @error('password')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
            <div class="mt-2">
                <div class="form-check !ps-0">
                    <input class="form-check-input" type="checkbox" name="remember" id="defaultCheck1">
                    <label class="form-check-label text-[#8c9097] dark:text-white/50 font-normal" for="defaultCheck1">
                        Remember Me
                    </label>
                </div>
            </div>
        </div>
        <!-- Hidden Fields for Latitude and Longitude -->
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">
        <div class="xl:col-span-12 col-span-12 grid mt-2">
            <button type="submit" class="ti-btn ti-btn-primary !bg-primary btn-wave !text-white !font-medium">Sign In</button>
        </div>
    </div>
</form>

                        {{-- End of login form --}}

                        <div class="text-center">
                            <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mt-4">Don't have an account? <a href="{{ route('sign-up') }}" class="text-primary">Sign Up</a></p>
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

    <script>
    function captureLocation(event) {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return false;
        }

        // Prevent form submission until location is captured
        event.preventDefault();

        navigator.geolocation.getCurrentPosition(
            (position) => {
                // Populate the hidden fields with latitude and longitude
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;

                // Submit the form after location is captured
                event.target.submit();
            },
            (error) => {
                let errorMessage = 'An error occurred while fetching your location. Please allow location access and try again.';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = 'Permission to access location was denied.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = 'Location information is unavailable.';
                        break;
                    case error.TIMEOUT:
                        errorMessage = 'The request to get user location timed out.';
                        break;
                }
                alert(errorMessage);
            }
        );

        return false; // Prevent default form submission until location is captured
    }
</script>


@endsection
