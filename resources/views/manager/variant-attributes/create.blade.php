@extends('manager.layout.master')
@section('title', 'Add Attribute')

@section('page-content')

    <div class="max-w-2xl">
        <a href="{{ route('manager.menu-items.attributes.index', $item) }}"
            class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white/75 text-hut-blue shadow-sm backdrop-blur transition hover:bg-white hover:text-hut-dark"
            aria-label="Back to attributes" title="Back to attributes">
            <i class="fas fa-arrow-left text-sm" aria-hidden="true"></i>
        </a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-display font-bold text-hut-dark mb-6">Add Attribute for {{ $item->name }}</h2>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                    <p class="font-medium mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('manager.menu-items.attributes.store', $item) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Attribute Name *</label>
                    <input type="text" name="name" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        value="{{ old('name') }}" placeholder="e.g. Size">
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" aria-label="Create attribute" title="Create attribute"
                        class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hut-green text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-hut-green/90">
                        <i class="fas fa-check text-sm" aria-hidden="true"></i>
                        <span
                            class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Create
                            attribute</span>
                    </button>
                    <a href="{{ route('manager.menu-items.attributes.index', $item) }}" aria-label="Cancel" title="Cancel"
                        class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white/80 text-slate-600 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-slate-50">
                        <i class="fas fa-xmark text-sm" aria-hidden="true"></i>
                        <span
                            class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection