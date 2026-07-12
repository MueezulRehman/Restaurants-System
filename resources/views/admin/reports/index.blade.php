@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-hut-dark">Reports</h2>
            <p class="text-sm text-gray-500">View and manage saved reports for your restaurant.</p>
        </div>
        <a href="{{ route('manager.reports.create') }}" class="inline-flex items-center rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">+ Generate Report</a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Name</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Type</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Generated</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Created by</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reports as $report)
                        <tr>
                            <td class="px-4 py-3 font-medium text-hut-dark">{{ $report->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ ucfirst($report->type) }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $report->generated_at->format('M d, Y H:i') }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $report->user?->name ?? 'Unknown' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('manager.reports.show', $report) }}" class="text-hut-yellow hover:text-amber-600">View</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No reports have been generated yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="{{ $reports->hasPages() ? 'pt-2' : '' }}">
        {{ $reports->links() }}
    </div>
</div>
@endsection
