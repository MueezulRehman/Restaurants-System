@extends('layouts.admin')
@section('title', $posConfig['title'])

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3">
            <input type="text" id="scan-input" autofocus
                placeholder="{{ $posConfig['search_placeholder'] }}"
                class="w-full border border-gray-200 rounded-lg px-4 py-3 text-base focus:border-hut-yellow focus:ring-1 focus:ring-hut-yellow outline-none">
            <p class="text-xs text-gray-400 mt-1">Scan a barcode, or type a name / code and press Enter.</p>
        </div>

        <div id="search-results" class="grid grid-cols-2 sm:grid-cols-3 gap-3"></div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <h3 class="font-display font-semibold text-hut-dark text-sm mb-3">All {{ $posConfig['item_label_plural'] }}</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="all-items">
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
            </div>
        </div>
    </div>

    {{-- Cart / checkout panel --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 h-fit lg:sticky lg:top-4">
        <h2 class="font-display font-bold text-hut-dark mb-3">Current Bill</h2>

        <div id="cart-lines" class="space-y-2 max-h-96 overflow-y-auto mb-3">
            <p id="cart-empty" class="text-sm text-gray-400 text-center py-6">No items yet — scan or search a {{ strtolower($posConfig['item_label']) }}.</p>
        </div>

        <div class="border-t border-gray-100 pt-3 space-y-1 text-sm">
            <div class="flex justify-between font-display font-bold text-lg text-hut-dark">
                <span>Total</span>
                <span id="cart-total">Rs. 0</span>
            </div>
        </div>

        <form id="checkout-form" method="POST" action="{{ route('manager.pos.checkout') }}" class="mt-4 space-y-2">
            @csrf
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
    const cart = []; // {type, id, quantity}
    let meta = {}; // key `${type}:${id}` -> {name, price, stock}

    const scanInput = document.getElementById('scan-input');
    const resultsBox = document.getElementById('search-results');
    const lookupUrl = @json(route('manager.pos.lookup'));

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
            if (item.variants && item.variants.length) {
                item.variants.forEach(v => {
                    resultsBox.insertAdjacentHTML('beforeend', resultCardHtml('variant', v.id, item.name + ' — ' + v.name, v.sku, v.price, v.quantity_available));
                });
            } else {
                resultsBox.insertAdjacentHTML('beforeend', resultCardHtml('menu_item', item.id, item.name, item.sku, item.price, item.track_stock ? item.stock_quantity : null));
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
    const checkoutForm = document.getElementById('checkout-form');

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
            cart.push({ type, id, quantity: 1 });
        }
        renderCart();
    }

    function renderCart() {
        linesBox.querySelectorAll('.cart-line').forEach(el => el.remove());
        let total = 0;

        cart.forEach((line, idx) => {
            const info = meta[line.type + ':' + line.id];
            total += info.price * line.quantity;
            linesBox.insertAdjacentHTML('beforeend', `
                <div class="cart-line flex items-center justify-between text-sm border-b border-gray-50 pb-2">
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
        totalBox.textContent = 'Rs. ' + total.toLocaleString();
        cartInput.value = JSON.stringify(cart);
        checkoutBtn.disabled = cart.length === 0;
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

    function resetCart() {
        cart.length = 0;
        meta = {};
        renderCart();
    }

    window.addEventListener('pageshow', () => {
        resetCart();
    });

    checkoutForm.addEventListener('submit', () => {
        cartInput.value = JSON.stringify(cart);
        resetCart();
        checkoutForm.reset();
    });
})();
</script>
@endsection
