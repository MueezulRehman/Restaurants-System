@extends('layouts.admin')

@section('title', 'Restaurants')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Restaurants</h2>
            <p class="text-sm text-gray-500">Register and manage restaurant accounts from the main admin panel.</p>
        </div>
        <a href="{{ route('admin.restaurants.create') }}" class="inline-flex items-center rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">+ Add Restaurant</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Domain</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Plan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Owners</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($restaurants as $restaurant)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <form action="{{ route('admin.restaurants.enter', $restaurant) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="font-medium text-hut-dark hover:text-hut-green hover:underline text-left" title="Click to manage this restaurant's menu, POS, cashbook, and staff">
                                    {{ $restaurant->name }}
                                </button>
                            </form>
                            <div class="text-sm text-gray-500">{{ $restaurant->slug }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $restaurant->domain ?? $restaurant->custom_domain ?? 'No domain' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ ucfirst($restaurant->plan) }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $restaurant->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ ucfirst($restaurant->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $restaurant->users_count }}</td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center gap-2">
                                <form action="{{ route('admin.restaurants.enter', $restaurant) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-hut-green hover:text-hut-green/70 font-medium">Manage</button>
                                </form>
                                <a href="{{ route('admin.restaurants.edit', $restaurant) }}" class="text-hut-yellow hover:text-amber-600">Edit</a>
                                <form action="{{ route('admin.restaurants.destroy', $restaurant) }}" method="POST" onsubmit="return confirm('Delete this restaurant?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">No restaurants registered yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
