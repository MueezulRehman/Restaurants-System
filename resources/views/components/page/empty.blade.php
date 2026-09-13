@props([
    'title' => 'Nothing here yet',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'page-empty']) }}>
    <span class="page-empty__icon" aria-hidden="true"><i class="fas fa-inbox"></i></span>
    <h3>{{ $title }}</h3>
    @if($description)
        <p>{{ $description }}</p>
    @endif
    {{ $slot }}
</div>
