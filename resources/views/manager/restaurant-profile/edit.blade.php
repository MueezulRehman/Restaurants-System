@extends('manager.layout.master')

@section('title', 'Business Profile')

@section('page-content')
    <div class="max-w-4xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-6">
            <h2 class="text-2xl font-semibold text-hut-dark">Business Profile</h2>
            <p class="text-sm text-gray-500">Update contact details, logo, business hours, and operational settings.</p>
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
            </div>

            @include('manager.restaurant-profile.hours-section', [
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

                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <h3 class="mb-2 text-lg font-semibold text-hut-dark">Medical Queue Notifications</h3>
                    <p class="mb-4 text-sm text-gray-500">Notifications are off by default. Enable a channel only after confirming patient consent at check-in.</p>
                    <label class="flex items-center gap-2 text-sm font-medium">
                        <input type="hidden" name="queue_notifications_enabled" value="0">
                        <input type="checkbox" name="queue_notifications_enabled" value="1"
                            @checked(old('queue_notifications_enabled', $restaurant->queue_notifications_enabled ?? false))>
                        Enable queue notifications
                    </label>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach(['sms' => 'SMS', 'whatsapp' => 'WhatsApp'] as $value => $label)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="queue_notification_channels[]" value="{{ $value }}"
                                    @checked(in_array($value, old('queue_notification_channels', $restaurant->queue_notification_channels ?? []), true))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white">Save changes</button>
        </form>
    </div>
@endsection