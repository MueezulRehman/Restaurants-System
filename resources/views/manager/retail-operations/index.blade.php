@extends('manager.layout.master')
@section('title', 'Retail Operations')
@section('page-content')
    <div class="mb-6">
        <h1 class="text-2xl font-display font-bold text-hut-dark">Retail Operations</h1>
        <p class="mt-1 text-sm text-gray-500">Brands, collections, loyalty, transfers, trade-ins, and installment plans.</p>
    </div>
    <div class="grid gap-5 lg:grid-cols-3">
        @foreach([['brand', 'Add brand', 'name,description'], ['collection', 'Add collection', 'name,season,starts_at,ends_at'], ['transfer', 'Record stock transfer', 'from_location,to_location,item_name,quantity'], ['trade_in', 'Record trade-in', 'item_name,serial_number,estimated_value,condition'], ['installment', 'Create installment plan', 'item_name,total_amount,deposit,months,next_due_at']] as [$type, $title, $fields])
            <section class="flex min-h-[23rem] flex-col rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-hut-dark">{{ $title }}</h2>
                <form method="POST" action="{{ route('manager.retail-operations.store') }}" class="mt-3 flex flex-1 flex-col space-y-2">@csrf<input
                        type="hidden" name="type" value="{{ $type }}">@if(in_array($type, ['trade_in', 'installment']))<input
                                type="search" data-customer-search placeholder="Search customer by name or phone"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            <div data-customer-results
                                class="hidden max-h-40 overflow-y-auto rounded-lg border border-gray-200 bg-white"></div><select
                                name="customer_id" required data-customer-select
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                <option value="">Customer</option>@foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} · {{ $customer->phone }}</option>@endforeach
                        </select>@endif @foreach(explode(',', $fields) as $field)<input name="{{ $field }}" {{ in_array($field, ['name', 'item_name', 'total_amount', 'estimated_value', 'months', 'quantity', 'from_location', 'to_location']) ? 'required' : '' }}
                        type="{{ str_contains($field, 'at') ? 'date' : (in_array($field, ['quantity', 'total_amount', 'deposit', 'estimated_value']) ? 'number' : 'text') }}"
                        step="0.01" placeholder="{{ ucwords(str_replace('_', ' ', $field)) }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">@endforeach<button
                        class="mt-auto w-full rounded-lg bg-hut-dark px-3 py-2 text-sm font-semibold text-white">Save</button>
                </form>
        </section>@endforeach
        <section class="flex min-h-[23rem] flex-col rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-hut-dark">Loyalty points</h2>
            <form method="POST" action="{{ route('manager.retail-operations.store') }}" class="mt-3 flex flex-1 flex-col space-y-2">@csrf<input
                    type="hidden" name="type" value="loyalty"><select name="customer_id" required
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Customer</option>@foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }} · {{ $customer->phone }}</option>@endforeach
                </select><input name="points" type="number" min="0" required placeholder="Points"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"><button
                    class="mt-auto w-full rounded-lg bg-hut-dark px-3 py-2 text-sm font-semibold text-white">Update points</button>
            </form>
        </section>
    </div>
    <div class="mt-6 grid gap-5 lg:grid-cols-2">
        <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Brands and collections</h2>@foreach($brands as $brand)
            <p class="mt-2 text-sm">Brand: {{ $brand->name }}</p>@endforeach @foreach($collections as $collection)
                <p class="mt-2 text-sm">Collection: {{ $collection->name }}
                    {{ $collection->season ? '(' . $collection->season . ')' : '' }}
            </p>@endforeach
        </section>
        <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Recent transfers and trade-ins</h2>@foreach($transfers as $transfer)
                <p class="mt-2 text-sm">{{ $transfer->item_name }}: {{ $transfer->from_location }} →
                    {{ $transfer->to_location }}
            </p>@endforeach @foreach($tradeIns as $tradeIn)
                <p class="mt-2 text-sm">Trade-in: {{ $tradeIn->item_name }} · Rs.
                    {{ number_format($tradeIn->estimated_value, 2) }}
            </p>@endforeach
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-customer-search]').forEach(search => {
                const select = search.parentElement.querySelector('[data-customer-select]');
                const results = search.parentElement.querySelector('[data-customer-results]'); search.addEventListener('input', () => { const term = search.value.toLowerCase().trim(); results.innerHTML = ''; Array.from(select.options).forEach((option, index) => { if (index) { option.hidden = term !== '' && !(option.textContent || '').toLowerCase().includes(term); if (term && !option.hidden) { const result = document.createElement('button'); result.type = 'button'; result.className = 'block w-full px-3 py-2 text-left text-sm hover:bg-gray-50'; result.textContent = option.textContent; result.onclick = () => { select.value = option.value; search.value = option.textContent; results.classList.add('hidden'); }; results.appendChild(result); } } }); results.classList.toggle('hidden', !term || results.childElementCount === 0); });
            });
        });
    </script>
@endsection