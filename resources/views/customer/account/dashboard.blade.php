@extends('layouts.customer')

@section('title', 'My Orders — ' . ($customer->restaurant->name ?? 'CodeIbex'))

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">My orders</h1>
            <p class="text-sm text-gray-500">Welcome back, {{ $customer->name }}</p>
        </div>
        <form method="POST" action="{{ route('customer.logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-hut-dark bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded">Logout</button>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-hut-green/10 border border-hut-green/30 text-hut-dark text-sm rounded-lg p-3 mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($orders->isEmpty())
        <div class="menu-card p-8 text-center">
            <p class="text-gray-500">You haven't placed any orders yet.</p>
            <a href="{{ route('home') }}" class="btn-primary inline-block mt-4">Browse the menu</a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($orders as $order)
            <a href="{{ route('orders.track', $order->tracking_token) }}" class="menu-card p-4 flex items-center justify-between hover:border-hut-green/40 transition-colors">
                <div>
                    <p class="font-semibold text-hut-dark">{{ $order->order_number }}</p>
                    <p class="text-xs text-gray-500">{{ $order->created_at->format('d M Y, h:i A') }} • {{ str_replace('_', ' ', ucfirst($order->order_type)) }}</p>
                </div>
                <div class="text-right">
                    <span class="badge-status bg-hut-yellow/20 text-hut-yellow-dark">{{ $order->status_label ?? ucfirst($order->status) }}</span>
                    <p class="font-bold text-hut-green mt-1">Rs. {{ number_format($order->total) }}</p>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
