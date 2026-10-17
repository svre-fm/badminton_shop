@php
    // ตัวแปรจาก ProductController@index:
    // $products, $selectedType, $totalInCategory, $facets, $commonFacets, $favoriteIds

    $titles = ['RACKET' => 'Badminton racket', 'STRING' => 'Badminton string', 'GRIP' => 'Grip', 'SHUTTLECOCK' => 'Shuttlecock'];
    $title  = $titles[$selectedType] ?? 'All products';

    // ชื่อกลุ่มตัวกรองที่แสดงบน UI (คีย์ = ชื่อพารามิเตอร์)
    $labels = [
        'balance_point'         => 'Balance Point',
        'shaft'                 => 'Shaft',
        'flexibility'           => 'Flexibility',
        'weight'                => 'Weight',
        'grip_size'             => 'Grip size',
        'thickness'             => 'Thickness',
        'string_characteristic' => 'Characteristic',
        'shuttle_type'          => 'Type',
        'speed_rating'          => 'Speed rating',
        'grip_type'             => 'Type',
        'material'              => 'Material',
    ];

    $sorts = [
        'latest'     => 'Latest',
        'price_low'  => 'Price: Low to High',
        'price_high' => 'Price: High to Low',
        'top_sale'   => 'Top sale',
    ];
    $currentSort = request('sort', 'latest');

    // สี: รองรับสีผสมคั่นด้วย "/" เช่น Black/Orange (ชุดเดียวกับหน้า show)
    $colorHex = [
        'black' => '#000000', 'white' => '#ffffff', 'blue' => '#4072af', 'light blue' => '#8ec5ff',
        'red' => '#e53935', 'orange' => '#ff9800', 'yellow' => '#ffd982', 'lime' => '#b6e22e',
        'green' => '#43a047', 'pink' => '#f9c9ff', 'cherry' => '#b0003a', 'aqua' => '#3dd6d0',
    ];
    $hex = fn ($c) => str_starts_with(trim($c), '#') ? trim($c) : ($colorHex[strtolower(trim($c))] ?? '#cccccc');
    $swatch = function ($c) use ($hex) {
        $parts = array_values(array_filter(array_map('trim', explode('/', $c))));
        if (count($parts) < 2) {
            return $hex($parts[0] ?? $c);
        }
        return 'linear-gradient(135deg, ' . $hex($parts[0]) . ' 50%, ' . $hex($parts[1]) . ' 50%)';
    };

    $imageUrl = fn ($path) => \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/']) ? $path : asset($path);

    // ----- ป้าย Applied Filters: กดแล้วลบตัวกรองนั้นออก -----
    $query = request()->query();

    $urlWithout = function (string $key, ?string $value = null) use ($query) {
        $q = $query;
        if ($value === null) {
            unset($q[$key]);
        } else {
            $left = array_values(array_diff((array) ($q[$key] ?? []), [$value]));
            if ($left) {
                $q[$key] = $left;
            } else {
                unset($q[$key]);
            }
        }
        unset($q['page']);

        return route('products.index', $q);
    };

    $chips = [];
    foreach ($facets as $param => $options) {
        foreach ((array) request($param) as $v) {
            $chips[] = ['label' => $v, 'url' => $urlWithout($param, (string) $v)];
        }
    }
    foreach ((array) request('brand') as $v) {
        $chips[] = ['label' => $v, 'url' => $urlWithout('brand', (string) $v)];
    }
    foreach ((array) request('color') as $v) {
        $chips[] = ['label' => $v, 'url' => $urlWithout('color', (string) $v)];
    }
    if (request()->filled('min_price') || request()->filled('max_price')) {
        $q = $query;
        unset($q['min_price'], $q['max_price'], $q['page']);
        $min = request()->filled('min_price') ? number_format((float) request('min_price')) : '0';
        $max = request()->filled('max_price') ? number_format((float) request('max_price')) : '∞';
        $chips[] = ['label' => "{$min} – {$max}", 'url' => route('products.index', $q)];
    }

    $clearUrl = route('products.index', array_filter(['type' => request('type'), 'sort' => request('sort')]));
@endphp

