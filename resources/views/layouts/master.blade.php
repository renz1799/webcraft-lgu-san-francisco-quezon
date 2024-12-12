<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" class="light" data-header-styles="light" data-menu-styles="dark">

    <head>

        <!-- META DATA -->
		<meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="Author" content="Spruko Technologies Private Limited">
        <meta name="Description" content="Laravel Tailwind CSS Responsive Admin Web Dashboard Template">
        <meta name="keywords" content="admin panel in laravel, tailwind, tailwind template admin, laravel admin panel, tailwind css dashboard, admin dashboard template, admin template, tailwind laravel, template dashboard, admin panel tailwind, tailwind css admin template, laravel tailwind template, laravel tailwind, tailwind admin dashboard">
        
        <!-- TITLE -->
		<title> Ynex - Laravel Tailwind CSS Admin & Dashboard Template </title>

        <!-- FAVICON -->
        <link rel="icon" href="{{asset('build/assets/images/brand-logos/favicon.ico')}}" type="image/x-icon">

        <!-- ICONS CSS -->
        <link href="{{asset('build/assets/iconfonts/icons.css')}}" rel="stylesheet">

        <!-- Datatables CSS -->
        <link href="{{asset('build/assets/jquery.dataTables.min.css')}}" rel="stylesheet">
        
        <!-- APP SCSS -->
        @vite(['resources/sass/app.scss'])

        @include('layouts.components.styles')

        <!-- Include jQuery -->
        <script src="{{asset('build/assets/jquery-3.6.0.min.js')}}"></script>
        
        <!-- MAIN JS -->
        <script src="{{asset('build/assets/main.js')}}"></script>

        @yield('styles')

        <style>
            #deleteConfirmationModal {
    visibility: hidden; /* Start hidden */
    opacity: 0; /* Fully transparent */
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1050;

    transition: opacity 0.3s ease, visibility 0.3s ease; /* Smooth transitions */
}

#deleteConfirmationModal.active {
    visibility: visible; /* Show the modal */
    opacity: 1; /* Fully opaque */
}

        </style>

        
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

                <!-- Include the Global Delete Confirmation Modal -->
                @include('partials.delete-modal')

            <!-- END SEARCH-MODAL -->

            <!-- FOOTER -->
            
            @include('layouts.components.footer')

            <!-- END FOOTER -->

		</div>
        <!-- END PAGE-->

        <!-- SCRIPTS -->

        @include('layouts.components.scripts')

        @yield('scripts')

        <!-- Datatables JS -->
        <script src="{{asset('build/assets/jquery.dataTables.min.js')}}"></script>
        <script src="{{asset('build/assets/dataTables.bootstrap5.min.js')}}"></script>

        <!-- STICKY JS -->
		<script src="{{asset('build/assets/sticky.js')}}"></script>
                
        <!-- Delete modal JS -->
         <script src="{{asset('build/assets/delete-modal.js')}}"></script>

        <!-- APP JS -->
		@vite('resources/js/app.js')


        <!-- CUSTOM-SWITCHER JS -->
        @vite('resources/assets/js/custom-switcher.js')

        
        <!-- END SCRIPTS -->

	</body>
</html>
