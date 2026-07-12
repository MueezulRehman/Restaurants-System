@extends('layouts.admin')

@php($errors = $errors ?? new \Illuminate\Support\ViewErrorBag())

@section('title', 'Generate Report')

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-hut-dark">Generate a new report</h2>
        <p class="text-sm text-gray-500">Choose report type and date range to run a new analysis.</p>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('manager.reports.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700">Report Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Report Type</label>
                @if(empty($types))
                    <p class="mt-2 text-sm text-gray-500">You do not currently have access to any report modules.</p>
                @else
                    <select name="type" class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                        <option value="">Select a type</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" @selected(old('type') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                @endif
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">From</label>
                    <input type="date" name="date_from" value="{{ old('date_from') }}" class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @error('date_from')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">To</label>
                    <input type="date" name="date_to" value="{{ old('date_to') }}" class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @error('date_to')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center rounded-lg bg-hut-dark px-5 py-2 text-sm font-semibold text-white hover:bg-gray-800">Generate Report</button>
                <a href="{{ route('manager.reports.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to reports</a>
            </div>
        </form>
    </div>
</div>
@endsection
