<header class="platform-header sticky top-0 z-50 shadow-lg">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <img src="{{ asset('images/codeibex-mark.svg') }}" alt="{{ $platformName }}"
                class="h-10 w-10 rounded-xl object-cover">
            <div>
                <p class="font-display text-lg font-bold leading-none text-white">{{ $platformName }}</p>
                <p class="text-[10px] tracking-wide text-hut-yellow">{{ $platformTagline }}</p>
            </div>
        </a>
        <nav class="flex items-center gap-5 text-sm">
            <a href="{{ route('home') }}"
                class="hidden text-white transition-colors hover:text-hut-yellow sm:inline">Businesses</a>
            <a href="{{ route('orders.lookup.form') }}"
                class="hidden text-white transition-colors hover:text-hut-yellow sm:inline">Track Order</a>
            @auth('customer')
                <a href="{{ route('account.dashboard') }}"
                    class="hidden text-white transition-colors hover:text-hut-yellow sm:inline">My Orders</a>
            @else
                <a href="{{ route('customer.login') }}"
                    class="text-sm text-white transition-colors hover:text-hut-yellow">Login</a>
            @endauth
            @auth
                <form method="POST"
                    action="{{ auth()->user()->role === 'super_admin' ? route('admin.logout') : route('manager.logout') }}">
                    @csrf
                    <button type="submit" class="rounded bg-white/10 px-3 py-1 text-sm text-white hover:bg-white/20">Staff
                        Logout</button>
                </form>
            @endauth
        </nav>
    </div>
</header>