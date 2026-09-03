{{--
  @include('customer.partials.open-status-badge', ['restaurant' => $currentRestaurant])
--}}
@if(isset($restaurant) && method_exists($restaurant, 'openClosedLabel'))
    @php
        $open = $restaurant->isOpenNow() && !($restaurant->is_closed_today ?? false);
    @endphp
    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $open ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
        <span class="h-1.5 w-1.5 rounded-full {{ $open ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
        {{ $restaurant->openClosedLabel() }}
        @if(! $open && method_exists($restaurant, 'nextOpenLabel') && $restaurant->nextOpenLabel())
            <span class="font-normal text-slate-500">· {{ $restaurant->nextOpenLabel() }}</span>
        @endif
    </span>
@endif
