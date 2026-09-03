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
                <img src="{{ $restLogo }}" alt="{{ $restaurant->name }} logo" class="h-24 w-24 rounded-full object-contain border border-gray-200 bg-white p-1" />
            @endif
            <a href="{{ $restaurant->getPublicUrl() }}" target="_blank" class="inline-flex items-center justify-center rounded-lg bg-hut-green px-4 py-2 text-sm font-semibold text-white hover:bg-hut-green/90">View public menu</a>
        </div>
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Orders today</p>
        <p class="text-2xl font-display font-bold text-hut-dark">{{ $stats['orders_today'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Income today</p>
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

<div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Business Performance</p>
            <h3 class="text-lg font-semibold text-hut-dark">Income, expense and profit summary</h3>
        </div>
    </div>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        @foreach($stats['period_summaries'] as $summary)
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-sm font-semibold text-hut-dark">{{ $summary['label'] }}</p>
                <p class="mt-2 text-xs text-gray-500">Income</p>
                <p class="text-lg font-semibold text-hut-green">Rs. {{ number_format($summary['income']) }}</p>
                <p class="mt-2 text-xs text-gray-500">Expense</p>
                <p class="text-lg font-semibold text-hut-red">Rs. {{ number_format($summary['expense']) }}</p>
                <p class="mt-2 text-xs text-gray-500">Profit</p>
                <p class="text-lg font-semibold {{ $summary['profit'] >= 0 ? 'text-hut-green' : 'text-hut-red' }}">Rs. {{ number_format($summary['profit']) }}</p>
            </div>
        @endforeach
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Low stock items</p>
        <p class="text-2xl font-display font-bold {{ $stats['low_stock_items'] > 0 ? 'text-hut-red' : 'text-hut-dark' }}">{{ $stats['low_stock_items'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Monthly net profit</p>
        <p class="text-2xl font-display font-bold {{ $stats['monthly_net_profit'] >= 0 ? 'text-hut-green' : 'text-hut-red' }}">Rs. {{ number_format($stats['monthly_net_profit']) }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">New customer feedback</p>
        <p class="text-2xl font-display font-bold text-hut-dark">{{ $stats['new_customer_feedback'] }}</p>
    </div>
</div>

@if($bestSeller)
<div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 mb-6">
    <p class="text-sm text-gray-500">🔥 Best seller today: <span class="font-semibold text-hut-dark">{{ $bestSeller->item_name }}</span> ({{ $bestSeller->total_qty }} sold)</p>
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
