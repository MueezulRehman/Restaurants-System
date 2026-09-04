{{-- Include this partial in create.blade.php and edit.blade.php --}}
<div>
    <label class="mb-2 block text-sm font-medium text-gray-700">Homepage Visibility</label>
    <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2">
        <input type="checkbox" name="show_on_homepage" value="1" {{ old('show_on_homepage', optional($restaurant ?? null)->show_on_homepage) ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-hut-dark" />
        <span class="text-sm text-gray-700">Show on public homepage</span>
    </label>
    <p class="mt-1 text-xs text-gray-500">When enabled, this business appears in the main domain listing so customers
        can find and order from it.</p>
</div>
<div>
    <label class="mb-2 block text-sm font-medium text-gray-700">Homepage Sort Order</label>
    <input type="number" name="homepage_sort_order" min="0"
        value="{{ old('homepage_sort_order', optional($restaurant ?? null)->homepage_sort_order ?? 0) }}"
        class="w-full rounded-lg border border-gray-300 px-3 py-2" />
    <p class="mt-1 text-xs text-gray-500">Lower numbers appear first on the homepage.</p>
</div>