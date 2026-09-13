@props(['restaurant' => null, 'platformName' => 'CodeIbex'])

<x-layouts.footer variant="customer" {{ $attributes->merge(['class' => 'customer-footer text-white mt-12']) }}>
    <div class="max-w-6xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <p class="font-display font-bold text-hut-yellow text-lg mb-2">{{ $restaurant?->name ?? $platformName }}</p>
            @if(!empty($restaurant?->address))
                <p class="text-sm text-gray-300">{!! nl2br(e($restaurant->address)) !!}</p>
            @else
                <p class="text-sm text-gray-300">
                    {{ \App\Models\PlatformSetting::getValue('platform_address', '') ?: 'Platform address will be updated soon.' }}
                </p>
            @endif
        </div>
        <div>
            <p class="font-display font-semibold mb-2">Contact</p>
            @if(!empty($restaurant?->phone))
                <p class="text-sm text-gray-300">📞 {{ $restaurant->phone }}</p>
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
    <div class="flex flex-wrap justify-center gap-x-5 gap-y-2 border-t border-white/10 px-4 py-3 text-xs text-gray-400">
        <a href="{{ route('privacy') }}" class="hover:text-white">Privacy Policy</a>
        <a href="{{ route('terms') }}" class="hover:text-white">Terms</a>
        <a href="{{ route('faq') }}" class="hover:text-white">FAQ</a>
    </div>
    <div class="border-t border-white/10 text-center py-3 text-xs text-gray-400">
        &copy; {{ date('Y') }} {{ $restaurant?->name ?? 'CodeIbex' }}. All rights reserved.
    </div>
</x-layouts.footer>
