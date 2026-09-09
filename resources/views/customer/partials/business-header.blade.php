<header class="business-header sticky top-0 z-50 shadow-lg">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
        <a href="{{ route('menu.restaurant', $restaurant->slug) }}" class="flex items-center gap-2">
            @if(!empty($restaurant->logo_path))
                <img src="{{ asset('storage/' . $restaurant->logo_path) }}" alt="{{ $restaurant->name }}"
                    class="h-10 w-10 rounded-full object-cover">
            @else
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-hut-yellow font-display text-lg font-bold text-hut-dark">
                    {{ strtoupper(substr($restaurant->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <p class="font-display text-lg font-bold leading-none text-white">{{ $restaurant->name }}</p>
                @if(!empty($restaurant->tagline))
                    <p class="text-[10px] tracking-wide text-hut-yellow">{{ $restaurant->tagline }}</p>
                @endif
            </div>
        </a>
        <nav class="flex items-center gap-5 text-sm">
            <a href="{{ route('menu.restaurant', $restaurant->slug) }}"
                class="hidden text-white transition-colors hover:text-hut-yellow sm:inline">Menu</a>
            <a href="{{ route('orders.lookup.form') }}"
                class="hidden text-white transition-colors hover:text-hut-yellow sm:inline">Track Order</a>
            @if(!empty($restaurant->phone))
                <a href="tel:{{ preg_replace('/\D+/', '', $restaurant->phone) }}"
                    class="hidden text-white transition-colors hover:text-hut-yellow md:inline">{{ $restaurant->phone }}</a>
            @endif
            <a id="checkout-link" href="{{ route('checkout') }}" class="relative hidden">
                <button class="btn-accent flex items-center gap-1.5 !px-4 !py-2" type="button">
                    <span aria-hidden="true">🛒</span>
                    <span id="cart-count-badge"
                        class="flex h-5 w-5 items-center justify-center rounded-full bg-hut-dark text-xs text-white">0</span>
                </button>
            </a>
            @auth('customer')
                <a href="{{ route('account.dashboard') }}"
                    class="hidden text-white transition-colors hover:text-hut-yellow sm:inline">My Orders</a>
                <form method="POST" action="{{ route('customer.logout') }}">
                    @csrf
                    <button type="submit"
                        class="rounded bg-white/10 px-3 py-1 text-sm text-white hover:bg-white/20">Logout</button>
                </form>
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