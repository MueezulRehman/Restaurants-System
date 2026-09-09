@extends('layouts.admin')

@section('title', 'Edit Stock Adjustment')

@section('content')
    <div class="max-w-2xl space-y-6">
        <x-back-link href="{{ route('manager.stock.adjustments.index') }}" label="Back to Adjustment History" />

        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Edit Stock Adjustment</h1>
            <p class="mt-1 text-sm text-gray-500">Update the explanation for this stock movement. Quantity records cannot be changed.</p>
        </div>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Date</p>
                    <p class="mt-1 text-sm font-medium text-hut-dark">{{ $adjustment->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Quantity change</p>
                    <p class="mt-1 text-sm font-semibold {{ $adjustment->change_quantity >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        {{ $adjustment->change_quantity > 0 ? '+' : '' }}{{ $adjustment->change_quantity }}
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Reference</p>
                    <p class="mt-1 text-sm font-medium text-hut-dark">{{ $adjustment->reference_id ?: 'Manual adjustment' }}</p>
                </div>
            </div>

            <form action="{{ route('manager.stock.adjustments.update', $adjustment) }}" method="POST" class="mt-6 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700">Reason</label>
                    <select id="reason" name="reason" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-hut-green focus:outline-none focus:ring-2 focus:ring-hut-green/20">
                        @foreach(['sale' => 'Sale', 'return' => 'Return', 'recount' => 'Recount', 'damage' => 'Damage', 'expiry' => 'Expiry', 'purchase' => 'Purchase', 'adjustment' => 'Adjustment', 'correction' => 'Correction', 'other' => 'Other'] as $key => $label)
                            <option value="{{ $key }}" {{ old('reason', $adjustment->reason) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea id="notes" name="notes" rows="4" maxlength="1000" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-hut-green focus:outline-none focus:ring-2 focus:ring-hut-green/20">{{ old('notes', $adjustment->notes) }}</textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="rounded-lg bg-hut-green px-5 py-2 text-sm font-semibold text-white hover:bg-hut-green/90">Save changes</button>
                    <a href="{{ route('manager.stock.adjustments.index') }}" class="rounded-lg border border-gray-200 px-5 py-2 text-sm font-medium text-hut-dark hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
