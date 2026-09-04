@extends('layouts.customer')

@section('title', ($platform['name'] ?? 'CodeIbex') . ' — Businesses')

@section('content')
    <div class="min-h-screen bg-gradient-to-b from-slate-50 to-white">
        {{-- Hero --}}
        <section class="relative overflow-hidden border-b border-slate-200 bg-white">
            <div class="mx-auto max-w-6xl px-4 py-12 sm:py-16">
                <div class="text-center">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                        {{ $platform['hero_title'] ?? $platform['name'] ?? 'CodeIbex' }}
                    </h1>
                    <p class="mx-auto mt-3 max-w-2xl text-base text-slate-600">
                        {{ $platform['hero_subtitle'] ?? 'One platform for discovering and ordering from independent businesses.' }}
                    </p>
                </div>

                {{-- Search --}}
                <form action="{{ route('home') }}" method="GET" class="mx-auto mt-8 max-w-xl">
                    <div
                        class="platform-control flex overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <input type="search" name="q" value="{{ $search }}" placeholder="Search by name or area..."
                            class="w-full border-0 bg-transparent px-5 py-3.5 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0"
                            autocomplete="off" />
                        <button type="submit" class="platform-button px-6 text-sm font-semibold text-white transition">
                            Search
                        </button>
                    </div>
                </form>
            </div>
        </section>

        {{-- Business Grid --}}
        <section class="mx-auto max-w-6xl px-4 py-10">
            @if($search)
                <p class="mb-6 text-sm text-slate-500">
                    Showing results for <span class="font-semibold text-slate-800">“{{ $search }}”</span>
                    · <a href="{{ route('home') }}" class="platform-link hover:underline">Clear</a>
                </p>
            @endif

            @if($restaurants->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                    <div
                        class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-2xl text-slate-500">
                        <i class="fas fa-store"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-slate-800">No businesses found</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        @if($search)
                            Try a different search term.
                        @else
                            New businesses will appear here as they join CodeIbex.
                        @endif
                    </p>
                </div>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($restaurants as $restaurant)
                        <a href="{{ route('menu.restaurant', $restaurant->slug) }}"
                            class="platform-card group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">

                            {{-- Cover / Logo --}}
                            <div
                                class="platform-cover relative flex h-40 items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100">
                                @if($restaurant->logo_path)
                                    <img src="{{ asset('storage/' . $restaurant->logo_path) }}" alt="{{ $restaurant->name }}"
                                        class="h-full w-full object-cover" />
                                @else
                                    <i class="fas fa-store text-5xl text-slate-400 opacity-60"></i>
                                @endif
                                @if($restaurant->businessType)
                                    <span
                                        class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-xs font-medium text-slate-700 shadow-sm">
                                        {{ $restaurant->businessType->name }}
                                    </span>
                                @endif
                            </div>

                            {{-- Body --}}
                            <div class="flex flex-1 flex-col p-5">
                                <h3 class="platform-heading text-lg font-semibold text-slate-900">
                                    {{ $restaurant->name }}
                                </h3>
                                @if($restaurant->address)
                                    <p class="mt-1 line-clamp-2 text-sm text-slate-500">
                                        {{ $restaurant->address }}
                                    </p>
                                @endif
                                <div class="mt-auto flex items-center justify-between pt-4">
                                    <span class="platform-link text-xs font-medium uppercase tracking-wide">
                                        View Menu →
                                    </span>
                                    @if($restaurant->custom_domain || $restaurant->domain)
                                        <span class="text-xs text-slate-400" title="Has custom domain">🌐</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection

@push('scripts')
    <style>
        .platform-control:focus-within {
            box-shadow: 0 0 0 2px var(--platform-primary, #2E5E99);
        }

        .platform-button {
            background: var(--platform-primary, #2E5E99);
        }

        .platform-button:hover {
            background: var(--platform-dark, #0D2440);
        }

        .platform-link {
            color: var(--platform-primary, #2E5E99);
        }

        .platform-card:hover {
            border-color: var(--platform-accent, #7BA4D0);
        }

        .platform-cover {
            background: linear-gradient(135deg, var(--platform-light, #E7F0FA), #f8fafc);
        }

        .platform-heading:hover {
            color: var(--platform-primary, #2E5E99);
        }
    </style>
@endpush