@props(['label' => 'Primary navigation', 'variant' => 'internal'])

<aside data-layout-variant="{{ $variant }}"
    {{ $attributes->merge(['class' => 'layout-sidebar layout-sidebar--' . $variant]) }}>
    {{ $slot }}
</aside>
