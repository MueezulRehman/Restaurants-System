@extends('layouts.admin')
@section('title', 'Warranty & Repairs')
@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-display font-bold text-hut-dark">Warranty & Repairs</h1>
        <p class="mt-1 text-sm text-gray-500">Register device warranty claims and manage repair intake through collection.
        </p>
    </div>
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Serial</th>
                            <th class="px-4 py-3">Technician</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Collection</th>
                            <th class="px-4 py-3">Update</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">@forelse($cases as $case)
                        <tr>
                            <td class="px-4 py-3 capitalize">{{ $case->case_type }}</td>
                            <td class="px-4 py-3 font-medium">{{ $case->title }}</td>
                            <td class="px-4 py-3">{{ $case->customer?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $case->serial_number ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $case->technician?->name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $case->status) }}</td>
                            <td class="px-4 py-3">
                                @if($case->collection_notified_at)
                                    <span class="text-xs text-green-700">Notified
                                        {{ $case->collection_notified_at->diffForHumans() }}</span>
                                @elseif($case->status === 'ready' && $case->customer_id)
                                    <form method="POST" action="{{ route('manager.service-cases.notify-collection', $case) }}">
                                        @csrf<button class="text-xs font-semibold text-hut-dark hover:underline">Notify
                                            customer</button></form>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('manager.service-cases.update', $case) }}"
                                    class="flex gap-2">@csrf @method('PATCH')<select name="status"
                                        class="rounded border border-gray-200 px-2 py-1 text-xs">@foreach(['received', 'diagnosing', 'approved', 'in_progress', 'ready', 'completed', 'cancelled'] as $status)
                                            <option value="{{ $status }}" {{ $case->status === $status ? 'selected' : '' }}>
                                                {{ str_replace('_', ' ', ucfirst($status)) }}
                                        </option>@endforeach
                                    </select><select name="assigned_to"
                                        class="rounded border border-gray-200 px-2 py-1 text-xs">
                                        <option value="">Technician</option>@foreach($technicians as $technician)
                                            <option value="{{ $technician->id }}"
                                                @selected($case->assigned_to === $technician->id)>{{ $technician->name }}</option>
                                        @endforeach
                                    </select><input name="parts_used" value="{{ $case->parts_used }}"
                                        placeholder="Parts used"
                                        class="w-32 rounded border border-gray-200 px-2 py-1 text-xs"><input
                                        name="final_cost" type="number" min="0" step="0.01" value="{{ $case->final_cost }}"
                                        placeholder="Final cost"
                                        class="w-24 rounded border border-gray-200 px-2 py-1 text-xs"><button
                                        class="rounded bg-hut-dark px-2 py-1 text-xs text-white">Save</button>
                                </form>
                            </td>
                    </tr>@empty<tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-500">No service cases yet.</td>
                        </tr>@endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-5 py-4">{{ $cases->links() }}</div>
        </section>
        <section class="h-fit rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">New case</h2>
            <form method="POST" action="{{ route('manager.service-cases.store') }}" class="mt-4 space-y-3">@csrf<select
                    name="case_type" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="repair">Repair</option>
                    <option value="warranty">Warranty</option>
                </select><input type="search" id="service-customer-search" placeholder="Search customer by name or phone"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <div id="service-customer-results"
                    class="hidden max-h-40 overflow-y-auto rounded-lg border border-gray-200 bg-white"></div><select
                    id="service-customer-select" name="customer_id"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Customer</option>@foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }} · {{ $customer->phone }}</option>@endforeach
                </select><select name="menu_item_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Product</option>@foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                </select><input name="serial_number" placeholder="IMEI / serial number"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"><input name="title" required
                    placeholder="Issue or claim title"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"><textarea name="description" rows="3"
                    placeholder="Problem details"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"></textarea>
                <div class="grid grid-cols-2 gap-2"><input type="number" name="estimated_cost" min="0" step="0.01"
                        placeholder="Estimated cost"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"><input type="date" name="due_at"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"></div>
                <select name="assigned_to" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Assign technician (optional)</option>@foreach($technicians as $technician)
                    <option value="{{ $technician->id }}">{{ $technician->name }}</option>@endforeach
                </select><button class="w-full rounded-lg bg-hut-dark px-4 py-2.5 text-sm font-semibold text-white">Create
                    case</button>
            </form>
            <button type="button" id="toggle-service-new-customer"
                class="mt-3 text-xs font-semibold text-hut-dark hover:underline">+ Add new customer</button>
            <form id="service-new-customer" method="POST" action="{{ route('manager.customers.store') }}"
                class="mt-2 hidden space-y-2">@csrf<input type="hidden" name="redirect_to_pos" value="0"><input name="name"
                    required placeholder="Customer name"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"><input name="phone" required
                    placeholder="Phone" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"><button
                    class="rounded-lg border border-hut-yellow/40 bg-hut-yellow/10 px-3 py-2 text-sm font-semibold">Register
                    customer</button></form>
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const search = document.getElementById('service-customer-search'); const select = document.getElementById('service-customer-select');
            const results = document.getElementById('service-customer-results');
            if (search && select && results) search.addEventListener('input', () => { const term = search.value.toLowerCase().trim(); results.innerHTML = ''; Array.from(select.options).forEach((option, index) => { if (index) { option.hidden = term !== '' && !(option.textContent || '').toLowerCase().includes(term); if (term && !option.hidden) { const result = document.createElement('button'); result.type = 'button'; result.className = 'block w-full px-3 py-2 text-left text-sm hover:bg-gray-50'; result.textContent = option.textContent; result.onclick = () => { select.value = option.value; search.value = option.textContent; results.classList.add('hidden'); }; results.appendChild(result); } } }); results.classList.toggle('hidden', !term || results.childElementCount === 0); });
            const toggle = document.getElementById('toggle-service-new-customer'); const form = document.getElementById('service-new-customer');
            if (toggle && form) toggle.addEventListener('click', () => { form.classList.toggle('hidden'); toggle.textContent = form.classList.contains('hidden') ? '+ Add new customer' : '− Close'; });
        });
    </script>
@endsection