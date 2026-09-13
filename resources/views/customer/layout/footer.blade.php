    <x-customer.footer :restaurant="$r" :platform-name="$platformName" />

    <div id="cookie-consent"
        class="fixed inset-x-3 bottom-3 z-40 hidden rounded-xl border border-slate-200 bg-white p-4 text-slate-800 shadow-2xl sm:inset-x-auto sm:right-4 sm:max-w-md">
        <p class="text-sm font-semibold">Cookies and privacy</p>
        <p class="mt-1 text-xs leading-5 text-slate-600">We use essential cookies to keep CodeIbex secure and remember
            your preferences. Optional analytics only runs after consent.</p>
        <div class="mt-3 flex justify-end gap-2">
            <button type="button" id="cookie-reject"
                class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600">Essential
                only</button>
            <button type="button" id="cookie-accept"
                class="rounded-lg bg-hut-dark px-3 py-2 text-xs font-semibold text-white">Accept analytics</button>
        </div>
    </div>