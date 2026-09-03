@extends('layouts.admin')

@section('title', 'Platform Settings')

@section('content')
    <div class="mx-auto max-w-3xl space-y-8">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Platform Settings</h2>
            <p class="text-sm text-gray-500">Bank details for subscriptions and public homepage branding.</p>
        </div>

        <form action="{{ route('admin.platform.settings.update') }}" method="POST" enctype="multipart/form-data"
            class="space-y-8">
            @csrf
            @method('PUT')

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
                <h3 class="text-lg font-semibold text-hut-dark">Subscription bank details</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm">Bank name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $bank['bank_name'] ?? '') }}"
                            class="w-full rounded-lg border px-3 py-2" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm">Account title</label>
                        <input type="text" name="account_title"
                            value="{{ old('account_title', $bank['account_title'] ?? '') }}"
                            class="w-full rounded-lg border px-3 py-2" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm">Account number</label>
                        <input type="text" name="account_number"
                            value="{{ old('account_number', $bank['account_number'] ?? '') }}"
                            class="w-full rounded-lg border px-3 py-2" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm">IBAN</label>
                        <input type="text" name="iban" value="{{ old('iban', $bank['iban'] ?? '') }}"
                            class="w-full rounded-lg border px-3 py-2" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm">Branch</label>
                        <input type="text" name="branch" value="{{ old('branch', $bank['branch'] ?? '') }}"
                            class="w-full rounded-lg border px-3 py-2" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm">Instructions</label>
                        <textarea name="instructions" rows="3"
                            class="w-full rounded-lg border px-3 py-2">{{ old('instructions', $bank['instructions'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
                <h3 class="text-lg font-semibold text-hut-dark">Homepage branding</h3>
                <p class="text-sm text-gray-500">Controls the multi-business homepage visitors see on the main domain.</p>
                <div>
                    <label class="mb-1 block text-sm">Hero title</label>
                    <input type="text" name="homepage_hero_title"
                        value="{{ old('homepage_hero_title', $home['homepage_hero_title']) }}"
                        class="w-full rounded-lg border px-3 py-2" />
                </div>
                <div>
                    <label class="mb-1 block text-sm">Hero subtitle</label>
                    <textarea name="homepage_hero_subtitle" rows="2"
                        class="w-full rounded-lg border px-3 py-2">{{ old('homepage_hero_subtitle', $home['homepage_hero_subtitle']) }}</textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm">Sale badge text</label>
                    <input type="text" name="homepage_sale_badge_text"
                        value="{{ old('homepage_sale_badge_text', $home['homepage_sale_badge_text']) }}"
                        class="w-full rounded-lg border px-3 py-2" placeholder="Sale live" />
                </div>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="homepage_show_sale_badges" value="1" {{ $home['homepage_show_sale_badges'] ? 'checked' : '' }} />
                    Show “on sale” badges on businesses that have live item promotions
                </label>
                <div>
                    <label class="mb-1 block text-sm">Homepage banner image</label>
                    <input type="file" name="homepage_banner_image" accept="image/*"
                        class="w-full rounded-lg border px-3 py-2" />
                    @if(!empty($home['homepage_banner_image']))
                        <p class="mt-1 text-xs text-gray-500">Current: {{ $home['homepage_banner_image'] }}</p>
                        <img src="{{ asset('storage/' . $home['homepage_banner_image']) }}" alt="Banner"
                            class="mt-2 max-h-32 rounded-lg object-cover" />
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
                <h3 class="text-lg font-semibold text-hut-dark">Platform Identity</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm">Platform Name</label>
                        <input type="text" name="platform_name"
                            value="{{ old('platform_name', $platform['platform_name'] ?? '') }}" required
                            class="w-full rounded-lg border px-3 py-2" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm">Tagline</label>
                        <input type="text" name="platform_tagline"
                            value="{{ old('platform_tagline', $platform['platform_tagline'] ?? '') }}"
                            class="w-full rounded-lg border px-3 py-2" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm">Email</label>
                        <input type="email" name="platform_email"
                            value="{{ old('platform_email', $platform['platform_email'] ?? '') }}"
                            class="w-full rounded-lg border px-3 py-2" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm">Phone</label>
                        <input type="text" name="platform_phone"
                            value="{{ old('platform_phone', $platform['platform_phone'] ?? '') }}"
                            class="w-full rounded-lg border px-3 py-2" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm">Address</label>
                        <textarea name="platform_address" rows="2"
                            class="w-full rounded-lg border px-3 py-2">{{ old('platform_address', $platform['platform_address'] ?? '') }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm">Platform Logo</label>
                        <input type="file" name="platform_logo" accept="image/*"
                            class="w-full rounded-lg border px-3 py-2" />
                        @if(!empty($platform['platform_logo_path']))
                            <img src="{{ asset('storage/' . $platform['platform_logo_path']) }}"
                                alt="{{ $platform['platform_name'] }}" class="mt-2 h-16 w-16 rounded-lg object-contain" />
                        @else
                            <img src="{{ asset('images/codeibex-mark.svg') }}" alt="{{ $platform['platform_name'] }}"
                                class="mt-2 h-16 w-16 rounded-lg object-contain" />
                        @endif
                    </div>
                    <div class="sm:col-span-2">
                        <p class="mb-2 text-sm font-medium text-gray-700">Dashboard theme colors</p>
                        <div class="grid gap-4 sm:grid-cols-4">
                            @foreach(['theme_light' => 'Light', 'theme_accent' => 'Accent', 'theme_primary' => 'Primary', 'theme_dark' => 'Dark'] as $key => $label)
                                <label class="text-sm text-gray-600">
                                    <span class="mb-1 block">{{ $label }}</span>
                                    <input type="color" name="{{ $key }}" value="{{ old($key, $platform[$key] ?? '#000000') }}"
                                        class="h-11 w-full rounded border">
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-gray-500">These colors apply to the CodeIbex and manager dashboards.
                            Customer storefront colors remain controlled by each business profile.</p>
                    </div>
                </div>
            </div>

            <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white">Save settings</button>
        </form>
    </div>
@endsection