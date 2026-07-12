@extends('layouts.customer')

@section('title', 'Checkout — Taste Hut')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">

    <h1 class="text-2xl font-display font-bold text-hut-dark mb-6">Checkout</h1>

    @if(! $customer)
        <div class="bg-hut-green/10 border border-hut-green/30 rounded-lg p-3 mb-4 flex items-center justify-between gap-3 flex-wrap">
            <p class="text-sm text-hut-dark">Have an account? Login for faster checkout and to track your order history.</p>
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('customer.login') }}" class="text-sm font-medium text-hut-green hover:underline">Login</a>
                <span class="text-gray-300">|</span>
                <a href="{{ route('customer.register') }}" class="text-sm font-medium text-hut-green hover:underline">Create account</a>
            </div>
        </div>
    @endif

    <div class="menu-card p-4 mb-6">
        <p class="font-display font-semibold text-hut-dark mb-3">Your order</p>
        <div id="cart-items" class="divide-y divide-gray-100"></div>
        <div class="flex justify-between items-center pt-3 mt-2 border-t border-gray-200">
            <span class="font-semibold">Subtotal</span>
            <span id="cart-subtotal" class="font-bold text-hut-green">Rs. 0</span>
        </div>
    </div>

    <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form">
        @csrf
        <input type="hidden" name="cart" id="cart-field">
        @if(!empty($restaurant))
            <input type="hidden" name="restaurant_id" value="{{ $restaurant->id }}">
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="menu-card p-4 mb-4 space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-700">Order type</label>
                <div class="grid grid-cols-2 gap-2 mt-1.5">
                    @foreach(['dine_in' => 'Dine-in', 'takeaway' => 'Takeaway', 'delivery' => 'Home Delivery', 'online' => 'Online', 'table' => 'Table Order'] as $val => $label)
                    <label class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer has-[:checked]:border-hut-green has-[:checked]:bg-hut-green/5">
                        <input type="radio" name="order_type" value="{{ $val }}" {{ $val === 'delivery' ? 'checked' : '' }} class="accent-hut-green" onchange="toggleAddressField()">
                        <span class="text-sm">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Your name</label>
                <input type="text" name="customer_name" required class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('customer_name', optional($customer)->name) }}">
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Phone number</label>
                <input type="tel" name="customer_phone" required class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('customer_phone', optional($customer)->phone) }}" placeholder="03XX-XXXXXXX">
            </div>

            <div id="address-field">
                <label class="text-sm font-medium text-gray-700">Delivery address</label>
                <textarea name="address" rows="2" class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">{{ old('address', optional($customer)->default_address) }}</textarea>
            </div>

            <div id="table-number-field" class="hidden">
                <label class="text-sm font-medium text-gray-700">Table number</label>
                @if(!empty($tables) && $tables->count())
                    <select name="table_number" class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
                        <option value="">Select table</option>
                        @foreach($tables as $table)
                            <option value="{{ $table->number ?? $table->label }}" {{ old('table_number') === ($table->number ?? $table->label) ? 'selected' : '' }}>
                                {{ $table->number ?? $table->label }}@if($table->seats) — {{ $table->seats }} seats @endif
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="table_number" value="{{ old('table_number') }}" class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="e.g. T12 or 5">
                @endif
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Payment method</label>
                <div class="grid grid-cols-2 gap-2 mt-1.5">
                    <label class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer has-[:checked]:border-hut-green has-[:checked]:bg-hut-green/5">
                        <input type="radio" name="payment_method" value="cash" checked class="accent-hut-green">
                        <span class="text-sm">Cash on delivery</span>
                    </label>
                    <label class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer has-[:checked]:border-hut-green has-[:checked]:bg-hut-green/5">
                        <input type="radio" name="payment_method" value="online" class="accent-hut-green">
                        <span class="text-sm">Online</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Notes (optional)</label>
                <textarea name="notes" rows="2" class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Less spicy, extra dip, etc.">{{ old('notes') }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn-accent w-full text-base">Place order</button>
    </form>
</div>

@push('scripts')
<script>
function getCart() {
    return JSON.parse(localStorage.getItem('th_cart') || '[]');
}

function renderCart() {
    const cart = getCart();
    const container = document.getElementById('cart-items');
    const subtotalEl = document.getElementById('cart-subtotal');

    if (cart.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400 py-4 text-center">Your cart is empty — <a href="{{ route("home") }}" class="text-hut-green underline">browse the menu</a></p>';
        subtotalEl.textContent = 'Rs. 0';
        return;
    }

    let subtotal = 0;
    container.innerHTML = cart.map((item, i) => {
        const lineTotal = item.price * item.quantity;
        subtotal += lineTotal;
        return `<div class="flex justify-between items-center py-2 text-sm">
            <div>
                <p class="font-medium">${item.name} ${item.size_label ? `(${item.size_label})` : ''}</p>
                <p class="text-gray-400 text-xs">Qty: ${item.quantity} × Rs. ${item.price}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-semibold">Rs. ${lineTotal}</span>
                <button type="button" onclick="removeItem(${i})" class="text-red-400 hover:text-red-600 text-xs">✕</button>
            </div>
        </div>`;
    }).join('');
    subtotalEl.textContent = 'Rs. ' + subtotal.toLocaleString();
}

function removeItem(index) {
    const cart = getCart();
    cart.splice(index, 1);
    localStorage.setItem('th_cart', JSON.stringify(cart));
    renderCart();
}

function toggleAddressField() {
    const type = document.querySelector('input[name="order_type"]:checked').value;
    document.getElementById('address-field').style.display = type === 'delivery' ? 'block' : 'none';
    document.getElementById('table-number-field').style.display = type === 'table' ? 'block' : 'none';
}

document.getElementById('checkout-form').addEventListener('submit', function (e) {
    const cart = getCart();
    if (cart.length === 0) {
        e.preventDefault();
        alert('Your cart is empty.');
        return;
    }
    // Convert localStorage cart format into the structured array the
    // server-side validator and re-pricing logic expects.
    const payload = cart.map(i => ({
        type: i.type,
        id: i.id,
        quantity: i.quantity,
        size_label: i.size_label || null,
        topping_ids: Array.isArray(i.topping_ids) ? i.topping_ids : [],
        special_request: i.special_request || null,
    }));
    document.getElementById('cart-field').remove();
    payload.forEach((item, idx) => {
        Object.entries(item).forEach(([key, val]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `cart[${idx}][${key}]`;
            if (Array.isArray(val)) {
                val.forEach((arrayValue, arrayIndex) => {
                    const arrayInput = document.createElement('input');
                    arrayInput.type = 'hidden';
                    arrayInput.name = `cart[${idx}][${key}][${arrayIndex}]`;
                    arrayInput.value = arrayValue;
                    this.appendChild(arrayInput);
                });
            } else {
                input.value = val ?? '';
                this.appendChild(input);
            }
        });
    });
});

renderCart();
toggleAddressField();

// Clear cart after a successful order placement (order page redirect carries success message)
@if(session('success'))
    localStorage.removeItem('th_cart');
@endif
</script>
@endpush
@endsection
