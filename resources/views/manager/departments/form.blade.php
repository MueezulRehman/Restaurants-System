@php($editing = $department !== null)
<form method="POST" action="{{ $editing ? route('manager.departments.update', $department) : route('manager.departments.store') }}" class="mt-5 space-y-4">
    @csrf
    @if($editing) @method('PUT') @endif
    <div><label class="mb-1 block text-sm font-medium text-gray-700">Name</label><input name="name" value="{{ old('name', $department?->name) }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2"></div>
    <div><label class="mb-1 block text-sm font-medium text-gray-700">Code</label><input name="code" value="{{ old('code', $department?->code) }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2" placeholder="GEN-MED"></div>
    <div><label class="mb-1 block text-sm font-medium text-gray-700">Description</label><textarea name="description" rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2">{{ old('description', $department?->description) }}</textarea></div>
    <label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $department?->is_active ?? true))> Active department</label>
    <div class="flex justify-end gap-3"><a href="{{ route('manager.departments.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm">Cancel</a><button class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Save Department</button></div>
</form>
