@extends('manager.layout.master')
@section('title', 'Branches and Locations')
@section('page-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Branches and locations</h1>
            <p class="mt-1 text-sm text-gray-500">Create the locations used for stock transfers and branch reporting.</p>
        </div>
        @if(session('success'))
        <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif
        <form method="POST" action="{{ route('manager.branches.store') }}"
            class="grid gap-3 rounded-xl border border-gray-100 bg-white p-5 shadow-sm md:grid-cols-5">@csrf<input
                name="name" required placeholder="Branch name" class="rounded-lg border-gray-200 px-3 py-2 text-sm"><input
                name="code" required placeholder="Code" class="rounded-lg border-gray-200 px-3 py-2 text-sm"><input
                name="address" placeholder="Address" class="rounded-lg border-gray-200 px-3 py-2 text-sm"><input
                name="phone" placeholder="Phone" class="rounded-lg border-gray-200 px-3 py-2 text-sm"><button
                class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Add branch</button></form>
        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Address</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Update</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">@forelse($branches as $branch)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $branch->name }}</td>
                        <td class="px-4 py-3">{{ $branch->code }}</td>
                        <td class="px-4 py-3">{{ $branch->address ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $branch->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('manager.branches.update', $branch) }}"
                                class="flex flex-wrap gap-2">@csrf @method('PATCH')<input name="name"
                                    value="{{ $branch->name }}" required
                                    class="w-32 rounded border-gray-200 px-2 py-1 text-xs"><input name="code"
                                    value="{{ $branch->code }}" required
                                    class="w-20 rounded border-gray-200 px-2 py-1 text-xs"><input name="address"
                                    value="{{ $branch->address }}" placeholder="Address"
                                    class="w-32 rounded border-gray-200 px-2 py-1 text-xs"><input name="phone"
                                    value="{{ $branch->phone }}" placeholder="Phone"
                                    class="w-24 rounded border-gray-200 px-2 py-1 text-xs"><label
                                    class="flex items-center gap-1 text-xs"><input type="checkbox" name="is_active"
                                        value="1" @checked($branch->is_active)> Active</label><button
                                    class="text-xs font-semibold text-hut-blue">Save</button></form>
                        </td>
                </tr>@empty<tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-500">No branches configured.</td>
                    </tr>@endforelse
                </tbody>
            </table>
        </section>
    </div>
@endsection