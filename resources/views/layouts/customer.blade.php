<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $r = app()->bound('restaurant') ? app('restaurant') : null;
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
</head>

<body class="min-h-screen flex flex-col" style="{{ $r ? $r->themeCssVariables() : $platformTheme }}">

    <header class="bg-hut-dark sticky top-0 z-50 shadow-md">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            @php
                $r = $currentRestaurant ?? (app()->bound('restaurant') ? app('restaurant') : null);
            @endphp

            <a href="{{ route('home') }}" class="flex items-center gap-2">
                @if($r)
                    @if(!empty($r->logo_path))
                        <img src="{{ asset('storage/' . $r->logo_path) }}" alt="{{ $r->name }}"
                            class="w-10 h-10 rounded-full object-cover">
                    @else
                        <div
                            class="w-10 h-10 bg-hut-yellow rounded-full flex items-center justify-center font-display font-bold text-hut-dark text-lg">
                            {{ strtoupper(substr($r->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <p class="text-white font-display font-bold text-lg leading-none">{{ $r->name }}</p>
                        @if(!empty($r->tagline))
                            <p class="text-hut-yellow text-[10px] tracking-wide">{{ $r->tagline }}</p>
                        @endif
                    </div>
                @else
                    <img src="{{ asset('images/codeibex-mark.svg') }}" alt="{{ $platformName }}"
                        class="w-10 h-10 rounded-xl object-cover">
                    <div>
                        <p class="text-white font-display font-bold text-lg leading-none">{{ $platformName }}</p>
                        <p class="text-hut-yellow text-[10px] tracking-wide">{{ $platformTagline }}</p>
                    </div>
                @endif
            </a>
            <nav class="flex items-center gap-5 text-sm">
                <a href="{{ route('home') }}"
                    class="text-white hover:text-hut-yellow transition-colors hidden sm:inline">Menu</a>
                <a href="{{ route('orders.lookup.form') }}"
                    class="text-white hover:text-hut-yellow transition-colors hidden sm:inline">Track Order</a>
                @if(!empty($r?->phone))
                    <a href="tel:{{ preg_replace('/\D+/', '', $r->phone) }}"
                        class="text-white hover:text-hut-yellow transition-colors hidden md:inline">📞 {{ $r->phone }}</a>
                @endif
                <a id="checkout-link" href="{{ route('checkout') }}" class="relative hidden">
                    <button class="btn-accent flex items-center gap-1.5 !py-2 !px-4">
                        <span>🛒</span>
                        <span id="cart-count-badge"
                            class="text-xs bg-hut-dark text-white rounded-full w-5 h-5 flex items-center justify-center">0</span>
                    </button>
                </a>
                @auth('customer')
                    <a href="{{ route('account.dashboard') }}"
                        class="text-white hover:text-hut-yellow transition-colors hidden sm:inline">My Orders</a>
                    <form method="POST" action="{{ route('customer.logout') }}">
                        @csrf
                        <button type="submit"
                            class="text-sm text-white bg-white/10 hover:bg-white/20 px-3 py-1 rounded">Logout</button>
                    </form>
                @else
                    <a href="{{ route('customer.login') }}"
                        class="text-white hover:text-hut-yellow transition-colors text-sm">Login</a>
                @endauth

                @auth
                    <form method="POST"
                        action="{{ auth()->user()->role === 'super_admin' ? route('admin.logout') : route('manager.logout') }}">
                        @csrf
                        <button type="submit"
                            class="text-sm text-white bg-white/10 hover:bg-white/20 px-3 py-1 rounded">Staff Logout</button>
                    </form>
                @endauth
            </nav>
        </div>
    </header>

    @if (session('success'))
        <div class="bg-hut-green text-white text-center py-2 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-hut-dark text-white mt-12">
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