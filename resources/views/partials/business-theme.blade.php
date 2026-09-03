@php
    $r = $currentRestaurant ?? ($restaurant ?? (auth()->user()?->effectiveRestaurant() ?? null));
    $theme = is_array($r?->theme) ? $r->theme : [];
    $primary = $theme['primary'] ?? null;
    $secondary = $theme['secondary'] ?? null;
    $accent = $theme['accent'] ?? null;
@endphp
@if($primary || $secondary || $accent)
<style>
    :root {
        @if($primary) --hut-dark: {{ $primary }}; @endif
        @if($secondary) --hut-yellow: {{ $secondary }}; @endif
        @if($accent) --hut-green: {{ $accent }}; @endif
    }
    .bg-hut-dark { background-color: {{ $primary ?? '#0f3d2e' }} !important; }
    .text-hut-dark { color: {{ $primary ?? '#0f3d2e' }} !important; }
</style>
@endif
