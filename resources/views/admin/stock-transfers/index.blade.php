@extends('layouts.admin')
@section('title', 'Stock Transfers')
@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Stock Transfers</h1>
            <p class="mt-1 text-sm text-gray-500">Record stock movement between locations and track transfer status.</p>
        </div>

        <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Record transfer</h2>
            <form method="POST" action="{{ route('manager.stock-transfers.store') }}"
                class="mt-4 grid gap-3 md:grid-cols-5">
                @csrf
                <input name="from_location" required maxlength="100" placeholder="From location"
                    class="rounded-lg border px-3 py-2 text-sm">
                <input name="to_location" required maxlength="100" placeholder="To location"
                    class="rounded-lg border px-3 py-2 text-sm">
                <input name="item_name" required maxlength="255" placeholder="Item or SKU"
                    class="rounded-lg border px-3 py-2 text-sm">
                <input name="quantity" required type="number" min="0.001" step="0.001" placeholder="Quantity"
                    class="rounded-lg border px-3 py-2 text-sm">
                <button class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Record transfer</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Item</th>
                            <th class="px-4 py-3">From</th>
                            <th class="px-4 py-3">To</th>
                            <th class="px-4 py-3">Qty</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Update</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($transfers as $transfer)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $transfer->item_name }}</td>
                                <td class="px-4 py-3">{{ $transfer->from_location }}</td>
                                <td class="px-4 py-3">{{ $transfer->to_location }}</td>
                                <td class="px-4 py-3">{{ $transfer->quantity }}</td>
                                <td class="px-4 py-3">{{ str_replace('_', ' ', ucfirst($transfer->status)) }}</td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('manager.stock-transfers.update', $transfer) }}"
                                        class="flex gap-2">
                                        @csrf @method('PATCH')
                                        <select name="status" class="rounded border px-2 py-1 text-xs">
                                            @foreach(['pending', 'in_transit', 'completed', 'cancelled'] as $status)
                                                <option value="{{ $status }}" @selected($transfer->status === $status)>
                                                    {{ str_replace('_', ' ', ucfirst($status)) }}</option>
                                            @endforeach
                                        </select>
                                        <button class="text-xs font-semibold text-hut-blue">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-gray-500">No stock transfers recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transfers->hasPages())
            <div class="border-t p-4">{{ $transfers->links() }}</div> @endif
        </section>
    </div>
@endsection