@props([
    'name' => null,
    'price' => null,
    'image' => null,
    'rating' => null,
    'favorited' => null,   // null = hide heart
    'productId' => null,
    'colors' => [],
    'href' => null,
])

<article {{ $attributes->class(['group relative w-full']) }}>
    <div class="relative aspect-square overflow-hidden rounded-xl bg-tile ring-2 ring-transparent transition group-hover:ring-ink border border-mist">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $name }}" loading="lazy" class="size-full object-cover">
        @else
            <div class="grid size-full place-items-center bg-page px-3 text-center text-sm text-muted">{{ $name }}</div>
        @endif

        {{-- Stretched link covers the card; badge/heart sit above it (z-10) --}}
        @if ($href)
            <a href="{{ $href }}" class="absolute inset-0 z-0" aria-label="{{ $name }}"></a>
        @endif

        {{-- ดาว: มุมขวาบน --}}
        @if ($rating)
            <span class="pointer-events-none absolute right-2 top-2 z-10 flex items-center gap-0.5 rounded border border-star/60 bg-amber-50 px-1.5 text-xs font-medium">
                <span class="text-star">★</span>{{ $rating }}
            </span>
        @endif

        {{-- หัวใจ: มุมขวาล่าง --}}
        @if ($favorited !== null)
            <div class="absolute bottom-2 right-2 z-10">
                <x-shop.product-heart compact :product-id="$productId" :favorited="$favorited" />
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
