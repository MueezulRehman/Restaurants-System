@extends('manager.layout.master')
@section('title', 'Installments')
@section('page-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Installment Plans</h1>
            <p class="mt-1 text-sm text-gray-500">Track deposits, payment periods, due dates, and outstanding plans.</p>
        </div>
        <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Create plan</h2>
            <form method="POST" action="{{ route('manager.installments.store') }}" class="mt-4 grid gap-3 md:grid-cols-6">
                @csrf<select name="customer_id" class="rounded-lg border px-3 py-2 text-sm">
                    <option value="">Customer</option>@foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach
                </select><input name="item_name" required placeholder="Item"
                    class="rounded-lg border px-3 py-2 text-sm"><input name="total_amount" required type="number" min="0.01"
                    step="0.01" placeholder="Total" class="rounded-lg border px-3 py-2 text-sm"><input name="deposit"
                    required type="number" min="0" step="0.01" placeholder="Deposit"
                    class="rounded-lg border px-3 py-2 text-sm"><input name="months" required type="number" min="1"
                    max="120" placeholder="Months" class="rounded-lg border px-3 py-2 text-sm"><button
                    class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Create plan</button></form>
        </section>
        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Item</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Deposit</th>
                            <th class="px-4 py-3">Months</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Update</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">@forelse($plans as $plan)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $plan->item_name }}</td>
                            <td class="px-4 py-3">{{ $plan->customer?->name ?? 'Walk-in' }}</td>
                            <td class="px-4 py-3">{{ number_format((float) $plan->total_amount, 2) }}</td>
                            <td class="px-4 py-3">{{ number_format((float) $plan->deposit, 2) }}</td>
                            <td class="px-4 py-3">{{ $plan->months }}</td>
                            <td class="px-4 py-3">{{ ucfirst($plan->status) }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('manager.installments.update', $plan) }}"
                                    class="flex gap-2">@csrf @method('PATCH')<select name="status"
                                        class="rounded border px-2 py-1 text-xs">@foreach(['active', 'paid', 'overdue', 'cancelled'] as $status)
                                            <option value="{{ $status }}" @selected($plan->status === $status)>
                                        {{ ucfirst($status) }}</option>@endforeach
                                    </select><button class="text-xs font-semibold text-hut-blue">Save</button></form>
                            </td>
                    </tr>@empty<tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500">No installment plans recorded.</td>
                        </tr>@endforelse
                    </tbody>
                </table>
            </div>@if($plans->hasPages())
            <div class="border-t p-4">{{ $plans->links() }}</div>@endif
        </section>
    </div>
@endsection