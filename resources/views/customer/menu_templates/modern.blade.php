@extends('layouts.customer')

@section('title', 'Menu — ' . ($currentRestaurant->name ?? 'CodeIbex'))

@section('content')

    <div class="modern-storefront">

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
            $modernHeroImage = asset('images/menu/6a9a82937ace9_1788510867.jpg');
        @endphp

        {{-- ============ CHEEZIOUS-STYLE ORDER HEADER ============ --}}
        <section class="modern-order-header">
            <div class="modern-order-header__top">
                <div class="modern-brand">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $currentRestaurant->name }} logo" class="modern-brand__logo">
                    @else
                        <span class="modern-brand__initials">{{ $restaurantInitials }}</span>
                    @endif
                    <div>
                        <p class="modern-brand__name">{{ $currentRestaurant->name ?? 'Our Restaurant' }}</p>
                        <p class="modern-brand__sub">We Bake Happiness</p>
                    </div>
                </div>
                <label class="modern-search modern-search--top">
                    <i class="fas fa-magnifying-glass"></i>
                    <input id="modern-menu-search" type="search" placeholder="Find in {{ $currentRestaurant->name }}"
                        autocomplete="off" title="Search the menu">
                </label>
                <div class="modern-cart-wrap">
                    <a id="modern-cart-link" href="{{ route('checkout') }}" class="modern-cart-link" aria-label="Open cart"
                        aria-expanded="false">
                        <svg class="modern-cart-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M6 8h12l-1 12H7L6 8Zm3 0V6a3 3 0 0 1 6 0v2" />
                        </svg>
                        <span id="modern-cart-count">0</span>
                    </a>
                    <div id="modern-top-cart-preview" class="modern-cart-preview" role="status" aria-live="polite"></div>
                </div>
            </div>
            <div class="modern-order-modes" role="group" aria-label="Order mode">
                <button type="button" class="modern-mode is-active"><i
                        class="fas fa-motorcycle"></i><span>Delivery</span></button>
                <button type="button" class="modern-mode"><i
                        class="fas fa-person-walking-luggage"></i><span>Pick-up</span></button>
            </div>
        </section>

        <section class="modern-pizza-hero" aria-label="Taste Hut fresh pizza">
            <div class="modern-pizza-hero__image" style="background-image: url('{{ $modernHeroImage }}');"></div>
            <div class="modern-pizza-hero__scrim"></div>
            <div class="modern-pizza-hero__content">
                <span class="modern-pizza-hero__eyebrow">Taste Hut kitchen</span>
                <h1>Hot slices. Big flavour.</h1>
                <p>Freshly prepared for delivery, pickup, or your table.</p>
            </div>
        </section>

        {{-- ============ STICKY CATEGORY NAV (scroll-spy) ============ --}}
        @if($visibleCategories->count() > 1 || $deals->count())
            <nav id="menu-jumpnav" class="sticky top-[64px] z-40 bg-white/95 backdrop-blur border-b border-gray-100 shadow-sm">
                <div class="max-w-6xl mx-auto px-4">
                    <div class="flex gap-2 overflow-x-auto no-scrollbar py-3">
                        @if($deals->count())
                            <a href="#section-deals" data-jump="section-deals" data-filter="deals"
                                class="jumpnav-pill whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold border border-transparent text-gray-500 hover:text-hut-dark transition-colors">🎁
                                Deals</a>
                        @endif
                        @foreach($visibleCategories as $category)
                            <a href="#section-cat-{{ $category->id }}" data-jump="section-cat-{{ $category->id }}"
                                class="jumpnav-pill whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold border border-transparent text-gray-500 hover:text-hut-dark transition-colors">
                                {{ $category->icon ?? '📦' }} {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </nav>
        @endif

        {{-- ============ DEALS ============ --}}
        @if($deals->count())
            <section id="section-deals" class="modern-deals-panel hidden max-w-5xl mx-auto px-4 py-10 scroll-mt-32">
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
                                        class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-500"
                                        style="object-position: center;" />
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
                                    class="cart-add-btn w-full rounded-lg bg-hut-dark text-white text-sm font-semibold py-2.5 hover:bg-hut-yellow hover:text-hut-dark transition-colors">
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
                                        class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-500"
                                        style="object-position: center;" />
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

                                <div class="modern-card-options">
                                    @include('customer.menu_partials.item-variants', ['item' => $item, 'compact' => true])
                                    @include('customer.menu_partials.item-price', ['item' => $item, 'compact' => true])
                                </div>
                            </div>
                        </div>
                        <div id="product-modal-{{ $item->id }}" class="modern-product-modal hidden" role="dialog" aria-modal="true"
                            aria-labelledby="product-title-{{ $item->id }}">
                            <div class="modern-product-modal__backdrop" onclick="closeProductModal({{ $item->id }})"></div>
                            <div class="modern-product-modal__panel">
                                <button type="button" class="modern-product-modal__close"
                                    onclick="closeProductModal({{ $item->id }})" aria-label="Close">&times;</button>
                                @php $modalImageUrl = $resolveMenuImage($item->image); @endphp
                                @if($modalImageUrl)
                                    <img src="{{ $modalImageUrl }}" alt="{{ $item->name }}" class="modern-product-modal__image"
                                        loading="lazy" decoding="async">
                                @else
                                    <div class="modern-product-modal__image modern-product-modal__image--empty" aria-hidden="true">
                                        {{ $category->icon ?? '📦' }}
                                    </div>
                                @endif
                                <h2 id="product-title-{{ $item->id }}" class="text-xl font-bold text-slate-900">{{ $item->name }}
                                </h2>
                                @if($item->description)
                                <p class="mt-2 text-sm text-slate-600">{{ $item->description }}</p>@endif
                                @include('customer.menu_partials.item-variants', ['item' => $item])
                                @include('customer.menu_partials.item-price', ['item' => $item])
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        @if($visibleCategories->isEmpty() && $deals->isEmpty())
            <section class="max-w-md mx-auto px-4 py-24 text-center">
                <div class="text-5xl mb-4 opacity-30">📦</div>
                <p class="text-gray-400">The menu for {{ $currentRestaurant->name ?? 'this business' }} is being updated —
                    please
                    check back soon.</p>
            </section>
        @endif

        {{-- ============ FLOATING CART BAR ============ --}}
        <div id="floating-cart-bar" class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 hidden">
            <div class="modern-floating-cart-wrap">
                <a href="{{ route('checkout') }}"
                    class="flex items-center gap-3 bg-hut-dark text-white rounded-full pl-2 pr-5 py-2 shadow-2xl shadow-black/30 hover:bg-hut-yellow hover:text-hut-dark transition-colors">
                    <span
                        class="flex items-center justify-center w-9 h-9 rounded-full bg-hut-yellow text-hut-dark font-display font-bold text-sm"
                        id="floating-cart-count">0</span>
                    <span class="text-sm font-medium">View cart</span>
                    <span class="text-sm font-display font-bold" id="floating-cart-total">Rs. 0</span>
                </a>
                <div id="modern-floating-cart-preview" class="modern-cart-preview modern-cart-preview--bottom" role="status"
                    aria-live="polite"></div>
            </div>
        </div>

        <style>
            .modern-storefront {
                --modern-ink: #111111;
                --modern-muted: #738079;
                --modern-black: var(--tenant-primary, #0d3b26);
                --modern-yellow: var(--tenant-accent, #f4c400);
                background: #fffdf2;
                color: var(--modern-ink);
            }

            .modern-order-header {
                background: var(--modern-black);
                border-bottom: 4px solid var(--modern-yellow);
                color: #fff;
                box-shadow: 0 14px 32px rgba(13, 59, 38, .22);
                backdrop-filter: blur(18px);
                padding: 18px max(1rem, calc((100vw - 72rem) / 2));
                position: relative;
                z-index: 50;
            }

            .modern-order-header__top,
            .modern-order-modes {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }

            .modern-order-header__top {
                flex-wrap: wrap;
            }

            .modern-brand {
                display: flex;
                align-items: center;
                gap: .75rem;
            }

            .modern-brand__logo,
            .modern-brand__initials {
                width: 3rem;
                height: 3rem;
                border-radius: 999px;
                object-fit: cover;
                display: grid;
                place-items: center;
                background: var(--modern-yellow);
                color: var(--modern-black);
                font-weight: 800;
                font-size: 1rem;
            }

            .modern-brand__name {
                margin: 0;
                color: #fff;
                font-size: 1rem;
                font-weight: 800;
                letter-spacing: .01em;
            }

            .modern-brand__sub {
                margin: .15rem 0 0;
                color: #f8eaa0;
                font-size: .7rem;
            }

            .modern-cart-link {
                position: relative;
                display: inline-grid;
                place-items: center;
                width: 2.75rem;
                height: 2.75rem;
                border-radius: .85rem;
                background: var(--modern-yellow);
                color: var(--modern-black);
                text-decoration: none;
                transition: transform .2s ease, background .2s ease, box-shadow .2s ease;
            }

            .modern-cart-link:hover,
            .modern-cart-link:focus-visible {
                background: #fff;
                color: var(--modern-black);
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(244, 196, 0, .25);
                outline: none;
            }

            .modern-cart-icon {
                width: 1.35rem;
                height: 1.35rem;
                fill: none;
                stroke: currentColor;
                stroke-width: 1.8;
                stroke-linecap: round;
                stroke-linejoin: round;
            }

            .modern-cart-link span {
                position: absolute;
                right: -.3rem;
                top: -.3rem;
                min-width: 1.25rem;
                height: 1.25rem;
                display: grid;
                place-items: center;
                border-radius: 999px;
                background: #fff;
                color: var(--modern-black);
                font-size: .65rem;
                font-weight: 800;
            }

            .modern-order-modes {
                justify-content: flex-start;
                margin-top: 1.15rem;
                gap: .5rem;
            }

            .modern-mode {
                display: inline-flex;
                align-items: center;
                gap: .45rem;
                border: 1px solid #5b5b5b;
                border-radius: 999px;
                padding: .55rem .9rem;
                color: #fff;
                background: transparent;
                font-size: .75rem;
                font-weight: 700;
            }

            .modern-mode.is-active {
                border-color: var(--modern-yellow);
                background: var(--modern-yellow);
                color: var(--modern-black);
            }

            .modern-search {
                display: flex;
                align-items: center;
                gap: .65rem;
                margin-top: 1rem;
                border: 1px solid #3f3f3f;
                border-radius: .9rem;
                background: #fff;
                color: #777;
                padding: .8rem 1rem;
            }

            .modern-search input {
                width: 100%;
                border: 0;
                outline: 0;
                background: transparent;
                font-size: .85rem;
                color: #111;
            }

            .modern-search--top {
                flex: 1 1 18rem;
                max-width: 34rem;
                margin: 0 auto;
            }

            .modern-cart-wrap,
            .modern-floating-cart-wrap {
                position: relative;
                z-index: 95;
            }

            .modern-cart-preview {
                position: absolute;
                right: 0;
                top: calc(100% + .75rem);
                z-index: 90;
                width: min(19rem, calc(100vw - 2rem));
                padding: .85rem;
                border: 1px solid color-mix(in srgb, var(--modern-yellow) 55%, #fff);
                border-radius: 1rem;
                background: rgba(13, 59, 38, .97);
                color: #fff;
                box-shadow: 0 18px 45px rgba(13, 59, 38, .28);
                opacity: 0;
                pointer-events: none;
                transform: translateY(-6px) scale(.98);
                transition: opacity .2s ease, transform .2s ease;
            }

            .modern-cart-wrap:hover .modern-cart-preview,
            .modern-cart-wrap:focus-within .modern-cart-preview,
            .modern-cart-preview.is-open,
            .modern-floating-cart-wrap:hover .modern-cart-preview,
            .modern-floating-cart-wrap:focus-within .modern-cart-preview {
                opacity: 1;
                pointer-events: auto;
                transform: translateY(0) scale(1);
            }

            .modern-cart-preview--bottom {
                top: auto;
                right: 0;
                bottom: calc(100% + .75rem);
            }

            .modern-cart-preview__title {
                display: flex;
                justify-content: space-between;
                gap: .75rem;
                padding-bottom: .6rem;
                border-bottom: 1px solid rgba(255, 255, 255, .14);
                color: var(--modern-yellow);
                font-size: .72rem;
                font-weight: 800;
                letter-spacing: .12em;
                text-transform: uppercase;
            }

            .modern-cart-preview__item {
                display: flex;
                justify-content: space-between;
                gap: .75rem;
                padding: .55rem 0;
                border-bottom: 1px solid rgba(255, 255, 255, .08);
                font-size: .78rem;
            }

            .modern-cart-preview__muted {
                color: #cfcfcf;
                font-size: .7rem;
            }

            .modern-cart-preview__total {
                display: flex;
                justify-content: space-between;
                padding-top: .7rem;
                font-weight: 800;
            }

            .modern-cart-preview__action {
                display: block;
                margin-top: .75rem;
                border-radius: .65rem;
                background: var(--modern-yellow);
                color: var(--modern-black);
                padding: .55rem .7rem;
                text-align: center;
                font-size: .78rem;
                font-weight: 800;
                text-decoration: none;
            }

            .modern-cart-preview__action:hover {
                background: #fff;
            }

            .modern-pizza-hero {
                position: relative;
                min-height: 24rem;
                overflow: hidden;
                isolation: isolate;
                background: var(--modern-black);
            }

            .modern-pizza-hero__image,
            .modern-pizza-hero__scrim {
                position: absolute;
                inset: 0;
            }

            .modern-pizza-hero__image {
                background-position: center 54%;
                background-size: cover;
                transform: scale(1.03);
                animation: modern-hero-drift 12s ease-in-out infinite alternate;
            }

            .modern-pizza-hero__scrim {
                background: linear-gradient(90deg, rgba(13, 59, 38, .90), rgba(13, 59, 38, .50) 52%, rgba(13, 59, 38, .2));
                z-index: 1;
            }

            .modern-pizza-hero__content {
                position: relative;
                z-index: 2;
                max-width: 72rem;
                margin: 0 auto;
                padding: 4.25rem 1rem;
                color: #fff;
            }

            .modern-pizza-hero__eyebrow {
                display: inline-block;
                margin-bottom: .55rem;
                color: var(--modern-yellow);
                font-size: .7rem;
                font-weight: 800;
                letter-spacing: .16em;
                text-transform: uppercase;
            }

            .modern-pizza-hero h1 {
                max-width: 28rem;
                margin: 0;
                font-size: clamp(2rem, 5vw, 3.8rem);
                line-height: .98;
                letter-spacing: -.03em;
            }

            .modern-pizza-hero p {
                max-width: 24rem;
                margin: .8rem 0 0;
                color: #fff8d6;
                font-size: .9rem;
            }

            @keyframes modern-hero-drift {
                from {
                    transform: scale(1.03) translate3d(0, 0, 0);
                }

                to {
                    transform: scale(1.09) translate3d(-1%, -1%, 0);
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .modern-pizza-hero__image {
                    animation: none;
                }
            }

            .modern-storefront #menu-jumpnav {
                top: 64px;
                background: rgba(13, 59, 38, .92);
                border-color: #164a30;
                box-shadow: 0 10px 24px rgba(13, 59, 38, .14);
                backdrop-filter: blur(16px);
            }

            .modern-storefront .jumpnav-pill {
                color: #f8eaa0;
            }

            .modern-storefront .jumpnav-pill:hover {
                color: #fff;
            }

            .modern-storefront .jumpnav-pill.active {
                background: var(--modern-yellow);
                border-color: var(--modern-yellow) !important;
                color: var(--modern-black) !important;
            }

            .modern-storefront section[id^="section-"] {
                max-width: 72rem;
                padding-top: 2.25rem;
                padding-bottom: 1.25rem;
            }

            .modern-storefront section[id^="section-"]>div:first-child {
                margin-bottom: 1rem;
            }

            .modern-storefront section[id^="section-"] h2 {
                font-size: 1.35rem;
                letter-spacing: -.01em;
            }

            .modern-storefront .menu-card-v2 {
                border-radius: 1rem;
                border-color: color-mix(in srgb, var(--modern-yellow) 24%, #fff);
                background: color-mix(in srgb, #fff 82%, transparent);
                box-shadow: 0 5px 18px rgba(13, 59, 38, .09);
                backdrop-filter: blur(12px);
            }

            .modern-storefront .menu-card-v2:hover {
                transform: translateY(-5px);
                border-color: var(--modern-yellow);
                box-shadow: 0 16px 34px rgba(13, 59, 38, .16);
            }

            .modern-storefront .text-hut-green {
                color: var(--modern-black) !important;
            }

            .modern-storefront .bg-hut-green {
                background-color: var(--modern-black) !important;
            }

            .modern-storefront .border-hut-green\/50 {
                border-color: color-mix(in srgb, var(--modern-black) 55%, #fff) !important;
            }

            .modern-storefront .menu-card-v2 .aspect-\[4\/3\] {
                background: #fff8d6;
            }

            .modern-storefront .cart-add-btn {
                border-radius: .65rem;
                background: var(--modern-black);
            }

            .modern-storefront .cart-add-btn:hover {
                background: var(--modern-yellow);
                color: var(--modern-black);
            }

            .modern-storefront .modern-product-modal .menu-size-options .cart-add-btn {
                background: #fff;
                color: var(--modern-black);
                border-color: var(--modern-black);
            }

            .modern-storefront .modern-product-modal .menu-size-options .cart-add-btn:hover {
                background: var(--modern-yellow);
                color: var(--modern-black);
                border-color: var(--modern-black);
            }

            .modern-storefront .deal-price {
                background: var(--modern-yellow);
                color: var(--modern-black);
            }

            .modern-storefront #floating-cart-bar a {
                border-radius: .85rem;
                background: var(--modern-black);
                border: 2px solid var(--modern-yellow);
            }

            .modern-product-modal {
                position: fixed;
                inset: 0;
                z-index: 80;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            }

            .modern-product-modal.hidden {
                display: none;
            }

            .modern-product-modal__backdrop {
                position: absolute;
                inset: 0;
                background: rgba(15, 23, 20, .62);
            }

            .modern-product-modal__panel {
                position: relative;
                width: min(100%, 30rem);
                max-height: min(90vh, 42rem);
                overflow-y: auto;
                border-radius: 1.25rem;
                background: #fff;
                padding: 1.5rem;
                box-shadow: 0 24px 70px rgba(0, 0, 0, .25);
            }

            .modern-product-modal__close {
                position: absolute;
                top: .65rem;
                right: .85rem;
                color: #64748b;
                font-size: 1.7rem;
                line-height: 1;
            }

            .modern-product-modal__image {
                display: block;
                width: 100%;
                height: 10rem;
                margin: -.25rem 0 1rem;
                border-radius: .9rem;
                object-fit: cover;
                background: #eef2ee;
            }

            .modern-product-modal__image--empty {
                display: grid;
                place-items: center;
                color: var(--modern-yellow);
                font-size: 3.5rem;
            }

            .modern-product-modal .menu-size-options {
                flex-direction: column;
                overflow: visible;
            }

            .modern-product-modal .menu-size-options .cart-add-btn {
                width: 100%;
                text-align: left;
            }

            @media (min-width: 768px) {
                .modern-order-header {
                    padding-top: 1.35rem;
                    padding-bottom: 1.35rem;
                }

                .modern-search {
                    max-width: 34rem;
                }

                .modern-order-header__top {
                    max-width: 72rem;
                    margin: 0 auto;
                    flex-wrap: nowrap;
                }

                .modern-order-modes {
                    max-width: 72rem;
                    margin: 1rem auto 0;
                }

                .modern-search {
                    margin: 0 auto;
                }
            }

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

                    document.querySelectorAll('#modern-cart-count, #cart-count-badge').forEach((badge) => {
                        badge.textContent = count;
                    });

                    const preview = cart.length
                        ? `<div class="modern-cart-preview__title"><span>Your cart</span><span>${count} item${count === 1 ? '' : 's'}</span></div>`
                            + cart.slice(-4).map((item) => `<div class="modern-cart-preview__item"><span>${escapeCartText(item.name)}<span class="modern-cart-preview__muted"> × ${item.quantity}</span></span><strong>Rs. ${(item.price * item.quantity).toLocaleString()}</strong></div>`).join('')
                            + `<div class="modern-cart-preview__total"><span>Total</span><span>Rs. ${total.toLocaleString()}</span></div><a class="modern-cart-preview__action" href="{{ route('checkout') }}">View cart and checkout</a>`
                        : '<div class="modern-cart-preview__title"><span>Your cart</span><span>0 items</span></div><p class="modern-cart-preview__muted" style="padding: .8rem 0 0;">Your cart is empty.</p>';
                    document.querySelectorAll('.modern-cart-preview').forEach((panel) => {
                        panel.innerHTML = preview;
                    });

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

                function escapeCartText(value) {
                    return String(value ?? '').replace(/[&<>'"]/g, (char) => ({
                        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
                    }[char]));
                }
                document.addEventListener('DOMContentLoaded', updateCartBadge);

                document.addEventListener('DOMContentLoaded', function () {
                    const modes = document.querySelectorAll('.modern-mode');
                    modes.forEach(function (mode, index) {
                        mode.addEventListener('click', function () {
                            modes.forEach(function (item) { item.classList.remove('is-active'); });
                            mode.classList.add('is-active');
                            localStorage.setItem('th_order_mode', index === 0 ? 'delivery' : 'takeaway');
                        });
                    });

                    const search = document.getElementById('modern-menu-search');
                    if (!search) return;
                    search.addEventListener('input', function () {
                        const needle = this.value.trim().toLowerCase();
                        document.querySelectorAll('.modern-storefront .menu-card-v2').forEach(function (card) {
                            card.classList.toggle('hidden', needle !== '' && !card.textContent.toLowerCase().includes(needle));
                        });
                    });
                });

                function openProductModal(id) {
                    const modal = document.getElementById('product-modal-' + id);
                    if (modal) { modal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
                }
                function closeProductModal(id) {
                    const modal = document.getElementById('product-modal-' + id);
                    if (modal) { modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
                }

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
                            if (this.dataset.filter === 'deals') {
                                document.querySelectorAll('.modern-deals-panel').forEach(panel => panel.classList.remove('hidden'));
                                document.querySelectorAll('section[id^="section-cat-"]').forEach(section => section.classList.add('hidden'));
                                this.classList.add('active');
                                window.scrollTo({ top: document.getElementById('section-deals').getBoundingClientRect().top + window.scrollY - 120, behavior: 'smooth' });
                                return;
                            }
                            document.querySelectorAll('.modern-deals-panel').forEach(panel => panel.classList.add('hidden'));
                            document.querySelectorAll('section[id^="section-cat-"]').forEach(section => section.classList.remove('hidden'));
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

    </div>

@endsection