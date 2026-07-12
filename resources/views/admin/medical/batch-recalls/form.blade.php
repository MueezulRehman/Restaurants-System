@extends('layouts.admin')

@section('title', $recall->id ? 'Edit Recall' : 'Issue Batch Recall')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h2 class="text-2xl font-semibold text-hut-dark">{{ $recall->id ? 'Edit Batch Recall' : 'Issue New Batch Recall' }}</h2>
        <p class="text-sm text-gray-500">Track batch quality issues and recalls</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ $recall->id ? route('manager.batch-recalls.update', $recall) : route('manager.batch-recalls.store') }}" class="space-y-4">
            @csrf
            @if($recall->id) @method('PUT') @endif

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recall Number *</label>
                    <input type="text" name="recall_number" value="{{ old('recall_number', $recall->recall_number) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent @error('recall_number') border-red-500 @enderror">
                    @error('recall_number') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Medicine *</label>
                    <select name="medicine_id" id="medicine-select" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent @error('medicine_id') border-red-500 @enderror">
                        <option value="">Select a medicine...</option>
                        @foreach($medicines as $medicine)
                            <option value="{{ $medicine->id }}" {{ old('medicine_id', $recall->medicine_id) == $medicine->id ? 'selected' : '' }}>
                                {{ $medicine->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('medicine_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Batch *</label>
                    <select name="medicine_batch_id" id="batch-select" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent @error('medicine_batch_id') border-red-500 @enderror">
                        <option value="">Select a batch...</option>
                    </select>
                    @error('medicine_batch_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                    <select name="reason" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                        <option value="expiry" {{ old('reason', $recall->reason) === 'expiry' ? 'selected' : '' }}>Expiry</option>
                        <option value="quality" {{ old('reason', $recall->reason) === 'quality' ? 'selected' : '' }}>Quality Issue</option>
                        <option value="contamination" {{ old('reason', $recall->reason) === 'contamination' ? 'selected' : '' }}>Contamination</option>
                        <option value="regulatory" {{ old('reason', $recall->reason) === 'regulatory' ? 'selected' : '' }}>Regulatory Issue</option>
                        <option value="damage" {{ old('reason', $recall->reason) === 'damage' ? 'selected' : '' }}>Damage</option>
                        <option value="other" {{ old('reason', $recall->reason) === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recall Date *</label>
                    <input type="date" name="recall_date" value="{{ old('recall_date', $recall->recall_date) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Recalled *</label>
                    <input type="number" name="quantity_recalled" value="{{ old('quantity_recalled', $recall->quantity_recalled) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select name="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                        <option value="issued" {{ old('status', $recall->status) === 'issued' ? 'selected' : '' }}>Issued</option>
                        <option value="in_progress" {{ old('status', $recall->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ old('status', $recall->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $recall->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                <textarea name="description" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent" placeholder="Details about the recall reason...">{{ old('description', $recall->description) }}</textarea>
                @error('description') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Action Taken</label>
                <textarea name="action_taken" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent" placeholder="Steps taken to resolve this recall...">{{ old('action_taken', $recall->action_taken) }}</textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="px-6 py-2 bg-hut-green text-white rounded-lg hover:bg-hut-green/90 font-medium">
                    {{ $recall->id ? 'Update Recall' : 'Issue Recall' }}
                </button>
                <a href="{{ route('manager.batch-recalls.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // Populate batch dropdown when medicine is selected
    document.getElementById('medicine-select').addEventListener('change', function() {
        const medicineId = this.value;
        const batchSelect = document.getElementById('batch-select');
        batchSelect.innerHTML = '<option value="">Select a batch...</option>';
        
        if (medicineId) {
            // Fetch batches for this medicine (in production, use AJAX)
            const medicinesData = {!! json_encode($medicines->keyBy('id')->map(fn($m) => $m->batches)->toArray()) !!};
            if (medicinesData[medicineId]) {
                medicinesData[medicineId].forEach(batch => {
                    const option = document.createElement('option');
                    option.value = batch.id;
                    option.textContent = `${batch.batch_number} - Qty: ${batch.quantity}`;
                    batchSelect.appendChild(option);
                });
            }
        }
    });

    // Trigger on page load if medicine is pre-selected
    document.getElementById('medicine-select').dispatchEvent(new Event('change'));
</script>
@endsection
