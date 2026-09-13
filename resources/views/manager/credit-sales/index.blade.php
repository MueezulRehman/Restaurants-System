@extends('manager.layout.master')
@section('title', 'Credit Sales')
@section('page-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Credit Sales & Receivables</h1>
            <p class="mt-1 text-sm text-gray-500">Review outstanding wholesale balances and record customer payments.</p>
        </div>
        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Phone</th>
                            <th class="px-4 py-3">Outstanding</th>
                            <th class="px-4 py-3">Credit limit</th>
                            <th class="px-4 py-3">Record payment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">@forelse($customers as $customer)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $customer->name }}</td>
                            <td class="px-4 py-3">{{ $customer->phone }}</td>
                            <td class="px-4 py-3 font-semibold text-red-700">
                                {{ number_format((float) $customer->balance, 2) }}</td>
                            <td class="px-4 py-3">{{ number_format((float) $customer->credit_limit, 2) }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('manager.credit-sales.payment', $customer) }}"
                                    class="flex flex-wrap gap-2">@csrf<input name="amount" required type="number" min="0.01"
                                        max="{{ $customer->balance }}" step="0.01" placeholder="Amount"
                                        class="w-28 rounded border px-2 py-1 text-xs"><input name="description"
                                        placeholder="Note" class="w-40 rounded border px-2 py-1 text-xs"><button
                                        class="text-xs font-semibold text-hut-blue">Record</button></form>
                            </td>
                    </tr>@empty<tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">No outstanding credit balances.
                            </td>
                        </tr>@endforelse
                    </tbody>
                </table>
            </div>@if($customers->hasPages())
            <div class="border-t p-4">{{ $customers->links() }}</div>@endif
        </section>
    </div>
@endsection