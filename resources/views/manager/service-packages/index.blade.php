@extends('manager.layout.master')
@section('title', 'Service Packages')
@section('page-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Service packages</h1>
            <p class="mt-1 text-sm text-gray-500">Create salon packages, sell visits, and consume package visits.</p>
        </div>
        @if(session('success'))
        <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif
        <div class="grid gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('manager.service-packages.store') }}"
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">@csrf<h2 class="font-semibold">Create
                    package</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2"><input name="name" required placeholder="Package name"
                        class="rounded-lg border-gray-200 px-3 py-2 text-sm"><input name="included_visits" required
                        type="number" min="1" placeholder="Visits"
                        class="rounded-lg border-gray-200 px-3 py-2 text-sm"><input name="validity_days" required
                        type="number" min="1" placeholder="Validity days"
                        class="rounded-lg border-gray-200 px-3 py-2 text-sm"><input name="price" required type="number"
                        min="0" step="0.01" placeholder="Price" class="rounded-lg border-gray-200 px-3 py-2 text-sm"></div>
                <textarea name="description" rows="2" placeholder="Description"
                    class="mt-3 w-full rounded-lg border-gray-200 px-3 py-2 text-sm"></textarea><button
                    class="mt-3 rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Save package</button>
            </form>
            <form method="POST" action="{{ route('manager.service-packages.purchases.store') }}"
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">@csrf<h2 class="font-semibold">Sell package
                </h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2"><select name="service_package_id" required
                        class="rounded-lg border-gray-200 px-3 py-2 text-sm">
                        <option value="">Select package</option>@foreach($packages as $package)
                            <option value="{{ $package->id }}">{{ $package->name }} · {{ $package->included_visits }} visits
                        </option>@endforeach
                    </select><select name="customer_id" required class="rounded-lg border-gray-200 px-3 py-2 text-sm">
                        <option value="">Select customer</option>@foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach
                    </select><input name="starts_at" required type="date" value="{{ now()->toDateString() }}"
                        class="rounded-lg border-gray-200 px-3 py-2 text-sm"><input name="amount_paid" type="number" min="0"
                        step="0.01" placeholder="Amount paid" class="rounded-lg border-gray-200 px-3 py-2 text-sm"></div>
                <button class="mt-3 rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Sell package</button>
            </form>
        </div>
        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Package</th>
                        <th class="px-4 py-3">Valid until</th>
                        <th class="px-4 py-3">Remaining</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">@forelse($purchases as $purchase)
                    <tr>
                        <td class="px-4 py-3">{{ $purchase->customer->name }}</td>
                        <td class="px-4 py-3">{{ $purchase->package->name }}</td>
                        <td class="px-4 py-3">{{ $purchase->ends_at->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $purchase->remaining_visits }}</td>
                        <td class="px-4 py-3">@if($purchase->status === 'active' && $purchase->remaining_visits > 0)
                            <form method="POST"
                                action="{{ route('manager.service-packages.purchases.consume', $purchase) }}">@csrf
                                @method('PATCH')<button
                                    class="rounded bg-hut-dark px-2 py-1 text-xs font-semibold text-white">Use
                        visit</button></form>@else<span
                                    class="text-xs text-gray-500">{{ ucfirst($purchase->status) }}</span>@endif
                        </td>
                </tr>@empty<tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No package purchases yet.</td>
                    </tr>@endforelse
                </tbody>
            </table>
            <div class="border-t border-gray-100 px-5 py-4">{{ $purchases->links() }}</div>
        </section>
    </div>
@endsection