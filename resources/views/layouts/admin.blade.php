<!DOCTYPE html>
<html lang="en">

<head>
    <script>
        document.documentElement.dataset.dashboardTheme = localStorage.getItem('codeibex-dashboard-theme') || 'light';
    </script>

    @php
        $user = auth()->user();
        $isSuperAdmin = $user && $user->isSuperAdmin();
        $impersonatedRestaurant = $isSuperAdmin ? \App\Support\Tenancy::impersonatedRestaurant() : null;
        $showManagerNav = !$isSuperAdmin || ($impersonatedRestaurant && request()->is('manager/*'));
        $isManagerPanel = $showManagerNav && request()->is('manager/*');
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
        $managerTheme = $restaurant?->restaurantTheme;
        $customerTheme = $managerTheme?->customerTheme();
        $dashboardLightPalette = $managerTheme?->managerPalette('light') ?? [
            'background' => \App\Models\PlatformSetting::getValue('platform_theme_light', '#E7F0FA'),
            'surface' => \App\Models\PlatformSetting::getValue('platform_theme_light', '#E7F0FA'),
            'accent' => \App\Models\PlatformSetting::getValue('platform_theme_accent', '#7BA4D0'),
            'primary' => \App\Models\PlatformSetting::getValue('platform_theme_primary', '#2E5E99'),
            'dark' => \App\Models\PlatformSetting::getValue('platform_theme_dark', '#0D2440'),
        ];
        $dashboardDarkPalette = $managerTheme?->managerPalette('dark') ?? [
            'background' => '#0F172A',
            'surface' => '#0F172A',
            'accent' => '#93C5FD',
            'primary' => '#1D4ED8',
            'dark' => '#0B1220',
        ];
        $dashboardLight = $dashboardLightPalette['surface'];
        $dashboardAccent = $dashboardLightPalette['accent'];
        $dashboardPrimary = $dashboardLightPalette['primary'];
        $dashboardDark = $dashboardLightPalette['dark'];
        $navPrefix = $showManagerNav ? 'manager' : 'admin';
        $logoutRoute = $isSuperAdmin ? 'admin.logout' : 'manager.logout';
        $moduleEnabled = fn($key) => $user instanceof \App\Models\User && $user->hasModuleAccess($key);
        $recentNotifications = collect();
        $unreadNotificationCount = 0;
        if ($user instanceof \App\Models\User && ($restaurant || $isSuperAdmin)) {
            $recentNotifications = \App\Models\Notification::query()->latest()->limit(5)->get();
            $unreadNotificationCount = \App\Models\Notification::query()->whereNull('read_at')->count();
        }
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ $showManagerNav && $restaurant ? $restaurant->name : $platformName }}</title>
    <link rel="icon" href="{{ asset('images/codeibex-mark.svg') }}" type="image/svg+xml">
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
            color: var(--dashboard-accent);

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

        .dashboard-shell.manager-panel {
            background: var(--dashboard-light);
        }

        .dashboard-shell.manager-panel .dashboard-sidebar,
        .dashboard-shell.manager-panel .dashboard-header {
            background: linear-gradient(135deg, var(--dashboard-dark), color-mix(in srgb, var(--dashboard-primary) 78%, var(--dashboard-dark)));
            backdrop-filter: blur(18px);
        }

        .dashboard-shell.manager-panel main {
            background: var(--dashboard-light);
        }

        .dashboard-shell.manager-panel main>* {
            animation: dashboard-content-in 420ms ease both;
        }

        .dashboard-shell.platform-panel {
            background: #f4f6fa;
        }

        .dashboard-shell.platform-panel .dashboard-sidebar,
        .dashboard-shell.platform-panel .dashboard-header {
            background: linear-gradient(135deg, #172b4d, #2e5e99 58%, #21456f);
        }

        html[data-dashboard-theme="dark"] .dashboard-shell {
            background: var(--dashboard-dark-light);
            color: #e5e7eb;
        }

        html[data-dashboard-theme="dark"] .dashboard-shell .dashboard-sidebar,
        html[data-dashboard-theme="dark"] .dashboard-shell .dashboard-header {
            background: linear-gradient(135deg, var(--dashboard-dark-dark) 0%, color-mix(in srgb, var(--dashboard-dark-primary) 42%, var(--dashboard-dark-dark)) 100%);
        }

        html[data-dashboard-theme="dark"] .dashboard-shell .dashboard-sidebar a[class~="bg-white/20"] {
            background: color-mix(in srgb, var(--dashboard-dark-primary) 34%, var(--dashboard-dark-dark)) !important;
            color: var(--dashboard-dark-accent) !important;
            border-left-color: var(--dashboard-dark-accent);
            box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--dashboard-dark-accent) 24%, transparent), 0 6px 16px rgba(3, 7, 18, .28);
        }

        html[data-dashboard-theme="dark"] .dashboard-shell .dashboard-sidebar a[class~="bg-white/20"] i,
        html[data-dashboard-theme="dark"] .dashboard-shell .dashboard-sidebar a[class~="bg-white/20"] span {
            color: var(--dashboard-dark-accent) !important;
        }

        html[data-dashboard-theme="dark"] .dashboard-shell .dashboard-sidebar nav>a:not([class~="bg-white/20"]),
        html[data-dashboard-theme="dark"] .dashboard-shell .dashboard-sidebar nav>div a:not([class~="bg-white/20"]) {
            color: #D8E4F5;
        }

        html[data-dashboard-theme="dark"] .dashboard-shell .dashboard-sidebar nav>a:not([class~="bg-white/20"]):hover,
        html[data-dashboard-theme="dark"] .dashboard-shell .dashboard-sidebar nav>div a:not([class~="bg-white/20"]):hover {
            background: color-mix(in srgb, var(--dashboard-dark-primary) 18%, transparent);
            color: #FFFFFF;
        }

        html[data-dashboard-theme="dark"] .dashboard-shell main {
            background: var(--dashboard-dark-surface);
        }

        html[data-dashboard-theme="dark"] .dashboard-shell .bg-white,
        html[data-dashboard-theme="dark"] .dashboard-shell .bg-gray-50,
        html[data-dashboard-theme="dark"] .dashboard-shell .bg-slate-50 {
            background-color: var(--dashboard-dark-surface) !important;
            color: #e5e7eb;
        }

        html[data-dashboard-theme="dark"] .dashboard-shell .text-gray-500,
        html[data-dashboard-theme="dark"] .dashboard-shell .text-gray-600,
        html[data-dashboard-theme="dark"] .dashboard-shell .text-gray-700,
        html[data-dashboard-theme="dark"] .dashboard-shell .text-slate-500,
        html[data-dashboard-theme="dark"] .dashboard-shell .text-slate-600 {
            color: #cbd5e1 !important;
        }

        html[data-dashboard-theme="dark"] .dashboard-shell input,
        html[data-dashboard-theme="dark"] .dashboard-shell select,
        html[data-dashboard-theme="dark"] .dashboard-shell textarea {
            background-color: #111827;
            border-color: #475569;
            color: #f8fafc;
        }

        html[data-dashboard-theme="dark"] .dashboard-shell .border-gray-100,
        html[data-dashboard-theme="dark"] .dashboard-shell .border-gray-200,
        html[data-dashboard-theme="dark"] .dashboard-shell .border-gray-300 {
            border-color: #334155 !important;
        }

        html[data-dashboard-theme="dark"] .dashboard-shell .text-hut-dark {
            color: #f8fafc !important;
        }

        html[data-dashboard-theme="dark"] .dashboard-shell .bg-hut-dark {
            background-color: var(--dashboard-dark-primary) !important;
            color: #f8fafc !important;
        }

        html[data-dashboard-theme="dark"] .dashboard-shell .text-gray-400 {
            color: #94a3b8 !important;
        }

        .dashboard-shell .dashboard-header h1 {
            letter-spacing: -0.02em;
        }

        @keyframes dashboard-content-in {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .dashboard-shell.manager-panel main>* {
                animation: none;
            }
        }

        @media (max-width: 767px) {
            .dashboard-shell {
                overflow-x: hidden;
            }

            .dashboard-shell .dashboard-sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                z-index: 60;
                display: none;
                width: min(84vw, 18rem);
                max-width: 18rem;
            }

            .dashboard-shell .dashboard-sidebar.mobile-open {
                display: flex;
            }

            .dashboard-shell .mobile-nav-backdrop {
                display: block;
                position: fixed;
                inset: 0;
                z-index: 50;
                background: rgba(15, 23, 42, .42);
            }

            .dashboard-shell .mobile-nav-backdrop.hidden {
                display: none;
            }

            .dashboard-shell .dashboard-header {
                padding: .75rem 1rem;
            }

            .dashboard-shell .dashboard-header>div {
                gap: .75rem;
            }

            .dashboard-shell .dashboard-header h1 {
                font-size: 1.125rem;
                line-height: 1.35;
            }

            .dashboard-shell .dashboard-header p {
                margin-top: .125rem;
                font-size: .6875rem;
            }

            .dashboard-shell .dashboard-user-details {
                display: none;
            }

            .dashboard-shell main {
                min-width: 0;
                width: 100%;
                padding: 1rem;
            }

            .dashboard-shell main>* {
                min-width: 0;
                max-width: 100%;
            }

            .dashboard-shell main .flex,
            .dashboard-shell main .grid {
                min-width: 0;
            }

            .dashboard-shell main input,
            .dashboard-shell main select,
            .dashboard-shell main textarea,
            .dashboard-shell main button {
                max-width: 100%;
            }

            .dashboard-shell .dashboard-header>div>div:last-child {
                gap: .5rem;
            }

            .dashboard-shell .dashboard-header .border-l {
                padding-left: .5rem;
            }

            .dashboard-shell main table {
                min-width: 42rem;
            }

            .dashboard-shell main .overflow-hidden:has(> table) {
                overflow-x: auto;
            }

            .dashboard-shell .notification-panel {
                position: fixed;
                left: 1rem;
                right: 1rem;
                top: 4.25rem;
                width: auto;
            }
        }
    </style>
