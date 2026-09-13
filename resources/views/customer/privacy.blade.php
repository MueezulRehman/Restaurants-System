@extends('customer.layout.master')

@section('title', 'Privacy Policy')

@section('page-content')
    <div class="mx-auto max-w-4xl px-4 py-12 sm:py-16">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
            <p class="text-sm font-semibold uppercase tracking-wide text-hut-green">CodeIbex</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">Privacy Policy</h1>
            <p class="mt-3 text-sm text-slate-500">Last updated: {{ now()->toFormattedDateString() }}</p>
            <div class="prose prose-slate mt-8 max-w-none">
                <p>CodeIbex helps independent businesses manage their operations and customer experiences. This policy
                    explains what information we collect, why we use it, and the choices available to you.</p>
                <h2>Information we collect</h2>
                <p>We may collect account details, business profile information, order and transaction details, customer
                    contact information, and technical information needed to keep the platform secure and reliable.</p>
                <h2>How we use information</h2>
                <p>We use information to provide the platform, process orders, support businesses, improve reliability,
                    communicate service updates, and prevent fraud or misuse.</p>
                <h2>Sharing and retention</h2>
                <p>Business data is shared with the relevant business account for its operational workflows. We use service
                    providers only when needed to operate the platform and retain information for as long as necessary for
                    these purposes or legal obligations.</p>
                <h2>Your choices</h2>
                <p>You may request access, correction, or deletion of personal information through the business you
                    interacted with or the platform administrator.</p>
                <h2>Contact</h2>
                <p>For privacy questions, contact the CodeIbex platform team through the contact details shown on this site.
                </p>
            </div>
        </div>
    </div>
@endsection