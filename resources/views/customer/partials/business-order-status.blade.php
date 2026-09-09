@php
    $statusRestaurant = $restaurant ?? $currentRestaurant ?? null;
    $statusAccepting = $statusRestaurant && class_exists(\App\Support\BusinessHours::class)
        ? \App\Support\BusinessHours::isAcceptingOnlineOrders($statusRestaurant)
        : true;
    $statusLabel = $statusRestaurant && class_exists(\App\Support\BusinessHours::class)
        ? \App\Support\BusinessHours::label($statusRestaurant)
        : null;
    $statusNext = $statusRestaurant && class_exists(\App\Support\BusinessHours::class)
        ? \App\Support\BusinessHours::nextOpenLabel($statusRestaurant)
        : null;
    $statusMessage = trim((string) ($statusRestaurant?->closed_message ?? ''));
@endphp

@if($statusRestaurant && $statusLabel)
    <div
        class="border-b {{ $statusAccepting ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-rose-200 bg-rose-50 text-rose-900' }}">
        <div
            class="mx-auto flex max-w-5xl flex-wrap items-center justify-center gap-x-3 gap-y-1 px-4 py-3 text-center text-sm">
            <span class="inline-flex items-center gap-2 font-semibold">
                <span class="h-2 w-2 rounded-full {{ $statusAccepting ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                {{ $statusLabel }}
            </span>
            @if($statusMessage && !$statusAccepting)
                <span>{{ $statusMessage }}</span>
            @endif
            @if($statusNext)
                <span class="font-normal opacity-80">{{ $statusNext }}</span>
            @endif
        </div>
    </div>
    @if(!$statusAccepting)
        <style>
            body.business-ordering-closed .cart-add-btn,
            body.business-ordering-closed #floating-cart-bar,
            body.business-ordering-closed #checkout-link {
                display: none !important;
            }
        </style>
        <script>
            document.body.classList.add('business-ordering-closed');
        </script>
    @endif
@endif