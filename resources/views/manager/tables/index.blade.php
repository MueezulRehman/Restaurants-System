@extends('manager.layout.master')
@section('title', 'Tables')

@section('page-content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-lg font-bold">Tables</h1>
    <a href="{{ route('manager.tables.create') }}" class="btn-primary">Add Table</a>
</div>

<div class="bg-white rounded-xl shadow-sm p-4">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-xs text-gray-500">
                <th>Number / Label</th>
                <th>Seats</th>
                <th>Active</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($tables as $table)
                <tr class="border-t">
                    <td class="py-2">{{ $table->number ?? $table->label }}</td>
                    <td class="py-2">{{ $table->seats }}</td>
                    <td class="py-2">{{ $table->is_active ? 'Yes' : 'No' }}</td>
                    <td class="py-2 text-right">
                        <a href="{{ route('manager.tables.edit', $table) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-hut-blue hover:bg-blue-50" aria-label="Edit table" title="Edit table"><x-icons.edit class="h-4 w-4" /></a>
                        <form action="{{ route('manager.tables.destroy', $table) }}" method="POST" class="inline-block ml-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-hut-red hover:bg-red-50" aria-label="Delete table" title="Delete table"><x-icons.trash class="h-4 w-4" /></button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
