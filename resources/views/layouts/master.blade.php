<!DOCTYPE html>
<html
  lang="{{ str_replace('_','-',app()->getLocale()) }}"
  dir="{{ $themeStyle['dir'] ?? 'ltr' }}"
  @class([
    'dark'  => ($themeStyle['mode'] ?? 'light') === 'dark',
    'light' => ($themeStyle['mode'] ?? 'light') === 'light',
  ])
  data-nav-layout="{{ $themeStyle['nav'] ?? 'vertical' }}"
  data-menu-hover="{{ ($themeStyle['menuHover'] ?? false) ? '1' : '0' }}"
  {{-- keep these two if your template uses them --}}
  data-header-styles="{{ ($themeStyle['mode'] ?? 'light') === 'dark' ? 'dark' : 'light' }}"
  data-menu-styles="dark"
>
<head>
    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="Author" content="Spruko Technologies Private Limited">
    <meta name="Description" content="Laravel Tailwind CSS Responsive Admin Web Dashboard Template">
    <meta name="keywords" content="admin panel in laravel, tailwind, admin template, laravel tailwind, dashboard">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- TITLE -->
    <title>Webcraft</title>

    <!-- FAVICON -->
    <link rel="icon" href="{{ asset('build/assets/images/brand-logos/favicon.png') }}" type="image/x-icon">

    <!-- ICONS CSS -->
    <link href="{{ asset('build/assets/iconfonts/icons.css') }}" rel="stylesheet">


    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600&display=swap">

    <style>
    :root{
        --color-primary: {{ $themeColors['primary'] }};
        --color-success: {{ $themeColors['success'] }};
        --color-warning: {{ $themeColors['warning'] }};
        --color-danger:  {{ $themeColors['danger']  }};
    }
    </style>

    <!-- Tailwind SCSS -->
    @vite(['resources/sass/app.scss'])

    @include('layouts.components.styles')

    @vite(['resources/js/app.js'])


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Main JS -->
    <!--   <script src="{{ asset('build/assets/main.js') }}"></script> --> 

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

    @if(Route::is('sweetalerts'))
    @vite(['resources/js/sweetalert.js'])
    @endif

    @if(Route::is('logs.index'))
    @vite(['resources/js/datatables.css'])
    @vite(['resources/js/datatables.js'])
    @endif


    <!--
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="{{ asset('resources/assets/js/ecommerce-dashboard.js') }}"></script>
      -->

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Popper.js -->
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    
    <!-- Custom Scripts -->
  <!--   <script src="{{ asset('resources/assets/js/custom-switcher.js') }}"></script> -->
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
@vite(['resources/assets/js/custom-switcher.js'])


    <!-- END SCRIPTS -->
</body>
</html>