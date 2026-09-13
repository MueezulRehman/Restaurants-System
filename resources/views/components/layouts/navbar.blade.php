@props(['label' => 'Primary navigation', 'variant' => 'internal'])

<nav aria-label="{{ $label }}" data-layout-variant="{{ $variant }}"
    {{ $attributes->merge(['class' => 'layout-navbar layout-navbar--' . $variant]) }}>
    {{ $slot }}
</nav>
