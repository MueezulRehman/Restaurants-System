@extends('layouts.admin')
@section('title', 'Medical Records')

@section('content')
<div class="max-w-4xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Medical Records</h2>
            <p class="text-sm text-gray-500">Track patient and medicine information for medical-store operations.</p>
        </div>
    </div>

    <form action="{{ route('manager.medical-records.store') }}" method="POST" class="space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-4">
        @csrf
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Patient Name</label>
                <input type="text" name="patient_name" class="w-full rounded-lg border border-gray-300 px-3 py-2" required>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Medicine Name</label>
                <input type="text" name="medicine_name" class="w-full rounded-lg border border-gray-300 px-3 py-2" required>
            </div>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Notes</label>
            <textarea name="notes" rows="4" class="w-full rounded-lg border border-gray-300 px-3 py-2"></textarea>
        </div>
        <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white">Save Record</button>
    </form>

    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4">
        <p class="text-sm text-gray-500">No medical records yet. This screen is ready for the medical-store workflow and can be extended with patient history or prescriptions.</p>
    </div>
</div>
@endsection
