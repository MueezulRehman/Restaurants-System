@extends('layouts.admin')

@section('title', 'Business Profile')

@section('content')
    <div class="max-w-4xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-6">
            <h2 class="text-2xl font-semibold text-hut-dark">Business Profile</h2>
            <p class="text-sm text-gray-500">Update contact details, logo, colours, and optional theme by day of week.</p>
        </div>

        <form action="{{ route('manager.restaurant.profile.update') }}" method="POST" enctype="multipart/form-data"
            class="space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Business Name</label>
                    <input type="text" name="name" value="{{ old('name', $restaurant->name) }}" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $restaurant->email) }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $restaurant->phone) }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Address</label>
                    <input type="text" name="address" value="{{ old('address', $restaurant->address) }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Logo</label>
                    <input type="file" name="logo_path" accept="image/*"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                    @if($restaurant->logo_path)
                        <p class="mt-1 text-xs text-gray-500">Current: {{ basename($restaurant->logo_path) }}</p>
                    @endif
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Hero carousel images</label>
                    <p class="mb-2 text-xs text-gray-500">Upload up to 10 wide images. They rotate automatically behind the
                        public menu header. You can add {{ max(0, 10 - count($theme['hero_slides'] ?? [])) }} more.</p>
                    <input type="file" name="hero_slides[]" accept="image/*" multiple
                        class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                    @if(!empty($theme['hero_slides']))
                        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-5">
                            @foreach($theme['hero_slides'] as $slide)
                                @php
                                    $heroSlideUrl = is_array($slide)
                                        ? ($slide['path'] ?? $slide['image'] ?? null)
                                        : $slide;
                                    if (is_string($heroSlideUrl) && !\Illuminate\Support\Str::startsWith($heroSlideUrl, ['http://', 'https://'])) {
                                        $heroSlideUrl = ltrim($heroSlideUrl, '/');
                                        if (\Illuminate\Support\Str::startsWith($heroSlideUrl, 'public/')) {
                                            $heroSlideUrl = substr($heroSlideUrl, 7);
                                        }
                                        if (!\Illuminate\Support\Str::startsWith($heroSlideUrl, 'storage/')) {
                                            $heroSlideUrl = 'storage/' . $heroSlideUrl;
                                        }
                                        $heroSlideUrl = asset($heroSlideUrl);
                                    }
                                @endphp
                                @if(is_string($heroSlideUrl) && $heroSlideUrl !== '')
                                    <img src="{{ $heroSlideUrl }}" alt="Hero slide"
                                        class="aspect-video w-full rounded-lg border border-gray-200 object-cover" />
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                <h3 class="mb-3 text-lg font-semibold text-hut-dark">Default theme colours</h3>
                <div class="mb-4">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Predefined manager theme</label>
                    <select id="theme-preset" name="theme_preset" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                        @foreach(['codeibex' => 'CodeIbex Blue', 'emerald' => 'Emerald', 'royal' => 'Royal', 'midnight' => 'Midnight Teal', 'sunset' => 'Sunset'] as $key => $label)
                            @php $presetColors = ['codeibex'=>['#2E5E99','#0D2440','#7BA4D0','#E7F0FA'],'emerald'=>['#166534','#052E16','#4ADE80','#ECFDF5'],'royal'=>['#6D28D9','#24104F','#C4B5FD','#F5F3FF'],'midnight'=>['#0F766E','#042F2E','#5EEAD4','#F0FDFA'],'sunset'=>['#C2410C','#431407','#FDBA74','#FFF7ED']]; @endphp
                            <option value="{{ $key }}" data-colors='@json($presetColors[$key])' {{ old('theme_preset', $theme['preset'] ?? 'codeibex') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div id="theme-preset-preview" class="mt-3 flex items-center gap-2 rounded-lg border border-gray-200 bg-white p-3 text-xs text-gray-700"><span class="font-semibold">Preview</span><i class="h-6 w-6 rounded-full border" data-preview-color="0"></i><i class="h-6 w-6 rounded-full border" data-preview-color="1"></i><i class="h-6 w-6 rounded-full border" data-preview-color="2"></i><span class="ml-auto rounded px-2 py-1" data-preview-surface>Sample text</span></div>
                    <p class="mt-1 text-xs text-gray-500">A preset updates the manager dashboard colors. Fine-tune colors below when needed.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm">Primary</label>
                        <input type="color" name="theme_primary"
                            value="{{ old('theme_primary', $theme['primary'] ?? '#2E5E99') }}"
                            class="h-10 w-full rounded border" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm">Secondary</label>
                        <input type="color" name="theme_secondary"
                            value="{{ old('theme_secondary', $theme['secondary'] ?? '#0D2440') }}"
                            class="h-10 w-full rounded border" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm">Accent</label>
                        <input type="color" name="theme_accent"
                            value="{{ old('theme_accent', $theme['accent'] ?? '#7BA4D0') }}"
                            class="h-10 w-full rounded border" />
                    </div>
                </div>
                <div class="mt-5 border-t border-gray-200 pt-5">
                    <h4 class="mb-3 text-sm font-semibold text-hut-dark">Menu category tabs</h4>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <label class="text-sm text-gray-600">
                            <span class="mb-1 block">Tab background</span>
                            <input type="color" name="theme_tab_background"
                                value="{{ old('theme_tab_background', $theme['tab_background'] ?? '#FFFFFF') }}"
                                class="h-10 w-full rounded border" />
                        </label>
                        <label class="text-sm text-gray-600">
                            <span class="mb-1 block">Tab text</span>
                            <input type="color" name="theme_tab_text"
                                value="{{ old('theme_tab_text', $theme['tab_text'] ?? '#64748B') }}"
                                class="h-10 w-full rounded border" />
                        </label>
                        <label class="text-sm text-gray-600">
                            <span class="mb-1 block">Active tab</span>
                            <input type="color" name="theme_tab_active"
                                value="{{ old('theme_tab_active', $theme['tab_active'] ?? ($theme['primary'] ?? '#0f3d2e')) }}"
                                class="h-10 w-full rounded border" />
                        </label>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="mb-2 text-lg font-semibold text-hut-dark">Theme by day (optional)</h3>
                <p class="mb-4 text-sm text-gray-500">Override colours on specific days (e.g. weekend promo look). Leave
                    disabled to use default theme.</p>
                @php $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'weekend']; @endphp
                <div class="space-y-3">
                    @foreach($days as $day)
                        @php $row = $schedule[$day] ?? [];
                        $on = !empty($row); @endphp
                        <div class="grid items-center gap-2 rounded-lg border border-gray-100 p-3 sm:grid-cols-5">
                            <label class="inline-flex items-center gap-2 text-sm font-medium capitalize">
                                <input type="checkbox" name="schedule[{{ $day }}][enabled]" value="1" {{ $on ? 'checked' : '' }}
                                    class="form-checkbox" />
                                {{ $day }}
                            </label>
                            <input type="color" name="schedule[{{ $day }}][primary]"
                                value="{{ $row['primary'] ?? ($theme['primary'] ?? '#0f3d2e') }}"
                                class="h-9 w-full rounded border" title="Primary" />
                            <input type="color" name="schedule[{{ $day }}][secondary]"
                                value="{{ $row['secondary'] ?? ($theme['secondary'] ?? '#c9a227') }}"
                                class="h-9 w-full rounded border" title="Secondary" />
                            <input type="color" name="schedule[{{ $day }}][accent]"
                                value="{{ $row['accent'] ?? ($theme['accent'] ?? '#16a34a') }}"
                                class="h-9 w-full rounded border" title="Accent" />
                        </div>
                    @endforeach
                </div>
            </div>

            @include('admin.restaurant-profile.hours-section', [
                'restaurant' => $restaurant,
                'hours' => \App\Support\BusinessHours::normalized($restaurant),
            ])

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="mb-3 text-lg font-semibold text-hut-dark">POS Settings</h3>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm">
                        <input type="hidden" name="pos_allow_short_payment_without_debt" value="0" />
                        <input type="checkbox" name="pos_allow_short_payment_without_debt" value="1" {{ old('pos_allow_short_payment_without_debt', $restaurant->pos_allow_short_payment_without_debt ?? true) ? 'checked' : '' }} />
                        Allow small short payments without debt
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        Short payment threshold (Rs)
                        <input type="number" name="pos_short_payment_threshold" min="0"
                            value="{{ old('pos_short_payment_threshold', $restaurant->pos_short_payment_threshold ?? 10) }}"
                            class="ml-2 w-24 rounded border px-2 py-1" />
                    </label>
                </div>
            </div>

            <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white">Save changes</button>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('theme-preset');
            const preview = document.getElementById('theme-preset-preview');
            if (!select || !preview) return;
            const update = () => {
                const colors = JSON.parse(select.selectedOptions[0].dataset.colors || '[]');
                preview.querySelectorAll('[data-preview-color]').forEach((el, index) => el.style.backgroundColor = colors[index] || 'transparent');
                preview.querySelector('[data-preview-surface]').style.backgroundColor = colors[3] || '#fff';
                preview.querySelector('[data-preview-surface]').style.color = colors[1] || '#111827';
            };
            select.addEventListener('change', update); update();
        });
    </script>
@endsection