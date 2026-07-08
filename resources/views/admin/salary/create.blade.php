@extends('layouts.admin')
@section('title', 'Record Salary')

@section('content')

<div class="max-w-2xl">
    <a href="{{ route('admin.salary.index') }}" class="text-hut-green text-sm mb-4 inline-block hover:underline">← Back to Salary</a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-display font-bold text-hut-dark mb-6">Record Salary Payment</h2>

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

        <form action="{{ route('admin.salary.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Staff Member *</label>
                <select name="user_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
                    <option value="">Select staff member</option>
                    @foreach($staff as $member)
                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Amount (Rs.) *</label>
                <input type="number" name="amount" step="0.01" min="0" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('amount') }}" placeholder="0">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Month *</label>
                <input type="month" name="month" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('month', now()->toDateString()) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Additional notes (optional)">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="bg-hut-green text-white px-6 py-2 rounded-lg font-medium hover:bg-hut-green/90">Record Salary</button>
                <a href="{{ route('admin.salary.index') }}" class="border border-gray-200 text-hut-dark px-6 py-2 rounded-lg font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
