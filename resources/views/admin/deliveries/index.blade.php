@extends('layouts.admin')

@section('title', 'Deliveries')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Delivery Management</h2>
            <p class="text-sm text-gray-500">Assign riders and track delivery status in one place.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 border rounded-lg px-3 py-2">
                    <option value="">All</option>
                    @foreach(['pending','assigned','on_the_way','delivered'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$status)) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="px-4 py-2 rounded-lg bg-hut-dark text-white">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Order</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Rider</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Notes</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($deliveries as $delivery)
                    <tr>
                        <td class="px-4 py-3">{{ $delivery->order->order_number ?? '-' }}</td>
                        <td class="px-4 py-3">{{ ucfirst(str_replace('_',' ',$delivery->status)) }}</td>
                        <td class="px-4 py-3">{{ $delivery->rider->name ?? 'Unassigned' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $delivery->delivery_notes ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('manager.deliveries.update', $delivery) }}" class="flex flex-wrap gap-2 items-center">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="border rounded-lg px-2 py-1 text-sm">
                                    @foreach(['pending','assigned','on_the_way','delivered'] as $status)
                                        <option value="{{ $status }}" {{ $delivery->status === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$status)) }}</option>
                                    @endforeach
                                </select>
                                <select name="rider_id" class="border rounded-lg px-2 py-1 text-sm">
                                    <option value="">Unassigned</option>
                                    @foreach($riders as $rider)
                                        <option value="{{ $rider->id }}" {{ $delivery->rider_id == $rider->id ? 'selected' : '' }}>{{ $rider->name }}</option>
                                    @endforeach
                                </select>
                                <button class="px-3 py-1 rounded-lg bg-hut-yellow text-hut-dark font-medium">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No deliveries found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $deliveries->links() }}
    </div>
</div>
@endsection
