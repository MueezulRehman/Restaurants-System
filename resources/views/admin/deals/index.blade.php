@extends('layouts.admin')
@section('title', 'Deals')

@section('content')

<a href="{{ route('manager.dashboard') }}"
    class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white/75 text-hut-blue shadow-sm backdrop-blur transition hover:-translate-y-0.5 hover:bg-white hover:text-hut-dark"
    aria-label="Back to dashboard" title="Back to dashboard">
    <i class="fas fa-arrow-left text-sm" aria-hidden="true"></i>
</a>

@php
    $dealPosterFiles = collect(glob(public_path('images/deals/*')) ?: [])
        ->filter(fn($path) => is_file($path))
        ->sort()
        ->values();
    $resolveDealImage = function (?string $path): ?string {
        if (!$path) {
            return null;
        }
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }
        if (is_file(public_path('images/' . $path))) {
            return asset('images/' . $path);
        }
        if (is_file(public_path($path))) {
            return asset($path);
        }
        if (is_file(storage_path('app/public/' . $path))) {
            return asset('storage/' . $path);
        }
        return null;
    };
@endphp

<div class="flex justify-between items-center mb-6">
    <h2 class="text-lg font-display font-bold text-hut-dark">Special Deals</h2>
    <a href="{{ route('manager.deals.create') }}"
        class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hut-green text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-hut-green/90"
        aria-label="Add deal" title="Add deal">
        <x-icons.add class="h-5 w-5" />
        <span
            class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Add
            deal</span>
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
            <tr>
                <th class="px-4 py-3 text-left">Image</th>
                <th class="px-4 py-3 text-left">Deal Name</th>
                <th class="px-4 py-3 text-left">Description</th>
                <th class="px-4 py-3 text-right">Price</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($deals as $deal)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3">
                    @php
                        $dealThumb = $resolveDealImage($deal->image);
                        if (!$dealThumb) {
                            $dealPoster = $dealPosterFiles->get(max(0, ((int) $deal->deal_number) - 1));
                            $dealThumb = $dealPoster ? asset('images/deals/' . basename($dealPoster)) : null;
                        }
                    @endphp
                    @if($dealThumb)
                        <img src="{{ $dealThumb }}" class="h-12 w-16 object-cover rounded" alt="{{ $deal->name }}">
                    @else
                        <div class="h-12 w-16 bg-gray-100 rounded flex items-center justify-center text-xs text-gray-400">No
                            image</div>
                    @endif
                </td>
                <td class="px-4 py-3 font-medium text-hut-dark">{{ $deal->name }}</td>
                <td class="px-4 py-3 text-gray-600">{{ Str::limit($deal->description, 40) }}</td>
                <td class="px-4 py-3 text-right font-medium text-hut-green">Rs. {{ number_format($deal->price) }}</td>
                <td class="px-4 py-3 text-center">
                    @php($dealStatus = $deal->status())
                    <span
                        class="inline-flex items-center gap-1 text-xs font-medium
                                {{ $dealStatus === 'active' ? 'text-hut-green' : ($dealStatus === 'upcoming' ? 'text-amber-600' : 'text-gray-500') }}">
                        <span
                            class="w-2 h-2 rounded-full
                                    {{ $dealStatus === 'active' ? 'bg-hut-green' : ($dealStatus === 'upcoming' ? 'bg-amber-400' : 'bg-gray-300') }}"></span>
                        {{ ucfirst($dealStatus) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('manager.deals.edit', $deal) }}"
                            class="group relative inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-hut-green shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-emerald-100"
                            aria-label="Edit deal" title="Edit deal">
                            <x-icons.edit class="h-4 w-4" />
                            <span
                                class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Edit</span>
                        </a>
                        <form action="{{ route('manager.deals.destroy', $deal) }}" method="POST" class="inline"
                            data-confirm="Delete this deal?">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="group relative inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-red-100"
                                aria-label="Delete deal" title="Delete deal">
                                <x-icons.trash class="h-4 w-4" />
                                <span
                                    class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Delete</span>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No deals found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $deals->links() }}
</div>

@endsection