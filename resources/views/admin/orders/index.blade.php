@extends('layouts.admin')
@section('title', 'Orders')

@section('content')

<div class="flex flex-wrap gap-2 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <select name="type" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All types</option>
            @foreach(['dine_in' => 'Dine-in', 'takeaway' => 'Takeaway', 'delivery' => 'Delivery', 'online' => 'Online'] as $val => $label)
                <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All statuses</option>
            @foreach(['pending','confirmed','preparing','ready','out_for_delivery','delivered','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-4 py-2 text-left">Order #</th>
                <th class="px-4 py-2 text-left">Customer</th>
                <th class="px-4 py-2 text-left">Phone</th>
                <th class="px-4 py-2 text-left">Type</th>
                <th class="px-4 py-2 text-left">Status</th>
                <th class="px-4 py-2 text-left">Placed</th>
                <th class="px-4 py-2 text-right">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($orders as $order)
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                <td class="px-4 py-2 font-medium text-hut-dark">{{ $order->order_number }}</td>
                <td class="px-4 py-2">{{ $order->customer_name }}</td>
                <td class="px-4 py-2 text-gray-500">{{ $order->customer_phone }}</td>
                <td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $order->order_type) }}</td>
                <td class="px-4 py-2"><span class="badge-status bg-hut-yellow/20 text-hut-yellow-dark">{{ $order->status_label }}</span></td>
                <td class="px-4 py-2 text-gray-400">{{ $order->created_at->format('M d, h:i A') }}</td>
                <td class="px-4 py-2 text-right font-medium">Rs. {{ number_format($order->total) }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No orders found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $orders->links() }}</div>

@endsection
