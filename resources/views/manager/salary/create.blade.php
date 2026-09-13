@extends('manager.layout.master')
@section('title', 'Record Salary')

@section('page-content')
    <x-page.container max-width="md">
        <x-back-link href="{{ route('manager.salary.index') }}" label="Back to Salary" />
        <x-page.header title="Record salary payment" description="Create a payroll disbursement for a staff member." eyebrow="People & payroll" />
        <x-page.alerts />

        <x-page.card>
            <form action="{{ route('manager.salary.store') }}" method="POST" class="space-y-5">
                @csrf

                <div class="page-form-grid page-form-grid--2">
                <div class="page-field">
                    <label for="user_id">Staff Member <span class="text-red-600" aria-hidden="true">*</span></label>
                    <select name="user_id" required
                        id="user_id">
                        <option value="">Select staff member</option>
                        @foreach($staff as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="page-field">
                    <label for="amount">Amount (Rs.) <span class="text-red-600" aria-hidden="true">*</span></label>
                    <input type="number" name="amount" step="0.01" min="0" required
                        id="amount"
                        value="{{ old('amount') }}" placeholder="0">
                </div>

                <div class="page-field">
                    <label for="month">Month <span class="text-red-600" aria-hidden="true">*</span></label>
                    <input type="month" name="month" required
                        id="month"
                        value="{{ old('month', now()->toDateString()) }}">
                </div>

                <div class="page-field md:col-span-2">
                    <label for="notes">Notes</label>
                    <textarea name="notes" rows="2"
                        id="notes"
                        placeholder="Additional notes (optional)">{{ old('notes') }}</textarea>
                </div>

                </div>
                <x-page.actions>
                    <button type="submit" class="btn-primary">Record salary</button>
                    <a href="{{ route('manager.salary.index') }}"
                        class="btn-secondary">Cancel</a>
                </x-page.actions>
            </form>
        </x-page.card>
    </x-page.container>

@endsection