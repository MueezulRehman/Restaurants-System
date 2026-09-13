        <x-ceo.sidebar variant="ceo" id="ceo-sidebar"
            class="ceo-sidebar w-72 shrink-0 flex-col text-white shadow-2xl">
            <div class="border-b border-white/10 px-6 py-6">
                <div class="flex items-start justify-between gap-3">
                    <a href="{{ route('manager.ceo.dashboard') }}" class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-lg"><i class="fas fa-chart-pie"></i></span>
                    <span><span class="block text-lg font-bold">CEO Portal</span><span class="block text-xs text-white/60">Executive workspace</span></span>
                    </a>
                    <button type="button" id="ceo-sidebar-close" class="rounded-lg p-2 text-white/70 hover:bg-white/10 hover:text-white md:hidden" aria-label="Close navigation"><i class="fas fa-xmark"></i></button>
                </div>
            </div>
            <x-ceo.navbar variant="ceo" class="layout-scroll-region flex-1 space-y-1 px-4 py-6 text-sm">
                <p class="mb-3 px-3 text-[10px] font-semibold uppercase tracking-[.2em] text-white/50">Overview</p>
                <a href="{{ route('manager.ceo.dashboard') }}" aria-current="{{ $active('manager.ceo.dashboard') ? 'page' : 'false' }}" class="{{ $active('manager.ceo.dashboard') ? 'active' : '' }} flex items-center gap-3 rounded-lg px-3 py-3 hover:bg-white/10"><i class="fas fa-gauge-high w-5 text-center"></i> Dashboard</a>
                <a href="{{ route('manager.ceo.businesses.index') }}" aria-current="{{ $active('manager.ceo.businesses') ? 'page' : 'false' }}" class="{{ $active('manager.ceo.businesses') ? 'active' : '' }} flex items-center gap-3 rounded-lg px-3 py-3 hover:bg-white/10"><i class="fas fa-building w-5 text-center"></i> Businesses</a>
                <a href="{{ route('manager.ceo.branches.index') }}" aria-current="{{ $active('manager.ceo.branches') ? 'page' : 'false' }}" class="{{ $active('manager.ceo.branches') ? 'active' : '' }} flex items-center gap-3 rounded-lg px-3 py-3 hover:bg-white/10"><i class="fas fa-code-branch w-5 text-center"></i> All branches</a>
                <p class="mb-3 mt-8 px-3 text-[10px] font-semibold uppercase tracking-[.2em] text-white/50">Insights</p>
                <a href="{{ route('manager.ceo.reports.index') }}" aria-current="{{ $active('manager.ceo.reports') ? 'page' : 'false' }}" class="{{ $active('manager.ceo.reports') ? 'active' : '' }} flex items-center gap-3 rounded-lg px-3 py-3 hover:bg-white/10"><i class="fas fa-chart-line w-5 text-center"></i> Reports</a>
                <a href="{{ route('manager.ceo.alerts.index') }}" aria-current="{{ $active('manager.ceo.alerts') ? 'page' : 'false' }}" class="{{ $active('manager.ceo.alerts') ? 'active' : '' }} flex items-center gap-3 rounded-lg px-3 py-3 hover:bg-white/10"><i class="fas fa-bell w-5 text-center"></i> Alerts</a>
            </x-ceo.navbar>
            <div class="border-t border-white/10 p-4">
                <a href="{{ route('manager.ceo.profile') }}" aria-current="{{ $active('manager.ceo.profile') ? 'page' : 'false' }}" class="{{ $active('manager.ceo.profile') ? 'active' : '' }} flex items-center gap-3 rounded-lg px-3 py-3 text-sm hover:bg-white/10"><i class="fas fa-user-circle w-5 text-center"></i> My profile</a>
                <form method="post" action="{{ route('manager.logout') }}" class="mt-1">@csrf<button class="flex w-full items-center gap-3 rounded-lg px-3 py-3 text-left text-sm hover:bg-white/10"><i class="fas fa-right-from-bracket w-5 text-center"></i> Log out</button></form>
            </div>
        </x-ceo.sidebar>