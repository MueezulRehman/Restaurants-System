<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $r = request()->routeIs('home')
            ? null
            : (app()->bound('restaurant') ? app('restaurant') : null);
        $useModernMenuHeader = $r
            && request()->routeIs('menu.restaurant')
            && $r->getCustomerMenuTemplate() === 'modern';
        $platformName = \App\Models\PlatformSetting::getValue('platform_name', 'CodeIbex');
        $platformTagline = \App\Models\PlatformSetting::getValue('platform_tagline', 'Business platform');
        $platformTheme = implode('; ', [
            '--platform-light: ' . \App\Models\PlatformSetting::getValue('platform_theme_light', '#E7F0FA'),
            '--platform-accent: ' . \App\Models\PlatformSetting::getValue('platform_theme_accent', '#7BA4D0'),
            '--platform-primary: ' . \App\Models\PlatformSetting::getValue('platform_theme_primary', '#2E5E99'),
            '--platform-dark: ' . \App\Models\PlatformSetting::getValue('platform_theme_dark', '#0D2440'),
        ]);
    @endphp
    <title>@yield('title', ($r ? ($r->name . ($r->tagline ? ' — ' . $r->tagline : '')) : $platformName))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

    @if($r && !$useModernMenuHeader)
        @include('customer.partials.business-header', ['restaurant' => $r])
    @elseif(!$r)
        @include('customer.partials.platform-header', ['platformName' => $platformName, 'platformTagline' => $platformTagline])
    @endif

    @if (session('success'))
        <div class="bg-hut-green text-white text-center py-2 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if($r)
        @include('customer.partials.storefront-notice', ['restaurant' => $r])
    @endif

    <main class="customer-content flex-1">
        @yield('content')
    </main>

    <footer class="customer-footer text-white mt-12">
        <div class="max-w-6xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="font-display font-bold text-hut-yellow text-lg mb-2">{{ $r?->name ?? $platformName }}</p>
                @if(!empty($r?->address))
                    <p class="text-sm text-gray-300">{!! nl2br(e($r->address)) !!}</p>
                @else
                    <p class="text-sm text-gray-300">
                        {{ \App\Models\PlatformSetting::getValue('platform_address', '') ?: 'Platform address will be updated soon.' }}
                    </p>
                @endif
            </div>
            <div>
                <p class="font-display font-semibold mb-2">Contact</p>
                @if(!empty($r?->phone))
                    <p class="text-sm text-gray-300">📞 {{ $r->phone }}</p>
                @elseif(\App\Models\PlatformSetting::getValue('platform_phone', ''))
                    <p class="text-sm text-gray-300">📞 {{ \App\Models\PlatformSetting::getValue('platform_phone') }}</p>
                @else
                    <p class="text-sm text-gray-300">📞 Contact details coming soon</p>
                @endif
            </div>
            <div>
                <p class="font-display font-semibold mb-2">Follow Us</p>
                <p class="text-sm text-gray-300">Instagram · Facebook · TikTok</p>
            </div>
        </div>
        <div class="border-t border-white/10 text-center py-3 text-xs text-gray-400">
            &copy; {{ date('Y') }} {{ $r?->name ?? 'CodeIbex' }}. All rights reserved.
        </div>
    </footer>

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