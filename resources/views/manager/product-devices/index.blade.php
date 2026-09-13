@extends('manager.layout.master')
@section('title', 'Device Tracking')
@section('page-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">IMEI & Serial Tracking</h1>
            <p class="mt-1 text-sm text-gray-500">Register devices, warranties, customers, and lifecycle status.</p>
        </div>
        <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Register device</h2>
            <form method="POST" action="{{ route('manager.product-devices.store') }}"
                class="mt-4 grid gap-3 md:grid-cols-5">@csrf<select name="menu_item_id"
                    class="rounded-lg border px-3 py-2 text-sm">
                    <option value="">Product</option>@foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                </select><select name="customer_id" class="rounded-lg border px-3 py-2 text-sm">
                    <option value="">Customer</option>@foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach
                </select><select name="identifier_type" required class="rounded-lg border px-3 py-2 text-sm">
                    <option value="imei">IMEI</option>
                    <option value="serial">Serial</option>
                </select><input name="identifier_value" required placeholder="IMEI or serial number"
                    class="rounded-lg border px-3 py-2 text-sm"><input name="warranty_until" type="date"
                    class="rounded-lg border px-3 py-2 text-sm"><input type="hidden" name="status" value="in_stock"><button
                    class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white md:col-span-5">Register
                    device</button></form>
        </section>
        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Identifier</th>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Warranty</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Update</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">@forelse($devices as $device)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ strtoupper($device->identifier_type) }}:
                                {{ $device->identifier_value }}</td>
                            <td class="px-4 py-3">
                                {{ $device->menuItem?->name ?? $device->variant?->menuItem?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $device->customer?->name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-3">{{ $device->warranty_until?->format('d M Y') ?? '—' }}</td>
                            <td class="px-4 py-3">{{ ucfirst(str_replace('_', ' ', $device->status)) }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('manager.product-devices.update', $device) }}"
                                    class="flex gap-2">@csrf @method('PATCH')<select name="status"
                                        class="rounded border px-2 py-1 text-xs">@foreach(['in_stock', 'sold', 'repair', 'returned'] as $status)
                                            <option value="{{ $status }}" @selected($device->status === $status)>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach
                                    </select><button class="text-xs font-semibold text-hut-blue">Save</button></form>
                            </td>
                    </tr>@empty<tr>
                            <td colspan="6" class="px-4 py-10 text-center text-gray-500">No devices registered.</td>
                        </tr>@endforelse
                    </tbody>
                </table>
            </div>@if($devices->hasPages())
            <div class="border-t p-4">{{ $devices->links() }}</div>@endif
        </section>
    </div>
@endsection