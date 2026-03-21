<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" class="light" data-header-styles="light" data-menu-styles="dark">

    <head>

        <!-- META DATA -->
		<meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="Author" content="Spruko Technologies Private Limited">
        <meta name="Description" content="Laravel Tailwind CSS Responsive Admin Web Dashboard Template">
        <meta name="keywords" content="admin panel in laravel, tailwind, tailwind template admin, laravel admin panel, tailwind css dashboard, admin dashboard template, admin template, tailwind laravel, template dashboard, admin panel tailwind, tailwind css admin template, laravel tailwind template, laravel tailwind, tailwind admin dashboard">
        <meta name="force-password-change" content="{{ auth()->check() && auth()->user()->must_change_password ? '1' : '0' }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- TITLE -->
		<title> Webcraft Core System </title>

        <!-- FAVICON -->
        <link rel="icon" href="{{asset('build/assets/images/brand-logos/favicon.ico')}}" type="image/x-icon">

        <!-- ICONS CSS -->
        <link href="{{asset('build/assets/iconfonts/icons.css')}}" rel="stylesheet">

        
        <!-- APP SCSS -->
        @vite(['resources/sass/app.scss'])

        @include('layouts.components.styles')

        
        @include('layouts.components.theme-bootstrap')

        <!-- MAIN JS -->
        <script src="{{asset('build/assets/main.js')}}"></script>

        @yield('styles')

	</head>

	<body>
        <!-- SWITCHER -->

        @include('layouts.components.switcher')

        <!-- END SWITCHER -->

        <!-- LOADER -->
		<div id="loader">
			<img src="{{asset('build/assets/images/media/loader.svg')}}" alt="">
		</div>
		<!-- END LOADER -->

        <!-- PAGE -->
		<div class="page">

            <!-- HEADER -->

            @include('layouts.components.header')

            <!-- END HEADER -->

            <!-- SIDEBAR -->

            @include('layouts.components.sidebar')

            <!-- END SIDEBAR -->

            <!-- MAIN-CONTENT -->
            <div class="content">
                <div class="main-content">
            
                    @yield('content')

                </div>
            </div>

            <!-- END MAIN-CONTENT -->

            <!-- SEARCH-MODAL -->

            @include('layouts.components.search-modal')

            <!-- END SEARCH-MODAL -->

            <!-- FOOTER -->
            
            @include('layouts.components.footer')

            <!-- END FOOTER -->

		</div>
        <!-- END PAGE-->

        @include('layouts.components.modals.register-user')

        <!-- SCRIPTS -->

        @include('layouts.components.scripts')

        @stack('scripts')

                <!-- APP JS -->
		@vite('resources/core/js/app.js')
        @vite('resources/core/js/custom-entry.js')

        <!-- STICKY JS -->
		<script src="{{asset('build/assets/sticky.js')}}"></script>
                




        <!-- CUSTOM-SWITCHER JS -->
        @vite('resources/assets/js/custom-switcher.js')
        @vite('resources/core/js/theme-switcher.js')
        @if (auth()->check() && auth()->user()->must_change_password)
            @vite('resources/core/js/force-password-change.js')
        @endif

        
        <!-- END SCRIPTS -->

	</body>
</html>


