@extends('layouts.admin')

@section('title', 'Customer Details')

@section('content')
<div class="space-y-4">
    <div>
        <a href="{{ route('manager.customers.index') }}" class="text-sm text-gray-500 hover:text-hut-dark">← Back</a>
        <h2 class="mt-2 text-xl font-semibold text-hut-dark">{{ $customer->name }}</h2>
        <p class="text-sm text-gray-500">Phone: {{ $customer->phone }} · Email: {{ $customer->email ?: '—' }}</p>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Order #</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Total</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($customer->orders as $order)
                        <tr>
                            <td class="px-4 py-3 font-medium text-hut-dark">{{ $order->order_number }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $order->status_label }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ number_format($order->total, 2) }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $order->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">No orders found for this customer.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
