@extends('layouts.admin')
@section('title', 'Add Table')

@section('content')
<h1 class="text-lg font-bold mb-4">Add Table</h1>

<form method="POST" action="{{ route('manager.tables.store') }}" class="bg-white rounded-xl p-4 shadow-sm">
    @csrf
    <div class="space-y-3">
        <input name="number" placeholder="Table number" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
        <input name="label" placeholder="Label (optional)" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
        <input name="seats" type="number" min="1" placeholder="Seats" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
        <label class="inline-flex items-center"><input type="checkbox" name="is_active" checked class="mr-2"> Active</label>
        <div>
            <button class="btn-accent">Save</button>
            <a href="{{ route('manager.tables.index') }}" class="ml-3 text-sm text-gray-500">Cancel</a>
        </div>
    </div>
</form>
@endsection
