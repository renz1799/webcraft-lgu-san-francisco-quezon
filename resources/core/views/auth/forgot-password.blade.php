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
                    <a href="{{ route('login') }}">
                        <img src="{{ asset('build/assets/images/brand-logos/desktop-logo.png') }}" alt="logo" class="desktop-logo">
                        <img src="{{ asset('build/assets/images/brand-logos/desktop-dark.png') }}" alt="logo" class="desktop-dark">
                    </a>
                </div>
                <div class="box">
                    <div class="box-body !p-[3rem]">
                        <p class="h5 font-semibold mb-2 text-center">Forgot Password</p>
                        <p class="mb-4 text-[#8c9097] dark:text-white/50 opacity-[0.7] font-normal text-center">
                            Enter your platform email and we will send you a secure reset link.
                        </p>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="grid grid-cols-12 gap-y-4">
                                <div class="xl:col-span-12 col-span-12">
                                    <label for="forgot-email" class="form-label text-default">Email</label>
                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control form-control-lg w-full !rounded-md"
                                        id="forgot-email"
                                        placeholder="Enter your email"
                                        value="{{ old('email') }}"
                                        required
                                        autofocus
                                    >
                                    @error('email')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="xl:col-span-12 col-span-12 grid mt-2">
                                    <button type="submit" class="ti-btn ti-btn-primary !bg-primary btn-wave !text-white !font-medium">
                                        Email Reset Link
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="text-center">
                            <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mt-4">
                                Remembered your password?
                                <a href="{{ route('login') }}" class="text-primary">Back to Sign In</a>
                            </p>
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
@endsection
