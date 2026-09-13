@extends('manager.layout.master')
@section('title', 'Add Menu Item')

@section('page-content')

    <div class="max-w-2xl">
        <a href="{{ route('manager.menu-items.index') }}" class="text-hut-green text-sm mb-4 inline-block hover:underline">←
            Back to Menu Items</a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-display font-bold text-hut-dark mb-6">New Menu Item</h2>

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

            <form action="{{ route('manager.menu-items.store') }}" method="POST" enctype="multipart/form-data"
                class="space-y-4">
                @csrf

                <div class="rounded-xl border border-hut-yellow/40 bg-hut-yellow/10 p-4 space-y-2">
                    <p class="text-xs font-bold uppercase tracking-wide text-hut-dark">Quick register with barcode</p>
                    <p class="text-xs text-gray-600">Scan the product barcode first. The system will try to fill
                        <strong>name</strong> and <strong>price</strong> automatically so you do not type every field by
                        hand.
                    </p>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Barcode</label>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <input type="text" name="barcode" autofocus
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:border-hut-green focus:ring-2 focus:ring-hut-green/20"
                            value="{{ old('barcode') }}" placeholder="Scan or type barcode, then press Enter"
                            id="product-barcode-input" autocomplete="off">
                        @include('manager.partials.barcode-scanner', ['inputId' => 'product-barcode-input'])
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Item Name *</label>
                    <input type="text" name="name" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        value="{{ old('name') }}" placeholder="Auto-filled from barcode when possible">
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Category *</label>
                    <select name="category_id" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $selectedCategoryId ?? 0) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Season</label>
                    <select name="season"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
                        <option value="all-season" {{ old('season', 'all-season') === 'all-season' ? 'selected' : '' }}>All
                            season</option>
                        <option value="summer" {{ old('season') === 'summer' ? 'selected' : '' }}>Summer</option>
                        <option value="winter" {{ old('season') === 'winter' ? 'selected' : '' }}>Winter</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">SKU / Code</label>
                    <input type="text" name="sku"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        value="{{ old('sku') }}" placeholder="Optional — often same as barcode">
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Base Price (Rs.) *</label>
                        <input type="number" name="price" step="0.01" min="0" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                            value="{{ old('price') }}" placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Cost Price (Rs.)</label>
                        <input type="number" name="cost_price" step="0.01" min="0"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                            value="{{ old('cost_price') }}" placeholder="0">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Unit type</label>
                        <select name="unit_type"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
                            @foreach(['piece' => 'Piece', 'kg' => 'Kilogram (kg)', 'g' => 'Gram (g)', 'liter' => 'Liter (L)', 'dozen' => 'Dozen'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('unit_type', 'piece') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Price per unit (Rs.)</label>
                        <input type="number" name="price_per_unit" step="0.01" min="0" value="{{ old('price_per_unit') }}"
                            placeholder="Defaults to base price"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Unit</label>
                        <input type="text" name="unit"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                            value="{{ old('unit') }}" placeholder="e.g. piece, kg, bottle">
                    </div>
                    <div class="flex items-center gap-2 pt-7">
                        <input type="checkbox" name="has_variants" id="has_variants" value="1" {{ old('has_variants') ? 'checked' : '' }} class="rounded">
                        <label for="has_variants" class="text-sm text-hut-dark">Uses variants</label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        placeholder="Item description">{{ old('description') }}</textarea>
                </div>

                <x-page.file name="image" label="Food image" accept="image/*"
                    help="Use a clear square image for the menu card." />

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="available" id="available" value="1" {{ old('available') ? 'checked' : '' }}
                        class="rounded">
                    <label for="available" class="text-sm text-hut-dark">Available for order</label>
                </div>

                <div class="border border-gray-100 rounded-lg p-3 space-y-3">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="track_stock" id="track_stock" value="1" {{ old('track_stock') ? 'checked' : '' }} class="rounded">
                        <label for="track_stock" class="text-sm text-hut-dark">Track stock quantity (for Shop / Medical
                            POS)</label>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-sm text-hut-dark"><input type="checkbox"
                                name="allow_fractional_qty" value="1" @checked(old('allow_fractional_qty')) class="rounded">
                            Allow fractional quantities (e.g. 0.5 kg)</label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Stock Quantity</label>
                        <input type="number" name="stock_quantity" min="0"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                            value="{{ old('stock_quantity', 0) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Low Stock Alert Threshold</label>
                        <input type="number" name="low_stock_threshold" min="0"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                            value="{{ old('low_stock_threshold', 5) }}">
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" aria-label="Save menu item" title="Save menu item"
                        class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hut-green text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-hut-green/90">
                        <i class="fas fa-floppy-disk text-sm" aria-hidden="true"></i>
                        <span
                            class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100">Save
                            menu item</span>
                    </button>
                    <a href="{{ route('manager.menu-items.index') }}" aria-label="Cancel" title="Cancel"
                        class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50">
                        <i class="fas fa-xmark text-sm" aria-hidden="true"></i>
                        <span
                            class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100">Cancel</span>
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