@extends('layouts.customer')

@section('title', 'Terms of Service')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-12 sm:py-16">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
            <p class="text-sm font-semibold uppercase tracking-wide text-hut-green">CodeIbex</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">Terms of Service</h1>
            <p class="mt-3 text-sm text-slate-500">Last updated: {{ now()->toFormattedDateString() }}</p>
            <div class="prose prose-slate mt-8 max-w-none">
                <p>By using CodeIbex, you agree to use the platform lawfully, protect your account credentials, and provide
                    accurate information.</p>
                <h2>Business accounts</h2>
                <p>Business owners are responsible for their users, catalog information, customer communications, taxes, and
                    compliance with applicable laws.</p>
                <h2>Orders and payments</h2>
                <p>Businesses are responsible for fulfilling orders and handling refunds, returns, and customer support
                    according to their published policies and applicable law.</p>
                <h2>Acceptable use</h2>
                <p>Do not use CodeIbex to distribute unlawful content, interfere with the service, access another account,
                    or misuse customer information.</p>
                <h2>Availability</h2>
                <p>We work to keep the service available, but maintenance, outages, or events outside our control may
                    temporarily affect access.</p>
                <h2>Contact</h2>
                <p>Questions about these terms can be directed to the CodeIbex platform team through the contact details
                    shown on this site.</p>
            </div>
        </div>
    </div>
@endsection