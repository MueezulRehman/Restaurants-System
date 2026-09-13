@extends('ceo.layout.master')

@section('title', $branch->name)

@section('page-content')
    <div class="mb-6">
        <a href="{{ route('manager.ceo.branches.index') }}" class="text-sm text-blue-700 hover:underline">Back to branches</a>
        <h1 class="mt-2 text-2xl font-semibold text-hut-dark">{{ $branch->name }}</h1>
        <p class="text-sm text-gray-500">{{ $restaurant->name }}</p>
    </div>
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
        @foreach(['Sales' => 'Rs. ' . number_format($sales), 'Orders' => number_format($orders), 'Low stock' => number_format($low_stock)] as $label => $value)
            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                <p class="text-xs text-gray-400">{{ $label }}</p>
                <p class="mt-1 text-xl font-semibold text-hut-dark">{{ $value }}</p>
            </div>
        @endforeach
    </div>
@endsection
