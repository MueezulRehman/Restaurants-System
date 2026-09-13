@extends('customer.layout.master')

@section('title', $restaurant->name ?? 'Storefront Unavailable')

@section('page-content')
<div class="min-h-full bg-hut-dark flex items-center justify-center px-4 py-16"
    @if($restaurant) style="{{ $restaurant->themeCssVariables() }}" @endif>
    <div class="text-center max-w-xl">
        @if(!empty($restaurant->logo_path))
            <img src="{{ asset('storage/' . $restaurant->logo_path) }}" alt="{{ $restaurant->name }}" class="w-20 h-20 rounded-full object-cover mx-auto mb-4">
        @else
            <div class="w-20 h-20 bg-hut-yellow rounded-full flex items-center justify-center font-display font-bold text-hut-dark text-3xl mx-auto mb-4">{{ strtoupper(substr($restaurant->name ?? 'BUS', 0, 2)) }}</div>
        @endif
        <h1 class="text-3xl font-display font-bold text-white mb-4">Storefront Unavailable</h1>
        <p class="text-gray-300 mb-4">
            {{ $restaurant->name ?? 'This restaurant' }} is not currently accepting online orders.
        </p>
        <p class="text-gray-400 mb-6">
            That may be because the business is inactive, the subscription plan is inactive or expired, or the menu is temporarily disabled.
        </p>
        <a href="/" class="btn-accent">Back to Home</a>
    </div>
</div>
@endsection
