@if ($errors->any())<div class="mb-5 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ $doctor ? route('manager.doctors.update', $doctor) : route('manager.doctors.store') }}" class="space-y-5">
    @csrf @if($doctor) @method('PUT') @endif
    <div class="grid gap-4 md:grid-cols-2">
        <label class="text-sm font-medium text-hut-dark">Name *<input name="name" required value="{{ old('name', $doctor?->name) }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"></label>
        <label class="text-sm font-medium text-hut-dark">Specialty *<input name="specialty" required value="{{ old('specialty', $doctor?->specialty) }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"></label>
        <label class="text-sm font-medium text-hut-dark">Phone<input name="phone" value="{{ old('phone', $doctor?->phone) }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"></label>
        <label class="text-sm font-medium text-hut-dark">Email<input type="email" name="email" value="{{ old('email', $doctor?->email) }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"></label>
        <label class="text-sm font-medium text-hut-dark md:col-span-2">Address<textarea name="address" rows="2" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2">{{ old('address', $doctor?->address) }}</textarea></label>
        <label class="text-sm font-medium text-hut-dark">Status *<select name="status" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2">@foreach(['pending', 'active', 'declined'] as $status)<option value="{{ $status }}" @selected(old('status', $doctor?->status ?: 'pending') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
    </div>
    <button class="rounded-lg bg-hut-green px-4 py-2 text-sm font-semibold text-white">Save Doctor</button>
</form>
