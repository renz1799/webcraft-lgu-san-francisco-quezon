@extends('layouts.custom-master')

@section('styles')
<!-- Optional: add page-specific styles here -->
@endsection

@section('content')

@section('error-body')
<body>
@endsection

<div class="page error-bg dark:!bg-bodybg" id="particles-js">
    <!-- Start::error-page -->
    <div class="error-page">
        <div class="container text-defaulttextcolor text-defaultsize">
            <div class="text-center p-5 my-auto">
                <div class="flex items-center justify-center h-full">
                    <div class="xl:col-span-3"></div>

                    <div class="xl:col-span-6 col-span-12">
                        <p class="error-text sm:mb-0 mb-2">429</p>

                        <p class="text-[1.125rem] font-semibold mb-4 dark:text-defaulttextcolor/70">
                            Too many attempts 🚦
                        </p>

                        <div class="flex justify-center items-center mb-[3rem]">
                            <div class="xl:col-span-6 w-[50%]">
                                <p class="mb-0 opacity-[0.7]">
                                    You’ve made too many requests in a short period of time.
                                    Please wait a moment and try again.
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-center gap-3">
                            <a href="{{ route('login') }}"
                               class="ti-btn btn-wave bg-primary text-white font-semibold dark:border-defaultborder/10">
                                <i class="ri-refresh-line align-middle inline-block"></i>
                                TRY AGAIN
                            </a>

                            <a href="{{ url('/') }}"
                               class="ti-btn btn-wave bg-light text-defaulttextcolor font-semibold dark:border-defaultborder/10">
                                <i class="ri-home-line align-middle inline-block"></i>
                                HOME
                            </a>
                        </div>
                    </div>

                    <div class="xl:col-span-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<!-- Particles JS -->
<script src="{{ asset('build/assets/libs/particles.js/particles.js') }}"></script>

<!-- Error JS -->
@vite('resources/assets/js/error.js')
@endsection
