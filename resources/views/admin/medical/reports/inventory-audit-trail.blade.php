@extends('layouts.admin')

@section('title', 'Inventory Audit Trail')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('manager.medical-reports.index') }}" class="text-gray-600 hover:text-gray-800">← Back</a>
            <h1 class="text-3xl font-bold mt-2">Inventory Audit Trail</h1>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Action</th>
                    <th class="px-4 py-3 text-left">Item</th>
                    <th class="px-4 py-3 text-left">User</th>
                    <th class="px-4 py-3 text-left">Reason</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditLogs as $log)
                    <tr class="border-b">
                        <td class="px-4 py-3">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3">{{ ucfirst($log->action) }}</td>
                        <td class="px-4 py-3">{{ $log->item_type }} #{{ $log->item_id }}</td>
                        <td class="px-4 py-3">{{ $log->user->name ?? 'System' }}</td>
                        <td class="px-4 py-3">{{ $log->reason ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-center text-gray-500">No audit history available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
