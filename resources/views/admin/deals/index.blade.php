@extends('layouts.admin')
@section('title', 'Deals')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h2 class="text-lg font-display font-bold text-hut-dark">Special Deals</h2>
    <a href="{{ route('admin.deals.create') }}" class="bg-hut-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-hut-green/90">+ Add Deal</a>
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
                        $dealThumb = null;
                        if ($deal->image) {
                            if (file_exists(public_path('images/'.$deal->image))) {
                                $dealThumb = asset('images/'.$deal->image);
                            } elseif (file_exists(public_path($deal->image))) {
                                $dealThumb = asset($deal->image);
                            } else {
                                $dealThumb = asset('storage/'.$deal->image);
                            }
                        }
                    @endphp
                    @if($dealThumb)
                        <img src="{{ $dealThumb }}" class="h-12 w-16 object-cover rounded" alt="{{ $deal->name }}">
                    @else
                        <div class="h-12 w-16 bg-gray-100 rounded flex items-center justify-center text-xs text-gray-400">No image</div>
                    @endif
                </td>
                <td class="px-4 py-3 font-medium text-hut-dark">{{ $deal->name }}</td>
                <td class="px-4 py-3 text-gray-600">{{ Str::limit($deal->description, 40) }}</td>
                <td class="px-4 py-3 text-right font-medium text-hut-green">Rs. {{ number_format($deal->price) }}</td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center gap-1 text-xs font-medium">
                        @if($deal->active)
                            <span class="w-2 h-2 bg-hut-green rounded-full"></span> Active
                        @else
                            <span class="w-2 h-2 bg-gray-300 rounded-full"></span> Inactive
                        @endif
                    </span>
                </td>
                <td class="px-4 py-3 text-right space-x-2 flex justify-end">
                    <a href="{{ route('admin.deals.edit', $deal) }}" class="text-hut-green hover:underline text-xs font-medium">Edit</a>
                    <form action="{{ route('admin.deals.destroy', $deal) }}" method="POST" class="inline" onsubmit="return confirm('Delete this deal?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline text-xs font-medium">Delete</button>
                    </form>
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
