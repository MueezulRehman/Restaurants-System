@php
    $demoSlides = [
        'images/deals/6a462aca4a403_1782983370.jpeg',
        'images/deals/6a462ad7caf2b_1782983383.jpeg',
        'images/deals/6a462ae254f70_1782983394.jpeg',
        'images/deals/6a462aebea38d_1782983403.jpeg',
        'images/deals/6a462afd51f62_1782983421.jpeg',
    ];
    $restaurantBrandImage = null;
    if (!empty($currentRestaurant?->logo_path)) {
        $logoPath = $currentRestaurant->logo_path;
        if (\Illuminate\Support\Str::startsWith($logoPath, ['http://', 'https://'])) {
            $restaurantBrandImage = $logoPath;
        } elseif (file_exists(public_path('images/' . $logoPath))) {
            $restaurantBrandImage = asset('images/' . $logoPath);
        } elseif (file_exists(public_path($logoPath))) {
            $restaurantBrandImage = asset($logoPath);
        } else {
            $restaurantBrandImage = asset('storage/' . ltrim($logoPath, '/'));
        }
    }

    $slides = collect($heroSlides ?? [])->map(function ($slide) {
        if (is_array($slide)) {
            $slide = $slide['path'] ?? $slide['image'] ?? null;
        }

        if (!is_string($slide) || trim($slide) === '') {
            return null;
        }

        $slide = trim($slide);

        if (\Illuminate\Support\Str::startsWith($slide, ['http://', 'https://'])) {
            return $slide;
        }

        $slide = ltrim($slide, '/');

        if (\Illuminate\Support\Str::startsWith($slide, 'storage/')) {
            return asset($slide);
        }

        if (\Illuminate\Support\Str::startsWith($slide, 'public/')) {
            $slide = substr($slide, 7);
        }

        if (\Illuminate\Support\Str::startsWith($slide, 'restaurant-hero/') || str_contains($slide, '/')) {
            return asset('storage/' . $slide);
        }

        return asset('storage/' . $slide);
    })->filter()->values();

    if ($slides->isEmpty() && $restaurantBrandImage) {
        $slides = collect([$restaurantBrandImage]);
    }

    if ($slides->isEmpty()) {
        $slides = collect($demoSlides)->map(fn($slide) => asset($slide));
    }
@endphp

<div class="menu-hero__carousel" aria-hidden="true">
    @foreach($slides as $index => $slide)
        <div class="menu-hero__slide {{ $index === 0 ? 'is-active' : '' }}"
            style="background-image: url('{{ $slide }}'); background-position: center center; background-repeat: no-repeat; background-size: cover;">
        </div>
    @endforeach
</div>
<div class="menu-hero__scrim" aria-hidden="true"></div>

@once
    @push('styles')
        <style>
            .menu-hero__carousel,
            .menu-hero__scrim,
            .menu-hero__slide {
                position: absolute;
                inset: 0;
            }

            .menu-hero__carousel {
                z-index: 0;
            }

            .menu-hero__slide {
                position: absolute;
                inset: 0;
                background-position: center center;
                background-repeat: no-repeat;
                background-size: cover;
                opacity: 0;
                transform: scale(1.06);
                transition: opacity 900ms ease, transform 6s ease;
            }

            .menu-hero__slide.is-active {
                opacity: 1;
                transform: scale(1.02);
            }

            .menu-hero__scrim {
                z-index: 1;
                background: linear-gradient(90deg, rgba(6, 25, 49, 0.58), rgba(6, 25, 49, 0.35), rgba(6, 25, 49, 0.58));
            }

            @media (prefers-reduced-motion: reduce) {
                .menu-hero__slide {
                    transition: none;
                }
            }
        </style>
    @endpush
    @push('scripts')
        <script>
            document.querySelectorAll('.menu-hero__carousel').forEach((carousel) => {
                const slides = carousel.querySelectorAll('.menu-hero__slide');
                if (slides.length < 2) return;
                let active = 0;
                window.setInterval(() => {
                    slides[active].classList.remove('is-active');
                    active = (active + 1) % slides.length;
                    slides[active].classList.add('is-active');
                }, 5000);
            });
        </script>
    @endpush
@endonce