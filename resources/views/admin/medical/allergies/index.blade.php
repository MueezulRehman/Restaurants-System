@extends('layouts.admin')

@section('title', 'Customer Allergies')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Customer Allergies</h1>
        <a href="{{ route('manager.customer-allergies.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            Add Allergy
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
                    <th class="px-6 py-3 text-left">Customer</th>
                    <th class="px-6 py-3 text-left">Allergy Name</th>
                    <th class="px-6 py-3 text-left">Severity</th>
                    <th class="px-6 py-3 text-left">Trigger Medicines</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allergies as $allergy)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4">{{ $allergy->customer->name }}</td>
                        <td class="px-6 py-4">{{ $allergy->allergy_name }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded text-sm font-semibold 
                                @if($allergy->severity === 'mild') bg-blue-100 text-blue-800
                                @elseif($allergy->severity === 'moderate') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($allergy->severity) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($allergy->trigger_medicines)
                                <div class="flex flex-wrap gap-2">
                                    @forelse($allergy->triggerMedicines() as $medicine)
                                        <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-sm">
                                            {{ $medicine->name }}
                                        </span>
                                    @empty
                                        <span class="text-gray-500">No medicines specified</span>
                                    @endforelse
                                </div>
                            @else
                                <span class="text-gray-500">No medicines specified</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded text-sm font-semibold 
                                @if($allergy->is_active) bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $allergy->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center space-x-2">
                            <a href="{{ route('manager.customer-allergies.edit', $allergy->id) }}" 
                               class="text-blue-500 hover:text-blue-700">
                                Edit
                            </a>
                            <form action="{{ route('manager.customer-allergies.destroy', $allergy->id) }}" 
                                  method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700"
                                        onclick="return confirm('Are you sure?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No allergies recorded yet
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $allergies->links() }}
    </div>
</div>
@endsection
