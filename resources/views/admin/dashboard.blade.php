@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
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

<p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-2">Platform</p>
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Active businesses</p>
        <p class="text-2xl font-display font-bold text-hut-dark">{{ $platformStats['total_active_businesses'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Trials expiring this week</p>
        <p class="text-2xl font-display font-bold {{ $platformStats['trials_expiring_this_week'] > 0 ? 'text-hut-yellow-dark' : 'text-hut-dark' }}">{{ $platformStats['trials_expiring_this_week'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Overdue subscriptions</p>
        <p class="text-2xl font-display font-bold {{ $platformStats['overdue_subscriptions'] > 0 ? 'text-hut-red' : 'text-hut-dark' }}">{{ $platformStats['overdue_subscriptions'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Revenue this month</p>
        <p class="text-2xl font-display font-bold text-hut-green">Rs. {{ number_format($platformStats['revenue_this_month']) }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">New feedback/suggestions</p>
        <p class="text-2xl font-display font-bold text-hut-dark">{{ $platformStats['new_feedback'] }}</p>
    </div>
</div>

@if($bestSeller)
<div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 mb-6">
    <p class="text-sm text-gray-500">🔥 Best seller today: <span class="font-semibold text-hut-dark">{{ $bestSeller->item_name }}</span> ({{ $bestSeller->total_qty }} sold)</p>
</div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-100 font-display font-semibold text-hut-dark">Recent orders (all businesses)</div>
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
            <tr class="hover:bg-gray-50">
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
