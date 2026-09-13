@extends('manager.layout.master')
@section('title', 'Gym Memberships')
@section('page-content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Gym memberships</h1>
            <p class="mt-1 text-sm text-gray-500">Manage plans, renewals, and member check-ins.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    @if($trainers->isNotEmpty() || $trainers->isEmpty())
        <section class="mb-6 rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-hut-dark">Trainer management</h2>
            <form method="POST" action="{{ route('manager.gym.trainers.store') }}" class="mt-4 grid gap-3 sm:grid-cols-4">@csrf
                <input name="name" required placeholder="Trainer name"
                    class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <input name="specialty" placeholder="Specialty" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <input name="phone" placeholder="Phone" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <button class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Add trainer</button>
            </form>
            @if($trainers->isNotEmpty())
                <div class="mt-4 flex flex-wrap gap-2">@foreach($trainers as $trainer)<span
                class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700">{{ $trainer->name }}{{ $trainer->specialty ? ' · ' . $trainer->specialty : '' }}</span>@endforeach
                </div>
            @endif
        </section>
    @endif

    <section class="mb-6 rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-hut-dark">Trainer schedule and capacity</h2>
        <form method="POST" action="{{ route('manager.gym.trainer-schedules.store') }}"
            class="mt-4 grid gap-3 sm:grid-cols-5">@csrf
            <select name="gym_trainer_id" required class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <option value="">Trainer</option>@foreach($trainers as $trainer)
                <option value="{{ $trainer->id }}">{{ $trainer->name }}</option>@endforeach
            </select>
            <select name="day_of_week" required
                class="rounded-lg border border-gray-200 px-3 py-2 text-sm">@foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day => $label)
                <option value="{{ $day }}">{{ $label }}</option>@endforeach
            </select>
            <input type="time" name="starts_at" required class="rounded-lg border border-gray-200 px-3 py-2 text-sm"><input
                type="time" name="ends_at" required class="rounded-lg border border-gray-200 px-3 py-2 text-sm"><input
                type="number" name="capacity" required min="1" value="1" placeholder="Capacity"
                class="rounded-lg border border-gray-200 px-3 py-2 text-sm"><button
                class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white sm:col-span-5">Save
                schedule</button>
        </form>
        <div class="mt-4 flex flex-wrap gap-2">@foreach($schedules as $schedule)<span
            class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700">{{ $schedule->trainer->name }} ·
            {{ ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][$schedule->day_of_week] }}
            {{ substr($schedule->starts_at, 0, 5) }}-{{ substr($schedule->ends_at, 0, 5) }} · cap
        {{ $schedule->capacity }}</span>@endforeach</div>
    </section>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-hut-dark">Create membership plan</h2>
            <form method="POST" action="{{ route('manager.gym.plans.store') }}" class="mt-4 grid gap-3 sm:grid-cols-3">@csrf
                <input name="name" required placeholder="Plan name"
                    class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <input name="duration_days" required type="number" min="1" placeholder="Days"
                    class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <input name="price" required type="number" min="0" step="0.01" placeholder="Price"
                    class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <button class="sm:col-span-3 rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Save
                    plan</button>
            </form>
        </section>
        <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-hut-dark">Activate membership</h2>
            <form method="POST" action="{{ route('manager.gym.memberships.store') }}"
                class="mt-4 grid gap-3 sm:grid-cols-2">@csrf
                <select name="customer_id" required class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Select member</option>@foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>@endforeach
                </select>
                <select name="gym_plan_id" required class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Select plan</option>@foreach($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} - Rs. {{ number_format($plan->price, 2) }} /
                            {{ $plan->duration_days }} days
                    </option>@endforeach
                </select>
                <select name="gym_trainer_id" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Assign trainer (optional)</option>@foreach($trainers as $trainer)
                    <option value="{{ $trainer->id }}">{{ $trainer->name }}</option>@endforeach
                </select>
                <input name="starts_at" type="date" required value="{{ now()->toDateString() }}"
                    class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <input name="amount_paid" type="number" min="0" step="0.01" placeholder="Amount paid"
                    class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <button class="sm:col-span-2 rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Activate
                    membership</button>
            </form>
        </section>
    </div>

    <section class="mb-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="font-semibold text-hut-dark">Members</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Member</th>
                        <th class="px-4 py-3">Plan</th>
                        <th class="px-4 py-3">Period</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Trainer</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($memberships as $membership)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $membership->customer->name }}
                                <div class="text-xs text-gray-500">{{ $membership->customer->phone }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $membership->plan->name }}</td>
                            <td class="px-4 py-3">{{ $membership->starts_at->format('d M Y') }} -
                                {{ $membership->ends_at->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3"><span
                                    class="rounded-full px-2 py-1 text-xs font-semibold {{ $membership->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">{{ ucfirst($membership->status) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('manager.gym.memberships.trainer', $membership) }}"
                                    class="flex gap-2">@csrf @method('PATCH')
                                    <select name="gym_trainer_id" onchange="this.form.submit()"
                                        class="rounded border-gray-200 px-2 py-1 text-xs">
                                        <option value="">Unassigned</option>@foreach($trainers as $trainer)
                                        <option value="{{ $trainer->id }}" {{ $membership->gym_trainer_id === $trainer->id ? 'selected' : '' }}>{{ $trainer->name }}</option>@endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('manager.gym.memberships.renew', $membership) }}">
                                        @csrf<input type="hidden" name="amount_paid"
                                            value="{{ $membership->plan->price }}"><button
                                            class="rounded border border-gray-200 px-2 py-1 text-xs font-semibold">Renew</button>
                                    </form>@if($membership->status === 'active' && !$membership->ends_at->isPast())
                                        <form method="POST" action="{{ route('manager.gym.memberships.check-in', $membership) }}">
                                            @csrf<button
                                                class="rounded bg-hut-dark px-2 py-1 text-xs font-semibold text-white">Check
                                    in</button></form>@endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">No memberships found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 px-5 py-4">{{ $memberships->links() }}</div>
    </section>

    <section class="rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="font-semibold text-hut-dark">Recent check-ins</h2>
        </div>
        <div class="divide-y divide-gray-100">@forelse($checkIns as $checkIn)
            <div class="flex items-center justify-between px-5 py-3 text-sm"><span
                    class="font-medium">{{ $checkIn->customer->name }}</span><span
        class="text-gray-500">{{ $checkIn->checked_in_at->format('d M Y, H:i') }}</span></div>@empty<div
                    class="px-5 py-8 text-sm text-gray-500">No check-ins recorded yet.</div>@endforelse
        </div>
    </section>
@endsection