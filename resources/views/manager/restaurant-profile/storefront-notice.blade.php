@extends('manager.layout.master')

@section('title', 'Storefront Notice')

@section('page-content')
    <div class="max-w-3xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-2xl font-semibold text-hut-dark">Storefront Notice</h2>
        <p class="mt-1 text-sm text-gray-500">This message appears to customers in a modal when they open the storefront.</p>

        <form action="{{ route('manager.storefront-notice.update') }}" method="POST" class="mt-6 space-y-5">
            @csrf
            @method('PATCH')
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Title</label>
                <input name="title" value="{{ old('title', $notice->title) }}" maxlength="120" required class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Message</label>
                <textarea name="message" rows="5" maxlength="1000" required class="w-full rounded-lg border border-gray-300 px-3 py-2">{{ old('message', $notice->message) }}</textarea>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $notice->is_active) ? 'checked' : '' }}>
                Show this notice to customers
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="show_as_modal" value="0">
                <input type="checkbox" name="show_as_modal" value="1" {{ old('show_as_modal', $notice->show_as_modal ?? true) ? 'checked' : '' }}>
                Show as a modal each time the page loads
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="stop_orders" value="0">
                <input type="checkbox" name="stop_orders" value="1" {{ old('stop_orders', !($restaurant->accept_orders_when_closed ?? false)) ? 'checked' : '' }}>
                Do not receive orders while this notice is active
            </label>
            <p class="text-xs text-gray-500">When enabled, customers can read the notice but cannot place an order. The cart remains visible but disabled.</p>
            <div class="flex flex-wrap items-center gap-3">
                <button class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white">Save notice</button>
                @if($notice->exists)
                    <button type="submit" form="clear-storefront-notice" class="rounded-lg border border-rose-200 px-4 py-2 font-semibold text-rose-700">Clear notice</button>
                @endif
            </div>
        </form>
        @if($notice->exists)
            <form id="clear-storefront-notice" action="{{ route('manager.storefront-notice.destroy') }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
@endsection