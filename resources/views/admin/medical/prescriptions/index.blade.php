@extends('layouts.admin')

@section('title', 'Prescriptions')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Prescriptions</h2>
            <p class="text-sm text-gray-500">Manage and track patient prescriptions</p>
        </div>
        <a href="{{ route('manager.prescriptions.create') }}" class="px-4 py-2 bg-hut-green text-white rounded-lg hover:bg-hut-green/90 font-medium">
            ➕ Add Prescription
        </a>
    </div>

    @if($prescriptions->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
            <p class="text-gray-500 mb-4">No prescriptions added yet.</p>
            <a href="{{ route('manager.prescriptions.create') }}" class="text-hut-green hover:underline font-medium">
                Add your first prescription
            </a>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Prescription #</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Patient Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Doctor</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($prescriptions as $prescription)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-hut-dark">{{ $prescription->prescription_number }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $prescription->patient_name }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $prescription->doctor_name ?? '—' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $prescription->prescription_date->format('M d, Y') }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-block px-2 py-1 rounded text-xs font-medium
                                    @if($prescription->status === 'verified') bg-green-100 text-green-800
                                    @elseif($prescription->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($prescription->status === 'used') bg-blue-100 text-blue-800
                                    @elseif($prescription->status === 'expired') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-600 @endif">
                                    {{ ucfirst($prescription->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('manager.prescriptions.show', $prescription) }}" class="text-hut-green hover:text-hut-green/80">👁️ View</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $prescriptions->links() }}
        </div>
    @endif
</div>
@endsection
