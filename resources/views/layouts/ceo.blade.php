<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CEO Portal') — CodeIbex</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-800">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="{{ url('/') }}" class="font-display text-xl font-bold text-hut-dark">CodeIbex CEO</a>
            @auth
                <form method="post" action="{{ route('ceo.logout') }}">
                    @csrf
                    <button class="text-sm font-semibold text-gray-600 hover:text-hut-dark">Log out</button>
                </form>
            @endauth
        </div>
    </header>
    <main class="mx-auto max-w-7xl px-6 py-8">
        @yield('content')
    </main>
</body>
</html>
