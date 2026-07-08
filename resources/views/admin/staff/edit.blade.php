@extends('layouts.admin')
@section('title', 'Edit Staff Member')

@section('content')

<div class="max-w-2xl">
    <a href="{{ route('admin.staff.index') }}" class="text-hut-green text-sm mb-4 inline-block hover:underline">← Back to Staff</a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-display font-bold text-hut-dark mb-6">Edit Staff Member</h2>

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

        <form action="{{ route('admin.staff.update', $staff) }}" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Full Name *</label>
                <input type="text" name="name" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('name', $staff->name) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Email *</label>
                <input type="email" name="email" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('email', $staff->email) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Phone</label>
                <input type="tel" name="phone" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('phone', $staff->phone) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Role *</label>
                <select name="role" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
                    <option value="staff" {{ old('role', $staff->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="manager" {{ old('role', $staff->role) === 'manager' ? 'selected' : '' }}>Manager</option>
                </select>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="bg-hut-green text-white px-6 py-2 rounded-lg font-medium hover:bg-hut-green/90">Save Changes</button>
                <a href="{{ route('admin.staff.index') }}" class="border border-gray-200 text-hut-dark px-6 py-2 rounded-lg font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
