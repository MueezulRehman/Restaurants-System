@extends('layouts.admin')

@section('title', 'Prescription - ' . $prescription->prescription_number)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">{{ $prescription->prescription_number }}</h2>
            <p class="text-sm text-gray-500">Patient: {{ $prescription->patient_name }}</p>
        </div>
        <a href="{{ route('manager.prescriptions.index') }}" class="text-gray-600 hover:text-gray-800">← Back</a>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-hut-dark mb-4">Prescription Details</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-gray-600">Patient Name</p>
                    <p class="font-medium text-hut-dark">{{ $prescription->patient_name }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Doctor Name</p>
                    <p class="font-medium text-hut-dark">{{ $prescription->doctor_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Prescription Date</p>
                    <p class="font-medium text-hut-dark">{{ $prescription->prescription_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Valid Until</p>
                    <p class="font-medium {{ $prescription->isExpired() ? 'text-red-600' : 'text-hut-dark' }}">
                        {{ $prescription->valid_until?->format('M d, Y') ?? '—' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-hut-dark mb-4">Status</h3>
            <div class="mb-4">
                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium
                    @if($prescription->status === 'verified') bg-green-100 text-green-800
                    @elseif($prescription->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($prescription->status === 'used') bg-blue-100 text-blue-800
                    @elseif($prescription->status === 'expired') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-600 @endif">
                    {{ ucfirst($prescription->status) }}
                </span>
            </div>
            
            @if($prescription->verification_notes)
                <div class="border-t border-gray-200 pt-4">
                    <p class="text-gray-600 text-xs mb-2">Verification Notes</p>
                    <p class="text-sm text-gray-700">{{ $prescription->verification_notes }}</p>
                </div>
            @endif
        </div>
    </div>

    @if($prescription->image_path)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-hut-dark mb-4">Prescription Image</h3>
            <img src="{{ asset('storage/' . $prescription->image_path) }}" alt="Prescription" class="max-w-full h-auto rounded-lg border border-gray-200">
        </div>
    @endif

    <div class="flex gap-3">
        <a href="{{ route('manager.prescriptions.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
            Back to Prescriptions
        </a>
    </div>
</div>
@endsection
