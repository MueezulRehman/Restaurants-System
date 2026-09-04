<!DOCTYPE html>
<html lang="en">

<head>
    @php
        $user = auth()->user();
        $isSuperAdmin = $user && $user->isSuperAdmin();
        $impersonatedRestaurant = $isSuperAdmin ? \App\Support\Tenancy::impersonatedRestaurant() : null;
        $showManagerNav = !$isSuperAdmin || ($impersonatedRestaurant && request()->is('manager/*'));
        $restaurant = $showManagerNav ? ($impersonatedRestaurant ?? ($user ? $user->restaurant : null)) : null;
        $platformName = \App\Models\PlatformSetting::getValue('platform_name', 'CodeIbex');
        $platformTagline = \App\Models\PlatformSetting::getValue('platform_tagline', 'Business platform');
        $platformLogo = \App\Models\PlatformSetting::getValue('platform_logo_path', '');
        $dashboardStyle = $restaurant
            ? $restaurant->themeCssVariables()
            : implode('; ', [
                '--tenant-cream: ' . \App\Models\PlatformSetting::getValue('platform_theme_light', '#E7F0FA'),
                '--tenant-accent: ' . \App\Models\PlatformSetting::getValue('platform_theme_accent', '#7BA4D0'),
                '--tenant-accent-dark: ' . \App\Models\PlatformSetting::getValue('platform_theme_primary', '#2E5E99'),
                '--tenant-primary: ' . \App\Models\PlatformSetting::getValue('platform_theme_primary', '#2E5E99'),
                '--tenant-primary-light: ' . \App\Models\PlatformSetting::getValue('platform_theme_accent', '#7BA4D0'),
                '--tenant-dark: ' . \App\Models\PlatformSetting::getValue('platform_theme_dark', '#0D2440'),
            ]);
        $dashboardLight = $restaurant ? ($restaurant->theme['light'] ?? '#E7F0FA') : \App\Models\PlatformSetting::getValue('platform_theme_light', '#E7F0FA');
        $dashboardAccent = $restaurant ? ($restaurant->theme['accent'] ?? '#7BA4D0') : \App\Models\PlatformSetting::getValue('platform_theme_accent', '#7BA4D0');
        $dashboardPrimary = $restaurant ? ($restaurant->theme['primary'] ?? '#2E5E99') : \App\Models\PlatformSetting::getValue('platform_theme_primary', '#2E5E99');
        $dashboardDark = $restaurant ? ($restaurant->theme['secondary'] ?? '#0D2440') : \App\Models\PlatformSetting::getValue('platform_theme_dark', '#0D2440');
        $navPrefix = $showManagerNav ? 'manager' : 'admin';
        $logoutRoute = $isSuperAdmin ? 'admin.logout' : 'manager.logout';
        $moduleEnabled = fn($key) => $user instanceof \App\Models\User && $user->hasModuleAccess($key);
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ $showManagerNav && $restaurant ? $restaurant->name : $platformName }}</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .dashboard-shell .dashboard-sidebar {
            background: linear-gradient(180deg, var(--dashboard-dark), var(--dashboard-primary), var(--dashboard-dark));
        }

        .dashboard-shell .dashboard-header {
            background: linear-gradient(90deg, var(--dashboard-dark), var(--dashboard-primary), var(--dashboard-dark));
        }

        .dashboard-shell .dashboard-accent {
            color: var(--dashboard-accent);
        }

        .dashboard-shell .dashboard-surface {
            background-color: var(--dashboard-light);
        }

        .dashboard-shell .dashboard-sidebar a[class~="bg-white/20"] {
            background: linear-gradient(90deg, color-mix(in srgb, var(--dashboard-accent) 32%, transparent), color-mix(in srgb, var(--dashboard-primary) 26%, transparent));
            color: #ffffff;
            border-left: 3px solid var(--dashboard-accent);
            box-shadow: 0 8px 20px color-mix(in srgb, var(--dashboard-dark) 35%, transparent);
        }

        .dashboard-shell .dashboard-sidebar nav>div p {
            color: var(--dashboard-accent);
            letter-spacing: 0.16em;
            text-shadow: 0 1px 10px color-mix(in srgb, var(--dashboard-accent) 45%, transparent);
        }

        .dashboard-shell .dashboard-sidebar nav>div {
            border-top: 1px solid color-mix(in srgb, var(--dashboard-accent) 24%, transparent);
        }
    </style>
</head>

