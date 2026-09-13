<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <script>
        document.documentElement.dataset.dashboardTheme = localStorage.getItem('codeibex-dashboard-theme') || 'light';
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CEO Portal') — {{ $platformName }}</title>
    <link rel="icon" href="{{ asset('images/codeibex-mark.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="ceo-shell min-h-screen text-gray-800">
    <div class="flex min-h-screen">
        <div id="ceo-sidebar-backdrop" class="ceo-sidebar-backdrop hidden" aria-hidden="true"></div>
        @include('ceo.layout.sidebar')
        <div class="ceo-main flex min-w-0 flex-1 flex-col">
            @include('ceo.layout.header')
            <main class="ceo-content flex-1 px-4 py-5 sm:px-5 md:px-8 md:py-8" data-master-section="content">
                @yield('page-content')
            </main>
            @include('ceo.layout.footer')
        </div>
    </div>
    @stack('scripts')
</body>
</html>