@extends('layouts.custom-master')

@section('styles')
@endsection

@section('content')

@section('error-body')
<body>
@endsection

@php
    $flow = ($flow ?? 'reset') === 'invitation' ? 'invitation' : 'reset';
    $isInvitationFlow = $flow === 'invitation';
@endphp

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
                        <p class="h5 font-semibold mb-2 text-center">{{ $isInvitationFlow ? 'Set Your Password' : 'Reset Password' }}</p>
                        <p class="mb-4 text-[#8c9097] dark:text-white/50 opacity-[0.7] font-normal text-center">
                            {{ $isInvitationFlow
                                ? 'Create a password to activate your Core Platform account and complete your invitation.'
                                : 'Set a new password for your Core Platform account.' }}
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

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="flow" value="{{ $flow }}">

                            <div class="grid grid-cols-12 gap-y-4">
                                <div class="xl:col-span-12 col-span-12">
                                    <label for="reset-email" class="form-label text-default">Email</label>
                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control form-control-lg w-full !rounded-md"
                                        id="reset-email"
                                        placeholder="Enter your email"
                                        value="{{ old('email', $email) }}"
                                        required
                                        autofocus
                                    >
                                    @error('email')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="xl:col-span-12 col-span-12">
                                    <label for="reset-password" class="form-label text-default">New Password</label>
                                    <div class="input-group">
                                        <input
                                            type="password"
                                            name="password"
                                            class="form-control !border-s border-defaultborder dark:border-defaultborder/10 form-control-lg !rounded-s-md"
                                            id="reset-password"
                                            placeholder="Enter a new password"
                                            required
                                        >
                                        <button aria-label="button" class="ti-btn ti-btn-light !rounded-s-none !mb-0" type="button" onclick="createpassword('reset-password',this)">
                                            <i class="ri-eye-off-line align-middle"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="xl:col-span-12 col-span-12">
                                    <label for="reset-password-confirmation" class="form-label text-default">Confirm Password</label>
                                    <div class="input-group">
                                        <input
                                            type="password"
                                            name="password_confirmation"
                                            class="form-control !border-s border-defaultborder dark:border-defaultborder/10 form-control-lg !rounded-s-md"
                                            id="reset-password-confirmation"
                                            placeholder="Confirm your new password"
                                            required
                                        >
                                        <button aria-label="button" class="ti-btn ti-btn-light !rounded-s-none !mb-0" type="button" onclick="createpassword('reset-password-confirmation',this)">
                                            <i class="ri-eye-off-line align-middle"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="xl:col-span-12 col-span-12 grid mt-2">
                                    <button type="submit" class="ti-btn ti-btn-primary !bg-primary btn-wave !text-white !font-medium">
                                        {{ $isInvitationFlow ? 'Set Your Password' : 'Reset Password' }}
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="text-center">
                            <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mt-4">
                                Need to go back?
                                <a href="{{ route('login') }}" class="text-primary">Return to Sign In</a>
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
    <script src="{{ asset('build/assets/show-password.js') }}"></script>
@endsection
