@extends('manager.layout.master')
@section('title', 'New Reservation')
@section('page-content')
        <div class="max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h1 class="mb-6 text-2xl font-semibold text-hut-dark">New reservation</h1>
                <form method="POST" action="{{ route('manager.reservations.store') }}" class="grid gap-4 md:grid-cols-2">@csrf
                        <div>
                                <label class="text-sm font-medium">Guest name</label><input name="guest_name"
                                        value="{{ old('guest_name') }}" required class="mt-1 w-full rounded-lg border-gray-300">
                        </div>
                        <div><label class="text-sm font-medium">Phone</label><input name="guest_phone"
                                        value="{{ old('guest_phone') }}" class="mt-1 w-full rounded-lg border-gray-300"></div>
                        <div><label class="text-sm font-medium">Customer</label><select name="customer_id"
                                        class="mt-1 w-full rounded-lg border-gray-300">
                                        <option value="">Walk-in guest</option>@foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach
                                </select></div>
                        <div><label class="text-sm font-medium">Table</label><select name="table_id"
                                        class="mt-1 w-full rounded-lg border-gray-300">
                                        <option value="">Assign later</option>@foreach($tables as $table)
                                                <option value="{{ $table->id }}">{{ $table->label ?? $table->number }}
                                                        ({{ $table->seats }} seats)
                                        </option>@endforeach
                                </select></div>
                        <div><label class="text-sm font-medium">Party size</label><input type="number" min="1" name="party_size"
                                        value="{{ old('party_size', 1) }}" required
                                        class="mt-1 w-full rounded-lg border-gray-300"></div>
                        <div><label class="text-sm font-medium">Starts at</label><input type="datetime-local" name="starts_at"
                                        value="{{ old('starts_at') }}" required class="mt-1 w-full rounded-lg border-gray-300">
                        </div>
                        <div><label class="text-sm font-medium">Ends at</label><input type="datetime-local" name="ends_at"
                                        value="{{ old('ends_at') }}" class="mt-1 w-full rounded-lg border-gray-300"></div>
                        <div class="md:col-span-2"><label class="text-sm font-medium">Notes</label><textarea name="notes"
                                        rows="3" class="mt-1 w-full rounded-lg border-gray-300">{{ old('notes') }}</textarea>
                        </div>
                        <div class="md:col-span-2"><button
                                        class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white">Create
                                        reservation</button></div>
                </form>
</div>@endsection