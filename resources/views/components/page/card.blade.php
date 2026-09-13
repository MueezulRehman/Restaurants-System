@props([
    'title' => null,
    'description' => null,
    'header' => null,
])

<section {{ $attributes->merge(['class' => 'page-card']) }}>
    @if($title || $description || trim((string) $header))
        <div class="page-card__header">
            <div>
                @if($title)
                    <h2 class="page-card__title">{{ $title }}</h2>
                @endif
                @if($description)
                    <p class="page-card__description">{{ $description }}</p>
                @endif
            </div>
            @if(trim((string) $header))
                <div class="page-card__actions">{{ $header }}</div>
            @endif
        </div>
    @endif
    <div class="page-card__body">{{ $slot }}</div>
</section>
