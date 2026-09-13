@extends('customer.layout.master')

@section('title', ($restaurant->name ?? 'Business menu') . ' — Menu not published')

@section('page-content')
<div class="min-h-full bg-hut-dark flex items-center justify-center px-4 py-16"
    @if($restaurant) style="{{ $restaurant->themeCssVariables() }}" @endif>
    <div class="text-center max-w-xl">
        @include('customer.partials.storefront-notice', ['restaurant' => $restaurant])
        @if(!empty($restaurant->logo_path))
            <img src="{{ asset('storage/' . $restaurant->logo_path) }}" alt="{{ $restaurant->name }}"
                class="w-24 h-24 rounded-full object-cover mx-auto mb-6">
        @else
            <div
                class="w-24 h-24 mx-auto mb-6 rounded-full bg-hut-yellow flex items-center justify-center text-4xl font-display font-bold text-hut-dark">
                {{ strtoupper(substr($restaurant->name ?? 'BUS', 0, 2)) }}
            </div>
        @endif
        <h1 class="text-3xl md:text-4xl font-display font-bold text-white mb-4">
            {{ $restaurant->name ?? 'This business' }} has not published its menu yet
        </h1>
        <p class="text-gray-300 mb-6">The storefront is active, but no menu items or deals are currently published.
            Please check back after the business adds its catalog.</p>
        @if(!empty($restaurant->phone))
            <p class="text-gray-300 mb-4">📞 {{ $restaurant->phone }}</p>
        @endif
        <a href="{{ route('home') }}"
            class="inline-flex items-center justify-center rounded-full bg-white text-hut-dark px-5 py-3 font-semibold shadow-lg hover:bg-gray-100 transition">Back
            to business selection</a>
    </div>
</div>
@endsection