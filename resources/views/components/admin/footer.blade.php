@props(['variant' => 'manager'])

{{-- Shared internal footer for manager and super-admin pages. --}}
<x-layouts.footer :variant="$variant" {{ $attributes }}>
    {{ $slot }}
</x-layouts.footer>
