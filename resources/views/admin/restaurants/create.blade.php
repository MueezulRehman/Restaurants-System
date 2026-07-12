@extends('layouts.admin')

@section('title', 'Register Restaurant / Business')

@section('content')
<div class="max-w-4xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-hut-dark">Register a New Restaurant / Business</h2>
        <p class="text-sm text-gray-500">Create a restaurant or business account, assign a custom domain, and generate the initial owner credentials.</p>
    </div>

    <form action="{{ route('admin.restaurants.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Restaurant Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" name="slug" value="{{ old('slug') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Business Type</label>
                <select name="business_type_id" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    @foreach($businessTypes as $businessType)
                        <option value="{{ $businessType->id }}" {{ old('business_type_id') == $businessType->id ? 'selected' : '' }}>{{ $businessType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700">Modules</label>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach($modules as $module)
                        <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2">
                            <input type="checkbox" name="enabled_modules[]" value="{{ $module->id }}" {{ in_array($module->id, old('enabled_modules', $selectedModules)) ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-hut-dark" />
                            <span class="text-sm text-gray-700">{{ $module->name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-2">Select enabled features for the restaurant. Defaults are selected from the chosen business type.</p>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Custom Domain</label>
                <input type="text" name="custom_domain" value="{{ old('custom_domain') }}" placeholder="restaurant.com" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Restaurant Domain</label>
                <input type="text" name="domain" value="{{ old('domain') }}" placeholder="example.com" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Plan</label>
                <select name="plan" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    @forelse($subscriptionPlans as $plan)
                        <option value="{{ $plan->slug }}" {{ old('plan', $selectedPlanSlug) === $plan->slug ? 'selected' : '' }}>{{ $plan->name }}</option>
                    @empty
                        <option value="" selected>No subscription plans available yet</option>
                    @endforelse
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="trial">Trial</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Address</label>
                <input type="text" name="address" value="{{ old('address') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Restaurant Logo</label>
                <input type="file" name="logo_path" accept="image/*" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <h3 class="mb-4 text-lg font-semibold text-hut-dark">Restaurant Owner Account</h3>
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Owner Name</label>
                    <input type="text" name="owner_name" value="{{ old('owner_name') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Owner Email</label>
                    <input type="email" name="owner_email" value="{{ old('owner_email') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Owner Phone</label>
                    <input type="text" name="owner_phone" value="{{ old('owner_phone') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Owner Password</label>
                    <input type="text" name="owner_password" value="{{ old('owner_password') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white hover:bg-gray-800">Create Restaurant / Business</button>
            <a href="{{ route('admin.restaurants.index') }}" class="text-sm text-gray-500 hover:text-hut-dark">← Back</a>
        </div>
    </form>
</div>
@endsection
