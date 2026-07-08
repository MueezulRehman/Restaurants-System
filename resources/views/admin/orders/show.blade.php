@extends('layouts.admin')
@section('title', 'Order ' . $order->order_number)

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="font-display font-bold text-lg text-hut-dark">{{ $order->order_number }}</h2>
                <p class="text-sm text-gray-400">{{ $order->created_at->format('M d, Y · h:i A') }}</p>
            </div>
            <span class="badge-status bg-hut-yellow/20 text-hut-yellow-dark">{{ $order->status_label }}</span>
        </div>

        <div class="divide-y divide-gray-100 mb-4">
            @foreach($order->items as $item)
            <div class="py-2">
                <div class="flex justify-between text-sm">
                    <span>{{ $item->quantity }}x {{ $item->item_name }} {{ $item->size_label ? "({$item->size_label})" : '' }}</span>
                    <span class="font-medium">Rs. {{ number_format($item->total_price) }}</span>
                </div>
                @if($item->toppings->count())
                    <p class="text-xs text-gray-400 ml-4">+ {{ $item->toppings->pluck('topping_name')->join(', ') }}</p>
                @endif
                @if($item->special_request)
                    <p class="text-xs text-hut-red ml-4">Note: {{ $item->special_request }}</p>
                @endif
            </div>
            @endforeach
        </div>

        <div class="border-t border-gray-200 pt-3 space-y-1 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>Rs. {{ number_format($order->subtotal) }}</span></div>
            @if($order->delivery_fee > 0)
            <div class="flex justify-between"><span class="text-gray-500">Delivery fee</span><span>Rs. {{ number_format($order->delivery_fee) }}</span></div>
            @endif
            <div class="flex justify-between font-bold text-hut-green text-base"><span>Total</span><span>Rs. {{ number_format($order->total) }}</span></div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="font-display font-semibold text-hut-dark mb-3 text-sm">Customer</p>
            <p class="text-sm">{{ $order->customer_name }}</p>
            <p class="text-sm text-gray-500">{{ $order->customer_phone }}</p>
            @if($order->address)
                <p class="text-sm text-gray-500 mt-1">{{ $order->address }}</p>
            @endif
            <p class="text-xs text-gray-400 mt-2 capitalize">{{ str_replace('_', ' ', $order->order_type) }} · {{ $order->payment_method }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="font-display font-semibold text-hut-dark mb-3 text-sm">Update status</p>
            <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="space-y-2">
                @csrf
                @method('PATCH')
                <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    @foreach(['pending','confirmed','preparing','ready','out_for_delivery','delivered','cancelled'] as $s)
                        <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary w-full text-sm">Update — notifies customer live</button>
            </form>
        </div>

        <a href="{{ route('orders.track', $order->tracking_token) }}" target="_blank" class="block text-center text-sm text-hut-green underline">View customer tracking page →</a>
    </div>

</div>

@endsection
