@props(['variant' => 'manager'])

{{-- Navigation region used by both manager and super-admin sidebars. --}}
<x-layouts.navbar :variant="$variant" {{ $attributes }}>
    {{ $slot }}
</x-layouts.navbar>
