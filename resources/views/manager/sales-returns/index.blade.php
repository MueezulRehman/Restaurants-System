@extends('manager.layout.master')

@section('title', 'Returns & Exchanges')

@section('page-content')
    <div class="page-header">
        <div class="page-header__copy">
            <h1 class="page-title">Returns & Exchanges</h1>
            <p class="page-description">Return sold products, restore stock, and issue a cash refund or customer credit.</p>
        </div>
    </div>

    <div class="returns-layout">
        <section class="returns-card">
            <div class="returns-card__header">
                <h2 class="font-semibold text-hut-dark">Recent returns</h2>
            </div>
            <div class="returns-card__body">
                <table class="returns-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Amount</th>
                            <th>Refund</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                            <tr>
                                <td class="returns-table__primary">{{ $return->order?->order_number ?? '—' }}</td>
                                <td>{{ $return->orderItem?->item_name ?? '—' }}</td>
                                <td>{{ rtrim(rtrim(number_format((float) $return->quantity, 3), '0'), '.') }}</td>
                                <td>Rs. {{ number_format((float) $return->amount, 2) }}</td>
                                <td class="capitalize">{{ str_replace('_', ' ', $return->refund_method) }}</td>
                                <td class="returns-table__muted">{{ $return->created_at?->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="returns-empty">No returns recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-5 py-4">{{ $returns->links() }}</div>
        </section>

        <section class="returns-card returns-card--form">
            <h2 class="font-semibold text-hut-dark">Record a return</h2>
            <form method="POST" action="{{ route('manager.sales-returns.store') }}" class="returns-form mt-4 space-y-4">
                @csrf
                <div class="returns-field">
                    <label for="return-customer-search">Sold item</label>
                    <input type="search" id="return-customer-search" placeholder="Filter by customer name or phone"
                        class="mb-2">
                    <select id="return-order-item" name="order_item_id" required>
                        <option value="">Select an order item</option>
                        @foreach($orders as $order)
                            @foreach($order->items as $item)
                                <option value="{{ $item->id }}" data-customer="{{ strtolower(($order->customer?->name ?? $order->customer_name) . ' ' . ($order->customer?->phone ?? $order->customer_phone)) }}">{{ $order->order_number }} · {{ $item->item_name }} · {{ $order->customer?->name ?? $order->customer_name }} · Qty {{ $item->quantity }} · Rs. {{ number_format((float) $item->unit_price, 2) }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="returns-field">
                    <label for="return-quantity">Quantity</label>
                    <input id="return-quantity" type="number" name="quantity" min="0.001" step="0.001" required value="1">
                </div>
                <div class="returns-field">
                    <label for="return-refund-method">Refund method</label>
                    <select id="return-refund-method" name="refund_method" required>
                        <option value="cash">Cash refund</option>
                        <option value="customer_credit">Customer credit</option>
                    </select>
                </div>
                <div class="returns-field">
                    <label for="return-reason">Reason</label>
                    <input id="return-reason" type="text" name="reason" maxlength="255" placeholder="Wrong size, damaged, exchange...">
                </div>
                <button type="submit" class="returns-submit">Record return</button>
            </form>
        </section>
    </div>
@endsection