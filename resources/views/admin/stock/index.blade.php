@extends('layouts.admin')

@section('title', 'Stock Management')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Stock Management</h2>
            <p class="text-sm text-gray-500">
                @if($posMode === 'medical')
                    Adjust medicine batch stock and review inventory changes.
                @else
                    Adjust stock for menu items and variants and review recent changes.
                @endif
            </p>
        </div>
    </div>

    @if($posMode === 'medical')
        {{-- Medical Mode: Inventory Status Dashboard --}}
        <div class="grid md:grid-cols-3 gap-4">
            {{-- Expired Medicines --}}
            <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center text-xl">🔴</div>
                    <div>
                        <h4 class="font-semibold text-hut-dark">Expired</h4>
                        <p class="text-sm text-gray-600">Medicines past expiry date</p>
                    </div>
                </div>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @php
                        $expiredBatches = [];
                        foreach($medicines as $medicine) {
                            foreach($medicine->batches as $batch) {
                                if($batch->expiry_date && $batch->expiry_date->isPast()) {
                                    $expiredBatches[] = ['medicine' => $medicine, 'batch' => $batch];
                                }
                            }
                        }
                    @endphp
                    @forelse($expiredBatches as $item)
                        <div class="p-3 bg-white rounded border border-red-100">
                            <p class="text-sm font-medium text-hut-dark">{{ $item['medicine']->name }}</p>
                            <p class="text-xs text-gray-600">Batch: {{ $item['batch']->batch_number ?? 'N/A' }}</p>
                            <p class="text-xs text-red-600 font-semibold">Expired: {{ $item['batch']->expiry_date->format('M d, Y') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-green-700 font-medium">✓ No expired medicines</p>
                    @endforelse
                </div>
            </div>

            {{-- Expiring Soon (30 days) --}}
            <div class="bg-orange-50 border border-orange-200 rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center text-xl">🟠</div>
                    <div>
                        <h4 class="font-semibold text-hut-dark">Expiring Soon</h4>
                        <p class="text-sm text-gray-600">Within 30 days of expiry</p>
                    </div>
                </div>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @php
                        $expiringSoon = [];
                        foreach($medicines as $medicine) {
                            foreach($medicine->batches as $batch) {
                                if($batch->expiry_date && !$batch->expiry_date->isPast() && $batch->expiry_date->diffInDays(now()) <= 30) {
                                    $expiringSoon[] = ['medicine' => $medicine, 'batch' => $batch];
                                }
                            }
                        }
                    @endphp
                    @forelse($expiringSoon as $item)
                        <div class="p-3 bg-white rounded border border-orange-100">
                            <p class="text-sm font-medium text-hut-dark">{{ $item['medicine']->name }}</p>
                            <p class="text-xs text-gray-600">Batch: {{ $item['batch']->batch_number ?? 'N/A' }}</p>
                            <p class="text-xs text-orange-600 font-semibold">
                                Expires: {{ $item['batch']->expiry_date->format('M d, Y') }} ({{ $item['batch']->expiry_date->diffInDays(now()) }} days)
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-green-700 font-medium">✓ No medicines expiring soon</p>
                    @endforelse
                </div>
            </div>

            {{-- Low Stock Medicines --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center text-xl">🟡</div>
                    <div>
                        <h4 class="font-semibold text-hut-dark">Low Stock</h4>
                        <p class="text-sm text-gray-600">Inventory below minimum level</p>
                    </div>
                </div>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @php
                        $lowStock = [];
                        foreach($medicines as $medicine) {
                            $totalQty = $medicine->batches->sum('quantity');
                            $minStock = $medicine->min_stock ?? 0;
                            if($totalQty <= $minStock) {
                                $lowStock[] = ['medicine' => $medicine, 'qty' => $totalQty, 'min' => $minStock];
                            }
                        }
                    @endphp
                    @forelse($lowStock as $item)
                        <div class="p-3 bg-white rounded border border-yellow-100">
                            <p class="text-sm font-medium text-hut-dark">{{ $item['medicine']->name }}</p>
                            <p class="text-xs text-gray-600">Stock: {{ $item['qty'] }} (Min: {{ $item['min'] }})</p>
                            <p class="text-xs text-yellow-600 font-semibold">Restock needed</p>
                        </div>
                    @empty
                        <p class="text-sm text-green-700 font-medium">✓ All medicines well stocked</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- Stock Adjustment Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-hut-dark mb-4">Adjust Stock</h3>
        
        <form method="POST" action="{{ route('manager.stock.adjust') }}" class="grid md:grid-cols-2 gap-4">
            @csrf

            @if($posMode === 'medical')
                {{-- Medical Mode: Medicine Batch Selection --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Medicine</label>
                    <select id="medicine-select" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                        <option value="">Select a medicine...</option>
                        @foreach($medicines as $medicine)
                            <option value="{{ $medicine->id }}">{{ $medicine->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Batch</label>
                    <select name="item_id" id="batch-select" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent" required>
                        <option value="">Select a batch...</option>
                    </select>
                    <input type="hidden" name="item_type" value="medicine_batch">
                </div>
            @else
                {{-- Restaurant/Retail Mode: Menu Item/Variant Selection --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Type</label>
                    <select name="item_type" id="item-type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                        <option value="menu_item">Menu Item</option>
                        <option value="variant">Variant</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item</label>
                    <select name="item_id" id="item-select" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent" required>
                        <option value="">Select an item...</option>
                        @foreach($items as $item)
                            <option value="menu_item_{{ $item->id }}">{{ $item->name }} (Menu Item)</option>
                            @foreach($item->variants as $variant)
                                <option value="variant_{{ $variant->id }}">{{ $item->name }} / {{ $variant->variant_name }} (Variant)</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Change</label>
                <input type="number" name="quantity" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent" value="0" required>
                <p class="text-xs text-gray-500 mt-1">Use + for additions, - for reductions</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                <select name="reason" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                    <option value="purchase">Purchase</option>
                    <option value="sale">Sale</option>
                    <option value="return">Return</option>
                    <option value="recount">Recount</option>
                    <option value="damage">Damage</option>
                    <option value="expiry">Expiry</option>
                    <option value="adjustment">Adjustment</option>
                    <option value="correction">Correction</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent" placeholder="Optional notes about this adjustment..."></textarea>
            </div>

            <div class="md:col-span-2 flex gap-3">
                <button type="submit" class="px-6 py-2 rounded-lg bg-hut-green text-white font-medium hover:bg-hut-green/90">Update Stock</button>
                @if($posMode !== 'medical')
                    <button type="reset" class="px-6 py-2 rounded-lg bg-gray-200 text-gray-700 font-medium hover:bg-gray-300">Clear</button>
                @endif
            </div>
        </form>
    </div>

    {{-- Current Inventory Display --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-hut-dark">Current Inventory</h3>
        </div>

        @if($posMode === 'medical')
            {{-- Medical: Show Medicines and Batches --}}
            @forelse($medicines as $medicine)
                <div class="border-b border-gray-100 last:border-b-0">
                    <div class="px-6 py-3 bg-gray-50 font-semibold text-hut-dark">
                        {{ $medicine->name }}
                        @if($medicine->requires_prescription)
                            <span class="ml-2 text-xs bg-amber-100 text-amber-800 px-2 py-1 rounded">Rx</span>
                        @endif
                    </div>
                    
                    @if($medicine->batches->isEmpty())
                        <div class="px-6 py-3 text-sm text-gray-500">
                            <p class="italic">No batch stock. Add a purchase batch to enable sales.</p>
                        </div>
                    @else
                        <table class="w-full">
                            <thead class="bg-white">
                                <tr class="text-xs text-gray-600 uppercase tracking-wide">
                                    <th class="px-6 py-2 text-left">Batch #</th>
                                    <th class="px-6 py-2 text-left">Qty</th>
                                    <th class="px-6 py-2 text-left">Mfg Date</th>
                                    <th class="px-6 py-2 text-left">Expiry</th>
                                    <th class="px-6 py-2 text-left">Price</th>
                                    <th class="px-6 py-2 text-left">Rack</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($medicine->batches as $batch)
                                    <tr class="border-t border-gray-100 hover:bg-gray-50">
                                        <td class="px-6 py-3 text-sm font-medium">{{ $batch->batch_number ?? 'N/A' }}</td>
                                        <td class="px-6 py-3 text-sm">
                                            <span class="inline-block px-3 py-1 rounded-full font-semibold
                                                @if($batch->quantity <= ($medicine->min_stock ?? 0))
                                                    bg-red-100 text-red-800
                                                @elseif($batch->quantity <= ($medicine->min_stock ?? 0) * 1.5)
                                                    bg-yellow-100 text-yellow-800
                                                @else
                                                    bg-green-100 text-green-800
                                                @endif">
                                                {{ $batch->quantity }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-sm text-gray-600">{{ optional($batch->mfg_date)->format('M d, Y') ?? '—' }}</td>
                                        <td class="px-6 py-3 text-sm">
                                            @if($batch->expiry_date)
                                                <span class="
                                                    @if($batch->expiry_date->isPast())
                                                        text-red-600 font-semibold
                                                    @elseif($batch->expiry_date->diffInDays(now()) <= 30)
                                                        text-orange-600 font-semibold
                                                    @else
                                                        text-gray-600
                                                    @endif
                                                ">
                                                    {{ $batch->expiry_date->format('M d, Y') }}
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 text-sm">Rs. {{ number_format($batch->selling_price, 2) }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-600">{{ $batch->rack_number ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    <p class="italic">No medicines configured for this store yet.</p>
                </div>
            @endforelse
        @else
            {{-- Restaurant/Retail: Show Menu Items and Variants --}}
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">SKU</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <div>
                                    <p class="font-medium text-hut-dark">{{ $item->name }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600">Menu Item</td>
                            <td class="px-6 py-3">
                                <span class="inline-block px-3 py-1 rounded-full font-semibold
                                    @if($item->track_stock && $item->isLowStock())
                                        bg-red-100 text-red-800
                                    @elseif($item->track_stock)
                                        bg-green-100 text-green-800
                                    @else
                                        bg-gray-100 text-gray-600
                                    @endif">
                                    {{ $item->track_stock ? $item->stock_quantity : 'N/T' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $item->sku ?? '—' }}</td>
                        </tr>

                        @if($item->variants->isNotEmpty())
                            @foreach($item->variants as $variant)
                                <tr class="hover:bg-gray-50 bg-gray-50">
                                    <td class="px-6 py-3 pl-12">
                                        <p class="text-sm text-gray-600">{{ $variant->variant_name }}</p>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-600">Variant</td>
                                    <td class="px-6 py-3">
                                        <span class="inline-block px-3 py-1 rounded-full font-semibold
                                            @if($variant->quantity_available <= 0)
                                                bg-red-100 text-red-800
                                            @elseif($variant->quantity_available <= 5)
                                                bg-yellow-100 text-yellow-800
                                            @else
                                                bg-green-100 text-green-800
                                            @endif">
                                            {{ $variant->quantity_available }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $variant->sku ?? '—' }}</td>
                                </tr>
                            @endforeach
                        @endif
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                <p class="italic">No menu items configured yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>

    {{-- Recent Stock Adjustments --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-hut-dark">Recent Adjustments</h3>
        </div>

        <table class="w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Change</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Reason</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">User</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($adjustments as $adjustment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <p class="text-sm font-medium text-hut-dark">
                                @if($adjustment->variant)
                                    {{ $adjustment->variant->menuItem->name ?? 'Item' }} / {{ $adjustment->variant->variant_name }}
                                @else
                                    {{ $adjustment->notes ?? 'Stock Adjustment' }}
                                @endif
                            </p>
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-block px-3 py-1 rounded-full font-semibold text-sm
                                @if($adjustment->change_quantity > 0)
                                    bg-green-100 text-green-800
                                @else
                                    bg-red-100 text-red-800
                                @endif">
                                {{ $adjustment->change_quantity > 0 ? '+' : '' }}{{ $adjustment->change_quantity }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ ucfirst($adjustment->reason) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $adjustment->user->name ?? 'System' }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $adjustment->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <p class="italic">No adjustments yet.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($posMode === 'medical')
<script>
document.getElementById('medicine-select').addEventListener('change', function(e) {
    const medicineId = e.target.value;
    const batchSelect = document.getElementById('batch-select');
    
    // Clear previous options
    batchSelect.innerHTML = '<option value="">Select a batch...</option>';
    
    if (!medicineId) return;
    
    // Find the selected medicine and populate batches
    const medicine = @json($medicines->keyBy('id'));
    if (medicine[medicineId] && medicine[medicineId].batches) {
        medicine[medicineId].batches.forEach(batch => {
            const option = document.createElement('option');
            option.value = 'medicine_batch_' + batch.id;
            const expiryText = batch.expiry_date ? ' (Exp: ' + new Date(batch.expiry_date).toLocaleDateString() + ')' : '';
            option.textContent = (batch.batch_number || 'Batch ' + batch.id) + ' — Qty: ' + batch.quantity + expiryText;
            batchSelect.appendChild(option);
        });
    }
});
</script>
@endif
@endsection
