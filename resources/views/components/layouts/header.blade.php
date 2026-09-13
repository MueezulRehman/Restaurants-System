@props(['label' => 'Page header', 'variant' => 'internal'])

<header aria-label="{{ $label }}" data-layout-variant="{{ $variant }}"
    {{ $attributes->merge(['class' => 'layout-header layout-header--' . $variant]) }}>
    {{ $slot }}
</header>
