@props([
    'title',
    'description' => null,
    'eyebrow' => null,
])

<header {{ $attributes->merge(['class' => 'page-header']) }}>
    <div class="page-header__copy">
        @if($eyebrow)
            <p class="page-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 class="page-title">{{ $title }}</h1>
        @if($description)
            <p class="page-description">{{ $description }}</p>
        @endif
    </div>
    @if(trim((string) $actions))
        <div class="page-header__actions">{{ $actions }}</div>
    @endif
</header>
