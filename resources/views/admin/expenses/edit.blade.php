@extends('layouts.admin')
@section('title', 'Edit Expense')

@section('content')

<div class="max-w-2xl">
    <a href="{{ route('admin.expenses.index') }}" class="text-hut-green text-sm mb-4 inline-block hover:underline">← Back to Expenses</a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-display font-bold text-hut-dark mb-6">Edit Expense</h2>

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

        <form action="{{ route('admin.expenses.update', $expense) }}" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Category *</label>
                <input type="text" name="category" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('category', $expense->category) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Amount (Rs.) *</label>
                <input type="number" name="amount" step="0.01" min="0" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('amount', $expense->amount) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Date *</label>
                <input type="date" name="date" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('date', $expense->date->toDateString()) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Description *</label>
                <textarea name="description" rows="3" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">{{ old('description', $expense->description) }}</textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="bg-hut-green text-white px-6 py-2 rounded-lg font-medium hover:bg-hut-green/90">Save Changes</button>
                <a href="{{ route('admin.expenses.index') }}" class="border border-gray-200 text-hut-dark px-6 py-2 rounded-lg font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
