@extends('layouts.admin')

@section('title', 'Batch Recall - ' . $recall->recall_number)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">{{ $recall->recall_number }}</h2>
            <p class="text-sm text-gray-500">{{ $recall->medicine->name }} / {{ $recall->batch->batch_number }}</p>
        </div>
        <a href="{{ route('manager.batch-recalls.index') }}" class="text-gray-600 hover:text-gray-800">← Back</a>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-hut-dark mb-4">Recall Information</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-gray-600">Medicine</p>
                    <p class="font-medium text-hut-dark">{{ $recall->medicine->name }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Batch Number</p>
                    <p class="font-medium text-hut-dark">{{ $recall->batch->batch_number }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Reason</p>
                    <p class="font-medium text-hut-dark">{{ ucfirst(str_replace('_', ' ', $recall->reason)) }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Recall Date</p>
                    <p class="font-medium text-hut-dark">{{ $recall->recall_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Quantity Recalled</p>
                    <p class="font-medium text-hut-dark">{{ $recall->quantity_recalled }} units</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-hut-dark mb-4">Status</h3>
            <span class="inline-block px-3 py-1 rounded-full text-sm font-medium mb-4
                @if($recall->status === 'completed') bg-green-100 text-green-800
                @elseif($recall->status === 'in_progress') bg-blue-100 text-blue-800
                @elseif($recall->status === 'issued') bg-yellow-100 text-yellow-800
                @else bg-gray-100 text-gray-600 @endif">
                {{ ucfirst(str_replace('_', ' ', $recall->status)) }}
            </span>
            
            <div class="border-t border-gray-200 pt-4">
                <p class="text-gray-600 text-xs mb-2">Issued By</p>
                <p class="text-sm font-medium text-hut-dark">{{ $recall->issuedBy->name }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <h3 class="font-semibold text-hut-dark mb-4">Description</h3>
        <p class="text-sm text-gray-700">{{ $recall->description }}</p>
    </div>

    @if($recall->action_taken)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-hut-dark mb-4">Action Taken</h3>
            <p class="text-sm text-gray-700">{{ $recall->action_taken }}</p>
        </div>
    @endif

    <div class="flex gap-3">
        <a href="{{ route('manager.batch-recalls.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
            Back to Recalls
        </a>
    </div>
</div>
@endsection
