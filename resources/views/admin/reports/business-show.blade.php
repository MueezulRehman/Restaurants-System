@extends('layouts.admin')
@section('title', $restaurant->name . ' Report')

@section('content')
<div class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
    <div>
        <h2 class="text-2xl font-semibold text-hut-dark">{{ $restaurant->name }}</h2>
        <p class="text-sm text-gray-500">{{ $restaurant->businessType?->name }} · {{ $from }} → {{ $to }}</p>
    </div>
    <a href="{{ route('admin.reports.businesses') }}" class="text-sm text-gray-600 hover:underline">← All businesses</a>
</div>

<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <p class="text-xs text-gray-400">Orders</p>
        <p class="text-2xl font-bold text-hut-dark">{{ $summary['orders'] }}</p>
    </div>
    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <p class="text-xs text-gray-400">Revenue</p>
        <p class="text-2xl font-bold text-hut-green">Rs. {{ number_format($summary['revenue'], 2) }}</p>
    </div>
</div>

<div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3">Order #</th>
                <th class="px-4 py-3">Customer</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Total</th>
                <th class="px-4 py-3">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-medium">{{ $order->order_number }}</td>
                    <td class="px-4 py-3">{{ $order->customer_name }}</td>
                    <td class="px-4 py-3">{{ ucfirst(str_replace('_', ' ', $order->order_type)) }}</td>
                    <td class="px-4 py-3">{{ ucfirst($order->status) }}</td>
                    <td class="px-4 py-3">Rs. {{ number_format($order->total, 2) }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $order->created_at?->format('M d, Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No orders in this range</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $orders->withQueryString()->links() }}</div>
@endsection
