@extends('customer.layout.master')

@section('title', 'Login — ' . (($currentRestaurant ?? (app()->bound('restaurant') ? app('restaurant') : null))->name ?? 'CodeIbex'))

@section('page-content')
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
                    <div class="relative mt-1">
                        <input id="customer-password" type="password" name="password" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 pr-16 focus:outline-none focus:border-hut-green">
                        <button type="button" data-password-toggle="customer-password"
                            class="absolute inset-y-0 right-2 px-2 text-xs font-semibold text-gray-500 hover:text-hut-dark"
                            aria-label="Show password">Show</button>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" value="1" class="accent-hut-green" {{ old('remember') ? 'checked' : '' }}> Remember me
                </label>
                <button type="submit" class="btn-primary w-full">Login</button>
            </form>

            <p class="text-sm text-gray-500 mt-5 text-center">
                New here? <a href="{{ route('customer.register') }}"
                    class="text-hut-green font-medium hover:underline">Create an account</a>
            </p>
            <p class="text-sm text-gray-400 mt-2 text-center">
                Or <a href="{{ route('checkout') }}" class="hover:underline">continue as guest</a> — no account needed to
                order.
            </p>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach(button => button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.textContent = visible ? 'Show' : 'Hide';
            button.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
        }));
    </script>
@endpush