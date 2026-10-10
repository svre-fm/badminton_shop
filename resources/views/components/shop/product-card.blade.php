@props([
    'name' => 'Name',
    'price' => 100,
    'image' => null,
    'rating' => null,
    'favorited' => null,   // null = hide heart
    'colors' => [],
    'href' => '/product/badminton-racket/sample',
])

<article class="group relative w-full">
    <div class="relative aspect-square overflow-hidden rounded-xl bg-tile ring-2 ring-transparent transition group-hover:ring-ink">
        @if ($image)
            <img src="{{ $image }}" alt="" loading="lazy" class="size-full object-cover">
        @endif

        {{-- Stretched link covers the card; badge/heart sit above it (z-10) --}}
        <a href="{{ $href }}" class="absolute inset-0 z-0" aria-label="{{ $name }}"></a>

        @if ($rating || $favorited !== null)
            <div class="absolute bottom-2 right-2 z-10 flex items-center gap-1.5">
                @if ($rating)
                    <span class="flex items-center gap-0.5 rounded border border-star/60 bg-amber-50 px-1.5 text-xs font-medium">
                        <span class="text-star">★</span>{{ $rating }}
                    </span>
                @endif
                @if ($favorited !== null)
                    <button type="button" aria-label="Toggle favorite" aria-pressed="{{ $favorited ? 'true' : 'false' }}"
                            x-data="{ on: {{ $favorited ? 'true' : 'false' }} }" @click.stop="on = !on"
                            :class="on ? 'text-heart' : 'text-ink/60'">
                        <svg class="size-5" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" :fill="on ? 'currentColor' : 'none'">
                            <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/>
                        </svg>
                    </button>
                @endif
            </div>
        @endif
    </div>

    <div class="mt-1 flex items-baseline justify-between text-sm">
        <h3 class="font-medium">{{ $name }}</h3>
        <x-shop.price :amount="$price" class="text-muted" />
    </div>

    @if ($colors)
        <div class="mt-1 flex gap-1">
            @foreach ($colors as $hex)
                <span class="size-3 rounded-full border border-black/10" style="background: {{ $hex }}"></span>
            @endforeach
        </div>
    @endif
</article>
