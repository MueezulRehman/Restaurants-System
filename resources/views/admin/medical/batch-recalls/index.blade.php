@extends('layouts.admin')

@section('title', 'Batch Recalls')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Batch Recalls</h2>
            <p class="text-sm text-gray-500">Track and manage batch recalls and quality issues</p>
        </div>
        <a href="{{ route('manager.batch-recalls.create') }}" class="px-4 py-2 bg-hut-green text-white rounded-lg hover:bg-hut-green/90 font-medium">
            ⚠️ Issue Recall
        </a>
    </div>

    @if($recalls->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
            <p class="text-gray-500 mb-4">No batch recalls issued yet.</p>
            <p class="text-sm text-gray-600 mb-4">Good news! All batches are in good standing.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Recall #</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Medicine / Batch</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Qty Recalled</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Issued By</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recalls as $recall)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-hut-dark">{{ $recall->recall_number }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">
                                {{ $recall->medicine->name }}<br>
                                <span class="text-xs text-gray-500">Batch: {{ $recall->batch->batch_number ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-3 text-sm">
                                <span class="inline-block px-2 py-1 rounded text-xs font-medium
                                    @if($recall->reason === 'expiry') bg-red-100 text-red-800
                                    @elseif($recall->reason === 'quality') bg-yellow-100 text-yellow-800
                                    @else bg-orange-100 text-orange-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $recall->reason)) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $recall->quantity_recalled }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-block px-2 py-1 rounded text-xs font-medium
                                    @if($recall->status === 'completed') bg-green-100 text-green-800
                                    @elseif($recall->status === 'in_progress') bg-blue-100 text-blue-800
                                    @elseif($recall->status === 'issued') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-600 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $recall->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $recall->issuedBy->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('manager.batch-recalls.show', $recall) }}" class="text-hut-green hover:text-hut-green/80">👁️ View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $recalls->links() }}
        </div>
    @endif
</div>
@endsection
