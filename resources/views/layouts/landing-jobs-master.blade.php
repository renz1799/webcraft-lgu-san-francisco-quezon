<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="horizontal" data-nav-style="menu-click" data-menu-position="fixed" data-theme-mode="light" class="light">
    
    <head>

        <!-- Meta Data -->
		<meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="Author" content="Spruko Technologies Private Limited">
        <meta name="Description" content="Ynex –Laravel Tailwind CSS Responsive Admin Web Dashboard Template">
        <meta name="keywords" content="admin panel in laravel, tailwind, tailwind template admin, laravel admin panel, tailwind css dashboard, admin dashboard template, admin template, tailwind laravel, template dashboard, admin panel tailwind, tailwind css admin template, laravel tailwind template, laravel tailwind, tailwind admin dashboard">
        
        <!-- TITLE -->
		<title> Ynex - Laravel Tailwind CSS Admin & Dashboard Template </title>

        <!-- FAVICON -->
        <link rel="icon" href="{{asset('build/assets/images/brand-logos/favicon.ico')}}" type="image/x-icon">

        <!-- ICONS CSS -->
        <link href="{{asset('build/assets/iconfonts/icons.css')}}" rel="stylesheet">
        
        <!-- APP SCSS -->
        @vite(['resources/sass/app.scss'])


        @include('layouts.components.landing.styles')

        @yield('styles')


	</head>

    <body class="landing-body jobs-landing">

        <!-- SWITCHER -->

        @include('layouts.components.landing.switcher')

        <!-- END SWITCHER -->

        <!-- PAGE -->
		<div class="landing-page-wrapper">

            <!-- HEADER -->

            @include('layouts.components.landing.jobs-header')

            <!-- END HEADER -->

            <!-- SIDEBAR -->

            @include('layouts.components.landing.jobs-sidebar')

            <!-- END SIDEBAR -->

            <!-- MAIN-CONTENT -->
            <div class="main-content !p-0 landing-main dark:text-defaulttextcolor/70">
                @yield('content')

                    <!-- FOOTER -->
                    @include('layouts.components.landing.jobs-footer')

                    <!-- FOOTER -->
            </div>
            <!-- END MAIN-CONTENT -->
            
		</div>
        <!-- END PAGE-->

        <!-- SCRIPTS -->

        @include('layouts.components.landing.scripts')

        @yield('scripts')
        
        <!-- STICKY JS -->
		<script src="{{asset('build/assets/sticky.js')}}"></script>

        <!-- END SCRIPTS -->

	</body>
</html>
