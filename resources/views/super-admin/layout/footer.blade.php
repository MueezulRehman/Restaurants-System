        <footer class="border-t border-slate-200/70 bg-white/60 text-slate-500">
            <div class="layout-footer-inner">
                <span>&copy; {{ date('Y') }} {{ $showManagerNav && $restaurant ? $restaurant->name : $platformName }}</span>
                <span>{{ $showManagerNav ? 'Business management workspace' : 'Platform administration' }}</span>
            </div>
        </footer>