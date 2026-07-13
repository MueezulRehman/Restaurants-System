@extends('layouts.admin')
@section('title', $posConfig['title'])

@section('content')
@if(isset($errors) && $errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <p class="font-semibold mb-1">Could not complete the sale:</p>
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="grid grid-cols-1 lg:grid-cols-[minmax(0,2fr)_360px] gap-4 items-start">

    <div class="lg:col-span-1 space-y-4 min-w-0">
        <div class="rounded-2xl border border-gray-200 bg-linear-to-r from-white to-amber-50 p-4 shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Modern retail counter</p>
                    <h2 class="font-display font-semibold text-hut-dark">Search, scan, and checkout in a few taps</h2>
                </div>
                <div class="rounded-full border border-hut-yellow/30 bg-white px-3 py-1 text-sm text-hut-dark shadow-sm">{{ $customers->count() }} customer profiles</div>
            </div>
            <input type="text" id="scan-input" autofocus
                placeholder="{{ $posConfig['search_placeholder'] }}"
                class="w-full border border-gray-200 rounded-lg px-4 py-3 text-base focus:border-hut-yellow focus:ring-1 focus:ring-hut-yellow outline-none">
            <p class="text-xs text-gray-400 mt-1">Scan a barcode, or type a name / code and press Enter.</p>
        </div>

        <div id="search-results" class="grid grid-cols-2 sm:grid-cols-3 gap-3"></div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 max-h-[calc(100vh-8rem)] overflow-hidden flex flex-col">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="font-display font-semibold text-hut-dark text-sm">{{ $posConfig['item_label_plural'] }} Catalog</h3>
                    <p class="text-xs text-gray-500">Browse by category or search by medicine name, generic, or batch.</p>
                </div>
                @if(($showMedicalItems ?? false))
                    <div class="flex-1 md:max-w-md">
                        <input type="text" id="medical-search" placeholder="Search medicine or batch" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-hut-yellow focus:ring-1 focus:ring-hut-yellow outline-none">
                    </div>
                @endif
            </div>

            @if(($showMedicalItems ?? false))
                <div class="mt-3 flex flex-wrap gap-2" id="medical-category-pills">
                    <button type="button" class="medical-category-pill rounded-full border border-hut-yellow/40 bg-hut-yellow/10 px-3 py-1.5 text-xs font-semibold text-hut-dark {{ empty($selectedCategory) || $selectedCategory === 'all' ? 'active' : '' }}" data-category="all">All</button>
                    @foreach(($medicineCategories ?? collect()) as $category)
                        <button type="button" class="medical-category-pill rounded-full border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:border-hut-yellow hover:text-hut-dark {{ $selectedCategory === 'cat-' . $category->id ? 'active bg-hut-yellow/10 border-hut-yellow/40 font-bold' : '' }}" data-category="cat-{{ $category->id }}">{{ $category->name }}</button>
                    @endforeach
                    @if(($uncategorized ?? collect())->count())
                        <button type="button" class="medical-category-pill rounded-full border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:border-hut-yellow hover:text-hut-dark {{ $selectedCategory === 'uncategorized' ? 'active bg-hut-yellow/10 border-hut-yellow/40 font-bold' : '' }}" data-category="uncategorized">Uncategorized</button>
                    @endif
                </div>
                <div class="mt-2 text-[11px] text-gray-500">
                    @if(!empty($selectedCategory) && $selectedCategory !== 'all' && $selectedCategory !== 'uncategorized')
                        Showing: {{ $selectedCategoryName ?? 'Selected category' }}
                    @elseif($selectedCategory === 'uncategorized')
                        Showing: Uncategorized medicines
                    @else
                        Showing: All medicines
                    @endif
                </div>
            @endif

            <div class="mt-4 flex-1 overflow-y-auto pr-1" id="all-items">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @if(($showMedicalItems ?? false))
                    @foreach(($medicineCategories ?? collect()) as $category)
                        @if($category->medicines->isNotEmpty())
                             <div class="col-span-full" data-category-group="cat-{{ $category->id }}" data-category-id="{{ $category->id }}" data-category-name="{{ $category->name }}">
                                <div class="mb-2 rounded-lg bg-gray-50 px-3 py-2 text-sm font-semibold text-hut-dark">{{ $category->name }}</div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    @foreach($category->medicines as $medicine)
                                        @if($medicine->batches->isEmpty())
                                            <div class="bg-white border border-dashed border-gray-200 rounded-xl p-3 text-left shadow-sm" data-category-id="{{ $medicine->category_id ?? 0 }}" data-category-name="{{ $medicine->category?->name ?? 'Uncategorized' }}" data-search="{{ strtolower($medicine->name . ' ' . ($medicine->generic_name ?? '')) }}">
                                                <p class="font-display font-semibold text-sm text-hut-dark truncate">{{ $medicine->name }}</p>
                                                @if(!empty($medicine->dosage_form))
                                                    <p class="text-[11px] text-gray-500">Form: {{ $medicine->dosage_form }}</p>
                                                @endif
                                                @if(!empty($medicine->strength))
                                                    <p class="text-[11px] text-gray-500">Strength: {{ $medicine->strength }}</p>
                                                @endif
                                                <p class="text-[11px] text-gray-500">Category: {{ $medicine->category?->name ?? 'Uncategorized' }}</p>
                                                <p class="text-xs text-gray-400">No batch stock yet</p>
                                            </div>
                                        @else
                                            @foreach($medicine->batches as $batch)
                                                <button type="button" class="pos-item-card bg-white border border-gray-100 rounded-xl p-3 text-left shadow-sm hover:shadow-md hover:border-hut-yellow transition"
                                                    data-type="medicine_batch" data-id="{{ $batch->id }}"
                                                    data-name="{{ $medicine->name }} — Batch {{ $batch->batch_number }}"
                                                    data-sku="{{ $medicine->sku }}"
                                                    data-price="{{ $batch->selling_price }}"
                                                    data-stock="{{ $batch->quantity }}"
                                                    data-category-id="{{ $medicine->category_id ?? 0 }}"
                                                    data-category-name="{{ $medicine->category?->name ?? 'Uncategorized' }}"
                                                    data-search="{{ strtolower($medicine->name . ' ' . ($medicine->generic_name ?? '') . ' ' . $batch->batch_number) }}">
                                                    <p class="font-display font-semibold text-sm text-hut-dark truncate">{{ $medicine->name }}</p>
                                                    @if(!empty($medicine->dosage_form))
                                                        <p class="text-[11px] text-gray-500">Form: {{ $medicine->dosage_form }}</p>
                                                    @endif
                                                    @if(!empty($medicine->strength))
                                                        <p class="text-[11px] text-gray-500">Strength: {{ $medicine->strength }}</p>
                                                    @endif
                                                    <p class="text-[11px] text-gray-500">Category: {{ $medicine->category?->name ?? 'Uncategorized' }}</p>
                                                    <p class="text-xs text-gray-400">Batch: {{ $batch->batch_number }} · Exp: {{ $batch->expiry_date?->toDateString() ?? 'N/A' }}</p>
                                                    <p class="text-xs text-hut-green font-medium mt-1">Rs. {{ number_format($batch->selling_price) }}</p>
                                                    <p class="text-[11px] {{ $batch->quantity <= ($medicine->min_stock ?? 0) ? 'text-hut-red' : 'text-gray-400' }} mt-0.5">Stock: {{ $batch->quantity }}</p>
                                                </button>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if(($uncategorized ?? collect())->count())
                        <div class="col-span-full" data-category-group="uncategorized" data-category-id="" data-category-name="Uncategorized">
                            <div class="mb-2 rounded-lg bg-gray-50 px-3 py-2 text-sm font-semibold text-hut-dark">Uncategorized</div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach($uncategorized as $medicine)
                                    @if($medicine->batches->isEmpty())
                                        <div class="bg-white border border-dashed border-gray-200 rounded-xl p-3 text-left shadow-sm" data-category-id="{{ $medicine->category_id ?? 0 }}" data-category-name="{{ $medicine->category?->name ?? 'Uncategorized' }}" data-search="{{ strtolower($medicine->name . ' ' . ($medicine->generic_name ?? '')) }}">
                                            <p class="font-display font-semibold text-sm text-hut-dark truncate">{{ $medicine->name }}</p>
                                            <p class="text-xs text-gray-400">No batch stock yet</p>
                                        </div>
                                    @else
                                        @foreach($medicine->batches as $batch)
                                            <button type="button" class="pos-item-card bg-white border border-gray-100 rounded-xl p-3 text-left shadow-sm hover:shadow-md hover:border-hut-yellow transition"
                                                data-type="medicine_batch" data-id="{{ $batch->id }}"
                                                data-name="{{ $medicine->name }} — Batch {{ $batch->batch_number }}"
                                                data-sku="{{ $medicine->sku }}"
                                                data-price="{{ $batch->selling_price }}"
                                                data-stock="{{ $batch->quantity }}"
                                                data-category-id="{{ $medicine->category_id ?? 0 }}"
                                                data-category-name="{{ $medicine->category?->name ?? 'Uncategorized' }}"
                                                data-search="{{ strtolower($medicine->name . ' ' . ($medicine->generic_name ?? '') . ' ' . $batch->batch_number) }}">
                                                <p class="font-display font-semibold text-sm text-hut-dark truncate">{{ $medicine->name }}</p>
                                                <p class="text-xs text-gray-400">Batch: {{ $batch->batch_number }} · Exp: {{ $batch->expiry_date?->toDateString() ?? 'N/A' }}</p>
                                                <p class="text-xs text-hut-green font-medium mt-1">Rs. {{ number_format($batch->selling_price) }}</p>
                                                <p class="text-[11px] {{ $batch->quantity <= ($medicine->min_stock ?? 0) ? 'text-hut-red' : 'text-gray-400' }} mt-0.5">Stock: {{ $batch->quantity }}</p>
                                            </button>
                                        @endforeach
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    @foreach($items as $item)
                        @if($item->variants->count())
                            @foreach($item->variants as $variant)
                                <button type="button" class="pos-item-card bg-white border border-gray-100 rounded-xl p-3 text-left shadow-sm hover:shadow-md hover:border-hut-yellow transition"
                                    data-type="variant" data-id="{{ $variant->id }}"
                                    data-name="{{ $item->name }} — {{ $variant->variant_name }}"
                                    data-sku="{{ $variant->sku }}"
                                    data-price="{{ $variant->getEffectivePrice() }}"
                                    data-stock="{{ $variant->quantity_available }}">
                                    <p class="font-display font-semibold text-sm text-hut-dark truncate">{{ $item->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $variant->variant_name }} · {{ $variant->sku }}</p>
                                    <p class="text-xs text-hut-green font-medium mt-1">Rs. {{ number_format($variant->getEffectivePrice()) }}</p>
                                </button>
                            @endforeach
                        @else
                            <button type="button" class="pos-item-card bg-white border border-gray-100 rounded-xl p-3 text-left shadow-sm hover:shadow-md hover:border-hut-yellow transition"
                                data-type="menu_item" data-id="{{ $item->id }}"
                                data-name="{{ $item->name }}"
                                data-sku="{{ $item->sku }}"
                                data-price="{{ $item->price ?? 0 }}"
                                data-track-stock="{{ $item->track_stock ? '1' : '0' }}"
                                data-stock="{{ $item->stock_quantity }}">
                                <p class="font-display font-semibold text-sm text-hut-dark truncate">{{ $item->name }}</p>
                                <p class="text-xs text-gray-400">{{ $item->sku ?: 'No code' }}</p>
                                <p class="text-xs text-hut-green font-medium mt-1">Rs. {{ number_format($item->price ?? 0) }}</p>
                                @if($item->track_stock)
                                    <p class="text-[11px] {{ $item->isLowStock() ? 'text-hut-red' : 'text-gray-400' }} mt-0.5">Stock: {{ $item->stock_quantity }}</p>
                                @endif
                            </button>
                        @endif
                    @endforeach
                @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Cart / checkout panel --}}
    <div id="pos-cart-panel" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 h-fit lg:self-start max-h-[calc(100vh-2rem)] overflow-y-auto cursor-move select-none">
        <h2 class="font-display font-bold text-hut-dark mb-3">Current Bill</h2>

        <div id="cart-lines" class="space-y-2 max-h-64 overflow-y-auto mb-3">
            <p id="cart-empty" class="text-sm text-gray-400 text-center py-6">No items yet — scan or search a {{ strtolower($posConfig['item_label']) }}.</p>
        </div>

        <div class="border-t border-gray-100 pt-3 space-y-2 text-sm">
            <div class="flex justify-between font-display font-bold text-lg text-hut-dark">
                <span>Total</span>
                <span id="cart-total">Rs. 0</span>
            </div>
            <div class="rounded-lg border border-gray-100 bg-gray-50 p-2 space-y-2">
                <label for="cash-received" class="block text-[11px] font-semibold uppercase tracking-wide text-gray-500">Cash received</label>
                <input type="number" id="cash-received" name="amount_received" form="checkout-form" step="0.01" min="0" placeholder="Enter cash received" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-hut-green focus:ring-hut-green">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Change / balance</span>
                    <span id="cash-summary-text" class="font-semibold text-hut-dark">Rs. 0</span>
                </div>
            </div>
        </div>

        <div id="safety-warning" class="hidden rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800"></div>

        <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 space-y-2">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Customer</p>
                <span class="text-xs text-gray-400">Track balances</span>
            </div>
            <select id="customer-select" name="customer_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
                <option value="">Walk-in customer</option>
                @foreach(($customers ?? collect()) as $customer)
                    <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" data-phone="{{ $customer->phone }}">{{ $customer->name }} • {{ $customer->phone }} @if($customer->balance > 0) (Due Rs. {{ number_format($customer->balance, 2) }}) @endif</option>
                @endforeach
            </select>
            <form method="POST" action="{{ route('manager.customers.store') }}" class="space-y-2">
                @csrf
                <input type="hidden" name="redirect_to_pos" value="1">
                <input type="hidden" name="cart" id="customer-register-cart" value="[]">
                <div class="grid gap-2 sm:grid-cols-2">
                    <input type="text" name="name" required placeholder="New customer name" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
                    <input type="text" name="phone" required placeholder="Phone" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
                </div>
                <button type="submit" class="w-full rounded-lg border border-hut-yellow/40 bg-hut-yellow/10 px-3 py-2 text-sm font-semibold text-hut-dark hover:bg-hut-yellow/20">Register customer</button>
            </form>
        </div>

        <form id="checkout-form" method="POST" action="{{ route('manager.pos.checkout') }}" class="mt-4 space-y-2">
            @csrf
            @if(($posConfig['mode'] ?? '') === 'medical')
                <input type="hidden" name="order_type" value="takeaway">
                <div class="rounded-lg border border-green-100 bg-green-50 px-3 py-2 text-sm text-green-800">Only takeaway orders are supported in medical mode.</div>
                <div class="mt-3 space-y-3">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="prescription" value="1" class="h-4 w-4 rounded border-gray-300 text-hut-green focus:ring-hut-green">
                        <span>Prescription attached</span>
                    </label>
                    <input type="number" name="prescription_doctor_id" placeholder="Doctor ID (optional)" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-hut-green focus:ring-hut-green">
                </div>
            @else
                <select name="order_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    <option value="takeaway">Takeaway</option>
                    <option value="dine_in">Dine-in</option>
                    <option value="table">Table Order</option>
                    <option value="delivery">Delivery</option>
                    <option value="online">Online</option>
                </select>
            @endif
            <div id="table-number-wrapper" class="hidden">
                <input type="text" name="table_number" placeholder="Table number" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <input type="text" name="customer_name" placeholder="Customer name (optional)" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            <input type="text" name="customer_phone" placeholder="Phone (optional)" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            <select name="payment_method" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                <option value="cash">Cash</option>
                <option value="online">Online</option>
            </select>
            <input type="hidden" name="cart" id="cart-input">
            <button type="submit" id="checkout-btn" class="btn-accent w-full text-center" disabled>Complete Sale</button>
        </form>
    </div>
