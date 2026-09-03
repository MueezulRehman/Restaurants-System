@extends('layouts.admin')
@section('title', 'Attendance')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-display font-bold text-hut-dark">Attendance Records</h2>
        <a href="{{ route('manager.attendance.create') }}"
            class="bg-hut-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-hut-green/90">+ Record
            Attendance</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Staff Member</th>
                    <th class="px-4 py-3 text-left">Check In</th>
                    <th class="px-4 py-3 text-left">Check Out</th>
                    <th class="px-4 py-3 text-left">Hours</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($attendance as $record)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-600">{{ $record->created_at->format('d M, Y') }}</td>
                        <td class="px-4 py-3 font-medium text-hut-dark">{{ $record->user->name }}</td>
                        <td class="px-4 py-3">{{ $record->check_in }}</td>
                        <td class="px-4 py-3">{{ $record->check_out ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            @if($record->check_out)
                                {{ \Carbon\Carbon::parse($record->check_in)->diff(\Carbon\Carbon::parse($record->check_out))->format('%h:%I') }}
                                hrs
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-2 flex justify-end">
                            <a href="{{ route('manager.attendance.edit', $record) }}"
                                class="text-hut-green hover:underline text-xs font-medium">Edit</a>
                            <form action="{{ route('manager.attendance.destroy', $record) }}" method="POST" class="inline"
                                data-confirm="Delete this record?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-xs font-medium">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No attendance records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $attendance->links() }}
    </div>

@endsection