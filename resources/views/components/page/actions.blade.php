@props(['align' => 'end'])

<div {{ $attributes->merge(['class' => "page-actions page-actions--{$align}"]) }}>
    {{ $slot }}
</div>
