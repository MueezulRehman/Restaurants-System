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
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Menu / item picker --}}
    <div class="lg:col-span-2 space-y-4 min-w-0">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm space-y-3">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Restaurant POS</p>
                    <h2 class="font-display font-semibold text-hut-dark text-lg">Search menu &amp; build the bill</h2>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('manager.customers.index') }}" class="rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-semibold text-hut-dark hover:bg-hut-yellow/20">{{ $customers->count() }} customers</a>
                    <button type="button" id="toggle-all-items" class="rounded-full border border-hut-dark/20 bg-hut-dark px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-800">Show all items</button>
                </div>
            </div>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="search" id="pos-search" autocomplete="off"
                    placeholder="Search by item name or price…"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-10 pr-4 py-2.5 text-sm focus:border-hut-green focus:bg-white focus:ring-2 focus:ring-hut-green/20 outline-none" />
            </div>
        </div>

        <div class="flex flex-wrap gap-2" id="category-tabs">
            <button type="button" class="cat-tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-hut-dark text-white" data-cat="all">All</button>
            @foreach($categories as $cat)
                <button type="button" class="cat-tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-white border border-gray-200 hover:bg-gray-50" data-cat="{{ $cat->id }}">{{ $cat->icon }} {{ $cat->name }}</button>
            @endforeach
            @if($deals->count())
                <button type="button" class="cat-tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-white border border-gray-200 hover:bg-gray-50" data-cat="deals">🎁 Deals</button>
            @endif
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3" id="item-grid">
            @foreach($categories as $cat)
                @foreach($cat->availableMenuItems as $item)
                    <button type="button"
                        class="pos-item-card bg-white border border-gray-100 rounded-xl p-3 text-left shadow-sm hover:shadow-md hover:border-hut-yellow transition"
                        data-cat="{{ $cat->id }}"
                        data-id="{{ $item->id }}"
                        data-name="{{ $item->name }}"
                        data-price="{{ $item->price ?? 0 }}"
                        data-has-sizes="{{ $item->has_sizes ? '1' : '0' }}"
                        data-sizes='{{ $item->has_sizes ? $item->sizes->map(fn($s) => ["label" => $s->size_label, "price" => (float) $s->price])->toJson() : "[]" }}'
                        data-allows-toppings="{{ $item->allows_toppings ? '1' : '0' }}">
                        <p class="font-display font-semibold text-sm text-hut-dark truncate">{{ $item->name }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $item->has_sizes ? 'From Rs. ' . number_format($item->display_price) : 'Rs. ' . number_format($item->price) }}
                        </p>
                    </button>
                @endforeach
            @endforeach

            @foreach($deals as $deal)
                <button type="button"
                    class="pos-item-card bg-hut-yellow/10 border border-hut-yellow/30 rounded-xl p-3 text-left shadow-sm hover:shadow-md transition"
                    data-cat="deals"
                    data-deal="1"
                    data-id="{{ $deal->id }}"
                    data-name="{{ $deal->name }}"
                    data-price="{{ $deal->price }}"
                    data-has-sizes="0"
                    data-sizes="[]"
                    data-allows-toppings="0">
                    <p class="font-display font-semibold text-sm text-hut-dark truncate">🎁 {{ $deal->name }}</p>
                    <p class="text-xs text-hut-yellow-dark mt-1 font-medium">Rs. {{ number_format($deal->price) }}</p>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Current bill (fixed on the right) --}}
    <div class="bg-white rounded-2xl shadow-lg border border-hut-dark/10 p-4 h-fit lg:sticky lg:top-4 lg:max-h-[calc(100vh-6rem)] lg:overflow-y-auto order-first lg:order-none ring-1 ring-hut-yellow/20">
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-hut-yellow-dark">Billing</p>
                <h2 class="font-display font-bold text-hut-dark text-lg leading-tight">Current Bill</h2>
            </div>
            <span id="cart-count-badge" class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-hut-dark px-2 text-xs font-bold text-white">0</span>
        </div>

        <div id="cart-lines" class="space-y-2 max-h-64 overflow-y-auto mb-3">
            <p id="cart-empty" class="text-sm text-gray-400 text-center py-6">No items yet — search or tap a menu item.</p>
        </div>

        <div class="border-t border-gray-100 pt-3 space-y-2 text-sm">
            <div class="flex justify-between text-gray-500">
                <span>Total before discount</span>
                <span id="cart-subtotal">Rs. 0</span>
            </div>
            <div class="rounded-lg border border-dashed border-amber-200 bg-amber-50/50 p-2 space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-800">Bill discount</p>
                <div class="flex gap-2">
                    <select id="bill-discount-type" name="bill_discount_type" form="checkout-form" class="rounded-lg border border-gray-200 px-2 py-1.5 text-xs bg-white">
                        <option value="percent">%</option>
                        <option value="fixed">Rs</option>
                    </select>
                    <input type="number" id="bill-discount-value" name="bill_discount_value" form="checkout-form" min="0" step="1" value="0" placeholder="0" class="flex-1 rounded-lg border border-gray-200 px-2 py-1.5 text-xs">
                </div>
                <div class="flex justify-between text-xs text-amber-900">
                    <span>Discount</span>
                    <span id="bill-discount-amount">− Rs. 0</span>
                </div>
            </div>
            <div class="flex justify-between font-display font-bold text-lg text-hut-dark">
                <span>Total after discount</span>
                <span id="cart-total">Rs. 0</span>
            </div>
            <div class="rounded-lg border border-gray-100 bg-gray-50 p-2 space-y-2">
                <label for="cash-received" class="block text-[11px] font-semibold uppercase tracking-wide text-gray-500">Cash received</label>
                <input type="number" id="cash-received" name="amount_received" form="checkout-form" step="1" min="0" placeholder="Leave empty to charge customer debt" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-hut-green focus:ring-hut-green">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Change / balance</span>
                    <span id="cash-summary-text" class="font-semibold text-hut-dark">Rs. 0</span>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 space-y-2">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Customer</p>
                <span class="text-xs text-gray-400">Track balances</span>
            </div>
            <select id="customer-select" name="customer_id" form="checkout-form" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
                <option value="">Walk-in customer</option>
                @foreach(($customers ?? collect()) as $customer)
                    <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" data-phone="{{ $customer->phone }}" data-balance="{{ $customer->balance }}">{{ $customer->name }} • {{ $customer->phone }} @if($customer->balance > 0) (Due Rs. {{ number_format($customer->balance, 2) }}) @endif</option>
                @endforeach
            </select>
            <form method="POST" action="{{ route('manager.customers.store') }}" class="space-y-2">
                @csrf
                <div class="grid gap-2 sm:grid-cols-2">
                    <input type="text" name="name" required placeholder="New customer name" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
                    <input type="text" name="phone" required placeholder="Phone" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
                </div>
                <button type="submit" class="w-full rounded-lg border border-hut-yellow/40 bg-hut-yellow/10 px-3 py-2 text-sm font-semibold text-hut-dark hover:bg-hut-yellow/20">Register customer</button>
            </form>
        </div>

        <form id="checkout-form" method="POST" action="{{ route('manager.pos.checkout') }}" class="mt-4 space-y-2">
            @csrf
            <select name="order_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                <option value="takeaway">Takeaway</option>
                <option value="dine_in">Dine-in</option>
                <option value="table">Table Order</option>
                <option value="delivery">Delivery</option>
                <option value="online">Online</option>
            </select>
            <div id="table-number-wrapper" class="hidden">
                <select name="table_number" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    <option value="">Select table (optional)</option>
                    @foreach(($tables ?? collect()) as $t)
                        <option value="{{ $t->number ?? $t->label }}">{{ $t->number ?? $t->label }} @if($t->seats) — {{ $t->seats }} seats @endif</option>
                    @endforeach
                </select>
            </div>
            <input type="text" name="customer_name" placeholder="Customer name (optional)" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            <input type="text" name="customer_phone" placeholder="Phone (optional)" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            <select name="payment_method" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                <option value="cash">Cash</option>
                <option value="online">Online</option>
            </select>
            <textarea name="notes" placeholder="Notes (optional)" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></textarea>
            <input type="hidden" name="cart" id="cart-input">
            <button type="submit" id="checkout-btn" class="btn-accent w-full text-center" disabled>Complete Sale</button>
        </form>
    </div>
