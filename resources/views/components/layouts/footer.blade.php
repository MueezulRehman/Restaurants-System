@props(['label' => 'Site footer', 'variant' => 'internal'])

<footer aria-label="{{ $label }}" data-layout-variant="{{ $variant }}"
    {{ $attributes->merge(['class' => 'layout-footer layout-footer--' . $variant]) }}>
    {{ $slot }}
</footer>
