@extends('layouts.customer')

@section('title', 'Track Your Order — Taste Hut')

@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="menu-card p-6">
        <h1 class="text-xl font-display font-bold text-hut-dark mb-1">Track your order</h1>
        <p class="text-sm text-gray-500 mb-5">Enter your order number and the phone number you used.</p>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('orders.lookup') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-gray-700">Order number</label>
                <input type="text" name="order_number" required placeholder="TH-20260630-0001"
                    class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Phone number</label>
                <input type="tel" name="customer_phone" required placeholder="03XX-XXXXXXX"
                    class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
            </div>
            <button type="submit" class="btn-primary w-full">Track order</button>
        </form>
    </div>
</div>
@endsection
