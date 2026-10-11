@php
    // FavoriteController ต้องส่ง Laravel paginator ของรายการโปรดจริงที่เป็นของสมาชิกปัจจุบัน
    $favorites = $favorites ?? collect();
@endphp
<x-layouts.shop title="Favorites">
    <h1 class="mt-8 text-3xl font-bold">Your Favorite Products ({{ method_exists($favorites, 'total') ? $favorites->total() : $favorites->count() }})</h1>

    @if ($favorites->isEmpty())
        <p class="mt-10 text-muted">No favorites yet. Tap the heart on a product to save it here.</p>
    @else
        <div class="mt-6 grid grid-cols-2 gap-6 md:grid-cols-4 xl:grid-cols-5">
            @foreach ($favorites as $favorite)
                @php
                    $product = data_get($favorite, 'product');
                    $category = match (data_get($product, 'product_type')) {
                        'RACKET' => 'badminton-racket',
                        'STRING' => 'badminton-string',
                        'GRIP' => 'grip',
                        'SHUTTLECOCK' => 'shuttlecock',
                        default => 'badminton-racket',
                    };
                @endphp
                <x-shop.product-card
                    :name="data_get($product, 'name')"
                    :price="data_get($product, 'price')"
                    :image="data_get($product, 'image')"
                    :rating="data_get($product, 'rating')"
                    :favorited="true"
                    :href="route('shop.product', ['category' => $category, 'product_id' => data_get($product, 'product_id')])"
                />
            @endforeach
        </div>
        @if (method_exists($favorites, 'lastPage') && $favorites->lastPage() > 1)
            <x-shop.pagination :pages="$favorites->lastPage()" :current="$favorites->currentPage()" />
        @endif
    @endif
</x-layouts.shop>
