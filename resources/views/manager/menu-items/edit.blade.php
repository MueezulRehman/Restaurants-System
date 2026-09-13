@extends('manager.layout.master')
@section('title', 'Edit Menu Item')

@section('page-content')

    @php
        $currentImageUrl = null;
        if ($item->image) {
            if (is_file(public_path('images/' . $item->image))) {
                $currentImageUrl = asset('images/' . $item->image);
            } elseif (is_file(public_path($item->image))) {
                $currentImageUrl = asset($item->image);
            } elseif (is_file(storage_path('app/public/' . $item->image))) {
                $currentImageUrl = asset('storage/' . $item->image);
            }
        }
    @endphp

    <div class="max-w-2xl">
        <a href="{{ route('manager.menu-items.index') }}" class="text-hut-green text-sm mb-4 inline-block hover:underline">←
            Back to Menu Items</a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-display font-bold text-hut-dark mb-6">Edit Menu Item</h2>

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

            <form action="{{ route('manager.menu-items.update', $item) }}" method="POST" enctype="multipart/form-data"
                class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Item Name *</label>
                    <input type="text" name="name" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        value="{{ old('name', $item->name) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Category *</label>
                    <select name="category_id" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Season</label>
                    <select name="season"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
                        <option value="all-season" {{ old('season', $item->season ?? 'all-season') === 'all-season' ? 'selected' : '' }}>All season</option>
                        <option value="summer" {{ old('season', $item->season ?? '') === 'summer' ? 'selected' : '' }}>Summer
                        </option>
                        <option value="winter" {{ old('season', $item->season ?? '') === 'winter' ? 'selected' : '' }}>Winter
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Barcode</label>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <input type="text" name="barcode" id="product-barcode-input"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                            value="{{ old('barcode', $item->barcode) }}" placeholder="Scan or type the barcode (optional)">
                        @include('manager.partials.barcode-scanner', ['inputId' => 'product-barcode-input'])
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">SKU / Code</label>
                    <input type="text" name="sku"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        value="{{ old('sku', $item->sku) }}" placeholder="Optional SKU / internal code">
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Base Price
                            (Rs.){{ $sizes->isEmpty() ? ' *' : '' }}</label>
                        <input type="number" name="price" step="0.01" min="0" {{ $sizes->isEmpty() ? 'required' : '' }}
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                            value="{{ old('price', $item->price) }}" {{ $sizes->isNotEmpty() ? 'placeholder="Not used when size prices are set"' : '' }}>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Cost Price (Rs.)</label>
                        <input type="number" name="cost_price" step="0.01" min="0"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                            value="{{ old('cost_price', $item->cost_price) }}" placeholder="0">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Unit type</label>
                        <select name="unit_type"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
                            @foreach(['piece' => 'Piece', 'kg' => 'Kilogram (kg)', 'g' => 'Gram (g)', 'liter' => 'Liter (L)', 'dozen' => 'Dozen'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('unit_type', $item->unit_type ?: 'piece') === $value)>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Price per unit (Rs.)</label>
                        <input type="number" name="price_per_unit" step="0.01" min="0"
                            value="{{ old('price_per_unit', $item->price_per_unit) }}"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
                    </div>
                </div>

                @if($sizes->isNotEmpty())
                    <div class="border border-blue-100 bg-blue-50 rounded-lg p-4 space-y-3">
                        <div>
                            <h3 class="text-sm font-semibold text-hut-dark">Available size options</h3>
                            <p class="text-xs text-gray-600 mt-1">Size prices are managed from the Variants and Attributes
                                pages.</p>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-2">
                            @foreach($sizes as $size)
                                <div
                                    class="flex items-center justify-between rounded-lg border border-blue-100 bg-white px-3 py-2 text-sm">
                                    <span class="font-medium text-hut-dark">{{ $size->size_label }}</span>
                                    <span class="text-gray-600">Rs. {{ number_format($size->price) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Unit</label>
                        <input type="text" name="unit"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                            value="{{ old('unit', $item->unit) }}" placeholder="e.g. piece, kg, bottle">
                    </div>
                    <div class="flex items-center gap-2 pt-7">
                        <input type="checkbox" name="has_variants" id="has_variants" value="1" {{ old('has_variants', $item->has_variants) ? 'checked' : '' }} class="rounded">
                        <label for="has_variants" class="text-sm text-hut-dark">Uses variants</label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">{{ old('description', $item->description) }}</textarea>
                </div>

                <div>
                    <x-page.file name="image" label="Food image" accept="image/*"
                        help="Upload a replacement only when the menu image needs updating." />
                    @if($currentImageUrl)
                        <img src="{{ $currentImageUrl }}" alt="{{ $item->name }}"
                            class="mt-3 h-28 w-40 rounded-lg border border-gray-200 object-contain bg-gray-50" />
                        <p class="text-xs text-gray-500 mt-2">Current image: {{ basename($item->image) }}</p>
                    @elseif($item->image)
                        <p class="text-xs text-red-600 mt-2">The saved image file is missing. Upload a replacement.</p>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="available" id="available" value="1" {{ old('available', $item->is_available) ? 'checked' : '' }} class="rounded">
                    <label for="available" class="text-sm text-hut-dark">Available for order</label>
                </div>

                <div class="border border-gray-100 rounded-lg p-3 space-y-3">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="track_stock" id="track_stock" value="1" {{ old('track_stock', $item->track_stock) ? 'checked' : '' }} class="rounded">
                        <label for="track_stock" class="text-sm text-hut-dark">Track stock quantity (for Shop / Medical
                            POS)</label>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-sm text-hut-dark"><input type="checkbox"
                                name="allow_fractional_qty" value="1" @checked(old('allow_fractional_qty', $item->allow_fractional_qty)) class="rounded"> Allow fractional quantities (e.g. 0.5
                            kg)</label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Stock Quantity</label>
                        <input type="number" name="stock_quantity" min="0"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                            value="{{ old('stock_quantity', $item->stock_quantity) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Low Stock Alert Threshold</label>
                        <input type="number" name="low_stock_threshold" min="0"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                            value="{{ old('low_stock_threshold', $item->low_stock_threshold) }}">
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" aria-label="Save menu item" title="Save menu item"
                        class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hut-green text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-hut-green/90">
                        <i class="fas fa-floppy-disk text-sm" aria-hidden="true"></i>
                        <span
                            class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Save
                            changes</span>
                    </button>
                    <a href="{{ route('manager.menu-items.index') }}" aria-label="Cancel" title="Cancel"
                        class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white/80 text-slate-600 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-slate-50">
                        <i class="fas fa-xmark text-sm" aria-hidden="true"></i>
                        <span
                            class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Cancel</span>
                    </a>
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