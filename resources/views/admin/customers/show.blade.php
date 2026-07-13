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
            <div class="mt-3 flex flex-wrap gap-2">
                <form method="POST" action="{{ route('manager.customers.remind', $customer) }}">@csrf<button type="submit" class="rounded-lg bg-hut-yellow px-3 py-2 text-sm font-semibold text-hut-dark hover:bg-amber-400">Remind customer</button></form>
                <form method="POST" action="{{ route('manager.customers.payment', $customer) }}" class="flex gap-2">
                    @csrf
                    <input type="number" step="0.01" min="0" name="amount" placeholder="Payment" class="w-28 rounded-lg border border-gray-200 px-2 py-2 text-sm" />
                    <button type="submit" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Record payment</button>
                </form>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Order #</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Total</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($customer->orders as $order)
                        <tr>
                            <td class="px-4 py-3 font-medium text-hut-dark">{{ $order->order_number }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $order->status_label }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ number_format($order->total, 2) }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $order->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">No orders found for this customer.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
