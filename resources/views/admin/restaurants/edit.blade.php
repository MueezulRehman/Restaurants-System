@extends('layouts.admin')

@section('title', 'Edit Restaurant')

@section('content')
<div class="max-w-4xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-hut-dark">Edit Restaurant</h2>
        <p class="text-sm text-gray-500">Update the restaurant profile, plan, and custom domain.</p>
    </div>

    <form action="{{ route('admin.restaurants.update', $restaurant) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PATCH')

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Restaurant Name</label>
                <input type="text" name="name" value="{{ old('name', $restaurant->name) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $restaurant->slug) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
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
                <label class="mb-2 block text-sm font-medium text-gray-700">Custom Domain</label>
                <input type="text" name="custom_domain" value="{{ old('custom_domain', $restaurant->custom_domain) }}" placeholder="restaurant.com" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Restaurant Domain</label>
                <input type="text" name="domain" value="{{ old('domain', $restaurant->domain) }}" placeholder="example.com" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Plan</label>
                <select name="plan" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="basic" {{ old('plan', $restaurant->plan) === 'basic' ? 'selected' : '' }}>Basic</option>
                    <option value="pro" {{ old('plan', $restaurant->plan) === 'pro' ? 'selected' : '' }}>Pro</option>
                    <option value="enterprise" {{ old('plan', $restaurant->plan) === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="trial" {{ old('status', $restaurant->status) === 'trial' ? 'selected' : '' }}>Trial</option>
                    <option value="active" {{ old('status', $restaurant->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ old('status', $restaurant->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="cancelled" {{ old('status', $restaurant->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Address</label>
                <input type="text" name="address" value="{{ old('address', $restaurant->address) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Restaurant Logo</label>
                <input type="file" name="logo_path" accept="image/*" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                @if($restaurant->logo_path)
                    <p class="text-xs text-gray-500 mt-2">Current logo: <span class="font-medium">{{ basename($restaurant->logo_path) }}</span></p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white hover:bg-gray-800">Update Restaurant</button>
            <a href="{{ route('admin.restaurants.index') }}" class="text-sm text-gray-500 hover:text-hut-dark">← Back</a>
        </div>
    </form>
</div>
@endsection
