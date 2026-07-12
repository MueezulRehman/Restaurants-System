@extends('layouts.admin')

@section('title', $prescription->id ? 'Edit Prescription' : 'Add Prescription')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h2 class="text-2xl font-semibold text-hut-dark">{{ $prescription->id ? 'Edit Prescription' : 'Add New Prescription' }}</h2>
        <p class="text-sm text-gray-500">Upload and verify patient prescriptions</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ $prescription->id ? route('manager.prescriptions.update', $prescription) : route('manager.prescriptions.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prescription Number *</label>
                    <input type="text" name="prescription_number" value="{{ old('prescription_number', $prescription->prescription_number) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent @error('prescription_number') border-red-500 @enderror">
                    @error('prescription_number') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Patient Name *</label>
                    <input type="text" name="patient_name" value="{{ old('patient_name', $prescription->patient_name) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent @error('patient_name') border-red-500 @enderror">
                    @error('patient_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Doctor Name</label>
                    <input type="text" name="doctor_name" value="{{ old('doctor_name', $prescription->doctor_name) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                    <select name="customer_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                        <option value="">Select a customer...</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id', $prescription->customer_id) == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prescription Date *</label>
                    <input type="date" name="prescription_date" value="{{ old('prescription_date', $prescription->prescription_date) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent @error('prescription_date') border-red-500 @enderror">
                    @error('prescription_date') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valid Until</label>
                    <input type="date" name="valid_until" value="{{ old('valid_until', $prescription->valid_until) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select name="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                        <option value="pending" {{ old('status', $prescription->status) === 'pending' ? 'selected' : '' }}>Pending Verification</option>
                        <option value="verified" {{ old('status', $prescription->status) === 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="used" {{ old('status', $prescription->status) === 'used' ? 'selected' : '' }}>Used</option>
                        <option value="expired" {{ old('status', $prescription->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="rejected" {{ old('status', $prescription->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    @error('status') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prescription Image</label>
                    <input type="file" name="image_path" accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Max 2MB. Upload prescription photo for verification.</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Verification Notes</label>
                <textarea name="verification_notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent" placeholder="Notes on verification, issues found, etc.">{{ old('verification_notes', $prescription->verification_notes) }}</textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="px-6 py-2 bg-hut-green text-white rounded-lg hover:bg-hut-green/90 font-medium">
                    {{ $prescription->id ? 'Update Prescription' : 'Create Prescription' }}
                </button>
                <a href="{{ route('manager.prescriptions.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
