<!DOCTYPE html>
<html lang="en">

<head>
    <script>
        document.documentElement.dataset.dashboardTheme = localStorage.getItem('codeibex-dashboard-theme') || 'light';
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $shareImage }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $shareImage }}">
    <link rel="icon" href="{{ asset('images/codeibex-mark.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        .customer-shell {
            background: var(--tenant-cream, #f7faf8);
        }

        .customer-shell .platform-header {
            background: linear-gradient(110deg, var(--platform-dark), var(--platform-primary));
        }

        .customer-shell .business-header {
            background: linear-gradient(110deg, var(--tenant-dark), color-mix(in srgb, var(--tenant-primary) 76%, #0d3b26));
        }

        .customer-shell .customer-footer {
            background: var(--tenant-dark, #102f2a);
        }

        .customer-shell .customer-content {
            min-height: 60vh;
        }
    </style>
</head>

<body class="customer-shell min-h-screen flex flex-col" style="{{ $r ? $r->themeCssVariables() : $platformTheme }}">

    @include('customer.layout.sidebar')
    @include('customer.layout.navbar')
    @include('customer.layout.header')

    @include('customer.layout.footer')

    @if($analyticsId)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($analyticsId) }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() { dataLayer.push(arguments); }
            gtag('js', new Date());
            if (localStorage.getItem('codeibex-cookie-consent') === 'accepted') {
                gtag('config', @json($analyticsId), { anonymize_ip: true });
            }
        </script>
    @endif

    @stack('scripts')
    <!-- Confirm modal for public/customer pages (data-confirm) -->
    <div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4"
        aria-hidden="true">
        <div role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title"
            class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <div class="flex items-start gap-4">
                <div id="confirm-modal-icon" class="flex-shrink-0 mt-1">
                    <svg class="w-7 h-7 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M12 6v.01"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 id="confirm-modal-title" class="text-lg font-semibold text-hut-dark">Please confirm</h3>
                    <p id="confirm-modal-message" class="text-sm text-gray-600 mt-2">Are you sure you want to continue?
                    </p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button id="confirm-modal-cancel" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800">Cancel</button>
                <button id="confirm-modal-ok" class="px-4 py-2 rounded-lg bg-hut-dark text-white">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const consent = document.getElementById('cookie-consent');
            const storedConsent = localStorage.getItem('codeibex-cookie-consent');
            if (consent && !storedConsent) consent.classList.remove('hidden');
            document.getElementById('cookie-accept')?.addEventListener('click', () => {
                localStorage.setItem('codeibex-cookie-consent', 'accepted');
                consent?.classList.add('hidden');
                if (typeof gtag === 'function') gtag('config', @json($analyticsId), { anonymize_ip: true });
            });
            document.getElementById('cookie-reject')?.addEventListener('click', () => {
                localStorage.setItem('codeibex-cookie-consent', 'essential');
                consent?.classList.add('hidden');
            });
        })();
    </script>

    <script>
        (function () {
            const modal = document.getElementById('confirm-modal');
            const titleEl = document.getElementById('confirm-modal-title');
            const msgEl = document.getElementById('confirm-modal-message');
            const iconEl = document.getElementById('confirm-modal-icon');
            const okBtn = document.getElementById('confirm-modal-ok');
            const cancelBtn = document.getElementById('confirm-modal-cancel');
            let activeForm = null;
            let previousActive = null;

            function setVariant(variant) {
                okBtn.className = 'px-4 py-2 rounded-lg text-white';
                switch (variant) {
                    case 'danger':
                        okBtn.classList.add('bg-red-600');
                        iconEl.innerHTML = `\n                            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">\n                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M9.172 16.172a4 4 0 005.656 0L21 10.999 12 3 3 11l6.172 5.172z"></path>\n                            </svg>`;
                        break;
                    case 'primary':
                        okBtn.classList.add('bg-hut-dark');
                        iconEl.innerHTML = `\n                            <svg class="w-7 h-7 text-hut-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">\n                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>\n                            </svg>`;
                        break;
                    default:
                        okBtn.classList.add('bg-hut-dark');
                        iconEl.innerHTML = `\n                            <svg class="w-7 h-7 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">\n                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 6v.01"></path>\n                            </svg>`;
                }
            }

            function showModal(options) {
                previousActive = document.activeElement;
                activeForm = options.form || null;
                titleEl.textContent = options.title || 'Please confirm';
                msgEl.textContent = options.message || 'Are you sure you want to continue?';
                setVariant(options.variant || 'default');
                modal.removeAttribute('aria-hidden');
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                okBtn.focus();
            }

            function hideModal() {
                modal.setAttribute('aria-hidden', 'true');
                modal.classList.add('hidden');
                document.body.style.overflow = '';
                activeForm = null;
                if (previousActive && typeof previousActive.focus === 'function') {
                    previousActive.focus();
                }
            }

            document.addEventListener('click', function (e) {
                const el = e.target.closest('[data-confirm]');
                if (!el) return;
                if (el.tagName !== 'FORM') return;
                e.preventDefault();
                const message = el.getAttribute('data-confirm');
                const title = el.getAttribute('data-confirm-title') || '';
                const variant = el.getAttribute('data-confirm-variant') || 'default';
                showModal({ message, title, variant, form: el });
            }, true);

            okBtn.addEventListener('click', function () {
                if (!activeForm) return hideModal();
                activeForm.removeAttribute('data-confirm');
                activeForm.removeAttribute('data-confirm-title');
                activeForm.removeAttribute('data-confirm-variant');
                activeForm.submit();
                hideModal();
            });

            cancelBtn.addEventListener('click', function () {
                hideModal();
            });

            modal.addEventListener('click', function (e) {
                if (e.target === modal) hideModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    hideModal();
                }
            });
        })();
    </script>
</body>

</html>