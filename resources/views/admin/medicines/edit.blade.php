@extends('layouts.admin')
@section('title', 'Edit Medicine')

@section('content')
<div class="max-w-4xl">
    <a href="{{ route('manager.medicines.index') }}" class="text-hut-green text-sm mb-4 inline-block hover:underline">← Back to Medicines</a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-display font-bold text-hut-dark mb-6">Edit Medicine</h2>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                <p class="font-medium mb-2">Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('manager.medicines.update', $medicine) }}" method="POST" class="space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Medicine Name *</label>
                    <input type="text" name="name" required value="{{ old('name', $medicine->name) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Generic Name</label>
                    <input type="text" name="generic_name" value="{{ old('generic_name', $medicine->generic_name) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Image URL</label>
                    <input type="text" name="image" value="{{ old('image', $medicine->image) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Optional image URL">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Tax (%)</label>
                    <input type="number" name="tax" value="{{ old('tax', $medicine->tax) }}" step="0.01" min="0" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="e.g. 5.00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Dosage Form</label>
                    <input type="text" name="dosage_form" value="{{ old('dosage_form', $medicine->dosage_form) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Tablet, Syrup, Capsule">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Strength</label>
                    <input type="text" name="strength" value="{{ old('strength', $medicine->strength) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="500 mg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Category</label>
                    <select name="category_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $medicine->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">New Category</label>
                    <input type="text" name="new_category_name" value="{{ old('new_category_name') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Create new category">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $medicine->sku) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Barcode</label>
                    <input type="text" name="barcode" value="{{ old('barcode', $medicine->barcode) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Minimum Stock</label>
                    <input type="number" name="min_stock" value="{{ old('min_stock', $medicine->min_stock) }}" min="0" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Tax (%)</label>
                    <input type="number" name="tax" value="{{ old('tax', $medicine->tax) }}" step="0.01" min="0" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="requires_prescription" name="requires_prescription" value="1" {{ old('requires_prescription', $medicine->requires_prescription) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-hut-green focus:ring-hut-green">
                    <label for="requires_prescription" class="text-sm text-hut-dark">Requires Prescription</label>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="track_stock" name="track_stock" value="1" {{ old('track_stock', $medicine->track_stock) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-hut-green focus:ring-hut-green">
                    <label for="track_stock" class="text-sm text-hut-dark">Track Stock</label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Description</label>
                <textarea name="description" rows="4" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green">{{ old('description', $medicine->description) }}</textarea>
            </div>

            <div class="flex gap-3 pt-3">
                <button type="submit" class="bg-hut-green text-white px-6 py-2 rounded-lg font-medium hover:bg-hut-green/90">Update Medicine</button>
                <a href="{{ route('manager.medicines.index') }}" class="border border-gray-200 text-hut-dark px-6 py-2 rounded-lg font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const barcodeInput = document.querySelector('input[name="barcode"]');
    if (!barcodeInput) return;

    const nameInput = document.querySelector('input[name="name"]');
    const priceInput = document.querySelector('input[name="price"]');
    const costInput = document.querySelector('input[name="cost_price"]');
    const skuInput = document.querySelector('input[name="sku"]');
    const descInput = document.querySelector('textarea[name="description"]');
    const genericInput = document.querySelector('input[name="generic_name"]');

    // Status line under barcode
    let status = document.getElementById('barcode-lookup-status');
    if (!status) {
        status = document.createElement('p');
        status.id = 'barcode-lookup-status';
        status.className = 'mt-1 text-xs text-gray-500';
        barcodeInput.parentElement?.appendChild(status);
    }

    const lookupUrl = @json(route('manager.barcode.lookup'));
    let timer = null;
    let lastQueried = '';

    function setStatus(msg, ok) {
        status.textContent = msg || '';
        status.className = 'mt-1 text-xs ' + (ok === true ? 'text-green-600' : ok === false ? 'text-amber-600' : 'text-gray-500');
    }

    function fillIfEmpty(el, value) {
        if (!el || value === null || value === undefined || value === '') return;
        // Always fill name/price when user is adding via barcode (override empty or if they just scanned)
        if (!el.value || el.dataset.barcodeFilled === '1') {
            el.value = value;
            el.dataset.barcodeFilled = '1';
            el.classList.add('ring-1', 'ring-green-300');
            setTimeout(() => el.classList.remove('ring-1', 'ring-green-300'), 1200);
        }
    }

    function forceFill(el, value) {
        if (!el || value === null || value === undefined || value === '') return;
        el.value = value;
        el.dataset.barcodeFilled = '1';
        el.classList.add('ring-1', 'ring-green-300');
        setTimeout(() => el.classList.remove('ring-1', 'ring-green-300'), 1200);
    }

    function runLookup(force) {
        const code = (barcodeInput.value || '').trim().replace(/\s+/g, '');
        if (code.length < 4) {
            setStatus('');
            return;
        }
        if (!force && code === lastQueried) return;
        lastQueried = code;
        setStatus('Looking up barcode…');

        fetch(lookupUrl + '?barcode=' + encodeURIComponent(code), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(r => r.json())
            .then(data => {
                if (!data.found) {
                    setStatus(data.message || 'No match. Enter name and price manually.', false);
                    return;
                }
                // Prefer filling name always from barcode lookup when scanning
                forceFill(nameInput, data.name);
                if (data.generic_name) forceFill(genericInput, data.generic_name);
                if (data.sku && skuInput && !skuInput.value) forceFill(skuInput, data.sku);
                if (data.description && descInput && !descInput.value) forceFill(descInput, data.description);

                if (data.price !== null && data.price !== undefined) {
                    forceFill(priceInput, data.price);
                }
                if (data.cost_price !== null && data.cost_price !== undefined) {
                    forceFill(costInput, data.cost_price);
                }

                if (data.price !== null && data.price !== undefined) {
                    setStatus((data.message || 'Found') + ' Name and price filled.', true);
                } else {
                    setStatus((data.message || 'Name found.') + ' Enter selling price manually.', false);
                }
            })
            .catch(() => setStatus('Lookup failed. Check connection or enter details manually.', false));
    }

    barcodeInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            runLookup(true);
            nameInput?.focus();
        }
    });
    barcodeInput.addEventListener('blur', () => runLookup(false));
    barcodeInput.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => runLookup(false), 400);
    });
})();
</script>

@endsection
