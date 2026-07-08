<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Taste Hut</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex">

    <aside class="w-56 bg-hut-dark text-white flex-shrink-0 hidden md:flex flex-col">
        <div class="p-4 border-b border-white/10 flex items-center gap-2">
            <div class="w-9 h-9 bg-hut-yellow rounded-full flex items-center justify-center font-display font-bold text-hut-dark">TH</div>
            <span class="font-display font-semibold">Taste Hut</span>
        </div>
        <nav class="flex-1 p-3 space-y-1 text-sm">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-hut-yellow' : '' }}">📊 Dashboard</a>
            <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.orders.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🛒 Orders</a>
            
            <div class="pt-2 pb-1">
                <p class="px-3 py-1 text-xs text-gray-400 uppercase tracking-wide font-semibold">Menu Management</p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.categories.*') ? 'bg-white/10 text-hut-yellow' : '' }}">📂 Categories</a>
            <a href="{{ route('admin.menu-items.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.menu-items.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🍽️ Menu Items</a>
            <a href="{{ route('admin.deals.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.deals.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🎁 Deals</a>

            <div class="pt-2 pb-1">
                <p class="px-3 py-1 text-xs text-gray-400 uppercase tracking-wide font-semibold">Financial</p>
            </div>
            <a href="{{ route('admin.cashbook.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.cashbook.*') ? 'bg-white/10 text-hut-yellow' : '' }}">💰 Cashbook</a>
            <a href="{{ route('admin.expenses.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.expenses.*') ? 'bg-white/10 text-hut-yellow' : '' }}">💸 Expenses</a>

            <div class="pt-2 pb-1">
                <p class="px-3 py-1 text-xs text-gray-400 uppercase tracking-wide font-semibold">Staff & HR</p>
            </div>
            <a href="{{ route('admin.staff.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.staff.*') ? 'bg-white/10 text-hut-yellow' : '' }}">👥 Staff</a>
            <a href="{{ route('admin.attendance.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.attendance.*') ? 'bg-white/10 text-hut-yellow' : '' }}">✓ Attendance</a>
            <a href="{{ route('admin.salary.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.salary.*') ? 'bg-white/10 text-hut-yellow' : '' }}">💵 Salary</a>
            @if(auth()->user()->isSuperAdmin())
            <div class="pt-2 pb-1">
                <p class="px-3 py-1 text-xs text-gray-400 uppercase tracking-wide font-semibold">Platform</p>
            </div>
            <a href="{{ route('admin.restaurants.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.restaurants.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🏪 Restaurants</a>
            @else
            <div class="pt-2 pb-1">
                <p class="px-3 py-1 text-xs text-gray-400 uppercase tracking-wide font-semibold">Restaurant</p>
            </div>
            <a href="{{ route('admin.restaurant.profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.restaurant.profile.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🏠 My Restaurant</a>
            @endif
        </nav>
        <form action="{{ route('admin.logout') }}" method="POST" class="p-3 border-t border-white/10">
            @csrf
            <button class="text-sm text-gray-300 hover:text-white">⏻ Log out</button>
        </form>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-gradient-to-r from-hut-dark to-gray-800 border-b border-hut-yellow/20 px-4 py-4 flex justify-between items-center shadow-md">
            <div class="flex items-center gap-4">
                <div>
                    <h1 class="font-display font-bold text-white text-xl">@yield('title', 'Dashboard')</h1>
                    <p class="text-xs text-gray-300 mt-1">Restaurant Management System</p>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <!-- Date/Time -->
                <div class="hidden sm:block text-right">
                    <p class="text-sm font-medium text-white">{{ now()->format('l') }}</p>
                    <p class="text-xs text-gray-300">{{ now()->format('M d, Y') }}</p>
                </div>

                <!-- User Profile Section -->
                <div class="flex items-center gap-3 pl-6 border-l border-white/10">
                    <div class="text-right">
                        <p class="text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-hut-yellow capitalize font-medium">
                            @if(auth()->user()->role === 'admin')
                                👨‍💼 Administrator
                            @else
                                👤 {{ ucfirst(auth()->user()->role) }}
                            @endif
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-hut-yellow to-amber-600 rounded-full flex items-center justify-center font-bold text-hut-dark text-sm border-2 border-white/20">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                </div>
            </div>
        </header>

        @if(session('success'))
            <div class="bg-hut-green/10 text-hut-green text-sm px-4 py-2 border-b border-hut-green/20 flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <main class="flex-1 p-4 md:p-6">
            @yield('content')
        </main>
    </div>
</body>
</html>
