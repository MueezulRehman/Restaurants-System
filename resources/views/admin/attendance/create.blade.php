@extends('layouts.admin')
@section('title', 'Record Attendance')

@section('content')

<div class="max-w-2xl">
    <a href="{{ route('manager.attendance.index') }}" class="text-hut-green text-sm mb-4 inline-block hover:underline">← Back to Attendance</a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-display font-bold text-hut-dark mb-6">Record Attendance</h2>

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

        <form action="{{ route('manager.attendance.store') }}" method="POST" class="space-y-4">
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

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Check In Time *</label>
                    <input type="time" name="check_in" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Check Out Time</label>
                    <input type="time" name="check_out" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Optional notes">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="bg-hut-green text-white px-6 py-2 rounded-lg font-medium hover:bg-hut-green/90">Record</button>
                <a href="{{ route('manager.attendance.index') }}" class="border border-gray-200 text-hut-dark px-6 py-2 rounded-lg font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
