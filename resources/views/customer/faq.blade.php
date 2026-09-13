@extends('customer.layout.master')

@section('title', 'Frequently Asked Questions')

@section('page-content')
    <div class="mx-auto max-w-4xl px-4 py-12 sm:py-16">
        <div class="mb-8 text-center">
            <p class="text-sm font-semibold uppercase tracking-wide text-hut-green">CodeIbex help</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">Frequently Asked Questions</h1>
        </div>
        <div class="space-y-3">
            @foreach([
                    ['What is CodeIbex?', 'CodeIbex is a SaaS platform that helps businesses manage catalogs, orders, POS, customers, inventory, and day-to-day operations.'],
                    ['How do I find a business?', 'Use the public homepage search or open a business link shared by that business.'],
                    ['Who handles my order?', 'The business shown on your order manages preparation, fulfilment, refunds, and customer support.'],
                    ['Can businesses use custom domains?', 'Yes. Business owners can configure a custom domain through the platform administration workflow.'],
                    ['How do I get help with an account?', 'Contact the business or platform team using the support details provided on the relevant page.'],
                ] as [$question, $answer])
                <details class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <summary class="cursor-pointer list-none font-semibold text-slate-900">{{ $question }}<span
                            class="float-right text-hut-green">+</span></summary>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600">{{ $answer }}</p>
                </details>
            @endforeach
        </div>
    </div>
@endsection