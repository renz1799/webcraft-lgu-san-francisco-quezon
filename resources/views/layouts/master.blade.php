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

            /* Add spacing between the search bar and the table */
        /* General styling for all DataTables */
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 15px; /* Adjust the spacing here */
        }
            /* Customize the processing container */
        div.dataTables_processing {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 200px;
            margin-left: -100px;
            margin-top: -26px;
            text-align: center;
            padding: 2px;
            background: transparent; /* Remove any default background */
        }

        /* Target the loading dots container */
        div.dataTables_processing > div:last-child {
            position: relative;
            width: 80px;
            height: 15px;
            margin: 1em auto;
        }

        /* Style individual dots */
        div.dataTables_processing > div:last-child > div {
            position: absolute;
            top: 0;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background: black; /* Change the dots to black */
            animation-timing-function: cubic-bezier(0, 1, 1, 0);
        }

        /* Apply animations for the dots */
        div.dataTables_processing > div:last-child > div:nth-child(1) {
            left: 8px;
            animation: datatables-loader-1 0.6s infinite;
        }

        div.dataTables_processing > div:last-child > div:nth-child(2) {
            left: 8px;
            animation: datatables-loader-2 0.6s infinite;
        }

        div.dataTables_processing > div:last-child > div:nth-child(3) {
            left: 32px;
            animation: datatables-loader-2 0.6s infinite;
        }

        div.dataTables_processing > div:last-child > div:nth-child(4) {
            left: 56px;
            animation: datatables-loader-3 0.6s infinite;
        }

        /* Keyframes for the dots animations */
        @keyframes datatables-loader-1 {
            0% {
                transform: scale(0);
            }
            100% {
                transform: scale(1);
            }
        }

        @keyframes datatables-loader-3 {
            0% {
                transform: scale(1);
            }
            100% {
                transform: scale(0);
            }
        }

        @keyframes datatables-loader-2 {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(24px, 0);
            }
        }
        /* Generic Modal Backdrop */
        .modal-backdrop {
            opacity: 0.5; /* Standard opacity */
        }

        .hidden {
            display: none !important;
        }
        /* TABULATOR PART */
        .loginlog-dots {
        display: flex;
        gap: 6px;
        }

        .loginlog-dots span {
        width: 10px;
        height: 10px;
        background: #6b7280; /* gray-500 */
        border-radius: 50%;
        animation: loginlog-bounce 0.6s infinite alternate;
        }

        .loginlog-dots span:nth-child(2) { animation-delay: 0.15s; }
        .loginlog-dots span:nth-child(3) { animation-delay: 0.3s; }
        .loginlog-dots span:nth-child(4) { animation-delay: 0.45s; }

        @keyframes loginlog-bounce {
        from { transform: translateY(0); opacity: 0.4; }
        to   { transform: translateY(-6px); opacity: 1; }
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

            <!-- END SEARCH-MODAL -->

            <!-- FOOTER -->
            
            @include('layouts.components.footer')

            <!-- END FOOTER -->

		</div>
        <!-- END PAGE-->

        <!-- SCRIPTS -->

        @include('layouts.components.scripts')

        @stack('scripts')

                <!-- APP JS -->
		@vite('resources/js/app.js')
        @vite('resources/js/custom-entry.js')
        <!-- Datatables JS -->
        <script src="{{asset('build/assets/jquery.dataTables.min.js')}}"></script>
        <script src="{{asset('build/assets/dataTables.bootstrap5.min.js')}}"></script>

        <!-- STICKY JS -->
		<script src="{{asset('build/assets/sticky.js')}}"></script>
                
        <!-- Delete modal JS -->
         <script src="{{asset('build/assets/delete-modal.js')}}"></script>




        <!-- CUSTOM-SWITCHER JS -->
        @vite('resources/assets/js/custom-switcher.js')
        @if (auth()->check() && auth()->user()->must_change_password)
            @vite('resources/js/force-password-change.js')
        @endif

        
        <!-- END SCRIPTS -->

	</body>
</html>
