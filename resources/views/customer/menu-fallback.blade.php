@extends('layouts.customer')

@section('title', 'Menu — ' . (($currentRestaurant ?? (app()->bound('restaurant') ? app('restaurant') : null))->name ?? 'CodeIbex'))

@section('content')
<div class="max-w-6xl mx-auto px-4 py-16 text-center">
    <h1 class="text-4xl font-display font-bold mb-4">Freshly baked favorites</h1>
    <p class="text-lg text-gray-600 mb-6">Start your order</p>
</div>
@endsection
