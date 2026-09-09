@extends('layouts.admin')

@section('title', 'Returns & Exchanges')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-display font-bold text-hut-dark">Returns & Exchanges</h1>
        <p class="mt-1 text-sm text-gray-500">Return sold products, restore stock, and issue a cash refund or customer credit.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="font-semibold text-hut-dark">Recent returns</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Order</th>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Qty</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Refund</th>
                            <th class="px-4 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($returns as $return)
                            <tr>
                                <td class="px-4 py-3 font-medium text-hut-dark">{{ $return->order?->order_number ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $return->orderItem?->item_name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ rtrim(rtrim(number_format((float) $return->quantity, 3), '0'), '.') }}</td>
                                <td class="px-4 py-3">Rs. {{ number_format((float) $return->amount, 2) }}</td>
                                <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $return->refund_method) }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $return->created_at?->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">No returns recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-5 py-4">{{ $returns->links() }}</div>
        </section>

        <section class="h-fit rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-hut-dark">Record a return</h2>
            <form method="POST" action="{{ route('manager.sales-returns.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Sold item</label>
                    <input type="search" id="return-customer-search" placeholder="Filter by customer name or phone"
                        class="mb-2 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <select name="order_item_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="">Select an order item</option>
                        @foreach($orders as $order)
                            @foreach($order->items as $item)
                                <option value="{{ $item->id }}" data-customer="{{ strtolower(($order->customer?->name ?? $order->customer_name) . ' ' . ($order->customer?->phone ?? $order->customer_phone)) }}">{{ $order->order_number }} · {{ $item->item_name }} · {{ $order->customer?->name ?? $order->customer_name }} · Qty {{ $item->quantity }} · Rs. {{ number_format((float) $item->unit_price, 2) }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Quantity</label>
                    <input type="number" name="quantity" min="0.001" step="0.001" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" value="1">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Refund method</label>
                    <select name="refund_method" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="cash">Cash refund</option>
                        <option value="customer_credit">Customer credit</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Reason</label>
                    <input type="text" name="reason" maxlength="255" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Wrong size, damaged, exchange...">
                </div>
                <button type="submit" class="w-full rounded-lg bg-hut-dark px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-800">Record return</button>
            </form>
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const search = document.getElementById('return-customer-search');
            const select = document.querySelector('select[name="order_item_id"]');
            if (!search || !select) return;
            search.addEventListener('input', () => {
                const term = search.value.trim().toLowerCase();
                Array.from(select.options).forEach((option, index) => {
                    if (index === 0) return;
                    option.hidden = term !== '' && !(option.dataset.customer || '').includes(term);
                });
            });
        });
    </script>
@endsection