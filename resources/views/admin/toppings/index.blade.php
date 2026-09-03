@extends('layouts.admin')

@section('title', 'Toppings')

@section('content')
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-hut-dark">Toppings</h2>
                <p class="text-sm text-gray-500">Manage add-ons that can be selected on orders.</p>
            </div>
            <a href="{{ route('manager.toppings.create') }}"
                class="inline-flex items-center rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">+
                Add Topping</a>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Price</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($toppings as $topping)
                            <tr>
                                <td class="px-4 py-3 font-medium text-hut-dark">{{ $topping->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $topping->type ?: 'Standard' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ number_format($topping->price, 2) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('manager.toppings.edit', $topping) }}"
                                            class="text-hut-yellow hover:text-amber-600">Edit</a>
                                        <form action="{{ route('manager.toppings.destroy', $topping) }}" method="POST"
                                            data-confirm="Delete this topping?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">No toppings yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="{{ $toppings->hasPages() ? 'pt-2' : '' }}">
            {{ $toppings->links() }}
        </div>
    </div>
@endsection