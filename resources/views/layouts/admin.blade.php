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
    @php
        $user = auth()->user();
        $isSuperAdmin = $user && $user->isSuperAdmin();
        $impersonatedRestaurant = $isSuperAdmin ? \App\Support\Tenancy::impersonatedRestaurant() : null;
        $restaurant = $impersonatedRestaurant ?? ($user ? $user->restaurant : null);
        // Dashboard link + module nav follow whichever "hat" the user is
        // currently wearing — real restaurant staff always see the manager
        // nav; a super admin sees the platform nav UNLESS they've entered a
        // restaurant, in which case they see exactly what that restaurant's
        // own manager would see. Logging out, though, always ends the real
        // auth session regardless of impersonation state.
        $showManagerNav = ! $isSuperAdmin || $impersonatedRestaurant;
        $navPrefix = $showManagerNav ? 'manager' : 'admin';
        $logoutRoute = $isSuperAdmin ? 'admin.logout' : 'manager.logout';
        $moduleEnabled = fn ($key) => $user instanceof \App\Models\User && $user->hasModuleAccess($key);
    @endphp

    <aside class="w-56 bg-hut-dark text-white flex-shrink-0 hidden md:flex flex-col">
        <div class="p-4 border-b border-white/10 flex items-center gap-2">
            @if($restaurant)
                <div class="w-9 h-9 bg-hut-yellow rounded-full flex items-center justify-center font-display font-bold text-hut-dark">
                    {{ strtoupper(substr($restaurant->name, 0, 2)) }}
                </div>
                <span class="font-display font-semibold">{{ $restaurant->name }}</span>
            @else
                <div class="w-9 h-9 bg-hut-yellow rounded-full flex items-center justify-center font-display font-bold text-hut-dark">A</div>
                <span class="font-display font-semibold">Platform Admin</span>
            @endif
        </div>
        <nav class="flex-1 p-3 space-y-1 text-sm">
            <a href="{{ route($navPrefix . '.dashboard') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs($navPrefix . '.dashboard') ? 'bg-white/10 text-hut-yellow' : '' }}">📊 Dashboard</a>

            @if(! $showManagerNav)
                <div class="pt-2 pb-1">
                    <p class="px-3 py-1 text-xs text-gray-400 uppercase tracking-wide font-semibold">Platform</p>
                </div>
                <a href="{{ route('admin.restaurants.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.restaurants.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🏪 Restaurants &amp; Businesses</a>
                <a href="{{ route('admin.restaurants.create') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.restaurants.create') ? 'bg-white/10 text-hut-yellow' : '' }}">➕ Register Restaurant / Business</a>
                <a href="{{ route('admin.business-types.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.business-types.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🏷️ Business Types</a>
                <a href="{{ route('admin.modules.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.modules.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🧩 Modules</a>
                <a href="{{ route('admin.subscription-plans.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.subscription-plans.*') ? 'bg-white/10 text-hut-yellow' : '' }}">💳 Subscription Plans</a>
                <a href="{{ route('admin.feedback.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.feedback.*') ? 'bg-white/10 text-hut-yellow' : '' }}">💬 Feedback</a>
            @else
                @if($moduleEnabled('orders'))
                    <a href="{{ route('manager.orders.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.orders.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🛒 Orders</a>
                @endif

                @if($moduleEnabled('pos'))
                    <a href="{{ route('manager.pos.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.pos.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🧾 {{ $restaurant?->getPosConfig()['title'] ?? 'POS' }}</a>
                @endif

                @if($moduleEnabled('medical'))
                    <div class="pt-2 pb-1">
                        <p class="px-3 py-1 text-xs text-gray-400 uppercase tracking-wide font-semibold">Medical</p>
                    </div>
                    <a href="{{ route('manager.medicines.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.medicines.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🧬 Medicines</a>
                    <a href="{{ route('manager.purchases.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.purchases.*') ? 'bg-white/10 text-hut-yellow' : '' }}">📦 Purchase Batches</a>
                    <a href="{{ route('manager.suppliers.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.suppliers.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🏭 Suppliers</a>
                    <a href="{{ route('manager.prescriptions.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.prescriptions.*') ? 'bg-white/10 text-hut-yellow' : '' }}">📋 Prescriptions</a>
                    <a href="{{ route('manager.batch-recalls.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.batch-recalls.*') ? 'bg-white/10 text-hut-yellow' : '' }}">⚠️ Batch Recalls</a>
                    <a href="{{ route('manager.customer-allergies.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.customer-allergies.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🧪 Customer Allergies</a>
                    <a href="{{ route('manager.medicine-interactions.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.medicine-interactions.*') ? 'bg-white/10 text-hut-yellow' : '' }}">💊 Drug Interactions</a>
                    <a href="{{ route('manager.medical-records.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.medical-records.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🩺 Medical Records</a>
                    <a href="{{ route('manager.medical-reports.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.medical-reports.*') ? 'bg-white/10 text-hut-yellow' : '' }}">📊 Medical Reports</a>
                @endif

                @if($moduleEnabled('menu') || $moduleEnabled('categories') || $moduleEnabled('deals'))
                    <div class="pt-2 pb-1">
                        <p class="px-3 py-1 text-xs text-gray-400 uppercase tracking-wide font-semibold">Menu</p>
                    </div>
                    @if($moduleEnabled('categories'))
                        <a href="{{ route('manager.categories.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.categories.*') ? 'bg-white/10 text-hut-yellow' : '' }}">📂 Categories</a>
                    @endif
                    @if($moduleEnabled('menu'))
                        <a href="{{ route('manager.menu-items.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.menu-items.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🍽️ Menu Items</a>
                        <a href="{{ route('manager.toppings.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.toppings.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🧂 Toppings</a>
                        @if($moduleEnabled('variants'))
                            <a href="{{ route('manager.menu-items.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.menu-items.variants.*') || request()->routeIs('manager.menu-items.attributes.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🧩 Variants</a>
                        @endif
                        @if($moduleEnabled('tables'))
                            <a href="{{ route('manager.tables.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.tables.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🪑 Tables</a>
                        @endif
                    @endif
                    @if($moduleEnabled('deals'))
                        <a href="{{ route('manager.deals.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.deals.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🎁 Deals</a>
                    @endif
                @endif

                @if($moduleEnabled('cashbook') || $moduleEnabled('expenses'))
                    <div class="pt-2 pb-1">
                        <p class="px-3 py-1 text-xs text-gray-400 uppercase tracking-wide font-semibold">Financial</p>
                    </div>
                    @if($moduleEnabled('cashbook'))
                        <a href="{{ route('manager.cashbook.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.cashbook.*') ? 'bg-white/10 text-hut-yellow' : '' }}">💰 Cashbook</a>
                    @endif
                    @if($moduleEnabled('expenses'))
                        <a href="{{ route('manager.expenses.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.expenses.*') ? 'bg-white/10 text-hut-yellow' : '' }}">💸 Expenses</a>
                    @endif
                @endif

                @if($moduleEnabled('stock'))
                    <div class="pt-2 pb-1">
                        <p class="px-3 py-1 text-xs text-gray-400 uppercase tracking-wide font-semibold">Inventory</p>
                    </div>
                    <a href="{{ route('manager.stock.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.stock.*') ? 'bg-white/10 text-hut-yellow' : '' }}">📦 Stock Management</a>
                @endif

                @if($moduleEnabled('hr') || $moduleEnabled('staff') || $moduleEnabled('attendance') || $moduleEnabled('salary'))
                    <div class="pt-2 pb-1">
                        <p class="px-3 py-1 text-xs text-gray-400 uppercase tracking-wide font-semibold">HR</p>
                    </div>
                    @if($moduleEnabled('hr') || $moduleEnabled('staff'))
                        <a href="{{ route('manager.staff.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.staff.*') ? 'bg-white/10 text-hut-yellow' : '' }}">👥 Staff &amp; Module Access</a>
                    @endif
                    @if($moduleEnabled('hr') || $moduleEnabled('attendance'))
                        <a href="{{ route('manager.attendance.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.attendance.*') ? 'bg-white/10 text-hut-yellow' : '' }}">✓ Attendance</a>
                    @endif
                    @if($moduleEnabled('hr') || $moduleEnabled('salary'))
                        <a href="{{ route('manager.salary.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.salary.*') ? 'bg-white/10 text-hut-yellow' : '' }}">💵 Salary</a>
                    @endif
                @endif

                <div class="pt-2 pb-1">
                    <p class="px-3 py-1 text-xs text-gray-400 uppercase tracking-wide font-semibold">Restaurant</p>
                </div>
                <a href="{{ route('manager.restaurant.profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.restaurant.profile.*') ? 'bg-white/10 text-hut-yellow' : '' }}">🏠 My Restaurant</a>
                @if($moduleEnabled('reports'))
                    <a href="{{ route('manager.reports.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.reports.*') ? 'bg-white/10 text-hut-yellow' : '' }}">📈 Reports</a>
                @endif
                @if($moduleEnabled('customers'))
                    <a href="{{ route('manager.customers.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.customers.*') ? 'bg-white/10 text-hut-yellow' : '' }}">👤 Customers</a>
                @endif
                @if($moduleEnabled('feedback'))
                    <a href="{{ route('manager.feedback.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('manager.feedback.*') ? 'bg-white/10 text-hut-yellow' : '' }}">💬 Feedback</a>
                @endif
            @endif
        </nav>
        <form action="{{ route($logoutRoute) }}" method="POST" class="p-3 border-t border-white/10">
            @csrf
            <button class="text-sm text-gray-300 hover:text-white">⏻ Log out</button>
        </form>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-gradient-to-r from-hut-dark to-gray-800 border-b border-hut-yellow/20 px-4 py-4 flex justify-between items-center shadow-md">
            <div class="flex items-center gap-4">
                <div>
                    <h1 class="font-display font-bold text-white text-xl">@yield('title', 'Dashboard')</h1>
                    <p class="text-xs text-gray-300 mt-1">
                        @if($impersonatedRestaurant)
                            Managing {{ $impersonatedRestaurant->name }} (as Super Admin)
                        @elseif($isSuperAdmin)
                            Platform Administration
                        @else
                            Restaurant Management System
                        @endif
                    </p>
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

        @if($impersonatedRestaurant)
            <div class="bg-hut-yellow/20 text-hut-dark text-sm px-4 py-2 border-b border-hut-yellow/40 flex items-center justify-between">
                <span>🔎 You're managing <strong>{{ $impersonatedRestaurant->name }}</strong> as Super Admin — changes here affect this restaurant's real data.</span>
                <form action="{{ route('admin.restaurants.exit') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs font-semibold underline hover:no-underline">Exit to platform view</button>
                </form>
            </div>
        @endif

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
