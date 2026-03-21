<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Webcraft Core System') }}</title>

    {{-- Reuse the same app assets your normal pages use --}}
    @vite([
        'resources/sass/app.scss',
        'resources/core/js/app.js',
    ])
    <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css"
/>

    @stack('styles')
</head>
<body>
    <div class="min-h-screen bg-bodybg">
        <main class="py-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
