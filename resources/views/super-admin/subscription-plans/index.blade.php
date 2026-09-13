@extends('super-admin.layout.master')

@section('title', 'Subscription Plans')

@section('page-content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-hut-dark">Subscription Plans</h2>
                <p class="text-sm text-gray-500">Manage subscription tiers and billing options.</p>
            </div>
            <a href="{{ route('admin.subscription-plans.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
                aria-label="Add plan" title="Add plan"><x-icons.add class="h-4 w-4" /> Add Plan</a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Name
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Price
                            Monthly</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($plans as $plan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-hut-dark">{{ $plan->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $plan->price_monthly ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.subscription-plans.edit', $plan) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-hut-yellow hover:bg-hut-yellow/10 hover:text-amber-600"
                                        aria-label="Edit subscription plan" title="Edit subscription plan">
                                        <x-icons.edit class="h-4 w-4" />
                                    </a>
                                    <form action="{{ route('admin.subscription-plans.destroy', $plan) }}" method="POST"
                                        data-confirm="Delete this plan?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-500 hover:bg-red-50 hover:text-red-700"
                                            aria-label="Delete subscription plan" title="Delete subscription plan">
                                            <x-icons.trash class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">No subscription plans found yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection