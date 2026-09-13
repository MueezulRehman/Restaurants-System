@props(['variant' => 'manager'])

{{-- Shared authenticated header for manager and super-admin pages. --}}
<x-layouts.header :variant="$variant" {{ $attributes }}>
    {{ $slot }}
</x-layouts.header>
