@props([
    'href',
    'label' => 'Back',
])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'back-link mb-4 inline-flex items-center gap-2 text-sm font-medium text-hut-green transition-colors hover:text-hut-dark']) }}>
    <span class="back-link__icon" aria-hidden="true">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <g class="back-link__arrow">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M5 12h6m3 0h1.5m3 0h.5" />
                <path d="M5 12l4 4" />
                <path d="M5 12l4 -4" />
            </g>
        </svg>
    </span>
    <span>{{ $label }}</span>
</a>

<style>
    .back-link__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .back-link__arrow {
        transform-box: fill-box;
        transform-origin: center;
    }

    .back-link:hover .back-link__arrow {
        animation: back-link-nudge 500ms ease-in-out;
    }

    .back-link:active .back-link__arrow {
        transform: translateX(-2px) scale(0.94);
        transition: transform 120ms ease-out;
    }

    @keyframes back-link-nudge {

        0%,
        100% {
            transform: translateX(0);
        }

        50% {
            transform: translateX(-4px);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .back-link:hover .back-link__arrow {
            animation: none;
        }

        .back-link:active .back-link__arrow {
            transform: none;
        }
    }
</style>