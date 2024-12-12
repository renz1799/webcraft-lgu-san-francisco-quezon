@extends('layouts.custom-master')

@section('styles')
<!-- Add any specific styles for the error page here if needed -->
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
                        <p class="error-text sm:mb-0 mb-2">403</p>
                        <p class="text-[1.125rem] font-semibold mb-4 dark:text-defaulttextcolor/70">
                            Sorry 😔, you are not authorized to access this page.
                        </p>
                        <div class="flex justify-center items-center mb-[3rem]">
                            <div class="xl:col-span-6 w-[50%]">
                                <p class="mb-0 opacity-[0.7]">
                                    It seems you don't have the necessary permissions to view this page. Please contact your administrator if you believe this is an error.
                                </p>
                            </div>
                        </div>
                        <a href="{{ url('/') }}" class="ti-btn btn-wave bg-primary text-white font-semibold dark:border-defaultborder/10">
                            <i class="ri-arrow-left-line align-middle inline-block"></i> BACK TO HOME
                        </a>
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