</div>

<script>
(function () {
    const cart = []; // {type, id, quantity, name, price}
    const meta = {}; // key `${type}:${id}` -> {name, price, stock}
    const savedCart = @json($savedCart ?? []);
    const highlightedLine = @json($errorHighlight ?? null);
    const orderTypeSelect = document.querySelector('select[name="order_type"]');
    const tableNumberWrapper = document.getElementById('table-number-wrapper');
    const customerSelect = document.getElementById('customer-select');
    const customerNameInput = document.querySelector('input[name="customer_name"]');
    const customerPhoneInput = document.querySelector('input[name="customer_phone"]');
    const paymentMethodSelect = document.querySelector('select[name="payment_method"]');
    const cashReceivedInput = document.getElementById('cash-received');
    const cashSummaryText = document.getElementById('cash-summary-text');
    let currentTotal = 0;

    function toggleTableField() {
        if (!tableNumberWrapper || !orderTypeSelect) return;
        tableNumberWrapper.style.display = orderTypeSelect.value === 'table' ? 'block' : 'none';
    }

    if (orderTypeSelect) {
        orderTypeSelect.addEventListener('change', toggleTableField);
        toggleTableField();
    }

    if (customerSelect && customerNameInput && customerPhoneInput) {
        customerSelect.addEventListener('change', () => {
            const selected = customerSelect.selectedOptions[0];
            if (!selected || !selected.value) {
                customerNameInput.value = '';
                customerPhoneInput.value = '';
                return;
            }
            customerNameInput.value = selected.dataset.name || '';
            customerPhoneInput.value = selected.dataset.phone || '';
        });
    }

    function updateCashSummary() {
        if (!cashReceivedInput || !cashSummaryText) return;
        const received = parseFloat(cashReceivedInput.value) || 0;
        const difference = received - currentTotal;
        const absDifference = Math.abs(difference);
        const isChange = difference >= 0;
        cashSummaryText.textContent = 'Rs. ' + absDifference.toLocaleString() + (isChange ? ' change' : ' due');
        cashSummaryText.className = 'font-semibold ' + (isChange ? 'text-hut-green' : 'text-hut-red');
    }

    if (paymentMethodSelect && cashReceivedInput) {
        const setCashInputState = () => {
            const isCash = paymentMethodSelect.value === 'cash';
            cashReceivedInput.disabled = !isCash;
            cashReceivedInput.required = isCash;
            cashReceivedInput.placeholder = isCash ? 'Enter cash received' : 'Disabled for online payment';
            if (!isCash) {
                cashReceivedInput.value = '';
            }
            updateCashSummary();
        };

        paymentMethodSelect.addEventListener('change', setCashInputState);
        cashReceivedInput.addEventListener('input', updateCashSummary);
        setCashInputState();
    }

    const scanInput = document.getElementById('scan-input');
    const resultsBox = document.getElementById('search-results');
    const lookupUrl = @json(route('manager.pos.lookup'));
    const medicalSearchInput = document.getElementById('medical-search');
    const medicalCategoryPills = document.getElementById('medical-category-pills');
    const allItemsGrid = document.getElementById('all-items');

    function applyMedicalFilters() {
        if (!medicalSearchInput || !allItemsGrid) return;

        const term = medicalSearchInput.value.trim().toLowerCase();
        const activeCategory = medicalCategoryPills ? document.querySelector('.medical-category-pill.active')?.dataset.category || 'all' : 'all';
        let visibleItemCount = 0;
        let visibleGroupCount = 0;

        allItemsGrid.querySelectorAll('[data-category-group]').forEach((group) => {
            const groupCategoryId = group.dataset.categoryId || '';
            const groupCategoryGroup = group.dataset.categoryGroup || '';
            
            // Check if this group matches the selected category
            const groupMatchesCategory = activeCategory === 'all'
                || (activeCategory === 'uncategorized' && groupCategoryGroup === 'uncategorized')
                || (activeCategory.startsWith('cat-') && activeCategory === `cat-${groupCategoryId}`);

            let groupVisibleCount = 0;
            
            // Filter items within this category group
            group.querySelectorAll('.pos-item-card, [data-search]').forEach((item) => {
                const itemSearchText = (item.dataset.search || '').toLowerCase();
                const matchesSearch = !term || itemSearchText.includes(term);
                const itemCategoryId = item.dataset.categoryId || '';
                
                // Check if this item matches the selected category
                const itemMatchesCategory = activeCategory === 'all'
                    || (activeCategory === 'uncategorized' && (!itemCategoryId || itemCategoryId === '' || item.dataset.categoryName === 'Uncategorized'))
                    || (activeCategory.startsWith('cat-') && activeCategory === `cat-${itemCategoryId}`);

                const shouldShow = matchesSearch && itemMatchesCategory;
                item.style.display = shouldShow ? '' : 'none';
                if (shouldShow) groupVisibleCount++;
            });

            // Show/hide the entire category group header based on whether it has visible items
            const shouldShowGroup = groupMatchesCategory && groupVisibleCount > 0;
            group.style.display = shouldShowGroup ? '' : 'none';
            if (shouldShowGroup) {
                visibleGroupCount++;
                visibleItemCount += groupVisibleCount;
            }
        });

        // Show empty state if no items match
        const existingEmpty = document.getElementById('medical-empty-state');
        if (existingEmpty) existingEmpty.remove();
        if (visibleItemCount === 0) {
            const emptyState = document.createElement('div');
            emptyState.id = 'medical-empty-state';
            emptyState.className = 'col-span-full rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-6 text-center text-sm text-gray-500';
            emptyState.textContent = term 
                ? `No medicines match "${term}" in the ${activeCategory === 'all' ? 'catalog' : 'selected category'}.`
                : 'No medicines in this category.';
            allItemsGrid.appendChild(emptyState);
        }
    }

    if (medicalSearchInput) {
        medicalSearchInput.addEventListener('input', applyMedicalFilters);
    }

    if (medicalCategoryPills) {
        applyMedicalFilters();
        medicalCategoryPills.querySelectorAll('.medical-category-pill').forEach((pill) => {
            pill.addEventListener('click', () => {
            const category = pill.dataset.category || 'all';
            const url = new URL(window.location.href);
            url.searchParams.set('category', category);
            window.location.href = url.toString();
        });
    });
}
    let debounceTimer = null;
    scanInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const term = scanInput.value.trim();
        if (term.length < 2) { resultsBox.innerHTML = ''; return; }
        debounceTimer = setTimeout(() => runLookup(term), 250);
    });

    scanInput.addEventListener('keydown', (e) => {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const term = scanInput.value.trim();
        if (!term) return;
        runLookup(term, true);
    });

    function runLookup(term, autoAddIfSingle) {
        fetch(lookupUrl + '?q=' + encodeURIComponent(term))
            .then(r => r.json())
            .then(data => {
                renderResults(data.items || []);
                if (autoAddIfSingle && data.items && data.items.length === 1 && !data.items[0].has_sizes && data.items[0].variants.length === 0) {
                    addToCart('menu_item', data.items[0].id, data.items[0].name, data.items[0].price, data.items[0].track_stock ? data.items[0].stock_quantity : null);
                    scanInput.value = '';
                    resultsBox.innerHTML = '';
                }
            });
    }

    function renderResults(items) {
        resultsBox.innerHTML = '';
        items.forEach(item => {
            if (item.batches && item.batches.length) {
                item.batches.forEach(b => {
                    resultsBox.insertAdjacentHTML('beforeend', resultCardHtml('medicine_batch', b.id, item.name + ' — Batch ' + b.batch_number, item.sku || '', b.price, b.quantity));
                });
            } else if (item.variants && item.variants.length) {
                item.variants.forEach(v => {
                    resultsBox.insertAdjacentHTML('beforeend', resultCardHtml('variant', v.id, item.name + ' — ' + v.name, v.sku, v.price, v.quantity_available));
                });
            } else if (item.id && item.name) {
                resultsBox.insertAdjacentHTML('beforeend', `<div class="rounded-xl border border-dashed border-gray-200 bg-white p-3 text-sm text-gray-500">
                    <p class="font-semibold text-hut-dark">${escapeHtml(item.name)}</p>
                    <p class="text-xs text-gray-400">No batch stock yet. Add a purchase batch first.</p>
                </div>`);
            }
        });
    }

    function resultCardHtml(type, id, name, sku, price, stock) {
        return `<button type="button" class="result-card bg-hut-yellow/10 border border-hut-yellow/40 rounded-xl p-3 text-left shadow-sm hover:shadow-md transition"
                    data-type="${type}" data-id="${id}" data-name="${escapeHtml(name)}" data-price="${price}" data-stock="${stock === null ? '' : stock}">
                    <p class="font-display font-semibold text-sm text-hut-dark truncate">${escapeHtml(name)}</p>
                    <p class="text-xs text-gray-400">${sku ? escapeHtml(sku) : ''}</p>
                    <p class="text-xs text-hut-green font-medium mt-1">Rs. ${Number(price).toLocaleString()}</p>
                </button>`;
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

    document.addEventListener('click', (e) => {
        const card = e.target.closest('.pos-item-card, .result-card');
        if (!card) return;
        const stock = card.dataset.stock === '' || card.dataset.stock === undefined ? null : parseInt(card.dataset.stock);
        addToCart(card.dataset.type, parseInt(card.dataset.id), card.dataset.name, parseFloat(card.dataset.price), stock);
        if (card.classList.contains('result-card')) {
            scanInput.value = '';
            resultsBox.innerHTML = '';
            scanInput.focus();
        }
    });

    const linesBox = document.getElementById('cart-lines');
    const emptyMsg = document.getElementById('cart-empty');
    const totalBox = document.getElementById('cart-total');
    const cartInput = document.getElementById('cart-input');
    const checkoutBtn = document.getElementById('checkout-btn');
    const cartPanel = document.getElementById('pos-cart-panel');

    if (cartPanel) {
        let isDragging = false;
        let startX = 0;
        let startY = 0;
        let startLeft = 0;
        let startTop = 0;

        cartPanel.addEventListener('pointerdown', (event) => {
            if (event.target.closest('input, select, button, textarea, a')) return;
            isDragging = true;
            cartPanel.setPointerCapture(event.pointerId);
            startX = event.clientX;
            startY = event.clientY;
            const rect = cartPanel.getBoundingClientRect();
            startLeft = rect.left;
            startTop = rect.top;
            cartPanel.style.transition = 'none';
        });

        cartPanel.addEventListener('pointermove', (event) => {
            if (!isDragging) return;
            const deltaX = event.clientX - startX;
            const deltaY = event.clientY - startY;
            cartPanel.style.left = `${startLeft + deltaX}px`;
            cartPanel.style.top = `${startTop + deltaY}px`;
            cartPanel.style.position = 'fixed';
            cartPanel.style.zIndex = '60';
        });

        const stopDragging = () => {
            if (!isDragging) return;
            isDragging = false;
            cartPanel.style.transition = 'transform 0.15s ease';
        };

        cartPanel.addEventListener('pointerup', stopDragging);
        cartPanel.addEventListener('pointercancel', stopDragging);
        cartPanel.addEventListener('pointerleave', stopDragging);
    }

    function addToCart(type, id, name, price, stock) {
        const key = type + ':' + id;
        meta[key] = { name, price, stock };
        const existing = cart.find(l => l.type === type && l.id === id);
        if (existing) {
            if (stock !== null && existing.quantity + 1 > stock) {
                alert('Only ' + stock + ' in stock.');
                return;
            }
            existing.quantity += 1;
        } else {
            if (stock !== null && stock <= 0) {
                alert('Out of stock.');
                return;
            }
            cart.push({ type, id, quantity: 1, name, price });
        }
        renderCart();
    }

    function updateSafetyWarning() {
        const warningBox = document.getElementById('safety-warning');
        if (!warningBox) return;
        const hasMedicalItems = cart.some(line => line.type === 'medicine_batch');
        if (!hasMedicalItems) {
            warningBox.classList.add('hidden');
            warningBox.textContent = '';
            return;
        }
        warningBox.classList.remove('hidden');
        warningBox.textContent = 'Medical safety checks are active: allergy and drug-interaction warnings will be enforced at checkout.';
    }

    function matchesHighlight(line) {
        return highlightedLine && line.type === highlightedLine.type && line.id === highlightedLine.id;
    }

    function hydrateCart() {
        if (!Array.isArray(savedCart) || !savedCart.length) return;
        savedCart.forEach((line) => {
            const type = line.type || 'menu_item';
            const id = parseInt(line.id, 10);
            const quantity = parseInt(line.quantity || 1, 10);
            const name = line.name || `${type} ${id}`;
            const price = parseFloat(line.price || line.unitPrice || 0);
            const stock = line.stock ?? null;
            const key = type + ':' + id;
            meta[key] = { name, price, stock };
            cart.push({ type, id, quantity, name, price });
        });
        renderCart();
    }

    function renderCart() {
        linesBox.querySelectorAll('.cart-line').forEach(el => el.remove());
        let total = 0;

        cart.forEach((line, idx) => {
            const info = meta[line.type + ':' + line.id] || { name: line.name || '', price: line.price || 0, stock: null };
            total += info.price * line.quantity;
            linesBox.insertAdjacentHTML('beforeend', `
                <div class="cart-line flex items-center justify-between text-sm border-b border-gray-50 pb-2 ${matchesHighlight(line) ? 'rounded-lg border border-amber-300 bg-amber-50 px-2 py-2' : ''}">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-hut-dark truncate">${info.name}</p>
                        <p class="text-xs text-gray-400">Rs. ${info.price.toLocaleString()} each</p>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" class="qty-btn w-6 h-6 rounded bg-gray-100 hover:bg-gray-200" data-idx="${idx}" data-dir="-1">−</button>
                        <span class="w-6 text-center">${line.quantity}</span>
                        <button type="button" class="qty-btn w-6 h-6 rounded bg-gray-100 hover:bg-gray-200" data-idx="${idx}" data-dir="1">+</button>
                        <button type="button" class="remove-btn text-hut-red text-xs ml-1" data-idx="${idx}">✕</button>
                    </div>
                </div>`);
        });

        emptyMsg.style.display = cart.length ? 'none' : '';
        currentTotal = total;
        totalBox.textContent = 'Rs. ' + total.toLocaleString();
        cartInput.value = JSON.stringify(cart.map((line) => ({ ...line })));
        const registerCartInput = document.getElementById('customer-register-cart');
        if (registerCartInput) {
            registerCartInput.value = JSON.stringify(cart.map((line) => ({ ...line })));
        }
        checkoutBtn.disabled = cart.length === 0;
        updateSafetyWarning();
        updateCashSummary();
    }

    linesBox.addEventListener('click', (e) => {
        const qtyBtn = e.target.closest('.qty-btn');
        const removeBtn = e.target.closest('.remove-btn');
        if (qtyBtn) {
            const idx = parseInt(qtyBtn.dataset.idx);
            const info = meta[cart[idx].type + ':' + cart[idx].id];
            const dir = parseInt(qtyBtn.dataset.dir);
            if (dir > 0 && info.stock !== null && cart[idx].quantity + 1 > info.stock) {
                alert('Only ' + info.stock + ' in stock.');
                return;
            }
            cart[idx].quantity += dir;
            if (cart[idx].quantity <= 0) cart.splice(idx, 1);
            renderCart();
        } else if (removeBtn) {
            cart.splice(parseInt(removeBtn.dataset.idx), 1);
            renderCart();
        }
    });

    document.getElementById('checkout-form').addEventListener('submit', (event) => {
        cartInput.value = JSON.stringify(cart.map((line) => ({ ...line })));

        if (paymentMethodSelect?.value === 'cash') {
            const received = parseFloat(cashReceivedInput?.value || '0');
            if (!cashReceivedInput?.value || Number.isNaN(received) || received < 0) {
                event.preventDefault();
                alert('Please enter the cash received for this sale.');
                cashReceivedInput?.focus();
                return;
            }
        }
    });

    hydrateCart();
})();
</script>
@endsection
