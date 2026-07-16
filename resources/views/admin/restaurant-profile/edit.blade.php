@extends('layouts.admin')

@section('title', 'Restaurant Profile')

@section('content')
<div class="max-w-4xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-hut-dark">My Restaurant</h2>
        <p class="text-sm text-gray-500">Update your restaurant details and logo.</p>
    </div>

    <form action="{{ route('manager.restaurant.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PATCH')

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Restaurant Name</label>
                <input type="text" name="name" value="{{ old('name', $restaurant->name) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $restaurant->email) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $restaurant->phone) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Address</label>
                <input type="text" name="address" value="{{ old('address', $restaurant->address) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700">Restaurant Logo</label>
                <input type="file" name="logo_path" accept="image/*" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                <p class="text-xs text-gray-500 mt-2">Tip: upload a square PNG/JPG (recommended 512×512). The file will be stored in <code>storage/app/public/restaurant-logos</code>.</p>
                @if($restaurant->logo_path)
                    <p class="text-xs text-gray-500 mt-2">Current logo: {{ basename($restaurant->logo_path) }}</p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white hover:bg-gray-800">Save</button>
            <a href="{{ route('manager.dashboard') }}" class="text-sm text-gray-500 hover:text-hut-dark">← Back</a>
        </div>
    </form>
</div>
@endsection
