@php
    $imageUrl = fn ($path) => \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/']) ? $path : asset($path);
    $colorHex = [
        'black' => '#000000', 'white' => '#ffffff', 'blue' => '#4072af', 'light blue' => '#8ec5ff',
        'red' => '#e53935', 'orange' => '#ff9800', 'yellow' => '#ffd982', 'lime' => '#b6e22e',
        'green' => '#43a047', 'pink' => '#f9c9ff', 'cherry' => '#b0003a', 'aqua' => '#3dd6d0',
    ];
    $hex = fn ($color) => $colorHex[strtolower(trim($color))] ?? '#cccccc';
@endphp

<x-layouts.shop title="Home" active="home">
    <section class="py-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-muted">Most purchased</p>
                <h1 class="mt-2 text-4xl font-bold">Best Sellers</h1>
            </div>
            <a href="{{ route('products.index') }}" class="rounded-full border border-ink px-5 py-2 hover:bg-mist">
                Shop all products
            </a>
        </div>

        @if ($bestSellers->isEmpty())
            <p class="mt-8 text-muted">No completed orders yet.</p>
        @else
            <ul class="mt-8 grid grid-cols-2 gap-6 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($bestSellers as $product)
                    <li>
                        <div class="relative aspect-square overflow-hidden rounded-xl bg-tile">
                            <a href="{{ route('products.show', $product) }}" class="block size-full">
                                <img src="{{ $imageUrl($product->image) }}" alt="{{ $product->name }}" loading="lazy" class="size-full object-cover">
                            </a>
                            <span class="absolute left-2 top-2 rounded-full bg-white px-3 py-1 text-sm font-semibold">#{{ $loop->iteration }}</span>
                            @if ($product->reviews_avg_star)
                                <span class="absolute bottom-2 left-2 rounded border border-star/60 bg-amber-50 px-1 text-sm">
                                    <span class="text-star">★</span> {{ number_format((float) $product->reviews_avg_star, 1) }}
                                </span>
                            @endif
                            <div class="absolute right-2 top-2">
                                <x-shop.product-heart :product-id="$product->product_id" :active="in_array($product->product_id, $favoriteIds)" />
                            </div>
                        </div>

                        <a href="{{ route('products.show', $product) }}" class="mt-2 flex items-start justify-between gap-2">
                            <span class="line-clamp-2">{{ $product->name }}</span>
                            <x-shop.price :amount="$product->price" class="shrink-0 text-muted" />
                        </a>

                        <div class="mt-1 flex gap-1">
                            @foreach ($product->variants->take(6) as $variant)
                                @php
                                    $parts = array_values(array_filter(array_map('trim', explode('/', $variant->color))));
                                    $colorStyle = count($parts) > 1
                                        ? 'linear-gradient(135deg, ' . $hex($parts[0]) . ' 50%, ' . $hex($parts[1]) . ' 50%)'
                                        : $hex($parts[0] ?? $variant->color);
                                @endphp
                                <span class="size-3 rounded-full border border-black/10" title="{{ $variant->color }}" style="background: {{ $colorStyle }}"></span>
                            @endforeach
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.shop>
