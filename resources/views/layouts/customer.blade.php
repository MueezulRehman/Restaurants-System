<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Taste Hut — We Bake Happiness')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col">

    <header class="bg-hut-dark sticky top-0 z-50 shadow-md">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <div class="w-10 h-10 bg-hut-yellow rounded-full flex items-center justify-center font-display font-bold text-hut-dark text-lg">TH</div>
                <div>
                    <p class="text-white font-display font-bold text-lg leading-none">Taste Hut</p>
                    <p class="text-hut-yellow text-[10px] tracking-wide">WE BAKE HAPPINESS</p>
                </div>
            </a>
            <nav class="flex items-center gap-5 text-sm">
                <a href="{{ route('home') }}" class="text-white hover:text-hut-yellow transition-colors hidden sm:inline">Menu</a>
                <a href="{{ route('orders.lookup.form') }}" class="text-white hover:text-hut-yellow transition-colors hidden sm:inline">Track Order</a>
                <a href="tel:03490751767" class="text-white hover:text-hut-yellow transition-colors hidden md:inline">📞 0349-0751767</a>
                <a href="{{ route('checkout') }}" class="relative">
                    <button class="btn-accent flex items-center gap-1.5 !py-2 !px-4">
                        <span>🛒</span>
                        <span id="cart-count-badge" class="text-xs bg-hut-dark text-white rounded-full w-5 h-5 flex items-center justify-center">0</span>
                    </button>
                </a>
                @auth('customer')
                    <a href="{{ route('account.dashboard') }}" class="text-white hover:text-hut-yellow transition-colors hidden sm:inline">My Orders</a>
                    <form method="POST" action="{{ route('customer.logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-white bg-white/10 hover:bg-white/20 px-3 py-1 rounded">Logout</button>
                    </form>
                @else
                    <a href="{{ route('customer.login') }}" class="text-white hover:text-hut-yellow transition-colors text-sm">Login</a>
                @endauth

                @auth
                    <form method="POST" action="{{ auth()->user()->role === 'super_admin' ? route('admin.logout') : route('manager.logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-white bg-white/10 hover:bg-white/20 px-3 py-1 rounded">Staff Logout</button>
                    </form>
                @endauth
            </nav>
        </div>
    </header>

    @if (session('success'))
        <div class="bg-hut-green text-white text-center py-2 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-hut-dark text-white mt-12">
        <div class="max-w-6xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="font-display font-bold text-hut-yellow text-lg mb-2">Taste Hut</p>
                <p class="text-sm text-gray-300">Kanda, Nain Ranjha<br>Amir Ranjha Petrolium, Gojra–Qadirabad Road</p>
            </div>
            <div>
                <p class="font-display font-semibold mb-2">Order Now</p>
                <p class="text-sm text-gray-300">📞 0349-0751767</p>
                <p class="text-sm text-gray-300">📞 0343-7751767</p>
            </div>
            <div>
                <p class="font-display font-semibold mb-2">Follow @tasthut</p>
                <p class="text-sm text-gray-300">Instagram · Facebook · TikTok</p>
            </div>
        </div>
        <div class="border-t border-white/10 text-center py-3 text-xs text-gray-400">
            &copy; {{ date('Y') }} Taste Hut. All rights reserved.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
