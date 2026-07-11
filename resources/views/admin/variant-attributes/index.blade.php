@extends('layouts.admin')
@section('title', 'Attributes')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-lg font-display font-bold text-hut-dark">Attributes for {{ $item->name }}</h2>
        <p class="text-sm text-gray-500">Manage variant attribute groups for this item.</p>
    </div>
    <a href="{{ route('manager.menu-items.attributes.create', $item) }}" class="bg-hut-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-hut-green/90">+ Add Attribute</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
            <tr>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($attributes as $attribute)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 font-medium text-hut-dark">{{ $attribute->name }}</td>
                <td class="px-4 py-3 text-right space-x-2 flex justify-end">
                    <a href="{{ route('manager.menu-items.attributes.edit', [$item, $attribute]) }}" class="text-hut-green hover:underline text-xs font-medium">Edit</a>
                    <form action="{{ route('manager.menu-items.attributes.destroy', [$item, $attribute]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this attribute?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline text-xs font-medium">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="px-4 py-8 text-center text-gray-500">No attributes found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $attributes->links() }}
</div>

<div class="mt-6">
    <a href="{{ route('manager.menu-items.index') }}" class="text-hut-green text-sm hover:underline">← Back to Menu Items</a>
</div>

@endsection
