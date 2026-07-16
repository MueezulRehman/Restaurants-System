<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Taste Hut</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-linear-to-br from-slate-50 via-white to-slate-100 min-h-screen flex">
    @php
        $user = auth()->user();
        $isSuperAdmin = $user && $user->isSuperAdmin();
        $impersonatedRestaurant = $isSuperAdmin ? \App\Support\Tenancy::impersonatedRestaurant() : null;
        $restaurant = $impersonatedRestaurant ?? ($user ? $user->restaurant : null);
        $showManagerNav = ! $isSuperAdmin || $impersonatedRestaurant;
        $navPrefix = $showManagerNav ? 'manager' : 'admin';
        $logoutRoute = $isSuperAdmin ? 'admin.logout' : 'manager.logout';
        $moduleEnabled = fn ($key) => $user instanceof \App\Models\User && $user->hasModuleAccess($key);
    @endphp

    <!-- Modern Glassmorphic Sidebar -->
    <aside class="w-36 bg-linear-to-b from-hut-dark via-hut-green to-hut-dark text-white shrink-0 hidden md:flex flex-col shadow-2xl overflow-hidden relative">
        <!-- Glassmorphism backdrop -->
        <div class="absolute inset-0 bg-white/5 backdrop-blur-xl pointer-events-none"></div>
        
        <!-- Content -->
        <div class="relative z-10 flex flex-col h-full">
            <!-- Logo/Brand Section -->
            <div class="p-6 border-b border-white/10 backdrop-blur-md bg-linear-to-r from-white/10 to-transparent">
                <div class="flex items-center gap-3 mb-2">
                    @if($restaurant && !empty($restaurant->logo_path))
                        <img src="{{ asset('storage/' . $restaurant->logo_path) }}" alt="{{ $restaurant->name }}" class="w-12 h-12 rounded-xl object-cover shadow-lg">
                    @else
                        <div class="w-12 h-12 bg-linear-to-br from-hut-yellow to-amber-600 rounded-xl flex items-center justify-center font-display font-bold text-hut-dark shadow-lg">
                            {{ strtoupper(substr($restaurant?->name ?? 'P', 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <p class="font-display font-bold text-lg">{{ $restaurant?->name ?? 'Platform' }}</p>
                        <p class="text-xs text-hut-yellow font-semibold">Management</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-2 custom-scrollbar">
                <!-- Main Dashboard Link -->
                <a href="{{ route($navPrefix . '.dashboard') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 hover:scale-105 {{ request()->routeIs($navPrefix . '.dashboard') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                    <i class="fas fa-chart-line text-lg"></i>
                    <span>Dashboard</span>
                </a>

                @if(! $showManagerNav)
                    <!-- Platform Section -->
                    <div class="pt-4 pb-2">
                        <p class="px-4 py-2 text-xs text-hut-yellow/70 uppercase tracking-widest font-bold">Platform</p>
                    </div>
                    <a href="{{ route('admin.restaurants.index') }}" class="flex flex-col items-center gap-2 px-3 py-4 rounded-xl text-xs font-medium text-gray-200 transition-all duration-300 hover:bg-white/10" data-active="{{ request()->routeIs('admin.restaurants.*') }}"{{ request()->routeIs('admin.restaurants.*') ? ' style="background: linear-gradient(to right, rgba(255,255,255,.2), transparent); color: #FACC15; box-shadow: 0 10px 15px -3px rgba(0,0,0,.1); border-bottom: 4px solid #FACC15;"' : '' }}>
                        <i class="fas fa-store text-2xl"></i> <span class="text-center line-clamp-2 max-w-[3.5rem]">Restaurants</span>
                    </a>
                    <a href="{{ route('admin.restaurants.create') }}" class="nav-item" data-active="{{ request()->routeIs('admin.restaurants.create') }}">
                        <i class="fas fa-plus-circle"></i> <span>New Business</span>
                    </a>
                    <a href="{{ route('admin.business-types.index') }}" class="nav-item" data-active="{{ request()->routeIs('admin.business-types.*') }}">
                        <i class="fas fa-tag"></i> <span>Business Types</span>
                    </a>
                    <a href="{{ route('admin.modules.index') }}" class="nav-item" data-active="{{ request()->routeIs('admin.modules.*') }}">
                        <i class="fas fa-puzzle-piece"></i> <span>Modules</span>
                    </a>
                    <a href="{{ route('admin.subscription-plans.index') }}" class="nav-item" data-active="{{ request()->routeIs('admin.subscription-plans.*') }}">
                        <i class="fas fa-credit-card"></i> <span>Plans</span>
                    </a>
                    <a href="{{ route('admin.feedback.index') }}" class="nav-item" data-active="{{ request()->routeIs('admin.feedback.*') }}">
                        <i class="fas fa-comments"></i> <span>Feedback</span>
                    </a>
                @else
                    <!-- Manager Navigation -->
                    @if($moduleEnabled('orders'))
                        <a href="{{ route('manager.orders.index') }}" class="nav-item" data-active="{{ request()->routeIs('manager.orders.*') }}">
                            <i class="fas fa-shopping-cart"></i> <span>Orders</span>
                        </a>
                    @endif

                    @if($moduleEnabled('pos'))
                        <a href="{{ route('manager.pos.index') }}" class="nav-item" data-active="{{ request()->routeIs('manager.pos.*') }}">
                            <i class="fas fa-cash-register"></i> <span>{{ $restaurant?->getPosConfig()['title'] ?? 'POS' }}</span>
                        </a>
                        <a href="{{ route('manager.sales.index') }}" class="nav-item" data-active="{{ request()->routeIs('manager.sales.*') }}">
                            <i class="fas fa-chart-bar"></i> <span>Sales History</span>
                        </a>
                    @endif

                    <!-- Stock Analysis Link -->
                    @if($moduleEnabled('stock'))
                        <div class="pt-4 pb-2">
                            <p class="px-4 py-2 text-xs text-hut-yellow/70 uppercase tracking-widest font-bold">Inventory</p>
                        </div>
                        <a href="{{ route('manager.stock-analysis.index') }}" class="nav-item" data-active="{{ request()->routeIs('manager.stock-analysis.*') }}">
                            <i class="fas fa-chart-pie"></i> <span>Stock Analysis</span>
                        </a>
                        <a href="{{ route('manager.stock.index') }}" class="nav-item" data-active="{{ request()->routeIs('manager.stock.*') }}">
                            <i class="fas fa-boxes"></i> <span>Stock Management</span>
                        </a>
                    @endif

                    @if($moduleEnabled('medical'))
                        <div class="pt-4 pb-2">
                            <p class="px-4 py-2 text-xs text-hut-yellow/70 uppercase tracking-widest font-bold">Medical</p>
                        </div>
                        <a href="{{ route('manager.medicines.index') }}" class="nav-item" data-active="{{ request()->routeIs('manager.medicines.*') }}">
                            <i class="fas fa-pills"></i> <span>Medicines</span>
                        </a>
                        <a href="{{ route('manager.purchases.index') }}" class="nav-item" data-active="{{ request()->routeIs('manager.purchases.*') }}">
                            <i class="fas fa-box-open"></i> <span>Purchases</span>
                        </a>
                        <a href="{{ route('manager.suppliers.index') }}" class="nav-item" data-active="{{ request()->routeIs('manager.suppliers.*') }}">
                            <i class="fas fa-industry"></i> <span>Suppliers</span>
                        </a>
                    @endif

                    @if($moduleEnabled('menu') || $moduleEnabled('categories') || $moduleEnabled('deals'))
                        <div class="pt-4 pb-2">
                            <p class="px-4 py-2 text-xs text-hut-yellow/70 uppercase tracking-widest font-bold">Menu</p>
                        </div>
                        @if($moduleEnabled('menu'))
                            <a href="{{ route('manager.menu-items.index') }}" class="nav-item" data-active="{{ request()->routeIs('manager.menu-items.*') }}">
                                <i class="fas fa-utensils"></i> <span>Menu Items</span>
                            </a>
                        @endif
                        @if($moduleEnabled('categories'))
                            <a href="{{ route('manager.categories.index') }}" class="nav-item" data-active="{{ request()->routeIs('manager.categories.*') }}">
                                <i class="fas fa-folder-open"></i> <span>Categories</span>
                            </a>
                        @endif
                        @if($moduleEnabled('deals'))
                            <a href="{{ route('manager.deals.index') }}" class="nav-item" data-active="{{ request()->routeIs('manager.deals.*') }}">
                                <i class="fas fa-gift"></i> <span>Deals</span>
                            </a>
                        @endif
                    @endif

                    @if($moduleEnabled('cashbook') || $moduleEnabled('expenses'))
                        <div class="pt-4 pb-2">
                            <p class="px-4 py-2 text-xs text-hut-yellow/70 uppercase tracking-widest font-bold">Financial</p>
                        </div>
                        @if($moduleEnabled('cashbook'))
                            <a href="{{ route('manager.cashbook.index') }}" class="nav-item" data-active="{{ request()->routeIs('manager.cashbook.*') }}">
                                <i class="fas fa-money-bill-wave"></i> <span>Cashbook</span>
                            </a>
                        @endif
                        @if($moduleEnabled('expenses'))
                            <a href="{{ route('manager.expenses.index') }}" class="nav-item" data-active="{{ request()->routeIs('manager.expenses.*') }}">
                                <i class="fas fa-receipt"></i> <span>Expenses</span>
                            </a>
                        @endif
                    @endif

                    @if($moduleEnabled('hr') || $moduleEnabled('staff') || $moduleEnabled('attendance') || $moduleEnabled('salary'))
                        <div class="pt-4 pb-2">
                            <p class="px-4 py-2 text-xs text-hut-yellow/70 uppercase tracking-widest font-bold">HR</p>
                        </div>
                        @if($moduleEnabled('hr') || $moduleEnabled('staff'))
                            <a href="{{ route('manager.staff.index') }}" class="nav-item" data-active="{{ request()->routeIs('manager.staff.*') }}">
                                <i class="fas fa-users"></i> <span>Staff</span>
                            </a>
                        @endif
                        @if($moduleEnabled('hr') || $moduleEnabled('attendance'))
                            <a href="{{ route('manager.attendance.index') }}" class="nav-item" data-active="{{ request()->routeIs('manager.attendance.*') }}">
                                <i class="fas fa-check-circle"></i> <span>Attendance</span>
                            </a>
                        @endif
                    @endif

                    <div class="pt-4 pb-2">
                        <p class="px-4 py-2 text-xs text-hut-yellow/70 uppercase tracking-widest font-bold">Settings</p>
                    </div>
                    <a href="{{ route('manager.restaurant.profile.edit') }}" class="nav-item" data-active="{{ request()->routeIs('manager.restaurant.profile.*') }}">
                        <i class="fas fa-cog"></i> <span>Settings</span>
                    </a>
                    @if($moduleEnabled('reports'))
                        <a href="{{ route('manager.reports.index') }}" class="nav-item" data-active="{{ request()->routeIs('manager.reports.*') }}">
                            <i class="fas fa-file-chart-line"></i> <span>Reports</span>
                        </a>
                    @endif
                @endif
            </nav>

            <!-- Logout Button -->
            <div class="p-4 border-t border-white/10 backdrop-blur-md bg-gradient-to-r from-white/5 to-transparent">
                <form action="{{ route($logoutRoute) }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-200 hover:bg-red-500/20 hover:text-red-200 transition-all duration-300">
                        <i class="fas fa-power-off"></i> <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <!-- Modern Header -->
        <header class="bg-gradient-to-r from-hut-dark via-hut-green to-hut-dark text-white border-b border-white/10 px-6 py-4 shadow-lg backdrop-blur-md">
            <div class="flex justify-between items-center gap-6">
                <div class="flex-1">
                    <h1 class="font-display font-bold text-2xl tracking-tight">@yield('title', 'Dashboard')</h1>
                    <p class="text-sm text-gray-300 mt-1">
                        @if($impersonatedRestaurant)
                            <i class="fas fa-search mr-2"></i>Managing {{ $impersonatedRestaurant->name }}
                        @elseif($isSuperAdmin)
                            <i class="fas fa-crown mr-2"></i>Platform Administration
                        @else
                            <i class="fas fa-building mr-2"></i>Restaurant Management
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-6">
                    <!-- Current Time -->
                    <div class="hidden sm:block text-right text-sm">
                        <p class="font-semibold">{{ now()->format('D, M d') }}</p>
                        <p class="text-gray-400 text-xs">{{ now()->format('g:i A') }}</p>
                    </div>

                    <!-- User Profile -->
                    <div class="flex items-center gap-3 pl-6 border-l border-white/10">
                        <div class="text-right">
                            <p class="font-semibold text-sm">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-hut-yellow">
                                @if(auth()->user()->isSuperAdmin())
                                    Super Admin
                                @else
                                    Manager
                                @endif
                            </p>
                        </div>
                        <div class="w-10 h-10 bg-gradient-to-br from-hut-yellow to-amber-600 rounded-xl flex items-center justify-center font-bold text-hut-dark shadow-lg border-2 border-white/20">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Impersonation Banner -->
        @if($impersonatedRestaurant)
            <div class="bg-gradient-to-r from-hut-yellow/20 to-amber-100/20 text-hut-dark text-sm px-6 py-3 border-b border-hut-yellow/30 flex items-center justify-between backdrop-blur-sm">
                <span><i class="fas fa-info-circle mr-2"></i>You're managing <strong>{{ $impersonatedRestaurant->name }}</strong> — changes affect live data</span>
                <form action="{{ route('admin.restaurants.exit') }}" method="POST" class="inline">
                    @csrf
                    <button class="text-xs font-bold underline hover:no-underline">Exit</button>
                </form>
            </div>
        @endif

        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 text-green-700 text-sm px-6 py-3 border-b border-green-200 flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-auto">
            @yield('content')
        </main>
    </div>
