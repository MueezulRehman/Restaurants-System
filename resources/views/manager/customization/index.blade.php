@extends('manager.layout.master')
@section('title', 'Custom Fields and Workflows')
@section('page-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-hut-dark">Custom fields and workflows</h1>
            <p class="text-sm text-gray-500">Configure business-specific data and operational status flows.</p>
        </div>@if(session('success'))
        <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif<div
            class="grid gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('manager.customization.fields.store') }}"
                class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">@csrf<h2 class="mb-3 font-semibold">New
                    custom field</h2><input name="entity_type" required placeholder="Entity type, e.g. customer"
                    class="mb-3 w-full rounded-lg border-gray-300"><input name="name" required placeholder="Field label"
                    class="mb-3 w-full rounded-lg border-gray-300"><input name="field_key" placeholder="Key (optional)"
                    class="mb-3 w-full rounded-lg border-gray-300"><select name="field_type"
                    class="mb-3 w-full rounded-lg border-gray-300">
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                    <option value="select">Select</option>
                    <option value="boolean">Yes / No</option>
                </select><input name="options" placeholder="Options, comma separated"
                    class="mb-3 w-full rounded-lg border-gray-300"><label class="mb-3 flex gap-2 text-sm"><input
                        type="checkbox" name="is_required" value="1"> Required</label><button
                    class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Create field</button></form>
            <form method="POST" action="{{ route('manager.customization.workflows.store') }}"
                class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">@csrf<h2 class="mb-3 font-semibold">New
                    workflow</h2><input name="entity_type" required placeholder="Entity type, e.g. repair"
                    class="mb-3 w-full rounded-lg border-gray-300"><input name="name" required placeholder="Workflow name"
                    class="mb-3 w-full rounded-lg border-gray-300"><input name="statuses" required
                    placeholder="Statuses, comma separated" class="mb-3 w-full rounded-lg border-gray-300"><textarea
                    name="transitions" placeholder='Optional JSON transitions' rows="4"
                    class="mb-3 w-full rounded-lg border-gray-300"></textarea><button
                    class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Create workflow</button>
            </form>
        </div>
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="mb-3 font-semibold">Fields</h2>@forelse($fields as $field)
                    <div class="border-b border-gray-100 py-2 text-sm"><strong>{{ $field->name }}</strong> <span
                class="text-gray-500">({{ $field->entity_type }} · {{ $field->field_type }})</span></div>@empty<p
                        class="text-sm text-gray-500">No custom fields.</p>@endforelse
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="mb-3 font-semibold">Workflows</h2>@forelse($workflows as $workflow)
                    <div class="border-b border-gray-100 py-2 text-sm"><strong>{{ $workflow->name }}</strong> <span
                            class="text-gray-500">({{ $workflow->entity_type }})</span>
                        <div class="text-xs text-gray-500">{{ implode(' → ', $workflow->statuses ?: []) }}</div>
                </div>@empty<p class="text-sm text-gray-500">No custom workflows.</p>@endforelse
            </div>
        </div>
</div>@endsection