@php
    $noticeRestaurant = $restaurant ?? $currentRestaurant ?? (app()->bound('restaurant') ? app('restaurant') : null);
    $storefrontNoticeRecord = $noticeRestaurant?->getActiveStorefrontNotice();
    $storefrontNotice = $storefrontNoticeRecord?->message ?? $noticeRestaurant?->getStorefrontNotice();
    $statusAccepting = $noticeRestaurant && class_exists(\App\Support\BusinessHours::class)
        ? \App\Support\BusinessHours::isAcceptingOnlineOrders($noticeRestaurant) : true;
    $statusLabel = $noticeRestaurant && class_exists(\App\Support\BusinessHours::class)
        ? \App\Support\BusinessHours::label($noticeRestaurant) : null;
    $statusNext = $noticeRestaurant && class_exists(\App\Support\BusinessHours::class)
        ? \App\Support\BusinessHours::nextOpenLabel($noticeRestaurant) : null;
    $statusMessage = trim((string) ($noticeRestaurant?->closed_message ?? ''));
@endphp

@if($noticeRestaurant && ($storefrontNotice || ! $statusAccepting))
    @if(($storefrontNoticeRecord?->show_as_modal ?? false) && $storefrontNotice)
        <div id="storefront-status-modal" class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/60 px-4" role="dialog" aria-modal="true" aria-labelledby="storefront-status-title">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-600">{{ $noticeRestaurant->name }}</p>
                        <h2 id="storefront-status-title" class="mt-2 text-2xl font-bold text-slate-900">{{ $storefrontNoticeRecord?->title ?? 'Ordering status' }}</h2>
                    </div>
                    <button type="button" data-close-storefront-status class="text-2xl leading-none text-slate-400 hover:text-slate-700" aria-label="Close">&times;</button>
                </div>
                @if($storefrontNotice)
                    <p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $storefrontNotice }}</p>
                @endif
                @if(! $statusAccepting)
                    <div class="mt-4 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-900">
                        <strong>{{ $statusLabel }}</strong>
                        @if($statusMessage)<span class="block mt-1">{{ $statusMessage }}</span>@endif
                        @if($statusNext)<span class="block mt-1 opacity-80">{{ $statusNext }}</span>@endif
                    </div>
                @endif
                <button type="button" data-close-storefront-status class="mt-6 w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Continue</button>
            </div>
        </div>
        <script>
            document.querySelectorAll('[data-close-storefront-status]').forEach(function (button) {
                button.addEventListener('click', function () {
                    document.getElementById('storefront-status-modal')?.remove();
                });
            });
        </script>
    @else
        <div class="border-l-4 border-amber-500 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <strong class="font-semibold">{{ $noticeRestaurant->name }}</strong>
            <span class="ml-2">{{ $storefrontNotice ?: $statusMessage ?: 'This storefront has a temporary notice.' }}</span>
        </div>
    @endif

    @if(!$statusAccepting)
        <style>
            body.business-ordering-closed .cart-add-btn,
            body.business-ordering-closed #floating-cart-bar a,
            body.business-ordering-closed #checkout-link {
                pointer-events: none !important;
                cursor: not-allowed !important;
                opacity: .55 !important;
                filter: grayscale(1);
            }
            body.business-ordering-closed .cart-add-btn {
                cursor: not-allowed !important;
                opacity: .55 !important;
                filter: grayscale(1);
            }
        </style>
        <script>document.body.classList.add('business-ordering-closed');</script>
    @endif
@endif