@extends('manager.layout.master')
@section('title', 'Branch Inventory')
@section('page-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Branch inventory</h1>
            <p class="mt-1 text-sm text-gray-500">Maintain branch-level balances and review stock available for transfer.
            </p>
        </div>@if(session('success'))
        <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif
        <form method="GET" class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"><label
                class="text-sm font-medium">Branch</label><select name="branch_id" onchange="this.form.submit()"
                class="ml-3 rounded-lg border-gray-200 px-3 py-2 text-sm">@foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected($branchId === $branch->id)>{{ $branch->name }}
                ({{ $branch->code }})</option>@endforeach
            </select></form>
        <form method="POST" action="{{ route('manager.branch-inventory.adjust') }}"
            class="grid gap-3 rounded-xl border border-gray-100 bg-white p-5 shadow-sm md:grid-cols-4">@csrf<input
                type="hidden" name="branch_id" value="{{ $branchId }}"><input type="hidden" name="item_type"
                value="menu_item"><select name="item_id" required class="rounded-lg border-gray-200 px-3 py-2 text-sm">
                <option value="">Item</option>@foreach($items as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
            </select><input name="quantity" type="number" min="0" step="0.001" required placeholder="Balance"
                class="rounded-lg border-gray-200 px-3 py-2 text-sm"><button
                class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Save balance</button></form>
        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Item ID</th>
                        <th class="px-4 py-3">Quantity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">@forelse($inventory as $row)
                    <tr>
                        <td class="px-4 py-3">{{ ucfirst(str_replace('_', ' ', $row->item_type)) }}</td>
                        <td class="px-4 py-3">{{ $row->item_id }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $row->quantity }}</td>
                </tr>@empty<tr>
                        <td colspan="3" class="px-4 py-10 text-center text-gray-500">No branch inventory balances yet.</td>
                    </tr>@endforelse
                </tbody>
            </table>
        </section>
    </div>
@endsection