@extends('layouts.admin')

@section('title', 'Customer Details')

@section('content')
<div class="space-y-4">
    <div>
        <a href="{{ route('manager.customers.index') }}" class="text-sm text-gray-500 hover:text-hut-dark">← Back</a>
        <h2 class="mt-2 text-xl font-semibold text-hut-dark">{{ $customer->name }}</h2>
        <p class="text-sm text-gray-500">Phone: {{ $customer->phone }} · Email: {{ $customer->email ?: '—' }}</p>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Current balance</p>
            <p class="mt-2 text-2xl font-semibold text-hut-dark">Rs. {{ number_format($customer->balance, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Last reminder</p>
            <p class="mt-2 text-sm text-gray-600">{{ $customer->last_reminder_at?->format('M d, Y H:i') ?? 'Not reminded yet' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Quick actions</p>
            <div class="mt-3 flex flex-wrap gap-2 items-center">
                <a href="{{ route('manager.customers.statement', $customer) }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg bg-hut-dark px-3 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                    <i class="fas fa-file-invoice"></i> Statement / PDF
                </a>
                @if($customer->email)
                <form method="POST" action="{{ route('manager.customers.statement.email', $customer) }}" class="inline">
                    @csrf
                    <button type="submit" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-paper-plane"></i> Email statement
                    </button>
                </form>
                @endif
                <form method="POST" action="{{ route('manager.customers.remind', $customer) }}">@csrf<button type="submit" class="rounded-lg bg-hut-yellow px-3 py-2 text-sm font-semibold text-hut-dark hover:bg-amber-400">Remind (Email / WhatsApp)</button></form>
                @if($customer->phone)
                    @php
                        $waPhone = preg_replace('/\D+/', '', $customer->phone);
                        if (strlen($waPhone) === 10) { $waPhone = '92' . ltrim($waPhone, '0'); }
                        elseif (strlen($waPhone) === 11 && str_starts_with($waPhone, '0')) { $waPhone = '92' . substr($waPhone, 1); }
                        $shop = auth()->user()->effectiveRestaurant()->name ?? config('app.name');
                        $waText = rawurlencode(
                            "🏪 *{$shop}*\n━━━━━━━━━━━━━━\nHello *{$customer->name}*,\n\n💰 *Balance due: Rs. " . number_format((float) $customer->balance, 2) . "*\n\nPlease settle at your earliest convenience. Reply here or visit us — thank you!"
                        );
                    @endphp
                    <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-700">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                @endif
                @if($customer->email)
                    <a href="mailto:{{ $customer->email }}?subject={{ rawurlencode('Balance reminder') }}&body={{ rawurlencode('Hello ' . $customer->name . ', your current balance is Rs. ' . number_format((float) $customer->balance, 2) . '.') }}"
                       class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-envelope"></i> Email
                    </a>
                @endif
                <form method="POST" action="{{ route('manager.customers.payment', $customer) }}" class="flex gap-2">
                    @csrf
                    <input type="number" step="0.01" min="0" name="amount" placeholder="Payment" class="w-28 rounded-lg border border-gray-200 px-2 py-2 text-sm" />
                    <button type="submit" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Record payment</button>
                </form>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-hut-dark">Bills &amp; item details</h3>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($customer->orders as $order)
                <div class="p-4 space-y-3">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-hut-dark">{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $order->created_at->format('M d, Y H:i') }}
                                · {{ $order->status_label }}
                                · {{ ucfirst($order->payment_method ?? 'cash') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-hut-dark">Rs. {{ number_format($order->total, 2) }}</p>
                            <p class="text-xs text-gray-500">Received Rs. {{ number_format((float) ($order->amount_received ?? 0), 2) }}</p>
                            <div class="mt-2 flex flex-wrap justify-end gap-2">
                                <a href="{{ route('manager.pos.receipt', ['order' => $order, 'print' => 1]) }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs font-semibold text-hut-dark hover:bg-hut-yellow/20">
                                    <i class="fas fa-receipt"></i> Receipt / PDF
                                </a>
                                @if($customer->email)
                                    <form method="POST" action="{{ route('manager.customers.receipt.email', [$customer, $order]) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                            <i class="fas fa-paper-plane"></i> Email receipt
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Item</th>
                                    <th class="px-3 py-2 text-right font-semibold text-gray-600">Qty</th>
                                    <th class="px-3 py-2 text-right font-semibold text-gray-600">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($order->items as $item)
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">
                                            {{ $item->item_name }}
                                            @if($item->size_label)
                                                <span class="block text-xs text-gray-400">{{ $item->size_label }}</span>
                                            @endif
                                            @foreach($item->toppings as $t)
                                                <span class="block text-xs text-gray-400">+ {{ $t->topping_name }}</span>
                                            @endforeach
                                        </td>
                                        <td class="px-3 py-2 text-right text-gray-600">{{ $item->quantity }}</td>
                                        <td class="px-3 py-2 text-right text-gray-700">Rs. {{ number_format((float) $item->total_price, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-3 text-center text-gray-400">No line items on this bill.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-gray-500">No orders found for this customer.</div>
            @endforelse
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-hut-dark">Balance activity</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Date</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Type</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Amount</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Source</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Note</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($customer->balanceTransactions as $transaction)
                        <tr>
                            <td class="px-4 py-3 text-gray-600">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-4 py-3 capitalize text-gray-600">{{ $transaction->type }}</td>
                            <td class="px-4 py-3 text-gray-600">Rs. {{ number_format($transaction->amount, 2) }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $transaction->source }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $transaction->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No balance activity yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
