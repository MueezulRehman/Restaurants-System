            <x-ceo.header variant="ceo"
                class="ceo-header ceo-header-inner flex items-center justify-between px-5 py-4 text-white shadow-md md:px-8">
                <div class="ceo-header-title flex items-center gap-3">
                    <button type="button" id="ceo-sidebar-toggle" class="ceo-sidebar-toggle rounded-lg p-2 text-white/80 hover:bg-white/10 hover:text-white" aria-controls="ceo-sidebar" aria-expanded="false" aria-label="Open navigation"><i class="fas fa-bars"></i></button>
                    <span class="hidden text-white/80 sm:inline"><i class="fas fa-chart-pie"></i></span>
                    <div><p class="text-xs text-white/60">Executive workspace</p><h1 class="text-lg font-semibold">@yield('title', 'CEO Dashboard')</h1></div>
                </div>
                <div class="ceo-header-actions flex items-center gap-3 text-sm">
                    <div class="ceo-nav-search" id="ceo-nav-search">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <input id="ceo-nav-search-input" type="search" placeholder="Search pages..." autocomplete="off" aria-label="Search available CEO pages">
                        <div id="ceo-nav-search-results" class="ceo-nav-search-results hidden"></div>
                    </div>
                    <label class="sr-only" for="dashboard-theme-preset">Theme preset</label>
                    <select id="dashboard-theme-preset"
                        class="h-9 rounded-lg border-0 bg-white/10 px-2 text-xs text-white focus:ring-2 focus:ring-white/40">
                        <option value="default" class="text-slate-900">Default</option>
                        <option value="ocean" class="text-slate-900">Ocean</option>
                        <option value="forest" class="text-slate-900">Forest</option>
                        <option value="sunset" class="text-slate-900">Sunset</option>
                    </select>
                    <span class="hidden text-white/75 sm:inline">{{ auth()->user()->name }}</span><a href="{{ route('manager.ceo.profile') }}" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/15 hover:bg-white/25"><i class="fas fa-user"></i></a>
                </div>
            </x-ceo.header>