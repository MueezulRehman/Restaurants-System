{{-- Include once in the manager layout (layouts/admin.blade.php) when a business context exists.
Requires: Laravel Echo + Reverb configured (VITE_REVERB_ENABLED=true) --}}

@php
    $listenerRestaurant = auth()->user()?->effectiveRestaurant()
        ?? (\App\Support\Tenancy::impersonatedRestaurant());
@endphp

@if($listenerRestaurant)
    <script>
        (function () {
            if (typeof window.Echo === 'undefined' || !window.Echo) {
                return;
            }

            const restaurantId = {{ (int) $listenerRestaurant->id }};
            const channelName = 'restaurant.' + restaurantId + '.orders';

            window.Echo.channel(channelName)
                .listen('.order.placed', function (payload) {
                    // Browser notification when tab is open
                    if (window.Notification && Notification.permission === 'granted') {
                        new Notification('New order #' + (payload.order_number || payload.id), {
                            body: (payload.customer_name || 'Customer') + ' · ' + (payload.total || '') + ' · ' + (payload.order_type || ''),
                            tag: 'order-' + payload.id,
                        });
                    }

                    // Optional toast / badge — dispatch a DOM event apps can listen to
                    window.dispatchEvent(new CustomEvent('codeibex:new-order', { detail: payload }));

                    // Simple visible flash if a container exists
                    const box = document.getElementById('new-order-toast');
                    if (box) {
                        box.textContent = 'New order ' + (payload.order_number || '#' + payload.id) + ' — ' + (payload.customer_name || '');
                        box.classList.remove('hidden');
                        setTimeout(function () { box.classList.add('hidden'); }, 8000);
                    }

                    // If on orders index, soft reload after a short delay
                    if (window.location.pathname.indexOf('/manager/orders') !== -1) {
                        setTimeout(function () { window.location.reload(); }, 1500);
                    }
                });

            // Request notification permission once
            if (window.Notification && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        })();
    </script>
    <div id="new-order-toast"
        class="hidden fixed bottom-4 right-4 z-50 max-w-sm rounded-xl bg-emerald-600 px-4 py-3 text-sm font-medium text-white shadow-lg">
    </div>
@endif