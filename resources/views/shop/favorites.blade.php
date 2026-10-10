@php
    $allItems = collect(range(1, 16));
    $itemsPerPage = 4;
    $pageCount = (int) ceil($allItems->count() / $itemsPerPage);
    $requestedPage = filter_var(request()->query('page', 1), FILTER_VALIDATE_INT);
    $currentPage = min($pageCount, max(1, $requestedPage === false ? 1 : $requestedPage));
    $items = $allItems->forPage($currentPage, $itemsPerPage);
@endphp
<x-layouts.shop title="Favorites">
    <h1 class="mt-8 text-3xl font-bold">Your Favorite Products ({{ $allItems->count() }})</h1>

    @if ($allItems->isEmpty())
        <p class="mt-10 text-muted">No favorites yet. Tap the heart on a product to save it here.</p>
    @else
        <div class="mt-6 grid grid-cols-2 gap-6 md:grid-cols-4 xl:grid-cols-5">
            @foreach ($items as $i)
                <x-shop.product-card
                    name="Racket {{ $i }}"
                    :price="100"
                    :rating="4.9"
                    :favorited="true"
                    :href="route('shop.product', ['category' => 'badminton-racket', 'slug' => 'badminton-racket-'.$i])"
                />
            @endforeach
        </div>
        <x-shop.pagination :pages="$pageCount" :current="$currentPage" />
    @endif
</x-layouts.shop>
