@extends('manager.layout.master')
@section('title', 'Edit Deal')

@section('page-content')

    <div class="max-w-2xl">
        <a href="{{ route('manager.deals.index') }}"
            class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white/75 text-hut-blue shadow-sm backdrop-blur transition hover:-translate-y-0.5 hover:bg-white hover:text-hut-dark"
            aria-label="Back to deals" title="Back to deals">
            <i class="fas fa-arrow-left text-sm" aria-hidden="true"></i>
        </a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-display font-bold text-hut-dark mb-6">Edit Deal</h2>

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

            <form action="{{ route('manager.deals.update', $deal) }}" method="POST" enctype="multipart/form-data"
                class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Deal Name *</label>
                    <input type="text" name="name" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        value="{{ old('name', $deal->name) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Price (Rs.) *</label>
                    <input type="number" name="price" step="0.01" min="0" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        value="{{ old('price', $deal->price) }}">
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Start Date</label>
                        <input type="date" name="start_date"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                            value="{{ old('start_date', optional($deal->start_date)->format('Y-m-d')) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">End Date</label>
                        <input type="date" name="end_date"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                            value="{{ old('end_date', optional($deal->end_date)->format('Y-m-d')) }}">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">{{ old('description', $deal->description) }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="active" id="active" value="1" {{ old('active', $deal->is_active) ? 'checked' : '' }} class="rounded">
                    <label for="active" class="text-sm text-hut-dark">Active (show on menu)</label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Image (optional)</label>
                    @php
                        $preview = null;
                        if ($deal->image) {
                            if (file_exists(public_path('images/' . $deal->image))) {
                                $preview = asset('images/' . $deal->image);
                            } elseif (file_exists(public_path($deal->image))) {
                                $preview = asset($deal->image);
                            } else {
                                $preview = asset('storage/' . $deal->image);
                            }
                        }
                    @endphp
                    @if($preview)
                        <div class="mb-2">
                            <img src="{{ $preview }}" alt="{{ $deal->name }}" class="h-24 rounded-lg object-cover">
                        </div>
                    @endif
                    <input type="file" name="image" accept="image/*" class="w-full">
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" aria-label="Save deal" title="Save deal"
                        class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hut-green text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-hut-green/90">
                        <i class="fas fa-floppy-disk text-sm" aria-hidden="true"></i>
                        <span
                            class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Save
                            deal</span>
                    </button>
                    <a href="{{ route('manager.deals.index') }}" aria-label="Cancel" title="Cancel"
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