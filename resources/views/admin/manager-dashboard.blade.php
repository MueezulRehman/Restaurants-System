@extends('layouts.admin')
@section('title', 'My Dashboard')

@section('content')

<div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm mb-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Your Restaurant</p>
            <h2 class="text-2xl font-semibold text-hut-dark">{{ $restaurant->name }}</h2>
            <p class="text-sm text-gray-500">{{ $restaurant->address }}</p>
            <p class="mt-2 text-sm text-gray-600">Phone: {{ $restaurant->phone }} • Email: {{ $restaurant->email }}</p>
        </div>
        <div class="space-y-3 md:text-right">
            @php
                $restLogo = null;
                if ($restaurant->logo_path) {
                    if (file_exists(public_path('images/'.$restaurant->logo_path))) {
                        $restLogo = asset('images/'.$restaurant->logo_path);
                    } elseif (file_exists(public_path($restaurant->logo_path))) {
                        $restLogo = asset($restaurant->logo_path);
                    } else {
                        $restLogo = asset('storage/'.$restaurant->logo_path);
                    }
                }
            @endphp
            @if($restLogo)
                <img src="{{ $restLogo }}" alt="{{ $restaurant->name }} logo" class="h-24 w-24 rounded-full object-cover border border-gray-200" />
            @endif
            <a href="{{ route('menu.restaurant', $restaurant->slug) }}" target="_blank" class="inline-flex items-center justify-center rounded-lg bg-hut-green px-4 py-2 text-sm font-semibold text-white hover:bg-hut-green/90">View public menu</a>
        </div>
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Orders today</p>
        <p class="text-2xl font-display font-bold text-hut-dark">{{ $stats['orders_today'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Revenue today</p>
        <p class="text-2xl font-display font-bold text-hut-green">Rs. {{ number_format($stats['revenue_today']) }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Pending orders</p>
        <p class="text-2xl font-display font-bold text-hut-yellow-dark">{{ $stats['pending_orders'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Expenses today</p>
        <p class="text-2xl font-display font-bold text-hut-red">Rs. {{ number_format($stats['expenses_today']) }}</p>
    </div>
</div>

@if($bestSeller)
<div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 mb-6">
    <p class="text-sm text-gray-500">🔥 Best seller today: <span class="font-semibold text-hut-dark">{{ $bestSeller->item_name }}</span> ({{ $bestSeller->total_qty }} sold)</p>
</div>
@endif

<div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-hut-dark">Accessible manager modules</p>
            <p class="text-sm text-gray-500">These are the modules available to this manager right now.</p>
        </div>
    </div>
    <div class="mt-3 flex flex-wrap gap-2">
        @forelse($enabledModules as $module)
            <span class="rounded-full bg-hut-green/10 px-3 py-1 text-sm font-medium text-hut-green">{{ $module->name }}</span>
        @empty
            <span class="text-sm text-gray-500">No module access has been granted yet.</span>
            @if(auth()->user()?->role === 'admin')
                <a href="{{ route('manager.staff.index') }}" class="text-sm text-hut-green hover:underline">Grant access to managers</a>
            @elseif(auth()->user()?->role === 'manager')
                <p class="text-sm text-gray-500">Ask your restaurant admin to grant you access from Staff management.</p>
            @endif
        @endforelse
    </div>
</div>

@if($enabledModules->isNotEmpty())
<div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <p class="text-sm font-semibold text-hut-dark">Quick access</p>
            <p class="text-sm text-gray-500">Open the manager modules you have permission to use.</p>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @if($enabledModules->contains(fn($module) => $module->key === 'menu'))
        <a href="{{ route('manager.menu-items.index') }}" class="block rounded-2xl border border-gray-200 bg-hut-yellow/5 p-4 hover:border-hut-yellow hover:bg-hut-yellow/10 transition">
            <p class="text-sm text-gray-500">Menu module</p>
            <h3 class="mt-2 text-lg font-semibold text-hut-dark">Manage menu items</h3>
            <p class="mt-2 text-sm text-gray-600">Create items, assign categories, and configure stock, sizes, variants, and toppings.</p>
        </a>
        @endif

        @if($enabledModules->contains(fn($module) => $module->key === 'categories'))
        <a href="{{ route('manager.categories.index') }}" class="block rounded-2xl border border-gray-200 bg-hut-blue/5 p-4 hover:border-hut-blue hover:bg-hut-blue/10 transition">
            <p class="text-sm text-gray-500">Categories</p>
            <h3 class="mt-2 text-lg font-semibold text-hut-dark">Manage categories</h3>
            <p class="mt-2 text-sm text-gray-600">Organize your menu into sections like pizza, drinks, and desserts.</p>
        </a>
        @endif

        @if($enabledModules->contains(fn($module) => $module->key === 'deals'))
        <a href="{{ route('manager.deals.index') }}" class="block rounded-2xl border border-gray-200 bg-hut-green/5 p-4 hover:border-hut-green hover:bg-hut-green/10 transition">
            <p class="text-sm text-gray-500">Deals</p>
            <h3 class="mt-2 text-lg font-semibold text-hut-dark">Manage deals</h3>
            <p class="mt-2 text-sm text-gray-600">Create combo offers and promotions for the menu.</p>
        </a>
        @endif
    </div>
</div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-100 font-display font-semibold text-hut-dark">Recent orders</div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-4 py-2 text-left">Order #</th>
                <th class="px-4 py-2 text-left">Customer</th>
                <th class="px-4 py-2 text-left">Type</th>
                <th class="px-4 py-2 text-left">Status</th>
                <th class="px-4 py-2 text-right">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($recentOrders as $order)
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('manager.orders.show', $order) }}'">
                <td class="px-4 py-2 font-medium text-hut-dark">{{ $order->order_number }}</td>
                <td class="px-4 py-2">{{ $order->customer_name }}</td>
                <td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $order->order_type) }}</td>
                <td class="px-4 py-2"><span class="badge-status bg-hut-yellow/20 text-hut-yellow-dark">{{ $order->status_label }}</span></td>
                <td class="px-4 py-2 text-right font-medium">Rs. {{ number_format($order->total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
