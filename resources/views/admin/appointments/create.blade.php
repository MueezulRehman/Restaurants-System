@extends('layouts.admin')
@section('title', 'Book Appointment')
@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <x-back-link href="{{ route('manager.appointments.index') }}" label="Back to appointments" class="mb-3" />
            <h1 class="text-2xl font-display font-bold text-hut-dark">Book appointment</h1>
            <p class="mt-1 text-sm text-gray-500">Assign a service, customer, time, and optional team member.</p>
        </div>
        <form method="POST" action="{{ route('manager.appointments.store') }}"
            class="space-y-5 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Customer</label>
                    <select name="customer_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="">Select customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                {{ $customer->name }} - {{ $customer->phone }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Staff member</label>
                    <select name="staff_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="">Unassigned</option>
                        @foreach($staff as $member)
                            <option value="{{ $member->id }}" @selected(old('staff_id') == $member->id)>{{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Service</label>
                <input name="service_name" required value="{{ old('service_name') }}" placeholder="e.g. Haircut and styling"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Starts</label>
                    <input type="datetime-local" name="starts_at" required value="{{ old('starts_at') }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Ends</label>
                    <input type="datetime-local" name="ends_at" required value="{{ old('ends_at') }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Status</label>
                    <select name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        @foreach(['scheduled', 'confirmed'] as $status)
                            <option value="{{ $status }}" @selected(old('status', 'scheduled') === $status)>{{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Price</label>
                    <input type="number" name="price" min="0" step="0.01" value="{{ old('price') }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Notes</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"
                    placeholder="Optional customer or service notes">{{ old('notes') }}</textarea>
            </div>
            <button class="rounded-lg bg-hut-dark px-5 py-2.5 text-sm font-semibold text-white">Book appointment</button>
        </form>
    </div>
@endsection