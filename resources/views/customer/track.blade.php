@extends('layouts.customer')

@section('title', 'Track Order ' . $order->order_number . ' — Taste Hut')

@section('content')
<div class="max-w-xl mx-auto px-4 py-8">

    <div class="menu-card p-6 text-center">
        <p class="text-xs text-gray-400 uppercase tracking-wide">Order</p>
        <h1 class="text-2xl font-display font-bold text-hut-dark mb-1">{{ $order->order_number }}</h1>
        <p class="text-sm text-gray-500 mb-6">Placed {{ $order->created_at->diffForHumans() }}</p>

        @if($order->status === 'cancelled')
            <div class="bg-red-50 text-red-700 rounded-lg p-4 font-medium">This order was cancelled.</div>
        @else
            <p id="status-label" class="text-lg font-display font-semibold text-hut-green mb-1">{{ $order->status_label }}</p>
            <p class="text-sm text-gray-500 mb-4">
                Estimated time: <span id="eta-label" class="font-medium">{{ $order->estimated_minutes }} mins</span>
            </p>

            <!-- Progress bar -->
            <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden mb-2">
                <div id="progress-bar" class="bg-hut-green h-3 rounded-full transition-all duration-700 ease-out" style="width: {{ $order->progress_percent }}%"></div>
            </div>
            <p id="progress-percent-label" class="text-xs text-gray-400 mb-6">{{ $order->progress_percent }}% complete</p>

            <!-- Step labels -->
            <div class="flex justify-between text-[10px] text-gray-400 mb-6">
                @php
                    $steps = $order->order_type === 'delivery'
                        ? ['confirmed' => 'Confirmed', 'preparing' => 'Preparing', 'ready' => 'Ready', 'out_for_delivery' => 'Out for delivery', 'delivered' => 'Delivered']
                        : ['confirmed' => 'Confirmed', 'preparing' => 'Preparing', 'ready' => 'Ready', 'delivered' => 'Picked up'];
                @endphp
                @foreach($steps as $key => $label)
                    <span class="{{ array_search($order->status, array_keys($steps)) >= $loop->index ? 'text-hut-green font-medium' : '' }}">{{ $label }}</span>
                @endforeach
            </div>
        @endif

        <div class="text-left bg-hut-cream rounded-lg p-4 mt-2">
            <p class="font-display font-semibold text-hut-dark mb-2 text-sm">Order details</p>
            @foreach($order->items as $item)
                <div class="flex justify-between text-sm py-1">
                    <span>{{ $item->quantity }}x {{ $item->item_name }} {{ $item->size_label ? "({$item->size_label})" : '' }}</span>
                    <span class="text-gray-500">Rs. {{ number_format($item->total_price) }}</span>
                </div>
            @endforeach
            <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between text-sm font-semibold">
                <span>Total</span>
                <span class="text-hut-green">Rs. {{ number_format($order->total) }}</span>
            </div>
        </div>

        <div class="text-left text-sm text-gray-500 mt-4 space-y-1">
            <p><span class="font-medium text-gray-700">Type:</span> {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}</p>
            @if($order->address)
                <p><span class="font-medium text-gray-700">Address:</span> {{ $order->address }}</p>
            @endif
        </div>
    </div>

    <p class="text-center text-xs text-gray-400 mt-4">
        Bookmark this page to keep tracking your order — no login needed.
    </p>
</div>

@push('scripts')
<script>
// Subscribes to a PRIVATE channel keyed by this order's unguessable tracking
// token. Only someone with this exact URL (i.e. the customer who placed it)
// ever connects to this channel, so live updates can never be observed by
// other customers.
window.Echo.private('order.{{ $order->tracking_token }}')
    .listen('.status.updated', (e) => {
        document.getElementById('status-label').textContent = e.status_label;
        document.getElementById('eta-label').textContent = e.estimated_minutes + ' mins';
        document.getElementById('progress-bar').style.width = e.progress_percent + '%';
        document.getElementById('progress-percent-label').textContent = e.progress_percent + '% complete';

        if (e.status === 'delivered') {
            document.getElementById('eta-label').textContent = 'Completed';
        }
    });

@if(session('success'))
    localStorage.removeItem('th_cart');
@endif
</script>
@endpush
@endsection
