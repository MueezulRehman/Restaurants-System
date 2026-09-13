@extends('manager.layout.master')
@section('title', 'Trade-ins')
@section('page-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Trade-ins</h1>
            <p class="mt-1 text-sm text-gray-500">Record used devices, valuations, inspection, and customer credit status.
            </p>
        </div>
        <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Record trade-in</h2>
            <form method="POST" action="{{ route('manager.trade-ins.store') }}" class="mt-4 grid gap-3 md:grid-cols-6">
                @csrf
                <select name="customer_id" class="rounded-lg border px-3 py-2 text-sm">
                    <option value="">Customer</option>@foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach
                </select>
                <input name="item_name" required placeholder="Device / item" class="rounded-lg border px-3 py-2 text-sm">
                <input name="serial_number" placeholder="IMEI / serial" class="rounded-lg border px-3 py-2 text-sm">
                <input name="estimated_value" required type="number" min="0" step="0.01" placeholder="Estimated value"
                    class="rounded-lg border px-3 py-2 text-sm">
                <input name="condition" placeholder="Condition" class="rounded-lg border px-3 py-2 text-sm">
                <button class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Record</button>
            </form>
        </section>
        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Item</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Serial</th>
                            <th class="px-4 py-3">Value</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Update</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">@forelse($tradeIns as $tradeIn)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $tradeIn->item_name }}</td>
                            <td class="px-4 py-3">{{ $tradeIn->customer?->name ?? 'Walk-in' }}</td>
                            <td class="px-4 py-3">{{ $tradeIn->serial_number ?: '—' }}</td>
                            <td class="px-4 py-3">{{ number_format((float) $tradeIn->estimated_value, 2) }}</td>
                            <td class="px-4 py-3">{{ ucfirst($tradeIn->status) }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('manager.trade-ins.update', $tradeIn) }}"
                                    class="flex gap-2">@csrf @method('PATCH')<select name="status"
                                        class="rounded border px-2 py-1 text-xs">@foreach(['received', 'inspected', 'accepted', 'rejected', 'credited'] as $status)
                                            <option value="{{ $status }}" @selected($tradeIn->status === $status)>
                                        {{ ucfirst($status) }}</option>@endforeach
                                    </select><button class="text-xs font-semibold text-hut-blue">Save</button></form>
                            </td>
                    </tr>@empty<tr>
                            <td colspan="6" class="px-4 py-10 text-center text-gray-500">No trade-ins recorded.</td>
                        </tr>@endforelse
                    </tbody>
                </table>
            </div>
            @if($tradeIns->hasPages())
            <div class="border-t p-4">{{ $tradeIns->links() }}</div> @endif
        </section>
    </div>
@endsection