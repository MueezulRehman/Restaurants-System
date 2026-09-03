{{-- Include once in manager layout for in-browser order alerts --}}
<script>
(function () {
    if (!('Notification' in window)) return;
    const key = 'codeibex_notify_permission';
    function ask() {
        if (Notification.permission === 'default' && !sessionStorage.getItem(key)) {
            Notification.requestPermission().finally(() => sessionStorage.setItem(key, '1'));
        }
    }
    document.addEventListener('DOMContentLoaded', ask);
    window.codeibexNotifyOrder = function (title, body) {
        if (Notification.permission === 'granted') {
            new Notification(title || 'New order', {
                body: body || 'You have a new order',
                icon: '/favicon.ico',
            });
        }
    };
})();
</script>
