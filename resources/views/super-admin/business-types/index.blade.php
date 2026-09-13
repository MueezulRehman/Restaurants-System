@extends('super-admin.layout.master')

@section('title', 'Business Types')

@section('page-content')
    <x-page.container>
        <x-page.header title="Business Types" description="Define the business categories used during restaurant onboarding." eyebrow="Platform configuration">
            <x-slot:actions>
                <a href="{{ route('admin.business-types.create') }}" class="btn-primary">Add business type</a>
            </x-slot:actions>
        </x-page.header>
        <x-page.alerts />

        <x-page.card>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Name
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Modules
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($businessTypes as $businessType)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-hut-dark">{{ $businessType->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $businessType->modules_count }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $businessType->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $businessType->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.business-types.edit', $businessType) }}"
                                        class="group relative inline-flex h-8 w-8 items-center justify-center rounded-lg text-hut-yellow hover:bg-hut-yellow/10 hover:text-amber-600"
                                        aria-label="Edit business type" title="Edit business type">
                                        <x-icons.edit class="h-4 w-4" />
                                    </a>
                                    <form action="{{ route('admin.business-types.destroy', $businessType) }}" method="POST"
                                        data-confirm="Delete this business type?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="group relative inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-500 hover:bg-red-50 hover:text-red-700"
                                            aria-label="Delete business type" title="Delete business type">
                                            <x-icons.trash class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">No business types found yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-page.card>
    </x-page.container>
@endsection