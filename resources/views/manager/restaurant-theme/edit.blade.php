@extends('manager.layout.master')

@section('title', 'Theme')

@section('page-content')
    @php
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'weekend'];
        $light = $theme->managerPalette('light');
        $dark = $theme->managerPalette('dark');
        $customColors = [$customer['primary'], $customer['secondary'], $customer['accent'], $customer['light']];
    @endphp

    <div class="mx-auto max-w-5xl space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Business Theme</h2>
            <p class="text-sm text-gray-500">Choose a unified CodeIbex theme for the manager workspace and storefront, then
                fine-tune the storefront colors.</p>
        </div>

        <form action="{{ route('manager.business.theme.update') }}" method="POST" enctype="multipart/form-data"
            class="space-y-6">
            @csrf
            @method('PATCH')

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-hut-dark">Manager dashboard</h3>
                <p class="mt-1 text-sm text-gray-500">The selected unified preset controls the manager sidebar and header.
                    You can set the manager content background independently for light and dark mode.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="text-sm text-gray-600"><span class="mb-1 block">Manager light content
                            background</span><input type="color" name="manager_light_surface"
                            value="{{ old('manager_light_surface', $light['surface']) }}"
                            class="h-10 w-full rounded border"></label>
                    <label class="text-sm text-gray-600"><span class="mb-1 block">Manager dark content
                            background</span><input type="color" name="manager_dark_surface"
                            value="{{ old('manager_dark_surface', $dark['surface']) }}"
                            class="h-10 w-full rounded border"></label>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-hut-dark">Customer storefront</h3>
                <p class="mt-1 text-sm text-gray-500">These colors affect the public menu and are independent from the
                    manager dashboard theme.</p>
                <div class="mt-4">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Unified theme preset</label>
                    <select id="theme-preset" name="theme_preset"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                        <option value="custom" data-colors='@json($customColors)'>Custom colors</option>
                        @foreach($presets as $key => [$label, $colors])
                            <option value="{{ $key }}" data-colors='@json($colors)' {{ old('theme_preset', $customer['preset'] ?? 'codeibex') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div id="theme-preset-preview"
                        class="mt-3 flex items-center gap-2 rounded-lg border border-gray-200 bg-white p-3 text-xs text-gray-700">
                        <span class="font-semibold">Preview</span><i class="h-6 w-6 rounded-full border"
                            data-preview-color="0"></i><i class="h-6 w-6 rounded-full border" data-preview-color="1"></i><i
                            class="h-6 w-6 rounded-full border" data-preview-color="2"></i><span
                            class="ml-auto rounded px-2 py-1" data-preview-surface>Sample text</span>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">A preset updates the complete platform appearance. Choose Custom
                        colors when you want to adjust only the customer storefront palette below. These four colors do not
                        override the manager CodeIbex dashboard palette.</p>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-4">
                    @foreach(['primary' => 'Storefront primary', 'secondary' => 'Storefront secondary', 'accent' => 'Storefront accent', 'light' => 'Storefront page background'] as $key => $label)
                        <label class="text-sm text-gray-600"><span class="mb-1 block">{{ $label }}</span><input type="color"
                                name="theme_{{ $key }}" value="{{ old("theme_{$key}", $customer[$key] ?? '#000000') }}"
                                class="h-10 w-full rounded border"></label>
                    @endforeach
                </div>
                <div id="storefront-preview" class="mt-5 overflow-hidden rounded-xl border border-gray-200"
                    style="background-color: {{ $customer['light'] }};">
                    <div id="storefront-preview-header" class="p-4"
                        style="background: linear-gradient(110deg, {{ $customer['secondary'] }}, {{ $customer['primary'] }});">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-white/75">Customer storefront preview
                        </p>
                        <p class="mt-1 text-lg font-semibold text-white">{{ $restaurant->name }}</p>
                    </div>
                    <div class="p-4">
                        <div class="rounded-lg bg-white p-3 shadow-sm">
                            <p class="text-sm font-semibold" style="color: {{ $customer['secondary'] }};">Menu content area
                            </p>
                            <p class="mt-1 text-xs" style="color: {{ $customer['secondary'] }}; opacity: .72;">The
                                storefront page background uses the selected custom color.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-hut-dark">Hero carousel</h3>
                <p class="mt-1 text-sm text-gray-500">Upload up to 10 wide images for the public menu header. You can add
                    {{ max(0, 10 - count($customer['hero_slides'] ?? [])) }} more.
                </p>
                <input type="file" name="hero_slides[]" accept="image/*" multiple
                    class="mt-3 w-full rounded-lg border border-gray-300 px-3 py-2">
                @if(!empty($customer['hero_slides']))
                    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-5">
                        @foreach($customer['hero_slides'] as $slide)
                            @php $slidePath = is_array($slide) ? ($slide['path'] ?? $slide['image'] ?? null) : $slide; @endphp
                            @if(is_string($slidePath) && $slidePath !== '')
                                <img src="{{ str_starts_with($slidePath, 'http') ? $slidePath : asset('storage/' . ltrim($slidePath, '/')) }}"
                                    alt="Hero slide" class="aspect-video w-full rounded-lg border border-gray-200 object-cover">
                            @endif
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-hut-dark">Storefront theme by day</h3>
                <p class="mt-1 text-sm text-gray-500">Enable a day to override the customer storefront colors for promotions
                    or seasonal looks.</p>
                <div class="mt-4 space-y-3">
                    @foreach($days as $day)
                        @php $row = $schedule[$day] ?? []; @endphp
                        <div class="grid items-center gap-2 rounded-lg border border-gray-100 p-3 sm:grid-cols-5">
                            <label class="inline-flex items-center gap-2 text-sm font-medium capitalize"><input type="checkbox"
                                    name="schedule[{{ $day }}][enabled]" value="1" {{ $row ? 'checked' : '' }}>
                                {{ $day }}</label>
                            <input type="color" name="schedule[{{ $day }}][primary]"
                                value="{{ $row['primary'] ?? $customer['primary'] }}" class="h-9 w-full rounded border"
                                title="Primary">
                            <input type="color" name="schedule[{{ $day }}][secondary]"
                                value="{{ $row['secondary'] ?? $customer['secondary'] }}" class="h-9 w-full rounded border"
                                title="Secondary">
                            <input type="color" name="schedule[{{ $day }}][accent]"
                                value="{{ $row['accent'] ?? $customer['accent'] }}" class="h-9 w-full rounded border"
                                title="Accent">
                        </div>
                    @endforeach
                </div>
            </section>

            <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white">Save theme</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('theme-preset');
            const preview = document.getElementById('theme-preset-preview');
            const storefrontPreview = document.getElementById('storefront-preview');
            const storefrontHeader = document.getElementById('storefront-preview-header');
            if (!select || !preview) return;
            const storefrontColor = (key) => document.querySelector(`[name="theme_${key}"]`)?.value || '#000000';
            const updateStorefrontPreview = () => {
                if (!storefrontPreview || !storefrontHeader) return;
                storefrontPreview.style.backgroundColor = storefrontColor('light');
                storefrontHeader.style.background = `linear-gradient(110deg, ${storefrontColor('secondary')}, ${storefrontColor('primary')})`;
            };
            const update = () => {
                const colors = JSON.parse(select.selectedOptions[0].dataset.colors || '[]');
                preview.querySelectorAll('[data-preview-color]').forEach((el, index) => el.style.backgroundColor = colors[index] || 'transparent');
                preview.querySelector('[data-preview-surface]').style.backgroundColor = colors[3] || '#fff';
                preview.querySelector('[data-preview-surface]').style.color = colors[1] || '#111827';
            };
            const setCustomMode = () => {
                if (select.value !== 'custom') select.value = 'custom';
            };
            document.querySelectorAll('input[type="color"]').forEach(input => input.addEventListener('input', () => {
                setCustomMode();
                updateStorefrontPreview();
            }));
            select.addEventListener('change', () => {
                update();
                if (select.value === 'custom') return;
            });
            const colors = JSON.parse(select.selectedOptions[0].dataset.colors || '[]');
            preview.querySelectorAll('[data-preview-color]').forEach((el, index) => el.style.backgroundColor = colors[index] || 'transparent');
            preview.querySelector('[data-preview-surface]').style.backgroundColor = colors[3] || '#fff';
            preview.querySelector('[data-preview-surface]').style.color = colors[1] || '#111827';
            updateStorefrontPreview();
        });
    </script>
@endsection