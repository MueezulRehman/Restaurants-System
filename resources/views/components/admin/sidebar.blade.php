@props(['variant' => 'manager'])

{{-- Shared internal sidebar shell for manager and super-admin workspaces. --}}
<x-layouts.sidebar :variant="$variant" {{ $attributes }}>
    {{ $slot }}
</x-layouts.sidebar>
