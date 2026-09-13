@if ($errors->any())<div class="mb-5 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ $patient ? route('manager.patients.update', $patient) : route('manager.patients.store') }}" class="space-y-5">
    @csrf @if($patient) @method('PUT') @endif
    <div class="grid gap-4 md:grid-cols-2">
        <label class="text-sm font-medium text-hut-dark">Patient ID *<input name="patient_number" required value="{{ old('patient_number', $patient?->patient_number) }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"></label>
        <label class="text-sm font-medium text-hut-dark">Name *<input name="name" required value="{{ old('name', $patient?->name) }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"></label>
        <label class="text-sm font-medium text-hut-dark">CNIC<input name="cnic" value="{{ old('cnic', $patient?->cnic) }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"></label>
        <label class="text-sm font-medium text-hut-dark">Phone<input name="phone" value="{{ old('phone', $patient?->phone) }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"></label>
        <label class="text-sm font-medium text-hut-dark">Email<input type="email" name="email" value="{{ old('email', $patient?->email) }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"></label>
        <label class="text-sm font-medium text-hut-dark">Date of birth<input type="date" name="date_of_birth" value="{{ old('date_of_birth', $patient?->date_of_birth?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"></label>
        <label class="text-sm font-medium text-hut-dark">Gender<select name="gender" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"><option value="">Select gender</option>@foreach(['male', 'female', 'other'] as $gender)<option value="{{ $gender }}" @selected(old('gender', $patient?->gender) === $gender)>{{ ucfirst($gender) }}</option>@endforeach</select></label>
        <label class="text-sm font-medium text-hut-dark md:col-span-2">Address<textarea name="address" rows="2" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2">{{ old('address', $patient?->address) }}</textarea></label>
        <div class="md:col-span-2 rounded-lg border border-gray-200 bg-gray-50 p-4">
            <label class="flex items-center gap-2 text-sm font-medium text-hut-dark"><input type="checkbox" name="is_dependent" value="1" @checked(old('is_dependent', $patient?->is_dependent)) data-dependent-toggle> Who is this visit for? A dependent</label>
            <p class="mt-1 text-xs text-gray-500">Leave unchecked when registering the person themselves.</p>
            <div class="mt-4 grid gap-4 md:grid-cols-2 {{ old('is_dependent', $patient?->is_dependent) ? '' : 'hidden' }}" data-dependent-fields>
                <label class="text-sm font-medium text-hut-dark">Guardian name *<input name="guardian_name" value="{{ old('guardian_name', $patient?->guardian_name) }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"></label>
                <label class="text-sm font-medium text-hut-dark">Guardian CNIC *<input name="guardian_cnic" value="{{ old('guardian_cnic', $patient?->guardian_cnic) }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"></label>
                <label class="text-sm font-medium text-hut-dark">Guardian phone *<input name="guardian_phone" value="{{ old('guardian_phone', $patient?->guardian_phone) }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"></label>
                <label class="text-sm font-medium text-hut-dark">Relationship *<select name="relationship" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"><option value="">Select relationship</option>@foreach(['self', 'father', 'mother', 'son', 'daughter', 'spouse', 'brother', 'sister', 'guardian', 'other'] as $relationship)<option value="{{ $relationship }}" @selected(old('relationship', $patient?->relationship) === $relationship)>{{ ucfirst($relationship) }}</option>@endforeach</select></label>
            </div>
        </div>
        <p class="text-xs text-amber-700 md:col-span-2 {{ old('is_dependent', $patient?->is_dependent) ? 'hidden' : '' }}" data-cnic-warning>CNIC is optional, but recommended for adult patients to prevent duplicate records.</p>
    </div>
    <button class="rounded-lg bg-hut-green px-4 py-2 text-sm font-semibold text-white">Save Patient</button>
</form>
@push('scripts')
<script>
    document.querySelectorAll('[data-dependent-toggle]').forEach((toggle) => {
        const form = toggle.closest('form');
        const fields = form.querySelector('[data-dependent-fields]');
        const warning = form.querySelector('[data-cnic-warning]');
        const sync = () => {
            fields.classList.toggle('hidden', !toggle.checked);
            warning.classList.toggle('hidden', toggle.checked);
        };
        toggle.addEventListener('change', sync);
        sync();
    });
</script>
@endpush
