{{--
  Include inside Business Profile form (manager.restaurant.profile.edit).

  @include('admin.restaurant-profile.hours-section', [
      'restaurant' => $restaurant,
      'hours' => $restaurant->getOpeningHoursNormalized(), // after helpers added
  ])
--}}
@php
    $hours = $hours ?? (is_array($restaurant->opening_hours) ? $restaurant->opening_hours : []);
    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
@endphp

<div class="rounded-xl border border-gray-200 bg-white p-5 space-y-4">
    <div>
        <h3 class="text-lg font-semibold text-hut-dark">Opening hours</h3>
        <p class="text-sm text-gray-500">Customers can only place online orders while you are open (unless you allow closed orders).</p>
    </div>

    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 flex flex-wrap items-center gap-4">
        <label class="inline-flex items-center gap-2 text-sm font-medium text-amber-900">
            <input type="hidden" name="is_closed_today" value="0" />
            <input type="checkbox" name="is_closed_today" value="1" {{ old('is_closed_today', $restaurant->is_closed_today ?? false) ? 'checked' : '' }} class="form-checkbox" />
            Closed today (day off / emergency)
        </label>
        <input type="text" name="closed_message" value="{{ old('closed_message', $restaurant->closed_message) }}" placeholder="Message e.g. Closed for private event" class="flex-1 min-w-[200px] rounded-lg border border-amber-200 px-3 py-2 text-sm" />
    </div>

    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="hidden" name="accept_orders_when_closed" value="0" />
        <input type="checkbox" name="accept_orders_when_closed" value="1" {{ old('accept_orders_when_closed', $restaurant->accept_orders_when_closed ?? false) ? 'checked' : '' }} class="form-checkbox" />
        Still accept online orders when closed (pre-orders)
    </label>

    <div class="space-y-2">
        @foreach($days as $day)
            @php $row = $hours[$day] ?? ['open' => '09:00', 'close' => '22:00', 'closed' => false]; @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 items-center rounded-lg border border-gray-100 px-3 py-2">
                <span class="text-sm font-medium capitalize">{{ $day }}</span>
                <label class="inline-flex items-center gap-1 text-xs text-gray-600">
                    <input type="checkbox" name="opening_hours[{{ $day }}][closed]" value="1" {{ !empty($row['closed']) ? 'checked' : '' }} />
                    Day off
                </label>
                <input type="time" name="opening_hours[{{ $day }}][open]" value="{{ $row['open'] ?? '09:00' }}" class="rounded border px-2 py-1 text-sm" />
                <input type="time" name="opening_hours[{{ $day }}][close]" value="{{ $row['close'] ?? '22:00' }}" class="rounded border px-2 py-1 text-sm" />
            </div>
        @endforeach
    </div>
</div>
