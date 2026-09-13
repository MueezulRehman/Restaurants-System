@props([
    'title',
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'page-section']) }}>
    <div class="page-section__heading">
        <div>
            <h2 class="page-section__title">{{ $title }}</h2>
            @if($description)
                <p class="page-section__description">{{ $description }}</p>
            @endif
        </div>
        @if(trim((string) $actions))
            <div class="page-section__actions">{{ $actions }}</div>
        @endif
    </div>
    {{ $slot }}
</section>
