@extends('layouts.admin')

@section('title', 'Item Sales')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-hut-dark">Item Sales</h2>
                <p class="text-sm text-gray-500">Set percent or fixed (Rs) discounts on menu items. Live sales show on the
                    storefront.</p>
            </div>
            <a href="{{ route('manager.item-sales.create') }}"
                class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hut-green text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-hut-green/90"
                aria-label="Add sale" title="Add sale">
                <i class="fas fa-tag text-sm" aria-hidden="true"></i>
                <span
                    class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100">Add
                    sale</span>
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Item</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Offer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Schedule</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($promotions as $p)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $p->menuItem?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $p->label ?: 'Sale' }} —
                                @if($p->type === 'percent')
                                    {{ rtrim(rtrim(number_format($p->value, 2), '0'), '.') }}% off
                                @else
                                    Rs {{ number_format($p->value, 0) }} off
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $p->starts_at ? $p->starts_at->format('M d') : 'Anytime' }}
                                →
                                {{ $p->ends_at ? $p->ends_at->format('M d, Y') : 'No end' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($p->isLive())
                                    <span
                                        class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Live</span>
                                @elseif($p->is_active)
                                    <span
                                        class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">Scheduled</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-600">Off</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('manager.item-sales.edit', $p) }}"
                                    class="group relative inline-flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-hut-green transition hover:-translate-y-0.5 hover:bg-emerald-100"
                                    aria-label="Edit sale" title="Edit sale">
                                    <i class="fas fa-pen text-xs" aria-hidden="true"></i>
                                    <span
                                        class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100">Edit</span>
                                </a>
                                <form action="{{ route('manager.item-sales.destroy', $p) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Remove this sale?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" aria-label="Delete sale" title="Delete sale"
                                        class="group relative ml-2 inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:-translate-y-0.5 hover:bg-red-100">
                                        <i class="fas fa-trash text-xs" aria-hidden="true"></i>
                                        <span
                                            class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100">Delete</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No item sales yet. Create one to
                                promote products on your storefront.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $promotions->links() }}</div>
    </div>
@endsection