</div>

{{-- Size/topping picker modal --}}
<div id="item-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl p-5 w-full max-w-sm">
        <h3 id="modal-item-name" class="font-display font-bold text-hut-dark mb-3">Item</h3>
        <div id="modal-sizes" class="space-y-2 mb-3"></div>
        <div id="modal-toppings" class="space-y-2 mb-3"></div>
        <div class="flex gap-2">
            <button type="button" id="modal-add-btn" class="btn-primary flex-1">Add to Sale</button>
            <button type="button" id="modal-cancel-btn" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm">Cancel</button>
        </div>
    </div>
</div>


{{-- Shared: unpaid balance → customer debt (all business types) --}}
<div id="debt-confirm-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-200 overflow-hidden">
        <div class="bg-amber-600 text-white px-4 py-3">
            <p class="text-[10px] font-bold uppercase tracking-widest text-amber-100">Account debt</p>
            <h3 class="font-display font-bold text-lg">Confirm unpaid balance</h3>
        </div>
        <div class="p-4 space-y-3 text-sm">
            <p class="text-gray-600">Cash received is less than the bill total. Choose the customer whose account should hold the remaining amount.</p>
            <div id="debt-customer-picker-wrap" class="hidden space-y-1">
                <label class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Select customer</label>
                <select id="debt-customer-picker" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm bg-white"></select>
            </div>
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 space-y-2">
                <div class="flex justify-between"><span class="text-gray-500">Customer</span><span id="debt-customer-name" class="font-semibold text-hut-dark">—</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Phone</span><span id="debt-customer-phone" class="font-medium text-gray-700">—</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Current balance due</span><span id="debt-customer-balance" class="font-medium text-red-600">Rs. 0</span></div>
                <div class="flex justify-between border-t border-gray-200 pt-2"><span class="text-gray-500">Bill total</span><span id="debt-bill-total" class="font-semibold">Rs. 0</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Cash received</span><span id="debt-cash-received" class="font-medium">Rs. 0</span></div>
                <div class="flex justify-between text-base"><span class="font-semibold text-hut-dark">Amount to debt</span><span id="debt-amount" class="font-bold text-red-600">Rs. 0</span></div>
                <div class="flex justify-between text-xs text-gray-500"><span>New balance after sale</span><span id="debt-new-balance">Rs. 0</span></div>
            </div>
            <p id="debt-modal-error" class="text-xs text-red-600 hidden"></p>
            <div class="flex gap-2 pt-1">
                <button type="button" id="debt-cancel" class="flex-1 rounded-lg border border-gray-200 px-3 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancel</button>
                <button type="button" id="debt-confirm" class="flex-1 rounded-lg bg-amber-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-amber-700">Confirm &amp; complete sale</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const cart = [];
    const pkr = (n) => Math.max(0, Math.round(Number(n) || 0));
    const pkrFmt = (n) => 'Rs. ' + pkr(n).toLocaleString();
    let debtConfirmProceed = false; // {key, type, id, quantity, size_label, topping_ids, name, unitPrice}
    const savedCart = @json($savedCart ?? []);
    const highlightedLine = @json($errorHighlight ?? null);
    const toppings = @json($toppings->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'price' => (float) $t->price]));
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

    const grid = document.getElementById('item-grid');
    const tabs = document.getElementById('category-tabs');
    const modal = document.getElementById('item-modal');
    let pendingCard = null;

    tabs.addEventListener('click', (e) => {
        const btn = e.target.closest('.cat-tab-btn');
        if (!btn) return;
        tabs.querySelectorAll('.cat-tab-btn').forEach(b => b.classList.remove('bg-hut-dark', 'text-white'));
        btn.classList.add('bg-hut-dark', 'text-white');
        const cat = btn.dataset.cat;
        grid.querySelectorAll('.pos-item-card').forEach(card => {
            card.style.display = (cat === 'all' || card.dataset.cat === cat) ? '' : 'none';
        });
    });

    grid.addEventListener('click', (e) => {
        const card = e.target.closest('.pos-item-card');
        if (!card) return;
        pendingCard = card;

        const hasSizes = card.dataset.hasSizes === '1';
        const allowsToppings = card.dataset.allowsToppings === '1';

        if (!hasSizes && !allowsToppings) {
            addToCart(card, null, []);
            return;
        }

        openModal(card, hasSizes, allowsToppings);
    });

    function openModal(card, hasSizes, allowsToppings) {
        document.getElementById('modal-item-name').textContent = card.dataset.name;
        const sizesBox = document.getElementById('modal-sizes');
        const toppingsBox = document.getElementById('modal-toppings');
        sizesBox.innerHTML = '';
        toppingsBox.innerHTML = '';

        if (hasSizes) {
            const sizes = JSON.parse(card.dataset.sizes || '[]');
            sizes.forEach((s, i) => {
                sizesBox.insertAdjacentHTML('beforeend', `
                    <label class="flex items-center justify-between border border-gray-200 rounded-lg px-3 py-2 text-sm cursor-pointer">
                        <span><input type="radio" name="modal-size" value="${s.label}" ${i === 0 ? 'checked' : ''} class="mr-2">${s.label}</span>
                        <span>Rs. ${Number(s.price).toLocaleString()}</span>
                    </label>`);
            });
        }

        if (allowsToppings && toppings.length) {
            toppingsBox.insertAdjacentHTML('beforeend', '<p class="text-xs text-gray-400 mb-1">Toppings</p>');
            toppings.forEach(t => {
                toppingsBox.insertAdjacentHTML('beforeend', `
                    <label class="flex items-center justify-between border border-gray-200 rounded-lg px-3 py-2 text-sm cursor-pointer">
                        <span><input type="checkbox" name="modal-topping" value="${t.id}" data-price="${t.price}" class="mr-2">${t.name}</span>
                        <span>+Rs. ${Number(t.price).toLocaleString()}</span>
                    </label>`);
            });
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    document.getElementById('modal-cancel-btn').addEventListener('click', () => closeModal());
    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        pendingCard = null;
    }

    document.getElementById('modal-add-btn').addEventListener('click', () => {
        if (!pendingCard) return;
        const sizeInput = document.querySelector('input[name="modal-size"]:checked');
        const sizeLabel = sizeInput ? sizeInput.value : null;
        const toppingIds = Array.from(document.querySelectorAll('input[name="modal-topping"]:checked')).map(el => parseInt(el.value));
        addToCart(pendingCard, sizeLabel, toppingIds);
        closeModal();
    });

    function addToCart(card, sizeLabel, toppingIds) {
        const isDeal = card.dataset.deal === '1';
        let unitPrice = parseFloat(card.dataset.price);

        if (sizeLabel) {
            const sizes = JSON.parse(card.dataset.sizes || '[]');
            const match = sizes.find(s => s.label === sizeLabel);
            if (match) unitPrice = parseFloat(match.price);
        }

        toppingIds.forEach(id => {
            const t = toppings.find(t => t.id === id);
            if (t) unitPrice += parseFloat(t.price);
        });

        const key = [card.dataset.id, isDeal ? 'deal' : 'menu_item', sizeLabel || '', toppingIds.sort().join(',')].join('|');
        const existing = cart.find(l => l.key === key);
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({
                key,
                type: isDeal ? 'deal' : 'menu_item',
                id: parseInt(card.dataset.id),
                quantity: 1,
                size_label: sizeLabel,
                topping_ids: toppingIds,
                name: card.dataset.name + (sizeLabel ? ' (' + sizeLabel + ')' : ''),
                unitPrice,
            });
        }
        renderCart();
    }

    const linesBox = document.getElementById('cart-lines');
    const emptyMsg = document.getElementById('cart-empty');
    const totalBox = document.getElementById('cart-total');
    const cartInput = document.getElementById('cart-input');
    const checkoutBtn = document.getElementById('checkout-btn');

    function matchesHighlight(line) {
        return highlightedLine && line.type === highlightedLine.type && line.id === highlightedLine.id;
    }

    function hydrateCart() {
        if (!Array.isArray(savedCart) || !savedCart.length) return;
        savedCart.forEach((line) => {
            const type = line.type || 'menu_item';
            const id = parseInt(line.id, 10);
            const quantity = parseInt(line.quantity || 1, 10);
            const sizeLabel = line.size_label || null;
            const toppingIds = Array.isArray(line.topping_ids) ? line.topping_ids : [];
            const name = line.name || (type === 'deal' ? 'Deal' : 'Item');
            const unitPrice = parseFloat(line.unitPrice || line.price || 0);
            const key = [id, type, sizeLabel || '', toppingIds.sort().join(',')].join('|');
            cart.push({
                key,
                type,
                id,
                quantity,
                size_label: sizeLabel,
                topping_ids: toppingIds,
                name: name + (sizeLabel ? ' (' + sizeLabel + ')' : ''),
                unitPrice,
            });
        });
        renderCart();
    }

    function renderCart() {
        linesBox.querySelectorAll('.cart-line').forEach(el => el.remove());
        let total = 0;

        cart.forEach((line, idx) => {
            const lineGross = pkr(line.unitPrice * line.quantity);
            const ldType = line.line_discount_type || 'percent';
            const ldVal = parseFloat(line.line_discount_value || 0) || 0;
            let lineNet = lineGross;
            if (ldVal > 0) {
                if (ldType === 'percent') lineNet = pkr(lineGross * (1 - Math.min(100, ldVal) / 100));
                else lineNet = pkr(Math.max(0, lineGross - ldVal));
            }
            total += lineNet;
            linesBox.insertAdjacentHTML('beforeend', `
                <div class="cart-line space-y-1 text-sm border-b border-gray-50 pb-2 ${matchesHighlight(line) ? 'rounded-lg border border-amber-300 bg-amber-50 px-2 py-2' : ''}">
                    <div class="flex items-center justify-between gap-1">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-hut-dark truncate">${line.name}</p>
                            <p class="text-xs text-gray-400">Rs. ${pkr(line.unitPrice).toLocaleString()} × ${line.quantity}${ldVal > 0 ? ' · disc.' : ''}</p>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button" class="qty-btn w-6 h-6 rounded bg-gray-100 hover:bg-gray-200" data-idx="${idx}" data-dir="-1">−</button>
                            <span class="w-6 text-center">${line.quantity}</span>
                            <button type="button" class="qty-btn w-6 h-6 rounded bg-gray-100 hover:bg-gray-200" data-idx="${idx}" data-dir="1">+</button>
                            <button type="button" class="remove-btn text-hut-red text-xs ml-1" data-idx="${idx}">✕</button>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <select class="line-disc-type rounded border border-gray-200 text-[10px] px-1 py-0.5 bg-white" data-idx="${idx}">
                            <option value="percent" ${ldType === 'percent' ? 'selected' : ''}>%</option>
                            <option value="fixed" ${ldType === 'fixed' ? 'selected' : ''}>Rs</option>
                        </select>
                        <input type="number" min="0" step="1" value="${ldVal}" placeholder="Disc" class="line-disc-value w-16 rounded border border-gray-200 text-[10px] px-1 py-0.5" data-idx="${idx}">
                        <span class="text-[10px] text-gray-500 ml-auto">${pkrFmt(lineNet)}</span>
                    </div>
                </div>`);
        });

        emptyMsg.style.display = cart.length ? 'none' : '';
        const subEl = document.getElementById('cart-subtotal');
        if (subEl) subEl.textContent = pkrFmt(total);
        const billType = document.getElementById('bill-discount-type')?.value || 'percent';
        const billVal = parseFloat(document.getElementById('bill-discount-value')?.value || '0') || 0;
        let billDisc = 0;
        if (billVal > 0) {
            billDisc = billType === 'percent' ? pkr(total * Math.min(100, billVal) / 100) : pkr(Math.min(total, billVal));
        }
        const discEl = document.getElementById('bill-discount-amount');
        if (discEl) discEl.textContent = '− ' + pkrFmt(billDisc);
        currentTotal = pkr(Math.max(0, total - billDisc));
        totalBox.textContent = pkrFmt(currentTotal);
        cartInput.value = JSON.stringify(cart.map((line) => ({ ...line })));
        checkoutBtn.disabled = cart.length === 0;
        updateCashSummary && updateCashSummary();
        if (cartCountBadge) cartCountBadge.textContent = String(cart.reduce((n, l) => n + (l.quantity || 1), 0));
        updateCashSummary();
    }

    linesBox.addEventListener('click', (e) => {
        const qtyBtn = e.target.closest('.qty-btn');
        const removeBtn = e.target.closest('.remove-btn');
        if (qtyBtn) {
            const idx = parseInt(qtyBtn.dataset.idx);
            cart[idx].quantity += parseInt(qtyBtn.dataset.dir);
            if (cart[idx].quantity <= 0) cart.splice(idx, 1);
            renderCart();
        } else if (removeBtn) {
            cart.splice(parseInt(removeBtn.dataset.idx), 1);
            renderCart();
        }
    });

    document.getElementById('bill-discount-type')?.addEventListener('change', () => renderCart());
    document.getElementById('bill-discount-value')?.addEventListener('input', () => renderCart());
    linesBox.addEventListener('change', (e) => {
        const typeEl = e.target.closest('.line-disc-type');
        const valEl = e.target.closest('.line-disc-value');
        if (typeEl && cart[typeEl.dataset.idx]) {
            cart[typeEl.dataset.idx].line_discount_type = typeEl.value;
            renderCart();
        }
        if (valEl && cart[valEl.dataset.idx]) {
            cart[valEl.dataset.idx].line_discount_value = parseFloat(valEl.value) || 0;
            renderCart();
        }
    });

    document.getElementById('checkout-form').addEventListener('submit', (event) => {
        cartInput.value = JSON.stringify(cart.map((line) => ({ ...line })));
        const method = paymentMethodSelect?.value || 'cash';
        if (method !== 'cash') { debtConfirmProceed = false; return; }

        const receivedRaw = cashReceivedInput?.value;
        const received = (receivedRaw === '' || receivedRaw == null) ? null : pkr(receivedRaw);
        const total = currentTotal || 0;

        if (debtConfirmProceed) {
            debtConfirmProceed = false;
            if (received === null) cashReceivedInput.value = '0';
            else cashReceivedInput.value = String(pkr(received));
            return;
        }

        const paid = received == null ? 0 : pkr(received);
        const due = pkr(Math.max(0, total - paid));
        if (received != null && due <= 0) {
            cashReceivedInput.value = String(pkr(received));
            return;
        }

        // Unpaid / partial → modal (no browser alert)
        event.preventDefault();
        if (due <= 0) return;

        const pickerWrap = document.getElementById('debt-customer-picker-wrap');
        const picker = document.getElementById('debt-customer-picker');
        if (picker && customerSelect) {
            picker.innerHTML = '';
            Array.from(customerSelect.options).forEach((opt) => {
                if (!opt.value) return;
                const o = document.createElement('option');
                o.value = opt.value;
                o.textContent = opt.textContent;
                o.dataset.name = opt.dataset.name || '';
                o.dataset.phone = opt.dataset.phone || '';
                o.dataset.balance = opt.dataset.balance || '0';
                if (opt.selected) o.selected = true;
                picker.appendChild(o);
            });
            pickerWrap?.classList.toggle('hidden', !!customerSelect.value);
        }
        const fill = (opt) => {
            const currentBal = pkr(opt?.dataset?.balance || 0);
            document.getElementById('debt-customer-name').textContent = opt?.dataset?.name || opt?.textContent?.trim() || '—';
            document.getElementById('debt-customer-phone').textContent = opt?.dataset?.phone || '—';
            document.getElementById('debt-customer-balance').textContent = pkrFmt(currentBal);
            document.getElementById('debt-bill-total').textContent = pkrFmt(total);
            document.getElementById('debt-cash-received').textContent = pkrFmt(paid);
            document.getElementById('debt-amount').textContent = pkrFmt(due);
            document.getElementById('debt-new-balance').textContent = pkrFmt(currentBal + due);
        };
        const selected = customerSelect?.selectedOptions?.[0];
        fill(selected?.value ? selected : picker?.options?.[0]);
        document.getElementById('debt-modal-error')?.classList.add('hidden');
        document.getElementById('debt-confirm-modal')?.classList.remove('hidden');
        picker?.addEventListener('change', () => fill(picker.selectedOptions[0]));
    });

    document.getElementById('debt-cancel')?.addEventListener('click', () => {
        document.getElementById('debt-confirm-modal')?.classList.add('hidden');
    });
    document.getElementById('debt-confirm')?.addEventListener('click', () => {
        const picker = document.getElementById('debt-customer-picker');
        if (picker?.value && customerSelect) customerSelect.value = picker.value;
        if (!customerSelect?.value) {
            const err = document.getElementById('debt-modal-error');
            if (err) { err.textContent = 'Select a customer to put this balance on their account.'; err.classList.remove('hidden'); }
            document.getElementById('debt-customer-picker-wrap')?.classList.remove('hidden');
            return;
        }
        const selected = customerSelect.selectedOptions[0];
        if (customerNameInput) customerNameInput.value = selected.dataset.name || '';
        if (customerPhoneInput) customerPhoneInput.value = selected.dataset.phone || '';
        if (cashReceivedInput && (cashReceivedInput.value === '' || cashReceivedInput.value == null)) cashReceivedInput.value = '0';
        if (cashReceivedInput) cashReceivedInput.value = String(pkr(cashReceivedInput.value));
        debtConfirmProceed = true;
        document.getElementById('debt-confirm-modal')?.classList.add('hidden');
        document.getElementById('checkout-form')?.requestSubmit();
    });

    
    // Search by name / price number
    const posSearch = document.getElementById('pos-search');
    const toggleAllBtn = document.getElementById('toggle-all-items');
    let showAllItems = true;

    function filterItems() {
        const q = (posSearch?.value || '').trim().toLowerCase();
        const activeCat = document.querySelector('.cat-tab-btn.bg-hut-dark')?.dataset.cat || 'all';
        document.querySelectorAll('#item-grid .pos-item-card').forEach(card => {
            const name = (card.dataset.name || card.querySelector('.font-display')?.textContent || '').toLowerCase();
            const price = String(card.dataset.price || '');
            const cat = card.dataset.cat || '';
            const matchesSearch = !q || name.includes(q) || price.includes(q) || name.replace(/\s+/g,'').includes(q.replace(/\s+/g,''));
            const matchesCat = activeCat === 'all' || cat === activeCat || (activeCat === 'deals' && cat === 'deals');
            card.style.display = (matchesSearch && matchesCat) ? '' : 'none';
        });
    }

    if (posSearch) {
        posSearch.addEventListener('input', filterItems);
        posSearch.focus();
    }
    if (toggleAllBtn) {
        toggleAllBtn.addEventListener('click', () => {
            showAllItems = !showAllItems;
            const allBtn = document.querySelector('.cat-tab-btn[data-cat="all"]');
            if (allBtn) allBtn.click();
            if (posSearch) { posSearch.value = ''; filterItems(); }
            toggleAllBtn.textContent = showAllItems ? 'Show all items' : 'Filter by category';
            document.getElementById('item-grid')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    // Keep cart count badge in sync
    const cartCountBadge = document.getElementById('cart-count-badge');
    const _origRenderCart = typeof renderCart === 'function' ? renderCart : null;

    // Wire category tabs to search filter
    document.querySelectorAll('.cat-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => setTimeout(filterItems, 0));
    });

    hydrateCart();
})();
</script>


@if(session('print_order_id'))
<script>
(function () {
    var receiptUrl = @json(route('manager.pos.receipt', ['order' => session('print_order_id'), 'print' => 1]));
    var iframe = document.createElement('iframe');
    iframe.setAttribute('src', receiptUrl);
    iframe.setAttribute('title', 'Print receipt');
    iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;opacity:0;pointer-events:none;';
    document.body.appendChild(iframe);
    setTimeout(function () {
        try { iframe.remove(); } catch (e) {}
    }, 60000);
})();
</script>
@endif

@endsection