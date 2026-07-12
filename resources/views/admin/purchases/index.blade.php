@extends('layouts.admin')
@section('title', 'Purchase Batches')

@section('content')
<div class="bg-white rounded-xl p-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
        <div>
            <h1 class="font-display font-bold">Purchase Batches</h1>
            <p class="text-sm text-gray-500">View batch receipts and inventory entries for your medical store.</p>
        </div>
        <a href="{{ route('manager.purchases.create') }}" class="btn">Record New Batch</a>
    </div>

    @if($batches->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-sm text-gray-500">
            No purchase batches recorded yet. Use the button above to add stock and batch details.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 uppercase tracking-wide">
                        <th class="px-3 py-3">Medicine</th>
                        <th class="px-3 py-3">Batch</th>
                        <th class="px-3 py-3">Qty</th>
                        <th class="px-3 py-3">Purchase</th>
                        <th class="px-3 py-3">Selling</th>
                        <th class="px-3 py-3">Expiry</th>
                        <th class="px-3 py-3">Invoice</th>
                        <th class="px-3 py-3">Recorded</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $batch)
                        <tr class="border-t">
                            <td class="px-3 py-3">{{ $batch->medicine?->name ?? 'Unknown' }}</td>
                            <td class="px-3 py-3">{{ $batch->batch_number ?? 'N/A' }}</td>
                            <td class="px-3 py-3">{{ $batch->quantity }}</td>
                            <td class="px-3 py-3">{{ number_format($batch->purchase_price, 2) }}</td>
                            <td class="px-3 py-3">{{ number_format($batch->selling_price, 2) }}</td>
                            <td class="px-3 py-3">{{ optional($batch->expiry_date)->format('M d, Y') ?? 'None' }}</td>
                            <td class="px-3 py-3">{{ $batch->purchaseItem?->purchase?->invoice_no ?? '—' }}</td>
                            <td class="px-3 py-3">{{ optional($batch->purchaseItem?->purchase?->purchase_date)->format('M d, Y') ?? $batch->created_at->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $batches->links() }}</div>
    @endif
</div>
@endsection
