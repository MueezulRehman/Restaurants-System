@extends('manager.layout.master')

@section('title', 'Medicine Interactions')

@section('page-content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Medicine Interactions</h1>
        <a href="{{ route('manager.medicine-interactions.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            Add Interaction
        </a>
    </div>

    @if($message = session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ $message }}
        </div>
    @endif

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left">Medicine 1</th>
                    <th class="px-6 py-3 text-left">Medicine 2</th>
                    <th class="px-6 py-3 text-left">Type</th>
                    <th class="px-6 py-3 text-left">Description</th>
                    <th class="px-6 py-3 text-left">Recommended Action</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($interactions as $interaction)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4">{{ $interaction->medicineFirst->name }}</td>
                        <td class="px-6 py-4">{{ $interaction->medicineSecond->name }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded text-sm font-semibold 
                                @if($interaction->interaction_type === 'contraindicated') bg-red-100 text-red-800
                                @elseif($interaction->interaction_type === 'serious') bg-orange-100 text-orange-800
                                @elseif($interaction->interaction_type === 'moderate') bg-yellow-100 text-yellow-800
                                @else bg-blue-100 text-blue-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $interaction->interaction_type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ Str::limit($interaction->interaction_description, 50) }}</td>
                        <td class="px-6 py-4">{{ Str::limit($interaction->recommended_action, 50) }}</td>
                        <td class="px-6 py-4 text-center space-x-2">
                            <a href="{{ route('manager.medicine-interactions.edit', $interaction->id) }}"
                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-50 hover:text-blue-700"
                               aria-label="Edit medicine interaction" title="Edit medicine interaction">
                                <x-icons.edit class="h-4 w-4" />
                            </a>
                            <form action="{{ route('manager.medicine-interactions.destroy', $interaction->id) }}" 
                                  method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-500 hover:bg-red-50 hover:text-red-700"
                                        aria-label="Delete medicine interaction" title="Delete medicine interaction"
                                        onclick="return confirm('Are you sure?')">
                                    <x-icons.trash class="h-4 w-4" />
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No interactions recorded yet
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $interactions->links() }}
    </div>
</div>
@endsection
