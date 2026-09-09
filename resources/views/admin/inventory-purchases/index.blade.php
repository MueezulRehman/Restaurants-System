@extends('layouts.admin')
@section('title', 'Purchasing')
@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Purchasing</h1>
            <p class="mt-1 text-sm text-gray-500">Received stock and supplier purchase history.</p>
        </div><a href="{{ route('manager.purchasing.create') }}"
            class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Receive stock</a>
    </div>
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Supplier</th>
                    <th class="px-4 py-3">Invoice</th>
                    <th class="px-4 py-3">Items</th>
                    <th class="px-4 py-3">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">@forelse($purchases as $purchase)
                <tr>
                    <td class="px-4 py-3">{{ $purchase->purchase_date?->format('d M Y') }}</td>
                    <td class="px-4 py-3">{{ $purchase->supplier?->name ?? $purchase->supplier_name ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $purchase->invoice_no ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $purchase->items->count() }}</td>
                    <td class="px-4 py-3">Rs. {{ number_format((float) $purchase->total, 2) }}</td>
            </tr>@empty<tr>
                    <td colspan="5" class="px-4 py-10 text-center text-gray-500">No purchases recorded yet.</td>
                </tr>@endforelse
            </tbody>
        </table>
        <div class="border-t border-gray-100 px-5 py-4">{{ $purchases->links() }}</div>
    </div>
@endsection