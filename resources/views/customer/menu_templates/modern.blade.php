@extends('layouts.customer')

@section('title', 'Menu — ' . ($currentRestaurant->name ?? 'CodeIbex'))

@section('content')

    @php
        $logoUrl = null;
        $restaurantInitials = null;
        if (optional($currentRestaurant)->name) {
            $restaurantInitials = collect(explode(' ', trim($currentRestaurant->name)))
                ->take(2)
                ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                ->implode('');
        }
        if (optional($currentRestaurant)->logo_path) {
            $lp = $currentRestaurant->logo_path;
            if (Str::startsWith($lp, ['http://', 'https://'])) {
                $logoUrl = $lp;
            } elseif (file_exists(public_path('images/' . $lp))) {
                $logoUrl = asset('images/' . $lp);
            } elseif (file_exists(public_path($lp))) {
                $logoUrl = asset($lp);
            } else {
                $logoUrl = asset('storage/' . $lp);
            }
        }
        $visibleCategories = $categories->filter(fn($c) => $c->availableMenuItems->count() > 0);
        $restaurantUrl = $currentRestaurant->getPublicUrl();
        $dealPosterFiles = collect(glob(public_path('images/deals/*')) ?: [])
            ->filter(fn($path) => is_file($path))
            ->sort()
            ->values();
        $resolveMenuImage = function (?string $path): ?string {
            if (!$path) {
                return null;
            }
            if (Str::startsWith($path, ['http://', 'https://'])) {
                return $path;
            }
            if (is_file(public_path('images/' . $path))) {
                return asset('images/' . $path);
            }
            if (is_file(public_path($path))) {
                return asset($path);
            }
            if (is_file(storage_path('app/public/' . $path))) {
                return asset('storage/' . $path);
            }
            return null;
        };
        $heroImageUrl = null;
        $heroDeal = $deals->first(fn($deal) => !empty($deal->image));
        if ($heroDeal) {
            $heroImageUrl = $resolveMenuImage($heroDeal->image);
            if (!$heroImageUrl) {
                $heroPoster = $dealPosterFiles->get(max(0, ((int) $heroDeal->deal_number) - 1));
                $heroImageUrl = $heroPoster ? asset('images/deals/' . basename($heroPoster)) : null;
            }
        }
    @endphp

    {{-- ============ HERO ============ --}}
    <section class="menu-hero relative overflow-hidden bg-hut-dark" @if($heroImageUrl)
    style="--menu-hero-image: url('{{ $heroImageUrl }}');" @endif>
        <div class="menu-hero__grain"></div>
        @if($heroImageUrl)
            <div class="menu-hero__image" aria-hidden="true"></div>
        @endif
        <div class="menu-hero__glow menu-hero__glow--yellow"></div>
        <div class="menu-hero__glow menu-hero__glow--green"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 pt-14 pb-10 text-center">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $currentRestaurant->name }} logo"
                    class="mx-auto mb-5 h-28 w-28 rounded-full border-4 border-hut-yellow/80 object-cover shadow-lg shadow-black/30 animate-hero-pop"
                    style="object-fit: cover; object-position: center;">
            @elseif($restaurantInitials)
                <div
                    class="mx-auto mb-5 flex h-28 w-28 items-center justify-center rounded-full border-4 border-hut-yellow/80 bg-white/5 text-4xl font-display font-bold text-hut-yellow shadow-lg shadow-black/30 animate-hero-pop">
                    {{ $restaurantInitials }}
                </div>
            @endif

            <p class="uppercase tracking-[0.25em] text-hut-yellow/80 text-xs font-semibold mb-3">Order Online · Pickup or
                Delivery</p>
            <h1 class="text-4xl md:text-5xl font-display font-bold text-white mb-3 leading-tight">
                {{ $currentRestaurant->name ?? 'Our Restaurant' }}
            </h1>
            <p class="text-white/70 max-w-md mx-auto text-sm md:text-base">
                {{ $currentRestaurant->address ?? 'Available for pickup & delivery' }}
            </p>

            @if($restaurantUrl)
                <p class="text-xs text-white/40 mt-2">
                    <a href="{{ $restaurantUrl }}"
                        class="underline decoration-white/30 hover:text-hut-yellow hover:decoration-hut-yellow transition-colors">{{ parse_url($restaurantUrl, PHP_URL_HOST) }}</a>
                </p>
            @endif
        </div>
    </section>

    {{-- ============ STICKY CATEGORY NAV (scroll-spy) ============ --}}
    @if($visibleCategories->count() > 1 || $deals->count())
        <nav id="menu-jumpnav" class="sticky top-[64px] z-40 bg-white/95 backdrop-blur border-b border-gray-100 shadow-sm">
            <div class="max-w-5xl mx-auto px-2">
                <div class="flex gap-1 overflow-x-auto no-scrollbar py-2 px-2">
                    @if($deals->count())
                        <a href="#section-deals" data-jump="section-deals"
                            class="jumpnav-pill whitespace-nowrap rounded-full px-4 py-1.5 text-sm font-medium border border-transparent text-gray-500 hover:text-hut-dark transition-colors">🎁
                            Deals</a>
                    @endif
                    @foreach($visibleCategories as $category)
                        <a href="#section-cat-{{ $category->id }}" data-jump="section-cat-{{ $category->id }}"
                            class="jumpnav-pill whitespace-nowrap rounded-full px-4 py-1.5 text-sm font-medium border border-transparent text-gray-500 hover:text-hut-dark transition-colors">
                            {{ $category->icon ?? '📦' }} {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </nav>
    @endif

    {{-- ============ DEALS ============ --}}
    @if($deals->count())
        <section id="section-deals" class="max-w-5xl mx-auto px-4 py-10 scroll-mt-32">
            <div class="flex items-baseline gap-2 mb-5">
                <span class="text-2xl">🎁</span>
                <h2 class="font-display font-bold text-2xl text-hut-dark">Hot Deals</h2>
                <span class="text-xs text-gray-400 font-medium ml-auto">{{ $deals->count() }} available</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($deals as $deal)
                    <div
                        class="menu-card-v2 reveal group relative rounded-2xl bg-white border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        @php
                            $dealImg = $resolveMenuImage($deal->image);
                            if (!$dealImg) {
                                $dealPoster = $dealPosterFiles->get(max(0, ((int) $deal->deal_number) - 1));
                                $dealImg = $dealPoster ? asset('images/deals/' . basename($dealPoster)) : null;
                            }
                        @endphp

                        <div class="relative aspect-[4/3] bg-gradient-to-br from-hut-dark to-gray-800 overflow-hidden">
                            @if($dealImg)
                                <img src="{{ $dealImg }}" alt="{{ $deal->name }}" loading="lazy" decoding="async"
                                    class="w-full h-full object-contain group-hover:scale-[1.03] transition-transform duration-500"
                                    style="object-fit: contain; object-position: center;" />
                            @else
                                <div class="w-full h-full flex items-center justify-center text-5xl text-slate-400 opacity-40"><i
                                        class="fas fa-box-open"></i></div>
                            @endif
                            <div
                                class="absolute top-3 left-3 bg-hut-yellow text-hut-dark font-display font-bold text-sm rounded-full w-9 h-9 flex items-center justify-center shadow-md">
                                {{ $deal->deal_number }}
                            </div>
                            <div
                                class="deal-price absolute bottom-3 right-3 bg-hut-yellow text-hut-dark font-display font-bold text-base px-3 py-1.5 rounded-lg shadow-lg ring-2 ring-white/80">
                                Rs. {{ number_format($deal->price) }}
                            </div>
                        </div>

                        <div class="p-4">
                            <h3 class="font-display font-semibold text-hut-dark leading-snug mb-1">{{ $deal->name }}</h3>
                            <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $deal->description }}</p>
                            <button
                                onclick="addToCart({type:'deal', id:{{ $deal->id }}, name:'{{ addslashes($deal->name) }}', price:{{ $deal->price }}, quantity:1}, this)"
                                class="cart-add-btn w-full rounded-lg bg-hut-dark text-white text-sm font-semibold py-2.5 hover:bg-hut-green transition-colors">
                                Add to cart
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ CATEGORIES ============ --}}
    @foreach($visibleCategories as $category)
        <section id="section-cat-{{ $category->id }}" class="max-w-5xl mx-auto px-4 py-8 scroll-mt-32">
            <div class="flex items-baseline gap-2 mb-5">
                <span class="text-2xl">{{ $category->icon ?? '📦' }}</span>
                <h2 class="font-display font-bold text-2xl text-hut-dark">{{ $category->name }}</h2>
                <span class="text-xs text-gray-400 font-medium ml-auto">{{ $category->availableMenuItems->count() }}
                    items</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($category->availableMenuItems as $item)
                    <div
                        class="menu-card-v2 reveal group relative rounded-2xl bg-white border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        @php
                            $itemImg = $resolveMenuImage($item->image);
                        @endphp

                        <div class="relative aspect-[4/3] bg-gray-50 overflow-hidden">
                            @if($itemImg)
                                <img src="{{ $itemImg }}" alt="{{ $item->name }}" loading="lazy" decoding="async"
                                    class="w-full h-full object-contain group-hover:scale-[1.03] transition-transform duration-500"
                                    style="object-fit: contain; object-position: center;" />
                            @else
                                <div class="w-full h-full flex items-center justify-center text-4xl text-gray-200">
                                    {{ $category->icon ?? '📦' }}
                                </div>
                            @endif
                        </div>

                        <div class="p-4">
                            <h3 class="font-display font-semibold text-hut-dark leading-snug mb-1">{{ $item->name }}</h3>
                            @if($item->description)
                                <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $item->description }}</p>
                            @endif

                            @include('customer.menu_partials.item-variants', ['item' => $item])

                            @if($item->has_sizes)
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @foreach($item->sizes as $size)
                                        <button
                                            onclick="addToCart({type:'menu_item', id:{{ $item->id }}, name:'{{ addslashes($item->name) }}', price:{{ $size->price }}, size_label:'{{ $size->size_label }}', quantity:1}, this)"
                                            class="cart-add-btn text-xs border border-hut-green/50 text-hut-green rounded-lg px-2.5 py-1.5 hover:bg-hut-green hover:text-white hover:border-hut-green transition-colors font-medium">
                                            {{ $size->size_label }} <span class="opacity-70">· Rs. {{ number_format($size->price) }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex justify-between items-center mt-3">
                                    <span class="text-hut-green font-bold font-display">Rs. {{ number_format($item->price) }}</span>
                                    <button
                                        onclick="addToCart({type:'menu_item', id:{{ $item->id }}, name:'{{ addslashes($item->name) }}', price:{{ $item->price }}, quantity:1}, this)"
                                        class="cart-add-btn rounded-lg bg-hut-dark text-white text-sm font-semibold px-4 py-1.5 hover:bg-hut-green transition-colors">
                                        Add
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach

    @if($visibleCategories->isEmpty() && $deals->isEmpty())
        <section class="max-w-md mx-auto px-4 py-24 text-center">
            <div class="text-5xl mb-4 opacity-30">📦</div>
            <p class="text-gray-400">The menu for {{ $currentRestaurant->name ?? 'this business' }} is being updated — please
                check back soon.</p>
        </section>
    @endif

    {{-- ============ FLOATING CART BAR ============ --}}
    <div id="floating-cart-bar" class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 hidden">
        <a href="{{ route('checkout') }}"
            class="flex items-center gap-3 bg-hut-dark text-white rounded-full pl-2 pr-5 py-2 shadow-2xl shadow-black/30 hover:bg-hut-green transition-colors">
            <span
                class="flex items-center justify-center w-9 h-9 rounded-full bg-hut-yellow text-hut-dark font-display font-bold text-sm"
                id="floating-cart-count">0</span>
            <span class="text-sm font-medium">View cart</span>
            <span class="text-sm font-display font-bold" id="floating-cart-total">Rs. 0</span>
        </a>
    </div>

    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .menu-hero {
            min-height: 260px;
            isolation: isolate;
        }

        .menu-hero__image {
            position: absolute;
            inset: 0;
            background-image: linear-gradient(90deg, rgba(13, 36, 64, 0.96) 0%, rgba(13, 36, 64, 0.78) 48%, rgba(13, 36, 64, 0.45) 100%), var(--menu-hero-image);
            background-position: center;
            background-size: cover;
            opacity: 0.72;
            transform: scale(1.04);
            animation: hero-image-drift 12s ease-out both;
            z-index: 0;
        }

        .menu-hero__grain {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.06) 1px, transparent 0);
            background-size: 22px 22px;
            pointer-events: none;
            z-index: 1;
        }

        .menu-hero__glow {
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 9999px;
            filter: blur(90px);
            opacity: 0.22;
            pointer-events: none;
            z-index: 2;
        }

        .menu-hero__glow--yellow {
            background: var(--tenant-accent, #7BA4D0);
            top: -140px;
            left: -100px;
        }

        .menu-hero__glow--green {
            background: var(--tenant-primary, #2E5E99);
            bottom: -160px;
            right: -80px;
        }

        @keyframes hero-pop {
            0% {
                transform: scale(0.7);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes hero-image-drift {
            from {
                transform: scale(1.12);
            }

            to {
                transform: scale(1.04);
            }
        }

        .animate-hero-pop {
            animation: hero-pop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }

        .jumpnav-pill.active {
            background: color-mix(in srgb, var(--tenant-primary, #2E5E99) 10%, transparent);
            border-color: var(--tenant-primary, #2E5E99) !important;
            color: var(--tenant-dark, #0D2440) !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .animate-hero-pop {
                animation: none;
            }

            .menu-hero__image {
                animation: none;
                transform: none;
            }

            .menu-card-v2,
            .cart-add-btn {
                transition: none !important;
            }
        }

        .cart-add-btn.just-added {
            animation: added-pulse 0.35s ease;
        }

        @keyframes added-pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(0.94);
            }

            100% {
                transform: scale(1);
            }
        }

        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.6s ease, transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            transition-delay: var(--reveal-delay, 0s);
        }

        .reveal.reveal-visible {
            opacity: 1;
            transform: translateY(0);
        }

        @media (prefers-reduced-motion: reduce) {
            .reveal {
                opacity: 1;
                transform: none;
                transition: none;
            }
        }
    </style>

    @push('scripts')
        <script>
            // Cart lives in localStorage so it survives page reloads without needing an account
            function getCart() {
                return JSON.parse(localStorage.getItem('th_cart') || '[]');
            }
            function saveCart(cart) {
                localStorage.setItem('th_cart', JSON.stringify(cart));
                updateCartBadge();
            }
            function addToCart(item, button) {
                const cart = getCart();
                cart.push(item);
                saveCart(cart);
                const btn = button instanceof HTMLElement ? button : null;
                if (btn) {
                    const original = btn.textContent;
                    btn.textContent = 'Added ✓';
                    btn.classList.add('just-added');
                    setTimeout(() => {
                        btn.textContent = original;
                        btn.classList.remove('just-added');
                    }, 800);
                }
            }
            function updateCartBadge() {
                const cart = getCart();
                const count = cart.reduce((sum, i) => sum + i.quantity, 0);
                const total = cart.reduce((sum, i) => sum + (i.price * i.quantity), 0);

                const headerBadge = document.getElementById('cart-count-badge');
                if (headerBadge) headerBadge.textContent = count;

                const floatingBar = document.getElementById('floating-cart-bar');
                const floatingCount = document.getElementById('floating-cart-count');
                const floatingTotal = document.getElementById('floating-cart-total');
                if (floatingBar) {
                    if (count > 0) {
                        floatingBar.classList.remove('hidden');
                        floatingCount.textContent = count;
                        floatingTotal.textContent = 'Rs. ' + total.toLocaleString();
                    } else {
                        floatingBar.classList.add('hidden');
                    }
                }
            }
            document.addEventListener('DOMContentLoaded', updateCartBadge);

            // Scroll-spy for the sticky category nav
            document.addEventListener('DOMContentLoaded', function () {
                const pills = document.querySelectorAll('.jumpnav-pill');
                if (!pills.length) return;
                const sections = Array.from(pills).map(p => document.getElementById(p.dataset.jump)).filter(Boolean);

                function setActive(id) {
                    pills.forEach(p => p.classList.toggle('active', p.dataset.jump === id));
                }

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) setActive(entry.target.id);
                    });
                }, { rootMargin: '-140px 0px -70% 0px', threshold: 0 });

                sections.forEach(sec => observer.observe(sec));

                document.querySelectorAll('.jumpnav-pill').forEach(pill => {
                    pill.addEventListener('click', function (e) {
                        e.preventDefault();
                        const target = document.getElementById(this.dataset.jump);
                        if (target) {
                            window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - 120, behavior: 'smooth' });
                        }
                    });
                });

                // Nudge the horizontal pill scroller so the active pill stays in view
                const nav = document.getElementById('menu-jumpnav');
                if (nav) {
                    const scroller = nav.querySelector('.overflow-x-auto');
                    const activeObserver = new MutationObserver(() => {
                        const active = nav.querySelector('.jumpnav-pill.active');
                        if (active && scroller) {
                            scroller.scrollTo({ left: active.offsetLeft - 24, behavior: 'smooth' });
                        }
                    });
                    pills.forEach(p => activeObserver.observe(p, { attributes: true, attributeFilter: ['class'] }));
                }
            });

            // Cards fade + slide into view as they scroll on screen, staggered by
            // position within their own grid row so a whole section doesn't pop at once.
            document.addEventListener('DOMContentLoaded', function () {
                const cards = document.querySelectorAll('.reveal');
                if (!cards.length) return;

                cards.forEach((card, i) => {
                    card.style.setProperty('--reveal-delay', (i % 3) * 0.08 + 's');
                });

                if (!('IntersectionObserver' in window)) {
                    cards.forEach(card => card.classList.add('reveal-visible'));
                    return;
                }

                const revealObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('reveal-visible');
                            revealObserver.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

                cards.forEach(card => revealObserver.observe(card));
            });
        </script>
    @endpush

@endsection