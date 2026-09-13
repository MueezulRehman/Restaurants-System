@extends('manager.layout.master')
@section('title', 'Collections')
@section('page-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Collections</h1>
            <p class="mt-1 text-sm text-gray-500">Organize clothing and seasonal products into collections.</p>
        </div>
        <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Create collection</h2>
            <form method="POST" action="{{ route('manager.collections.store') }}" class="mt-4 grid gap-3 md:grid-cols-5">
                @csrf<input name="name" required placeholder="Collection name"
                    class="rounded-lg border px-3 py-2 text-sm"><input name="season" placeholder="Season"
                    class="rounded-lg border px-3 py-2 text-sm"><input name="starts_at" type="date"
                    class="rounded-lg border px-3 py-2 text-sm"><input name="ends_at" type="date"
                    class="rounded-lg border px-3 py-2 text-sm"><button
                    class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Create</button></form>
        </section>
        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Collection</th>
                            <th class="px-4 py-3">Season</th>
                            <th class="px-4 py-3">Dates</th>
                            <th class="px-4 py-3">Products</th>
                            <th class="px-4 py-3">Assign product</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">@forelse($collections as $collection)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $collection->name }}</td>
                            <td class="px-4 py-3">{{ $collection->season ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $collection->starts_at?->format('d M Y') ?? '—' }} to
                                {{ $collection->ends_at?->format('d M Y') ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $collection->menu_items_count }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('manager.collections.assign-item', $collection) }}"
                                    class="flex gap-2">@csrf<select name="menu_item_id" required
                                        class="rounded border px-2 py-1 text-xs">
                                        <option value="">Product</option>@foreach($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                                    </select><button class="text-xs font-semibold text-hut-blue">Assign</button></form>
                            </td>
                    </tr>@empty<tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">No collections created.</td>
                        </tr>@endforelse
                    </tbody>
                </table>
            </div>@if($collections->hasPages())
            <div class="border-t p-4">{{ $collections->links() }}</div>@endif
        </section>
    </div>
@endsection