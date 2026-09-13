@extends('manager.layout.master')
@section('title', 'Delivery Zones')
@section('page-content')
    <div class="mb-6">
        <h1 class="text-2xl font-display font-bold text-hut-dark">Delivery zones</h1>
        <p class="mt-1 text-sm text-gray-500">Set service areas, delivery fees, and minimum order values.</p>
    </div>
    <section class="mb-6 rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-hut-dark">Add zone</h2>
        <form method="POST" action="{{ route('manager.delivery-zones.store') }}" class="mt-4 grid gap-3 md:grid-cols-4">
            @csrf
            <input name="name" required placeholder="Zone name" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            <input name="area_pattern" required placeholder="Area keyword"
                class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            <input name="fee" required type="number" min="0" step="0.01" placeholder="Delivery fee"
                class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            <input name="minimum_order" type="number" min="0" step="0.01" placeholder="Minimum order"
                class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            <button class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white md:col-span-4">Save
                zone</button>
        </form>
    </section>
    <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Zone</th>
                        <th class="px-4 py-3">Area keyword</th>
                        <th class="px-4 py-3">Fee</th>
                        <th class="px-4 py-3">Minimum order</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($zones as $zone)
                        <tr>
                            <form method="POST" action="{{ route('manager.delivery-zones.update', $zone) }}">@csrf
                                @method('PUT')
                                <td class="px-4 py-3"><input name="name" value="{{ $zone->name }}"
                                        class="w-full rounded border border-gray-200 px-2 py-1 text-sm"></td>
                                <td class="px-4 py-3"><input name="area_pattern" value="{{ $zone->area_pattern }}"
                                        class="w-full rounded border border-gray-200 px-2 py-1 text-sm"></td>
                                <td class="px-4 py-3"><input name="fee" type="number" min="0" step="0.01"
                                        value="{{ $zone->fee }}" class="w-28 rounded border border-gray-200 px-2 py-1 text-sm">
                                </td>
                                <td class="px-4 py-3"><input name="minimum_order" type="number" min="0" step="0.01"
                                        value="{{ $zone->minimum_order }}"
                                        class="w-28 rounded border border-gray-200 px-2 py-1 text-sm"></td>
                                <td class="px-4 py-3"><label class="inline-flex items-center gap-2 text-xs"><input
                                            type="checkbox" name="is_active" value="1" @checked($zone->is_active)>
                                        Active</label></td>
                                <td class="px-4 py-3"><button
                                        class="text-xs font-semibold text-hut-dark hover:underline">Save</button>
                            </form>
                            <form method="POST" action="{{ route('manager.delivery-zones.destroy', $zone) }}" class="mt-2">@csrf
                                @method('DELETE')<button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 hover:bg-red-50" aria-label="Delete delivery zone" title="Delete delivery zone"><x-icons.trash class="h-4 w-4" /></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-gray-500">No delivery zones configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 px-5 py-4">{{ $zones->links() }}</div>
    </section>
@endsection