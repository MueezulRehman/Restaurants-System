@extends('manager.layout.master')
@section('title', 'Attributes')

@section('page-content')

    <a href="{{ route('manager.menu-items.index') }}"
        class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white/75 text-hut-blue shadow-sm backdrop-blur transition hover:bg-white hover:text-hut-dark"
        aria-label="Back to menu items" title="Back to menu items">
        <i class="fas fa-arrow-left text-sm" aria-hidden="true"></i>
    </a>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-lg font-display font-bold text-hut-dark">Attributes for {{ $item->name }}</h2>
            <p class="text-sm text-gray-500">Manage variant attribute groups for this item.</p>
        </div>
        <a href="{{ route('manager.menu-items.attributes.create', $item) }}"
            class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hut-green text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-hut-green/90"
            aria-label="Add attribute" title="Add attribute">
            <x-icons.add class="h-5 w-5" />
            <span
                class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Add
                attribute</span>
        </a>
    </div>

    @if($sizes->isNotEmpty())
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6">
            <h3 class="font-medium text-hut-dark mb-2">Size options</h3>
            <div class="flex flex-wrap gap-3 text-sm text-gray-700">
                @foreach($sizes as $size)
                    <span class="bg-white border border-blue-100 rounded-lg px-3 py-2">
                        {{ $size->size_label }} · Rs. {{ number_format($size->price) }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

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
                            <a href="{{ route('manager.menu-items.attributes.edit', [$item, $attribute]) }}"
                                class="group relative inline-flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-hut-green transition duration-200 hover:-translate-y-0.5 hover:bg-emerald-100"
                                aria-label="Edit attribute" title="Edit attribute">
                                <x-icons.edit class="h-4 w-4" />
                                <span
                                    class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Edit</span>
                            </a>
                            <form action="{{ route('manager.menu-items.attributes.destroy', [$item, $attribute]) }}"
                                method="POST" class="inline" data-confirm="Delete this attribute?">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="group relative inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition duration-200 hover:-translate-y-0.5 hover:bg-red-100"
                                    aria-label="Delete attribute" title="Delete attribute">
                                    <x-icons.trash class="h-4 w-4" />
                                    <span
                                        class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Delete</span>
                                </button>
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

@endsection