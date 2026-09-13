@props([
    'maxWidth' => '7xl',
    'class' => '',
])

<div {{ $attributes->merge(['class' => "page-container page-container--{$maxWidth} {$class}"]) }}>
    {{ $slot }}
</div>
