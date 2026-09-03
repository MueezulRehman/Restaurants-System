{{--
  Shows live + upcoming item promotions for this business on the storefront.
  Pass $upcomingPromotions collection (ItemPromotion with menuItem).
--}}
@if(isset($upcomingPromotions) && $upcomingPromotions->isNotEmpty())
<section class="mx-auto max-w-6xl px-4 pt-6">
    <div class="overflow-hidden rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 shadow-sm">
        <div class="flex flex-wrap items-center gap-3 px-5 py-4">
            <span class="rounded-full bg-red-500 px-3 py-1 text-xs font-bold uppercase tracking-wide text-white">Offers</span>
            <div class="flex flex-1 flex-wrap gap-2">
                @foreach($upcomingPromotions as $promo)
                    @php
                        $live = $promo->isLive();
                        $itemName = $promo->menuItem?->name ?? 'Item';
                        if ($promo->type === 'percent') {
                            $offer = rtrim(rtrim(number_format($promo->value, 2), '0'), '.') . '% off';
                        } else {
                            $offer = 'Rs ' . number_format($promo->value, 0) . ' off';
                        }
                    @endphp
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-white px-3 py-1 text-xs text-slate-700">
                        @if($live)
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            <strong>Live:</strong>
                        @else
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                            <strong>Coming:</strong>
                        @endif
                        {{ $itemName }} — {{ $offer }}
                        @if($promo->ends_at)
                            <span class="text-slate-400">until {{ $promo->ends_at->format('M d') }}</span>
                        @elseif($promo->starts_at && !$live)
                            <span class="text-slate-400">from {{ $promo->starts_at->format('M d') }}</span>
                        @endif
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
