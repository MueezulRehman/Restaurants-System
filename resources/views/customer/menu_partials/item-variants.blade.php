{{--
  Public cart: product variants with live stock.
  Expects $item (MenuItem) with variants relation loaded.

  @include('customer.menu_partials.item-variants', ['item' => $item])
--}}
@if(!empty($item->has_variants) && $item->relationLoaded('variants') && $item->variants->count())
    <div class="mt-2 space-y-1.5">
        <p class="text-[11px] font-medium uppercase tracking-wide text-gray-400">Options</p>
        <div class="flex flex-wrap gap-1.5">
            @foreach($item->variants->where('is_available', true) as $variant)
                @php
                    $price = (float) $variant->getEffectivePrice();
                    $qty = (float) $variant->quantity_available;
                    $soldOut = $qty <= 0;
                    if (class_exists(\App\Support\StorefrontPricing::class) && $item) {
                        $promo = \App\Support\StorefrontPricing::livePromotion($item);
                        if ($promo) {
                            $price = $promo->applyTo($price);
                        }
                    }
                @endphp
                <button
                    type="button"
                    @if($soldOut) disabled @endif
                    onclick="addToCart({type:'variant', id:{{ $variant->id }}, name:'{{ addslashes(($item->name ?? '').' — '.($variant->variant_name ?? '')) }}', price:{{ $price }}, quantity:1})"
                    class="cart-add-btn text-xs rounded-lg px-2.5 py-1.5 font-medium border transition-colors
                        {{ $soldOut
                            ? 'border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed'
                            : 'border-hut-green/50 text-hut-green hover:bg-hut-green hover:text-white' }}">
                    {{ $variant->variant_name }}
                    <span class="opacity-70">· Rs. {{ number_format($price) }}</span>
                    @if($soldOut)
                        <span class="text-red-500">· Sold out</span>
                    @elseif($qty <= 5)
                        <span class="text-amber-600">· {{ (int) $qty }} left</span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>
@endif
