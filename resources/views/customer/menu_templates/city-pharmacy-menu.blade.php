@extends('layouts.customer')

@section('title', 'Menu — ' . ($currentRestaurant->name ?? 'CodeIbex'))

@section('content')
    @php
        $visibleCategories = $categories->filter(fn($c) => $c->availableMenuItems->count() > 0);
    @endphp
    @include('customer.menu_partials.hero')
    @include('customer.menu_partials.jumpnav')
    @include('customer.menu_partials.deals')
    @include('customer.menu_partials.categories')
    @include('customer.menu_partials.floating_cart')
@endsection

@push('styles')
    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .menu-hero {
            min-height: 260px;
        }

        .menu-hero__grain {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.06) 1px, transparent 0);
            background-size: 22px 22px;
            pointer-events: none;
        }

        .menu-hero__glow {
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 9999px;
            filter: blur(90px);
            opacity: 0.22;
            pointer-events: none;
        }

        .menu-hero__glow--yellow {
            background: var(--tenant-accent, #7BA4D0);
            top: -140px;
            left: -100px;
        }

        .menu-hero__glow--green {
            background: var(--tenant-primary, #2E5E99);
            bottom: -160px;
            right: -80px;
        }

        @keyframes hero-pop {
            0% {
                transform: scale(0.7);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .animate-hero-pop {
            animation: hero-pop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }

        .jumpnav-pill.active {
            background: color-mix(in srgb, var(--tenant-primary, #2E5E99) 10%, transparent);
            border-color: var(--tenant-primary, #2E5E99) !important;
            color: var(--tenant-dark, #0D2440) !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .animate-hero-pop {
                animation: none;
            }

            .menu-card-v2,
            .cart-add-btn {
                transition: none !important;
            }
        }

        .cart-add-btn.just-added {
            animation: added-pulse 0.35s ease;
        }

        @keyframes added-pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(0.94);
            }

            100% {
                transform: scale(1);
            }
        }

        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.6s ease, transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            transition-delay: var(--reveal-delay, 0s);
        }

        .reveal.reveal-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .menu-card-v2 {
            will-change: transform, box-shadow;
            transition: transform 280ms cubic-bezier(.2, .9, .3, 1), box-shadow 280ms ease;
            transform-origin: center;
            backface-visibility: hidden;
        }

        .menu-card-v2:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 18px 40px rgba(17, 24, 39, 0.12);
        }

        .menu-card-v2 .relative img {
            transition: transform 420ms cubic-bezier(.2, .9, .3, 1);
            transform-origin: center center;
            will-change: transform;
        }

        .menu-card-v2:hover .relative img {
            transform: scale(1.04);
        }

        .cart-add-btn {
            position: relative;
            overflow: hidden;
        }

        .cart-add-btn .ripple {
            position: absolute;
            border-radius: 9999px;
            transform: scale(0);
            background: rgba(255, 255, 255, 0.25);
            animation: ripple 700ms ease-out;
            pointer-events: none;
        }

        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        #floating-cart-bar {
            transition: transform 420ms cubic-bezier(.2, .9, .3, 1), opacity 280ms ease;
        }

        #floating-cart-bar.hidden {
            transform: translateY(18px) scale(0.98);
            opacity: 0;
            pointer-events: none;
        }

        #floating-cart-bar.showing {
            transform: translateY(0);
            opacity: 1;
        }

        .jumpnav-pill {
            position: relative;
        }

        .jumpnav-pill::after {
            content: '';
            position: absolute;
            left: 12px;
            right: 12px;
            bottom: -6px;
            height: 3px;
            border-radius: 3px;
            background: transparent;
            transform-origin: left center;
            transition: transform 280ms ease, background 280ms ease;
            transform: scaleX(0);
        }

        .jumpnav-pill.active::after {
            transform: scaleX(1);
            background: linear-gradient(90deg, #1b3a2e, #219653);
        }
    </style>
@endpush

@push('scripts')
    <script>
        function getCart() {
            return JSON.parse(localStorage.getItem('th_cart') || '[]');
        }
        function saveCart(cart) {
            localStorage.setItem('th_cart', JSON.stringify(cart));
            updateCartBadge();
        }
        function addToCart(item) {
            const cart = getCart();
            cart.push(item);
            saveCart(cart);
            const btn = event.target.closest('button');
            if (btn) {
                const original = btn.textContent;
                btn.textContent = 'Added ✓';
                btn.classList.add('just-added');
                setTimeout(() => {
                    btn.textContent = original;
                    btn.classList.remove('just-added');
                }, 800);
            }
        }
        function updateCartBadge() {
            const cart = getCart();
            const count = cart.reduce((sum, i) => sum + i.quantity, 0);
            const total = cart.reduce((sum, i) => sum + (i.price * i.quantity), 0);

            const headerBadge = document.getElementById('cart-count-badge');
            if (headerBadge) headerBadge.textContent = count;

            const floatingBar = document.getElementById('floating-cart-bar');
            const floatingCount = document.getElementById('floating-cart-count');
            const floatingTotal = document.getElementById('floating-cart-total');
            if (floatingBar) {
                if (count > 0) {
                    floatingBar.classList.remove('hidden');
                    floatingCount.textContent = count;
                    floatingTotal.textContent = 'Rs. ' + total.toLocaleString();
                } else {
                    floatingBar.classList.add('hidden');
                }
            }
        }
        document.addEventListener('DOMContentLoaded', updateCartBadge);

        document.addEventListener('DOMContentLoaded', function () {
            const pills = document.querySelectorAll('.jumpnav-pill');
            if (!pills.length) return;
            const sections = Array.from(pills).map(p => document.getElementById(p.dataset.jump)).filter(Boolean);

            function setActive(id) {
                pills.forEach(p => p.classList.toggle('active', p.dataset.jump === id));
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) setActive(entry.target.id);
                });
            }, { rootMargin: '-140px 0px -70% 0px', threshold: 0 });

            sections.forEach(sec => observer.observe(sec));

            document.querySelectorAll('.jumpnav-pill').forEach(pill => {
                pill.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.getElementById(this.dataset.jump);
                    if (target) {
                        window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - 120, behavior: 'smooth' });
                    }
                });
            });

            const nav = document.getElementById('menu-jumpnav');
            if (nav) {
                const scroller = nav.querySelector('.overflow-x-auto');
                const activeObserver = new MutationObserver(() => {
                    const active = nav.querySelector('.jumpnav-pill.active');
                    if (active && scroller) {
                        scroller.scrollTo({ left: active.offsetLeft - 24, behavior: 'smooth' });
                    }
                });
                pills.forEach(p => activeObserver.observe(p, { attributes: true, attributeFilter: ['class'] }));
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const cards = document.querySelectorAll('.reveal');
            if (!cards.length) return;

            cards.forEach((card, i) => {
                card.style.setProperty('--reveal-delay', (i % 3) * 0.08 + 's');
            });

            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('reveal-visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

            cards.forEach(card => revealObserver.observe(card));
        });

        document.addEventListener('DOMContentLoaded', function () {
            function createRipple(e) {
                const btn = e.currentTarget;
                const rect = btn.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height) * 0.9;
                const ripple = document.createElement('span');
                ripple.className = 'ripple';
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
                ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
                btn.appendChild(ripple);
                setTimeout(() => ripple.remove(), 700);
            }

            document.querySelectorAll('.cart-add-btn').forEach(b => b.addEventListener('click', createRipple));

            const floatingBar = document.getElementById('floating-cart-bar');
            const observer = new MutationObserver(() => {
                if (!floatingBar) return;
                if (floatingBar.classList.contains('hidden')) {
                    floatingBar.classList.remove('showing');
                } else {
                    floatingBar.classList.add('showing');
                }
            });
            if (floatingBar) observer.observe(floatingBar, { attributes: true, attributeFilter: ['class'] });
        });

        (function () {
            const revealSelector = '.reveal';
            const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (prefersReduced) {
                document.querySelectorAll(revealSelector).forEach(el => el.classList.add('reveal-visible'));
                return;
            }

            let scrollTimer = null;

            function isMostlyVisible(el) {
                const rect = el.getBoundingClientRect();
                return rect.top < window.innerHeight * 0.85 && rect.bottom > window.innerHeight * 0.15;
            }

            function animateVisible(stagger = 80) {
                const elems = Array.from(document.querySelectorAll(revealSelector)).filter(el => !el.classList.contains('reveal-visible') && isMostlyVisible(el));
                elems.forEach((el, i) => {
                    setTimeout(() => el.classList.add('reveal-visible'), i * stagger);
                });
            }

            window.addEventListener('scroll', () => {
                if (scrollTimer) clearTimeout(scrollTimer);
                scrollTimer = setTimeout(() => animateVisible(60), 120);
            }, { passive: true });

            window.addEventListener('wheel', (e) => {
                if (Math.abs(e.deltaY) > window.innerHeight * 0.18) {
                    setTimeout(() => animateVisible(100), 80);
                }
            }, { passive: true });

            window.addEventListener('keydown', (e) => {
                const keys = ['PageDown', 'PageUp', 'Home', 'End', ' '];
                if (keys.includes(e.key)) {
                    setTimeout(() => animateVisible(120), 80);
                }
            });

            document.addEventListener('DOMContentLoaded', () => animateVisible(60));
        })();
    </script>
@endpush