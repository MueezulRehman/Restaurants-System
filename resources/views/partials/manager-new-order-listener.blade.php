{{-- Include once in the manager layout (layouts/admin.blade.php) when a business context exists.
Requires: Laravel Echo + Reverb configured (VITE_REVERB_ENABLED=true) --}}

@php
    $listenerRestaurant = auth()->user()?->effectiveRestaurant()
        ?? (\App\Support\Tenancy::impersonatedRestaurant());
@endphp

@if($listenerRestaurant)
    <script>
        (function () {
            const restaurantId = {{ (int) $listenerRestaurant->id }};
            const channelName = 'restaurant.' + restaurantId + '.orders';
            const lastSeenKey = 'codeibex:last-order-notification:' + restaurantId;

            function showOrderNotification(payload) {
                const title = payload.title || ('New order #' + (payload.order_number || payload.id));
                const body = payload.message || ((payload.customer_name || 'Customer') + ' · ' + (payload.total || '') + ' · ' + (payload.order_type || ''));

                if (window.Notification && Notification.permission === 'granted') {
                    new Notification(title, {
                        body: body,
                        tag: 'order-' + (payload.id || payload.notification_id),
                    });
                }

                window.dispatchEvent(new CustomEvent('codeibex:new-order', { detail: payload }));
                const box = document.getElementById('new-order-toast');
                if (box) {
                    box.textContent = title + ' — ' + body;
                    box.classList.remove('hidden');
                    setTimeout(function () { box.classList.add('hidden'); }, 8000);
                }
            }

            function pollNotifications() {
                const lastSeen = Number(localStorage.getItem(lastSeenKey) || 0);
                fetch('{{ route($isSuperAdmin ? 'admin.notifications.feed' : 'manager.notifications.feed') }}?after=' + lastSeen, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                })
                    .then(function (response) { return response.ok ? response.json() : null; })
                    .then(function (data) {
                        if (!data || !Array.isArray(data.notifications)) return;
                        let newest = lastSeen;
                        data.notifications.reverse().forEach(function (notification) {
                            newest = Math.max(newest, Number(notification.id));
                            if (lastSeen > 0) showOrderNotification({
                                id: notification.id,
                                notification_id: notification.id,
                                title: notification.title,
                                message: notification.message,
                            });
                        });
                        if (newest > lastSeen) localStorage.setItem(lastSeenKey, String(newest));
                    })
                    .catch(function () {});
            }

            if (window.Notification && Notification.permission === 'default') {
                Notification.requestPermission();
            }

            pollNotifications();
            window.setInterval(pollNotifications, 12000);

            function handleRealtimeOrder(payload) {
                showOrderNotification(payload);
                localStorage.setItem(lastSeenKey, String(Math.max(Number(localStorage.getItem(lastSeenKey) || 0), Number(payload.id || 0))));
                    // If on orders index, soft reload after a short delay
                    if (window.location.pathname.indexOf('/manager/orders') !== -1) {
                        setTimeout(function () { window.location.reload(); }, 1500);
                    }
            }

            if (typeof window.Echo !== 'undefined' && window.Echo) {
                window.Echo.channel(channelName).listen('.order.placed', handleRealtimeOrder);
            }
        })();
    </script>
    <div id="new-order-toast"
        class="hidden fixed bottom-4 right-4 z-50 max-w-sm rounded-xl bg-emerald-600 px-4 py-3 text-sm font-medium text-white shadow-lg">
    </div>
@endif