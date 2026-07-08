@extends('layouts.customer')

@section('title', 'Create Account — Taste Hut')

@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="menu-card p-6">
        <h1 class="text-xl font-display font-bold text-hut-dark mb-1">Create your account</h1>
        <p class="text-sm text-gray-500 mb-5">Faster checkout and a full history of every order you place.</p>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('customer.register.attempt') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-gray-700">Full name</label>
                <input type="text" name="name" required autofocus value="{{ old('name') }}"
                    class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Phone number</label>
                <input type="tel" name="phone" required value="{{ old('phone') }}" placeholder="03XX-XXXXXXX"
                    class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Email (optional)</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required
                    class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Confirm password</label>
                <input type="password" name="password_confirmation" required
                    class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
            </div>
            <button type="submit" class="btn-primary w-full">Create account</button>
        </form>

        <p class="text-sm text-gray-500 mt-5 text-center">
            Already have an account? <a href="{{ route('customer.login') }}" class="text-hut-green font-medium hover:underline">Login</a>
        </p>
        <p class="text-sm text-gray-400 mt-2 text-center">
            Or <a href="{{ route('checkout') }}" class="hover:underline">continue as guest</a> — no account needed to order.
        </p>
    </div>
</div>
@endsection