</head>

<body class="dashboard-shell {{ $isManagerPanel ? 'manager-panel' : 'platform-panel' }} min-h-screen flex"
    style="{{ $dashboardStyle }} --dashboard-light: {{ $dashboardLight }}; --dashboard-accent: {{ $dashboardAccent }}; --dashboard-primary: {{ $dashboardPrimary }}; --dashboard-dark: {{ $dashboardDark }}; --dashboard-dark-light: {{ $dashboardDarkPalette['background'] }}; --dashboard-dark-surface: {{ $dashboardDarkPalette['surface'] }}; --dashboard-dark-accent: {{ $dashboardDarkPalette['accent'] }}; --dashboard-dark-primary: {{ $dashboardDarkPalette['primary'] }}; --dashboard-dark-dark: {{ $dashboardDarkPalette['dark'] }};">

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
                    <a href="{{ route('admin.notifications.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('admin.notifications.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="flex-1 truncate">Notifications</span>
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
                    @if($moduleEnabled('appointments'))
                        <a href="{{ route('manager.appointments.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.appointments.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-calendar-check text-lg"></i>
                            <span class="flex-1 truncate">Appointments</span>
                        </a>
                    @endif
                    @if($moduleEnabled('memberships'))
                        <a href="{{ route('manager.gym.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.gym.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-id-card text-lg"></i>
                            <span class="flex-1 truncate">Gym Memberships</span>
                        </a>
                    @endif
                    @if($moduleEnabled('commissions'))
                        <a href="{{ route('manager.commissions.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.commissions.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-percent text-lg"></i>
                            <span class="flex-1 truncate">Commissions</span>
                        </a>
                    @endif
                    @if($moduleEnabled('recipes') || $moduleEnabled('production-batches'))
                        <a href="{{ route('manager.recipes.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.recipes.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-book-open text-lg"></i>
                            <span class="flex-1 truncate">Recipes & Production</span>
                        </a>
                    @endif

                    @if($moduleEnabled('sales-returns'))
                        <a href="{{ route('manager.sales-returns.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.sales-returns.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-rotate-left text-lg"></i>
                            <span class="flex-1 truncate">Returns & Exchanges</span>
                        </a>
                    @endif

                    @if($moduleEnabled('purchasing'))
                        <a href="{{ route('manager.purchasing.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.purchasing.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-truck-ramp-box text-lg"></i>
                            <span class="flex-1 truncate">Purchasing</span>
                        </a>
                    @endif
                    @if($moduleEnabled('expiry-tracking'))
                        <a href="{{ route('manager.expiry-tracking.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.expiry-tracking.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-calendar-days text-lg"></i>
                            <span class="flex-1 truncate">Expiry Tracking</span>
                        </a>
                    @endif
                    @if($moduleEnabled('delivery-zones'))
                        <a href="{{ route('manager.delivery-zones.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.delivery-zones.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-map-location-dot text-lg"></i>
                            <span class="flex-1 truncate">Delivery Zones</span>
                        </a>
                    @endif
                    @if($moduleEnabled('coupons'))
                        <a href="{{ route('manager.coupons.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.coupons.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-ticket text-lg"></i>
                            <span class="flex-1 truncate">Coupons</span>
                        </a>
                    @endif
                    @if($moduleEnabled('suppliers'))
                        <a href="{{ route('manager.suppliers.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.suppliers.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-building text-lg"></i>
                            <span class="flex-1 truncate">Suppliers</span>
                        </a>
                    @endif
                    @if($moduleEnabled('warranty') || $moduleEnabled('repairs'))
                        <a href="{{ route('manager.service-cases.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.service-cases.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-screwdriver-wrench text-lg"></i>
                            <span class="flex-1 truncate">Warranty & Repairs</span>
                        </a>
                    @endif
                    @if($moduleEnabled('barcode-labels'))
                        <a href="{{ route('manager.barcode-labels.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.barcode-labels.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}"><i
                                class="fas fa-barcode text-lg"></i><span class="flex-1 truncate">Barcode Labels</span></a>
                    @endif
                    @if($moduleEnabled('profit-margins'))
                        <a href="{{ route('manager.profit-margins.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.profit-margins.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}"><i
                                class="fas fa-chart-line text-lg"></i><span class="flex-1 truncate">Profit Margins</span></a>
                    @endif
                    @if($moduleEnabled('brands') || $moduleEnabled('collections') || $moduleEnabled('loyalty') || $moduleEnabled('stock-transfers') || $moduleEnabled('trade-ins') || $moduleEnabled('installments'))
                        <a href="{{ route('manager.retail-operations.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.retail-operations.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}"><i
                                class="fas fa-toolbox text-lg"></i><span class="flex-1 truncate">Retail Operations</span></a>
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

                    @if($moduleEnabled('item-sales'))
                        <a href="{{ route('manager.item-sales.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.item-sales.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-tags text-lg"></i>
                            <span class="flex-1 truncate">Item Sales</span>
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

                    @if($moduleEnabled('theme'))
                        <div class="pt-4 pb-2">
                            <p class="px-4 py-2 text-xs text-hut-yellow/70 uppercase tracking-widest font-bold">Settings</p>
                        </div>
                        <a href="{{ route('manager.restaurant.profile.edit') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.restaurant.profile.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-cog text-lg"></i>
                            <span class="flex-1 truncate">Settings</span>
                        </a>
                        <a href="{{ route('manager.business.theme.edit') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.business.theme.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-palette text-lg"></i>
                            <span class="flex-1 truncate">Theme</span>
                        </a>
                    @endif
                    @if($moduleEnabled('storefront-notices') || $moduleEnabled('theme'))
                        <a href="{{ route('manager.storefront-notice.edit') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.storefront-notice.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                            <i class="fas fa-bullhorn text-lg"></i>
                            <span class="flex-1 truncate">Storefront Notice</span>
                        </a>
                    @endif
                    <a href="{{ route('manager.notifications.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs('manager.notifications.*') ? 'bg-white/20 text-hut-yellow shadow-lg' : 'text-gray-200 hover:bg-white/10' }}">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="flex-1 truncate">Notifications</span>
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
    <button type="button" id="mobile-nav-backdrop" class="mobile-nav-backdrop hidden md:hidden"
        aria-label="Close navigation"></button>

    <div class="flex-1 flex flex-col min-w-0">
        <!-- Modern Header -->
        <header class="dashboard-header text-white border-b border-white/10 px-6 py-4 shadow-lg backdrop-blur-md">
            <div class="flex justify-between items-center gap-6">
                <div class="flex min-w-0 flex-1 items-start gap-2">
                    <button type="button" id="mobile-nav-toggle"
                        class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/10 text-white hover:bg-white/20 md:hidden"
                        aria-label="Open navigation" aria-expanded="false">
                        <i class="fas fa-bars" aria-hidden="true"></i>
                    </button>
                    <div class="min-w-0">
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
                </div>

                <div class="flex items-center gap-6">
                    <!-- Current Time -->
                    <div class="hidden sm:block text-right text-sm">
                        <p class="font-semibold">{{ now()->format('D, M d') }}</p>
                        <p class="text-gray-400 text-xs">{{ now()->format('g:i A') }}</p>
                    </div>

                    <div class="relative" id="notification-menu">
                        <button type="button" id="notification-menu-button"
                            class="relative flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white hover:bg-white/20"
                            aria-label="Recent notifications" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            @if($unreadNotificationCount)
                                <span
                                    class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                            @endif
                        </button>
                        <div id="notification-menu-panel"
                            class="notification-panel
                            absolute right-0 top-12 z-50 hidden w-80 overflow-hidden rounded-xl border border-gray-200 bg-white text-gray-800 shadow-2xl">
                            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                                <span class="font-semibold">Recent notifications</span>
                                <a href="{{ route($navPrefix . '.notifications.index') }}"
                                    class="text-xs font-medium text-blue-700 hover:underline">View all</a>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @forelse($recentNotifications as $recentNotification)
                                    <a href="{{ route($navPrefix . '.notifications.read.get', $recentNotification) }}"
                                        class="block border-b border-gray-100 px-4 py-3 hover:bg-gray-50 {{ $recentNotification->read_at ? '' : 'bg-amber-50' }}">
                                        <p class="text-sm font-semibold">{{ $recentNotification->title }}</p>
                                        <p class="mt-1 line-clamp-2 text-xs text-gray-600">
                                            {{ $recentNotification->message }}
                                        </p>
                                    </a>
                                @empty
                                    <p class="px-4 py-6 text-center text-sm text-gray-500">No notifications yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <button type="button" id="dashboard-theme-toggle"
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white hover:bg-white/20"
                        aria-label="Switch dashboard theme" title="Switch dashboard theme">
                        <i class="fas fa-moon" aria-hidden="true"></i>
                    </button>

                    <!-- User Profile -->
                    <div class="flex items-center gap-3 pl-6 border-l border-white/10">
                        <div class="dashboard-user-details text-right">
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

    <div id="admin-confirm-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/25 p-4 backdrop-blur-sm"
        aria-hidden="true">
        <div class="w-full max-w-md rounded-2xl border border-white/70 bg-white/85 p-6 shadow-2xl backdrop-blur-xl"
            role="dialog" aria-modal="true" aria-labelledby="admin-confirm-title">
            <div class="mb-5 flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600">
                    <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                </div>
                <div>
                    <h2 id="admin-confirm-title" class="text-lg font-semibold text-hut-dark">Confirm deletion</h2>
                    <p id="admin-confirm-message" class="mt-1 text-sm leading-6 text-slate-600">Are you sure you want to
                        delete this record?</p>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" id="admin-confirm-cancel"
                    class="rounded-lg border border-slate-200 bg-white/80 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-white">
                    Cancel
                </button>
                <button type="button" id="admin-confirm-submit"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const toggle = document.getElementById('mobile-nav-toggle');
            const sidebar = document.querySelector('.dashboard-sidebar');
            const backdrop = document.getElementById('mobile-nav-backdrop');
            if (!toggle || !sidebar || !backdrop) return;
            const close = () => {
                sidebar.classList.remove('mobile-open');
                backdrop.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.setAttribute('aria-label', 'Open navigation');
                toggle.querySelector('i').className = 'fas fa-bars';
            };
            toggle.addEventListener('click', function () {
                const open = !sidebar.classList.contains('mobile-open');
                sidebar.classList.toggle('mobile-open', open);
                backdrop.classList.toggle('hidden', !open);
                toggle.setAttribute('aria-expanded', String(open));
                toggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
                toggle.querySelector('i').className = open ? 'fas fa-xmark' : 'fas fa-bars';
            });
            backdrop.addEventListener('click', close);
            sidebar.querySelectorAll('a').forEach(link => link.addEventListener('click', close));
        })();
    </script>

    <script>
        (function () {
            const button = document.getElementById('notification-menu-button');
            const panel = document.getElementById('notification-menu-panel');
            if (!button || !panel) return;
            button.addEventListener('click', function () {
                const isHidden = panel.classList.toggle('hidden');
                button.setAttribute('aria-expanded', String(!isHidden));
            });
            document.addEventListener('click', function (event) {
                if (!event.target.closest('#notification-menu')) {
                    panel.classList.add('hidden');
                    button.setAttribute('aria-expanded', 'false');
                }
            });
        })();
    </script>

    @stack('scripts')

    <script>
        (function () {
            const button = document.getElementById('dashboard-theme-toggle');
            if (!button) return;
            const icon = button.querySelector('i');

            function applyTheme(theme) {
                document.documentElement.dataset.dashboardTheme = theme;
                localStorage.setItem('codeibex-dashboard-theme', theme);
                const dark = theme === 'dark';
                icon.className = dark ? 'fas fa-sun' : 'fas fa-moon';
                button.setAttribute('aria-label', dark ? 'Switch to light theme' : 'Switch to dark theme');
                button.title = dark ? 'Switch to light theme' : 'Switch to dark theme';
            }

            applyTheme(document.documentElement.dataset.dashboardTheme || 'light');
            button.addEventListener('click', () => {
                applyTheme(document.documentElement.dataset.dashboardTheme === 'dark' ? 'light' : 'dark');
            });
        })();
    </script>

    @include('partials.manager-new-order-listener')

    <script>
        (() => {
            const modal = document.getElementById('admin-confirm-modal');
            const message = document.getElementById('admin-confirm-message');
            const cancel = document.getElementById('admin-confirm-cancel');
            const submit = document.getElementById('admin-confirm-submit');
            let activeForm = null;

            const close = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
                activeForm = null;
            };

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-confirm]');
                const form = trigger?.matches('form') ? trigger : trigger?.form;
                if (!form) return;

                event.preventDefault();
                activeForm = form;
                message.textContent = trigger.getAttribute('data-confirm') || 'Are you sure you want to continue?';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.setAttribute('aria-hidden', 'false');
                cancel.focus();
            });

            cancel.addEventListener('click', close);
            submit.addEventListener('click', () => {
                if (!activeForm) return;
                const form = activeForm;
                close();
                form.submit();
            });
            modal.addEventListener('click', (event) => {
                if (event.target === modal) close();
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) close();
            });
        })();
    </script>