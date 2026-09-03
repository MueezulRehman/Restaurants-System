{{--
  Professional price display with live sale support.
  Expects $item (MenuItem), optional eager-loaded promotions.

  Include:
    @include('customer.menu_partials.item-price', ['item' => $item])
--}}
@php
    $base = (float) ($item->display_price ?? $item->price ?? 0);
    $hasSale = false;
    $salePrice = $base;
    $saleLabel = null;
    if (class_exists(\App\Support\StorefrontPricing::class)) {
        $salePrice = \App\Support\StorefrontPricing::unitPriceForMenuItem($item, null);
        $saleLabel = \App\Support\StorefrontPricing::saleLabel($item);
        $hasSale = $saleLabel !== null && $salePrice < $base;
    } elseif (isset($item->has_active_sale) && $item->has_active_sale) {
        $hasSale = true;
        $salePrice = (float) $item->sale_price;
        $saleLabel = $item->sale_label;
    }
@endphp

@if($item->has_sizes)
    <div class="flex flex-wrap gap-1.5 mt-2">
        @foreach($item->sizes as $size)
            @php
                $sizeBase = (float) $size->price;
                $sizeSale = class_exists(\App\Support\StorefrontPricing::class)
                    ? \App\Support\StorefrontPricing::unitPriceForMenuItem($item, $size->size_label)
                    : $sizeBase;
                $sizeOnSale = $sizeSale < $sizeBase;
            @endphp
            <button
                type="button"
                onclick="addToCart({type:'menu_item', id:{{ $item->id }}, name:'{{ addslashes($item->name) }}', price:{{ $sizeSale }}, size_label:'{{ $size->size_label }}', quantity:1})"
                class="cart-add-btn text-xs border border-hut-green/50 text-hut-green rounded-lg px-2.5 py-1.5 hover:bg-hut-green hover:text-white hover:border-hut-green transition-colors font-medium">
                {{ $size->size_label }}
                @if($sizeOnSale)
                    <span class="opacity-60 line-through">Rs. {{ number_format($sizeBase) }}</span>
                    <span class="font-bold">Rs. {{ number_format($sizeSale) }}</span>
                @else
                    <span class="opacity-70">· Rs. {{ number_format($sizeBase) }}</span>
                @endif
            </button>
        @endforeach
    </div>
    @if($saleLabel)
        <span class="mt-1 inline-block rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-semibold text-red-700">{{ $saleLabel }}</span>
    @endif
@else
    <div class="flex justify-between items-center mt-3 gap-2">
        <div class="flex flex-col">
            @if($hasSale)
                <span class="text-xs text-gray-400 line-through">Rs. {{ number_format($base) }}</span>
                <span class="text-hut-green font-bold font-display">Rs. {{ number_format($salePrice) }}</span>
                @if($saleLabel)
                    <span class="text-[10px] font-semibold text-red-600">{{ $saleLabel }}</span>
                @endif
            @else
                <span class="text-hut-green font-bold font-display">Rs. {{ number_format($base) }}</span>
            @endif
        </div>
        <button
            type="button"
            onclick="addToCart({type:'menu_item', id:{{ $item->id }}, name:'{{ addslashes($item->name) }}', price:{{ $salePrice }}, quantity:1})"
            class="cart-add-btn rounded-lg bg-hut-dark text-white text-sm font-semibold px-4 py-1.5 hover:bg-hut-green transition-colors">
            Add
        </button>
    </div>
@endif
