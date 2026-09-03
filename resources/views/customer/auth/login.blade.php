@extends('layouts.customer')

@section('title', 'Login — ' . (($currentRestaurant ?? (app()->bound('restaurant') ? app('restaurant') : null))->name ?? 'CodeIbex'))

@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="menu-card p-6">
        <h1 class="text-xl font-display font-bold text-hut-dark mb-1">Login to your account</h1>
        <p class="text-sm text-gray-500 mb-5">See your past orders and check out faster next time.</p>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('customer.login.attempt') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-gray-700">Phone number</label>
                <input type="tel" name="phone" required autofocus value="{{ old('phone') }}" placeholder="03XX-XXXXXXX"
                    class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required
                    class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="accent-hut-green"> Remember me
            </label>
            <button type="submit" class="btn-primary w-full">Login</button>
        </form>

        <p class="text-sm text-gray-500 mt-5 text-center">
            New here? <a href="{{ route('customer.register') }}" class="text-hut-green font-medium hover:underline">Create an account</a>
        </p>
        <p class="text-sm text-gray-400 mt-2 text-center">
            Or <a href="{{ route('checkout') }}" class="hover:underline">continue as guest</a> — no account needed to order.
        </p>
    </div>
</div>
@endsection