<x-layouts.shop :title="$title" active="shop">
    <div class="mt-8 grid gap-8 md:grid-cols-[18rem_1fr]">

        {{-- ================= Filter Products ================= --}}
        <aside class="self-start rounded-xl border border-ink/30 p-4">
            <h2 class="text-2xl font-bold">Filter Products</h2>

            <form id="filterForm" method="GET" action="{{ route('products.index') }}" onchange="this.submit()">
                @if (request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif

                {{-- ยังไม่เลือกหมวด: ให้เลือกหมวดก่อน --}}
                @unless ($selectedType)
                    <details class="mt-4" open>
                        <summary class="cursor-pointer rounded-full bg-mist px-4 py-1">Category</summary>
                        <ul class="mt-2 space-y-1 px-2">
                            @foreach ($titles as $key => $name)
                                <li><a class="hover:underline" href="{{ route('products.index', ['type' => strtolower($key)]) }}">{{ $name }}</a></li>
                            @endforeach
                        </ul>
                    </details>
                @endunless

                {{-- สเปคเฉพาะหมวด (Balance Point, Weight, ...) พร้อมจำนวน --}}
                @foreach ($facets as $param => $options)
                    @continue(count($options) === 0)
                    <details class="mt-4" open>
                        <summary class="cursor-pointer rounded-full bg-mist px-4 py-1">{{ $labels[$param] ?? \Illuminate\Support\Str::headline($param) }}</summary>
                        <ul class="mt-2 space-y-1 px-2">
                            @foreach ($options as $value => $n)
                                <li>
                                    <label class="flex cursor-pointer items-center gap-2">
                                        <input type="checkbox" name="{{ $param }}[]" value="{{ $value }}" @checked(in_array((string) $value, array_map('strval', (array) request($param)), true))>
                                        <span class="flex-1">{{ $value }}</span>
                                        <span class="text-sm text-muted">{{ $n }}</span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @endforeach

                {{-- Brand --}}
                @if ($commonFacets['brands']->isNotEmpty())
                    <details class="mt-4" open>
                        <summary class="cursor-pointer rounded-full bg-mist px-4 py-1">Brand</summary>
                        <ul class="mt-2 space-y-1 px-2">
                            @foreach ($commonFacets['brands'] as $brand => $n)
                                <li>
                                    <label class="flex cursor-pointer items-center gap-2">
                                        <input type="checkbox" name="brand[]" value="{{ $brand }}" @checked(in_array((string) $brand, array_map('strval', (array) request('brand')), true))>
                                        <span class="flex-1">{{ $brand }}</span>
                                        <span class="text-sm text-muted">{{ $n }}</span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @endif

                {{-- Price --}}
                <details class="mt-4" open>
                    <summary class="cursor-pointer rounded-full bg-mist px-4 py-1">Price</summary>
                    <div class="mt-2 flex items-center gap-2 px-2">
                        <input type="number" name="min_price" min="0" value="{{ request('min_price') }}" placeholder="From" aria-label="Minimum price"
                               class="w-full rounded-full border border-ink/30 bg-transparent px-3 py-1">
                        <span>–</span>
                        <input type="number" name="max_price" min="0" value="{{ request('max_price') }}" placeholder="To" aria-label="Maximum price"
                               class="w-full rounded-full border border-ink/30 bg-transparent px-3 py-1">
                    </div>
                </details>

                {{-- Color --}}
                @if ($commonFacets['colors']->isNotEmpty())
                    <details class="mt-4" open>
                        <summary class="cursor-pointer rounded-full bg-mist px-4 py-1">Color</summary>
                        <div class="mt-3 flex flex-wrap gap-3 px-2">
                            @foreach ($commonFacets['colors'] as $color => $n)
                                <label class="cursor-pointer" title="{{ $color }} ({{ $n }})">
                                    <input type="checkbox" name="color[]" value="{{ $color }}" class="peer sr-only"
                                           @checked(in_array((string) $color, array_map('strval', (array) request('color')), true))>
                                    <span class="block size-7 rounded-full border border-black/10 ring-2 ring-transparent ring-offset-2 peer-checked:ring-brand peer-focus-visible:ring-ink"
                                          style="background: {{ $swatch($color) }}"></span>
                                    <span class="sr-only">{{ $color }}</span>
                                </label>
                            @endforeach
                        </div>
                    </details>
                @endif

                <noscript><button type="submit" class="mt-4 rounded bg-ink px-4 py-1 text-white">Apply</button></noscript>
            </form>
        </aside>

        {{-- ================= Results ================= --}}
        <section>
            <h1 class="text-4xl font-bold">{{ $title }}</h1>
            <p class="mt-1 text-muted">
                Showing <b>{{ $products->total() }}</b> result from total <b>{{ $totalInCategory }}</b>
            </p>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($chips)
                        <span class="text-muted">Applied Filters:</span>
                        @foreach ($chips as $chip)
                            <a href="{{ $chip['url'] }}" class="rounded-full border border-ink px-3 py-0.5 hover:bg-mist" aria-label="Remove filter {{ $chip['label'] }}">
                                {{ $chip['label'] }} ✕
                            </a>
                        @endforeach
                        <a href="{{ $clearUrl }}" class="text-sm text-muted underline underline-offset-2">Clear all</a>
                    @endif
                </div>

                <label class="flex items-center gap-2">
                    <span class="text-muted">Sort by</span>
                    <select name="sort" form="filterForm" onchange="this.form.submit()" class="rounded-full bg-mist px-4 py-1">
                        @foreach ($sorts as $value => $label)
                            <option value="{{ $value }}" @selected($currentSort === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            @if ($products->isEmpty())
                <div class="mt-12 text-center text-muted">
                    <p>No products match your filters.</p>
                    <a href="{{ $clearUrl }}" class="mt-2 inline-block underline underline-offset-2">Clear all filters</a>
                </div>
            @else
                <ul class="mt-6 grid grid-cols-2 gap-6 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <li>
                            <div class="relative aspect-square overflow-hidden rounded-xl bg-tile">
                                <a href="{{ route('products.show', $product) }}" class="block size-full">
                                    <img src="{{ $imageUrl($product->image) }}" alt="{{ $product->name }}" loading="lazy" class="size-full object-cover">
                                </a>
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
                                    <span class="size-3 rounded-full border border-black/10" title="{{ $variant->color }}"
                                          style="background: {{ $swatch($variant->color) }}"></span>
                                @endforeach
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-8">{{ $products->links() }}</div>
            @endif
        </section>
    </div>
</x-layouts.shop>