@extends('layouts.customer')

@section('title', 'Menu — Taste Hut')

@section('content')

<section class="bg-hut-green relative overflow-hidden">
    <div class="max-w-6xl mx-auto px-4 py-10 text-center">
        @php
            $logoUrl = null;
            $restaurantInitials = null;
            if (optional($currentRestaurant)->name) {
                $restaurantInitials = collect(explode(' ', trim($currentRestaurant->name)))
                    ->take(2)
                    ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
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
        @endphp
        
        @if($logoUrl)
            <img src="{{ $logoUrl }}"
                 alt="{{ $currentRestaurant->name }} logo"
                 class="mx-auto mb-4 h-24 w-24 rounded-full border-4 border-white object-cover">
        @elseif($restaurantInitials)
            <div class="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-full border-4 border-white bg-white/20 text-3xl font-bold text-white">
                {{ $restaurantInitials }}
            </div>
        @endif
        <h1 class="text-3xl md:text-4xl font-display font-bold text-white mb-2">Order from {{ $currentRestaurant->name ?? 'our restaurant' }}</h1>
        <p class="text-hut-yellow font-medium">{{ $currentRestaurant->address ?? 'Available for pickup & delivery' }}</p>
        @php
            $restaurantUrl = $currentRestaurant->getPublicUrl();
        @endphp
        @if($restaurantUrl)
            <p class="text-sm text-gray-200 mt-1">Website: <a href="{{ $restaurantUrl }}" class="underline hover:text-white">{{ parse_url($restaurantUrl, PHP_URL_HOST) }}</a></p>
        @endif
    </div>
</section>

@if($deals->count())
<section class="max-w-6xl mx-auto px-4 py-8">
    <div class="section-header mb-4 inline-flex"><span>🎁</span> Hot Deals</div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($deals as $deal)
        <div class="menu-card p-4 border-l-4 border-hut-yellow relative" x-data>
            @php
                    $dealImg = null;
                    if ($deal->image) {
                        if (file_exists(public_path('images/'.$deal->image))) {
                            $dealImg = asset('images/'.$deal->image);
                        } elseif (file_exists(public_path($deal->image))) {
                            $dealImg = asset($deal->image);
                        } else {
                            $dealImg = asset('storage/'.$deal->image);
                        }
                    }
                @endphp
                @if($dealImg)
                    <div class="relative mb-3">
                        <img src="{{ $dealImg }}" alt="{{ $deal->name }}" class="w-full h-40 object-cover rounded-lg" />
                        <div class="absolute top-2 left-2 bg-hut-yellow text-hut-dark font-bold text-sm rounded-full w-9 h-9 flex items-center justify-center">{{ $deal->deal_number }}</div>
                    </div>
                @else
                <div class="mb-3 flex items-center justify-center bg-gray-50 rounded-lg h-40"> <span class="text-gray-300">No image</span> </div>
            @endif

            <div class="flex justify-between items-start mb-2">
                <h3 class="font-display font-semibold text-hut-dark">{{ $deal->name }}</h3>
                <span class="text-hut-green font-bold">Rs. {{ number_format($deal->price) }}</span>
            </div>
            <p class="text-sm text-gray-600 mb-3">{{ $deal->description }}</p>
            <button
                onclick="addToCart({type:'deal', id:{{ $deal->id }}, name:'{{ addslashes($deal->name) }}', price:{{ $deal->price }}, quantity:1})"
                class="btn-primary w-full !py-2 text-sm">Add to cart</button>
        </div>
        @endforeach
    </div>
</section>
@endif

@foreach($categories as $category)
@if($category->availableMenuItems->count())
<section class="max-w-6xl mx-auto px-4 py-6">
    <div class="section-header mb-4 inline-flex">
        <span>{{ $category->icon ?? '🍽️' }}</span> {{ $category->name }}
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($category->availableMenuItems as $item)
        <div class="menu-card p-4">
            @php
                $itemImg = null;
                if ($item->image) {
                    if (file_exists(public_path('images/'.$item->image))) {
                        $itemImg = asset('images/'.$item->image);
                    } elseif (file_exists(public_path($item->image))) {
                        $itemImg = asset($item->image);
                    } else {
                        $itemImg = asset('storage/'.$item->image);
                    }
                }
            @endphp
            @if($itemImg)
                <img src="{{ $itemImg }}" alt="{{ $item->name }}" class="mb-3 h-44 w-full rounded-2xl object-cover" />
            @else
                <div class="mb-3 h-44 w-full rounded-2xl bg-gray-50 flex items-center justify-center">
                    <span class="text-gray-300">No image</span>
                </div>
            @endif

            @if($item->description)
                <p class="text-xs text-gray-500 mb-2">{{ $item->description }}</p>
            @endif

            @if($item->has_sizes)
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach($item->sizes as $size)
                    <button
                        onclick="addToCart({type:'menu_item', id:{{ $item->id }}, name:'{{ addslashes($item->name) }}', price:{{ $size->price }}, size_label:'{{ $size->size_label }}', quantity:1})"
                        class="text-xs border border-hut-green text-hut-green rounded-lg px-3 py-1.5 hover:bg-hut-green hover:text-white transition-colors">
                        {{ $size->size_label }} · Rs. {{ number_format($size->price) }}
                    </button>
                    @endforeach
                </div>
            @else
                <div class="flex justify-between items-center mt-3">
                    <span class="text-hut-green font-bold">Rs. {{ number_format($item->price) }}</span>
                    <button
                        onclick="addToCart({type:'menu_item', id:{{ $item->id }}, name:'{{ addslashes($item->name) }}', price:{{ $item->price }}, quantity:1})"
                        class="btn-primary !py-1.5 !px-4 text-sm">Add</button>
                </div>
            @endif
        </div>
        @endforeach
    </div>
</section>
@endif
@endforeach

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
function addToCart(item) {
    const cart = getCart();
    cart.push(item);
    saveCart(cart);
    const btn = event.target;
    const original = btn.textContent;
    btn.textContent = 'Added ✓';
    setTimeout(() => btn.textContent = original, 800);
}
function updateCartBadge() {
    const count = getCart().reduce((sum, i) => sum + i.quantity, 0);
    document.getElementById('cart-count-badge').textContent = count;
}
document.addEventListener('DOMContentLoaded', updateCartBadge);
</script>
@endpush

@endsection