<body class="dashboard-shell bg-linear-to-br from-slate-50 via-white to-slate-100 min-h-screen flex"
    style="{{ $dashboardStyle }} --dashboard-light: {{ $dashboardLight }}; --dashboard-accent: {{ $dashboardAccent }}; --dashboard-primary: {{ $dashboardPrimary }}; --dashboard-dark: {{ $dashboardDark }};">

    <!-- Modern Glassmorphic Sidebar -->
    <aside
        class="dashboard-sidebar w-64 text-white shrink-0 hidden md:flex flex-col shadow-2xl overflow-hidden relative">
        <!-- Glassmorphism backdrop -->
        <div class="absolute inset-0 bg-white/5 backdrop-blur-xl pointer-events-none"></div>

        <!-- Content -->
        <div class="relative z-10 flex flex-col h-full">
            <!-- Logo/Brand Section -->
            <div class="p-6 border-b border-white/10 backdrop-blur-md bg-linear-to-r from-white/10 to-transparent">
                <div class="flex items-center gap-3 mb-2">
                    @php
                        $restLogo = null;
                        $logoPath = $restaurant?->logo_path ?: $platformLogo;
                        if (!$restaurant && !$platformLogo) {
                            $restLogo = asset('images/codeibex-mark.svg');
                        } elseif ($logoPath) {
                            $restLogo = asset('storage/' . ltrim($logoPath, '/'));
                        }
                    @endphp

                    @if($restaurant && method_exists($restaurant, 'getPublicUrl'))
                        <a href="{{ $restaurant->getPublicUrl() }}" target="_blank"
                            class="inline-block rounded-xl overflow-hidden group" aria-label="Open public menu">
                            @if($restLogo)
                                <img src="{{ $restLogo }}" alt="{{ $restaurant?->name ?? $platformName }}"
                                    class="w-12 h-12 rounded-xl object-contain bg-white/5 p-1 shadow-lg group-hover:scale-105 transition-transform" />
                            @else
                                <div
                                    class="w-12 h-12 bg-linear-to-br from-hut-yellow to-amber-600 rounded-xl flex items-center justify-center font-display font-bold text-hut-dark shadow-lg">
                                    {{ strtoupper(substr($restaurant?->name ?? 'P', 0, 1)) }}
                                </div>
                            @endif
                        </a>
                    @else
                        @if($restLogo)
                            <img src="{{ $restLogo }}" alt="{{ $restaurant?->name ?? $platformName }}"
                                class="w-12 h-12 rounded-xl object-contain bg-white/5 p-1 shadow-lg">
                        @else
                            <div
                                class="w-12 h-12 bg-linear-to-br from-hut-yellow to-amber-600 rounded-xl flex items-center justify-center font-display font-bold text-hut-dark shadow-lg">
                                {{ strtoupper(substr($restaurant?->name ?? 'P', 0, 1)) }}
                            </div>
                        @endif
                    @endif

                    <div>
                        <p class="font-display font-bold text-lg">{{ $restaurant?->name ?? $platformName }}</p>
                        <p class="text-xs text-hut-yellow font-semibold">
                            {{ $restaurant ? 'Business Management' : $platformTagline }}
                        </p>
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

                @if(!$showManagerNav)
                    <!-- Platform Section -->
                    <div class="pt-4 pb-2">
                        <p class="px-4 py-2 text-xs text-hut-yellow/70 uppercase tracking-widest font-bold">Platform</p>
                    </div>
                    <a href="{{ route('admin.restaurants.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-200 transition-all duration-300 hover:bg-white/10"
                        data-active="{{ request()->routeIs('admin.restaurants.*') }}" {{ request()->routeIs('admin.restaurants.*') ? ' style="background: linear-gradient(to right, rgba(255,255,255,.2), transparent); color: var(--tenant-accent, #7BA4D0); box-shadow: 0 10px 15px -3px rgba(0,0,0,.1); border-bottom: 4px solid var(--tenant-accent, #7BA4D0);"' : '' }}>
                        <i class="fas fa-store text-lg"></i>
                        <span class="flex-1 truncate">Businesses</span>
                    </a>
                    <a href="{{ route('admin.restaurants.create') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('admin.restaurants.create') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                        <i class="fas fa-plus-circle text-lg"></i>
                        <span class="flex-1 truncate">Register Business</span>
                    </a>
                    <a href="{{ route('admin.business-types.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('admin.business-types.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                        <i class="fas fa-tag text-lg"></i>
                        <span class="flex-1 truncate">Business Types</span>
                    </a>
                    <a href="{{ route('admin.modules.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('admin.modules.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                        <i class="fas fa-puzzle-piece text-lg"></i>
                        <span class="flex-1 truncate">Modules</span>
                    </a>
                    <a href="{{ route('admin.subscription-plans.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('admin.subscription-plans.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                        <i class="fas fa-credit-card text-lg"></i>
                        <span class="flex-1 truncate">Plans</span>
                    </a>
                    <a href="{{ route('admin.feedback.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('admin.feedback.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                        <i class="fas fa-comments text-lg"></i>
                        <span class="flex-1 truncate">Feedback</span>
                    </a>
                    <a href="{{ route('admin.account.edit') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('admin.account.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                        <i class="fas fa-user-circle text-lg"></i>
                        <span class="flex-1 truncate">My Account</span>
                    </a>
                    <a href="{{ route('admin.platform.settings') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('admin.platform.settings*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                        <i class="fas fa-sliders-h text-lg"></i>
                        <span class="flex-1 truncate">Platform Settings</span>
                    </a>
                @else
                    <!-- Manager Navigation -->
                    @if($moduleEnabled('orders'))
                        <a href="{{ route('manager.orders.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.orders.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-shopping-cart text-lg"></i>
                            <span class="flex-1 truncate">Orders</span>
                        </a>
                    @endif

                    @if($moduleEnabled('delivery'))
                        <a href="{{ route('manager.deliveries.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.deliveries.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-truck text-lg"></i>
                            <span class="flex-1 truncate">Deliveries</span>
                        </a>
                    @endif

                    @if($moduleEnabled('pos'))
                        <a href="{{ route('manager.pos.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.pos.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-cash-register text-lg"></i>
                            <span class="flex-1 truncate">{{ $restaurant?->getPosConfig()['title'] ?? 'POS' }}</span>
                        </a>
                        <a href="{{ route('manager.sales.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.sales.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-chart-bar text-lg"></i>
                            <span class="flex-1 truncate">Sales History</span>
                        </a>
                    @endif

                    @if($moduleEnabled('customers'))
                        <a href="{{ route('manager.customers.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.customers.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-user-friends text-lg"></i>
                            <span class="flex-1 truncate">Customers</span>
                        </a>
                    @endif

                    <!-- Stock Analysis Link -->
                    @if($moduleEnabled('stock'))
                        <div class="pt-4 pb-2">
                            <p class="px-4 py-2 text-xs text-hut-yellow/70 uppercase tracking-widest font-bold">Inventory</p>
                        </div>
                        <a href="{{ route('manager.stock-analysis.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.stock-analysis.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-chart-pie text-lg"></i>
                            <span class="flex-1 truncate">Stock Analysis</span>
                        </a>
                        <a href="{{ route('manager.stock.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.stock.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-boxes text-lg"></i>
                            <span class="flex-1 truncate">Stock Management</span>
                        </a>
                    @endif

                    @if($moduleEnabled('medical'))
                        <div class="pt-4 pb-2">
                            <p class="px-4 py-2 text-xs text-hut-yellow/70 uppercase tracking-widest font-bold">Medical</p>
                        </div>
                        <a href="{{ route('manager.medicines.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.medicines.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-pills text-lg"></i>
                            <span class="flex-1 truncate">Medicines</span>
                        </a>
                        <a href="{{ route('manager.purchases.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.purchases.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-box-open text-lg"></i>
                            <span class="flex-1 truncate">Purchases</span>
                        </a>
                        <a href="{{ route('manager.suppliers.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.suppliers.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-industry text-lg"></i>
                            <span class="flex-1 truncate">Suppliers</span>
                        </a>
                    @endif

                    @if($moduleEnabled('menu') || $moduleEnabled('categories') || $moduleEnabled('deals'))
                        <div class="pt-4 pb-2">
                            <p class="px-4 py-2 text-xs text-hut-yellow/70 uppercase tracking-widest font-bold">Menu</p>
                        </div>
                        @if($moduleEnabled('menu'))
                            <a href="{{ route('manager.menu-items.index') }}"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.menu-items.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                                <i class="fas fa-utensils text-lg"></i>
                                <span class="flex-1 truncate">Menu Items</span>
                            </a>
                        @endif
                        @if($moduleEnabled('categories'))
                            <a href="{{ route('manager.categories.index') }}"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.categories.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                                <i class="fas fa-folder-open text-lg"></i>
                                <span class="flex-1 truncate">Categories</span>
                            </a>
                        @endif
                        @if($moduleEnabled('deals'))
                            <a href="{{ route('manager.deals.index') }}"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.deals.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                                <i class="fas fa-gift text-lg"></i>
                                <span class="flex-1 truncate">Deals</span>
                            </a>
                        @endif
                    @endif

                    @if($moduleEnabled('cashbook') || $moduleEnabled('expenses'))
                        <div class="pt-4 pb-2">
                            <p class="px-4 py-2 text-xs text-hut-yellow/70 uppercase tracking-widest font-bold">Financial</p>
                        </div>
                        @if($moduleEnabled('cashbook'))
                            <a href="{{ route('manager.cashbook.index') }}"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.cashbook.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                                <i class="fas fa-money-bill-wave text-lg"></i>
                                <span class="flex-1 truncate">Cashbook</span>
                            </a>
                        @endif
                        @if($moduleEnabled('expenses'))
                            <a href="{{ route('manager.expenses.index') }}"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.expenses.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                                <i class="fas fa-receipt text-lg"></i>
                                <span class="flex-1 truncate">Expenses</span>
                            </a>
                        @endif
                    @endif

                    @if($moduleEnabled('hr') || $moduleEnabled('staff') || $moduleEnabled('attendance') || $moduleEnabled('salary'))
                        <div class="pt-4 pb-2">
                            <p class="px-4 py-2 text-xs text-hut-yellow/70 uppercase tracking-widest font-bold">HR</p>
                        </div>
                        @if($moduleEnabled('hr') || $moduleEnabled('staff'))
                            <a href="{{ route('manager.staff.index') }}"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.staff.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                                <i class="fas fa-users text-lg"></i>
                                <span class="flex-1 truncate">Staff</span>
                            </a>
                        @endif
                        @if($moduleEnabled('hr') || $moduleEnabled('attendance'))
                            <a href="{{ route('manager.attendance.index') }}"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.attendance.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                                <i class="fas fa-check-circle text-lg"></i>
                                <span class="flex-1 truncate">Attendance</span>
                            </a>
                        @endif
                    @endif

                    <div class="pt-4 pb-2">
                        <p class="px-4 py-2 text-xs text-hut-yellow/70 uppercase tracking-widest font-bold">Settings</p>
                    </div>
                    <a href="{{ route('manager.restaurant.profile.edit') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.restaurant.profile.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                        <i class="fas fa-cog text-lg"></i>
                        <span class="flex-1 truncate">Settings</span>
                    </a>
                    <a href="{{ route('manager.account.edit') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.account.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                        <i class="fas fa-user-circle text-lg"></i>
                        <span class="flex-1 truncate">My Account</span>
                    </a>
                    <a href="{{ route('manager.subscription.show') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.subscription.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                        <i class="fas fa-credit-card text-lg"></i>
                        <span class="flex-1 truncate">Subscription</span>
                    </a>
                    @if($moduleEnabled('reports'))
                        <a href="{{ route('manager.reports.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.reports.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-file-chart-line text-lg"></i>
                            <span class="flex-1 truncate">Reports</span>
                        </a>
                    @endif
                @endif
            </nav>

            <!-- Logout Button -->
            <div class="p-4 border-t border-white/10 backdrop-blur-md bg-gradient-to-r from-white/5 to-transparent">
                <form action="{{ route($logoutRoute) }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-200 hover:bg-red-500/20 hover:text-red-200 transition-all duration-300">
                        <i class="fas fa-power-off"></i> <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <!-- Modern Header -->
        <header class="dashboard-header text-white border-b border-white/10 px-6 py-4 shadow-lg backdrop-blur-md">
            <div class="flex justify-between items-center gap-6">
                <div class="flex-1">
                    <h1 class="font-display font-bold text-2xl tracking-tight">@yield('title', 'Dashboard')</h1>
                    <p class="text-sm text-gray-300 mt-1">
                        @if($impersonatedRestaurant)
                            <i class="fas fa-search mr-2"></i>Managing {{ $impersonatedRestaurant->name }}
                        @elseif($isSuperAdmin)
                            <i class="fas fa-crown mr-2"></i>Platform Administration
                        @else
                            <i class="fas fa-building mr-2"></i>Business Management
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
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-hut-yellow to-amber-600 rounded-xl flex items-center justify-center font-bold text-hut-dark shadow-lg border-2 border-white/20">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Impersonation Banner -->
        @if($impersonatedRestaurant)
            <div
                class="bg-gradient-to-r from-hut-yellow/20 to-amber-100/20 text-hut-dark text-sm px-6 py-3 border-b border-hut-yellow/30 flex items-center justify-between backdrop-blur-sm">
                <span><i class="fas fa-info-circle mr-2"></i>You're managing
                    <strong>{{ $impersonatedRestaurant->name }}</strong> — changes affect live data</span>
                <form action="{{ route('admin.restaurants.exit') }}" method="POST" class="inline">
                    @csrf
                    <button class="text-xs font-bold underline hover:no-underline">Exit</button>
                </form>
            </div>
        @endif

        <!-- Success Message -->
        @if(session('success'))
            <div
                class="bg-gradient-to-r from-green-50 to-emerald-50 text-green-700 text-sm px-6 py-3 border-b border-green-200 flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-auto">
            @yield('content')
        </main>
    </div>