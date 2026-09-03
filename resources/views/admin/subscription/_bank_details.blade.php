@php
    $bank = \App\Models\PlatformSetting::bankDetails();
    $hasBank = collect($bank)->filter(fn ($v) => filled($v))->isNotEmpty();
@endphp
@if($hasBank)
<div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 mb-8">
    <h3 class="text-lg font-semibold text-hut-dark mb-2">Pay subscription to Codeibex</h3>
    <p class="text-sm text-gray-600 mb-3">Transfer the amount due to the platform bank account below, then keep your payment receipt for confirmation.</p>
    <dl class="grid gap-2 text-sm md:grid-cols-2">
        @if($bank['bank_name'])
            <div><dt class="text-gray-500">Bank</dt><dd class="font-semibold text-hut-dark">{{ $bank['bank_name'] }}</dd></div>
        @endif
        @if($bank['account_title'])
            <div><dt class="text-gray-500">Account title</dt><dd class="font-semibold text-hut-dark">{{ $bank['account_title'] }}</dd></div>
        @endif
        @if($bank['account_number'])
            <div><dt class="text-gray-500">Account number</dt><dd class="font-semibold text-hut-dark">{{ $bank['account_number'] }}</dd></div>
        @endif
        @if($bank['iban'])
            <div><dt class="text-gray-500">IBAN</dt><dd class="font-semibold text-hut-dark">{{ $bank['iban'] }}</dd></div>
        @endif
        @if($bank['branch'])
            <div><dt class="text-gray-500">Branch</dt><dd class="font-semibold text-hut-dark">{{ $bank['branch'] }}</dd></div>
        @endif
    </dl>
    @if($bank['instructions'])
        <p class="mt-3 text-sm text-gray-700 border-t border-emerald-200 pt-3">{{ $bank['instructions'] }}</p>
    @endif
</div>
@endif
