@extends('layouts.admin')

@section('title', 'Stock')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Stock Management</h2>
            <p class="text-sm text-gray-500">Adjust stock for menu items and variants and review recent changes.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
        <form method="POST" action="{{ route('manager.stock.adjust') }}" class="grid md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Target</label>
                <select name="item_type" class="mt-1 w-full border rounded-lg px-3 py-2">
                    <option value="menu_item">Menu Item</option>
                    <option value="variant">Variant</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Item</label>
                <select name="item_id" class="mt-1 w-full border rounded-lg px-3 py-2">
                    @foreach($items as $item)
                        <option value="menu_item_{{ $item->id }}">{{ $item->name }} (menu item)</option>
                        @foreach($item->variants as $variant)
                            <option value="variant_{{ $variant->id }}">{{ $item->name }} / {{ $variant->variant_name }} (variant)</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Quantity change</label>
                <input type="number" name="quantity" class="mt-1 w-full border rounded-lg px-3 py-2" value="0" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Reason</label>
                <select name="reason" class="mt-1 w-full border rounded-lg px-3 py-2">
                    @foreach(['purchase','adjustment','return','recount','damage','expiry','correction','other'] as $reason)
                        <option value="{{ $reason }}">{{ ucfirst($reason) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" rows="2" class="mt-1 w-full border rounded-lg px-3 py-2"></textarea>
            </div>
            <div class="md:col-span-2">
                <button class="px-4 py-2 rounded-lg bg-hut-dark text-white">Update stock</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Item</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Change</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">User</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($adjustments as $adjustment)
                    <tr>
                        <td class="px-4 py-3">{{ $adjustment->variant ? $adjustment->variant->variant_name : 'Menu item' }}</td>
                        <td class="px-4 py-3">{{ $adjustment->change_quantity > 0 ? '+' : '' }}{{ $adjustment->change_quantity }}</td>
                        <td class="px-4 py-3">{{ $adjustment->getReasonLabel() }}</td>
                        <td class="px-4 py-3">{{ $adjustment->user->name ?? 'System' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No stock adjustments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
