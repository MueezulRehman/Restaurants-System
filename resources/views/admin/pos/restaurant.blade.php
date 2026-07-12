@extends('layouts.admin')
@section('title', $posConfig['title'])

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Menu / item picker --}}
    <div class="lg:col-span-2 space-y-4">
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

    {{-- Cart / checkout panel --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 h-fit lg:sticky lg:top-4">
        <h2 class="font-display font-bold text-hut-dark mb-3">Current Sale</h2>

        <div id="cart-lines" class="space-y-2 max-h-96 overflow-y-auto mb-3">
            <p id="cart-empty" class="text-sm text-gray-400 text-center py-6">No items yet — tap a menu item to add it.</p>
        </div>

        <div class="border-t border-gray-100 pt-3 space-y-2 text-sm">
            <div class="flex justify-between font-display font-bold text-lg text-hut-dark">
                <span>Total</span>
                <span id="cart-total">Rs. 0</span>
            </div>
            <div class="rounded-lg border border-gray-100 bg-gray-50 p-2 space-y-2">
                <label for="cash-received" class="block text-[11px] font-semibold uppercase tracking-wide text-gray-500">Cash received</label>
                <input type="number" id="cash-received" name="amount_received" step="0.01" min="0" placeholder="Enter cash received" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-hut-green focus:ring-hut-green">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Change / balance</span>
                    <span id="cash-summary-text" class="font-semibold text-hut-dark">Rs. 0</span>
                </div>
            </div>
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

<script>
(function () {
    const cart = []; // {key, type, id, quantity, size_label, topping_ids, name, unitPrice}
    const toppings = @json($toppings->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'price' => (float) $t->price]));
    const orderTypeSelect = document.querySelector('select[name="order_type"]');
    const tableNumberWrapper = document.getElementById('table-number-wrapper');
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

    function renderCart() {
        linesBox.querySelectorAll('.cart-line').forEach(el => el.remove());
        let total = 0;

        cart.forEach((line, idx) => {
            total += line.unitPrice * line.quantity;
            linesBox.insertAdjacentHTML('beforeend', `
                <div class="cart-line flex items-center justify-between text-sm border-b border-gray-50 pb-2">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-hut-dark truncate">${line.name}</p>
                        <p class="text-xs text-gray-400">Rs. ${line.unitPrice.toLocaleString()} each</p>
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
        cartInput.value = JSON.stringify(cart.map(({ key, name, unitPrice, ...rest }) => rest));
        checkoutBtn.disabled = cart.length === 0;
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

    document.getElementById('checkout-form').addEventListener('submit', (event) => {
        cartInput.value = JSON.stringify(cart.map(({ key, name, unitPrice, ...rest }) => rest));

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
})();
</script>
@endsection
