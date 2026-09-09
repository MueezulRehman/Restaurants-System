@extends('layouts.admin')
@section('title', 'Follow-up Reminders')
@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Follow-up Reminders</h1>
            <p class="mt-1 text-sm text-gray-500">Schedule and complete patient follow-ups.</p>
        </div>
        <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Schedule follow-up</h2>
            <form method="POST" action="{{ route('manager.follow-up-reminders.store') }}"
                class="mt-4 grid gap-3 md:grid-cols-4">@csrf<select name="customer_id" required
                    class="rounded-lg border px-3 py-2 text-sm">
                    <option value="">Patient</option>@foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach
                </select><select name="medical_record_id" class="rounded-lg border px-3 py-2 text-sm">
                    <option value="">Medical record (optional)</option>@foreach($records as $record)
                        <option value="{{ $record->id }}">{{ $record->patient_name }} — {{ $record->diagnosis ?: 'Record' }}
                    </option>@endforeach
                </select><input name="due_at" required type="datetime-local"
                    class="rounded-lg border px-3 py-2 text-sm"><input name="note" placeholder="Follow-up note"
                    class="rounded-lg border px-3 py-2 text-sm"><button
                    class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white md:col-span-4">Schedule
                    reminder</button></form>
        </section>
        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Patient</th>
                            <th class="px-4 py-3">Due</th>
                            <th class="px-4 py-3">Note</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Update</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">@forelse($reminders as $reminder)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $reminder->customer->name }}</td>
                            <td class="px-4 py-3">{{ $reminder->due_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $reminder->note ?: '—' }}</td>
                            <td class="px-4 py-3">{{ ucfirst($reminder->status) }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('manager.follow-up-reminders.update', $reminder) }}"
                                    class="flex gap-2">@csrf @method('PATCH')<select name="status"
                                        class="rounded border px-2 py-1 text-xs">@foreach(['scheduled', 'completed', 'cancelled'] as $status)
                                            <option value="{{ $status }}" @selected($reminder->status === $status)>
                                        {{ ucfirst($status) }}</option>@endforeach
                                    </select><button class="text-xs font-semibold text-hut-blue">Save</button></form>
                            </td>
                    </tr>@empty<tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">No follow-up reminders.</td>
                        </tr>@endforelse
                    </tbody>
                </table>
            </div>@if($reminders->hasPages())
            <div class="border-t p-4">{{ $reminders->links() }}</div>@endif
        </section>
    </div>
@endsection