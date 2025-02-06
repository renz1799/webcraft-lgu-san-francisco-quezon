<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" class="light" data-header-styles="light" data-menu-styles="dark">

<head>
    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="Author" content="Spruko Technologies Private Limited">
    <meta name="Description" content="Laravel Tailwind CSS Responsive Admin Web Dashboard Template">
    <meta name="keywords" content="admin panel in laravel, tailwind, admin template, laravel tailwind, dashboard">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- TITLE -->
    <title>Ynex - Laravel Tailwind CSS Admin & Dashboard Template</title>

    <!-- FAVICON -->
    <link rel="icon" href="{{ asset('build/assets/images/brand-logos/favicon.ico') }}" type="image/x-icon">

    <!-- ICONS CSS -->
    <link href="{{ asset('build/assets/iconfonts/icons.css') }}" rel="stylesheet">

    <!-- Datatables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600&display=swap">

    <!-- Tailwind SCSS -->
    @vite(['resources/sass/app.scss'])

    @include('layouts.components.styles')

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Main JS -->
    <script src="{{ asset('build/assets/main.js') }}"></script>

    @yield('styles')
</head>

<body>
    <!-- SWITCHER -->
    @include('layouts.components.switcher')
    <!-- END SWITCHER -->

    <!-- LOADER -->
    <div id="loader">
        <img src="{{ asset('build/assets/images/media/loader.svg') }}" alt="">
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
    <!-- END PAGE -->

    <!-- SCRIPTS -->
    @include('layouts.components.scripts')
    @yield('scripts')

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#your-table-id').DataTable();
        });
    </script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!--
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="{{ asset('resources/assets/js/ecommerce-dashboard.js') }}"></script>
      -->

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Popper.js -->
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    
    <!-- Custom Scripts -->
    <script src="{{ asset('resources/assets/js/custom-switcher.js') }}"></script>
    <script src="{{ asset('resources/assets/js/custom.js') }}"></script>

    <!-- Pickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/classic.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr"></script>

    <!-- Waves -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/node-waves/0.7.6/waves.min.js"></script>

    <!-- SimpleBar -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplebar/6.2.6/simplebar.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplebar/6.2.6/simplebar.min.js"></script>

    <!-- Choices.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/preline@1.0.0/dist/preline.js"></script>


    <!-- Sticky JS -->
    <script src="{{ asset('build/assets/sticky.js') }}"></script>

    <!-- Delete modal JS -->
    <script src="{{ asset('build/assets/delete-modal.js') }}"></script>

<!-- Laravel Vite JS -->
@vite(['resources/js/app.js', 'resources/assets/js/custom-switcher.js'])


    <!-- END SCRIPTS -->
</body>
</html>