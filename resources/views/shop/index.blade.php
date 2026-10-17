@php
    // product.product_type คือชนิดตาม EER; ชื่อ route ใช้ slug เพื่อคง URL ฝั่งหน้าเว็บ
    // ProductController ส่ง product, product_variant, filter_groups และ price_range จากฐานข้อมูล
    $product_type_by_route = [
        'badminton-racket' => 'RACKET',
        'badminton-string' => 'STRING',
        'grip' => 'GRIP',
        'shuttlecock' => 'SHUTTLECOCK',
    ];
    $category = request()->route('category', request()->query('category', 'badminton-racket'));
    $category = is_string($category) && isset($product_type_by_route[$category]) ? $category : 'badminton-racket';
    $product_type = $product_type_by_route[$category];
    $category_titles = [
        'badminton-racket' => 'Badminton racket',
        'badminton-string' => 'Badminton string',
        'grip' => 'Grip',
        'shuttlecock' => 'Shuttlecock',
    ];
    $brands = collect($brands ?? [])->all();
    $filter_groups = $filter_groups ?? [];
    $price_range = $price_range ?? [];
    $price_min = isset($price_range['min']) ? (float) $price_range['min'] : 0;
    $price_max = isset($price_range['max']) ? (float) $price_range['max'] : 0;
    $active_filters = $filter_groups[$product_type] ?? [];
    $products = collect($products ?? []);

    $requestedBrands = array_filter((array) request()->query('brand', []), 'is_string');
    $selectedBrands = array_values(array_intersect($requestedBrands, $brands));
    $requestedFilters = request()->query('filters', []);
    $requestedFilters = is_array($requestedFilters) ? $requestedFilters : [];
    $selectedFilters = collect($active_filters)->mapWithKeys(function ($filter) use ($requestedFilters) {
        $requested = $requestedFilters[$filter['key']] ?? [];
        $requested = is_array($requested) ? $requested : [$requested];

        $requested = array_filter($requested, 'is_string');

        return [$filter['key'] => array_values(array_intersect($requested, array_keys($filter['options'])))];
    })->all();
    foreach ($active_filters as $filter) {
        if (isset($filter['showWhen']) && ! in_array($filter['showWhen']['value'], $selectedFilters[$filter['showWhen']['key']], true)) {
            $selectedFilters[$filter['key']] = [];
        }
    }
    $price_gap = isset($price_gap) ? (float) $price_gap : 0;
    $minPrice = is_numeric(request()->query('min')) ? max($price_min, min($price_max, (float) request()->query('min'))) : null;
    $maxPrice = is_numeric(request()->query('max')) ? max($price_min, min($price_max, (float) request()->query('max'))) : null;

    if ($price_gap > 0 && $minPrice !== null && $maxPrice === null && $minPrice > $price_max - $price_gap) {
        $minPrice = $price_max - $price_gap;
    } elseif ($price_gap > 0 && $minPrice === null && $maxPrice !== null && $maxPrice < $price_min + $price_gap) {
        $maxPrice = $price_min + $price_gap;
    } elseif ($price_gap > 0 && $minPrice !== null && $maxPrice !== null && $maxPrice - $minPrice < $price_gap) {
        if ($minPrice + $price_gap <= $price_max) {
            $maxPrice = $minPrice + $price_gap;
        } else {
            $minPrice = $maxPrice - $price_gap;
        }
    }
@endphp

<x-layouts.shop :title="$category_titles[$category]" active="shop">
    <div
        class="mt-8 grid gap-6 lg:gap-8"
        :class="products.length ? 'lg:grid-cols-[17rem_1fr]' : 'lg:grid-cols-1'"
        x-data="{
            category: @js($category),
            product_type: @js($product_type),
            categoryTitles: @js($category_titles),
            swatchPalette: @js(\App\Support\ProductColorSwatch::palette()),
            products: @js($products),
            brandOptions: @js($brands),
            groups: @js($active_filters),
            filters: {
                brand: @js($selectedBrands),
                ...@js($selectedFilters),
                min: @js($minPrice),
                max: @js($maxPrice),
            },
            priceMin: @js($price_min),
            priceMax: @js($price_max),
            priceStep: 50,
            priceGap: @js($price_gap),
            draggingPriceHandle: null,
            sort: 'latest',
            sortOpen: false,
            sortOptions: [
                { value: 'latest', label: 'Latest' },
                { value: 'sales', label: 'Top sales' },
                { value: 'low', label: 'Price: low to high' },
                { value: 'high', label: 'Price: high to low' },
            ],
            swatchStyle(value) {
                const colors = String(value ?? '').split('/').map(color => this.swatchPalette[color.trim().toLowerCase()] ?? '#808080');
                if (colors.length === 1) return colors[0];
                const segment = 100 / colors.length;
                const stops = colors.map((color, index) => `${color} ${index * segment}% ${(index + 1) * segment}%`);
                return `linear-gradient(90deg, ${stops.join(', ')})`;
            },
            get priceLow() {
                return this.filters.min === null || this.filters.min === ''
                    ? this.priceMin
                    : Math.min(this.priceMax, Math.max(this.priceMin, Number(this.filters.min)));
            },
            get priceHigh() {
                return this.filters.max === null || this.filters.max === ''
                    ? this.priceMax
                    : Math.max(this.priceMin, Math.min(this.priceMax, Number(this.filters.max)));
            },
            get priceRangeStyle() {
                const start = ((this.priceLow - this.priceMin) / (this.priceMax - this.priceMin)) * 100;
                const end = ((this.priceHigh - this.priceMin) / (this.priceMax - this.priceMin)) * 100;

                return `left: ${start}%; right: ${100 - end}%`;
            },
            get priceLowPosition() {
                return ((this.priceLow - this.priceMin) / (this.priceMax - this.priceMin)) * 100;
            },
            get priceHighPosition() {
                return ((this.priceHigh - this.priceMin) / (this.priceMax - this.priceMin)) * 100;
            },
            snapPrice(value) {
                const steps = Math.round((value - this.priceMin) / this.priceStep);

                return Math.min(this.priceMax, this.priceMin + steps * this.priceStep);
            },
            setPriceMin(value) {
                const min = Math.max(this.priceMin, Math.min(this.priceHigh - this.priceGap, this.snapPrice(value)));
                this.filters.min = min === this.priceMin ? null : min;
            },
            setPriceMax(value) {
                const max = Math.min(this.priceMax, Math.max(this.priceLow + this.priceGap, this.snapPrice(value)));
                this.filters.max = max === this.priceMax ? null : max;
            },
            updatePriceMin(value) {
                if (value === '') this.filters.min = null;
                else this.setPriceMin(Number(value));
                this.syncQuery();
            },
            updatePriceMax(value) {
                if (value === '') this.filters.max = null;
                else this.setPriceMax(Number(value));
                this.syncQuery();
            },
            startPriceDrag(event, handle = null) {
                event.preventDefault();

                const track = event.currentTarget.closest('.price-range-slider');
                const bounds = track.getBoundingClientRect();
                const ratio = Math.min(1, Math.max(0, (event.clientX - bounds.left) / bounds.width));
                const value = this.snapPrice(this.priceMin + ratio * (this.priceMax - this.priceMin));

                if (!handle) {
                    handle = Math.abs(value - this.priceLow) <= Math.abs(value - this.priceHigh) ? 'min' : 'max';
                    this.draggingPriceHandle = handle;
                    this.movePriceDrag(event);
                } else {
                    this.draggingPriceHandle = handle;
                }

                track.setPointerCapture(event.pointerId);
            },
            movePriceDrag(event) {
                if (!this.draggingPriceHandle) return;

                const track = event.currentTarget.closest('.price-range-slider') ?? event.currentTarget;
                const bounds = track.getBoundingClientRect();
                const ratio = Math.min(1, Math.max(0, (event.clientX - bounds.left) / bounds.width));
                const value = this.snapPrice(this.priceMin + ratio * (this.priceMax - this.priceMin));

                if (this.draggingPriceHandle === 'min') this.setPriceMin(value);
                else this.setPriceMax(value);
            },
            endPriceDrag() {
                if (!this.draggingPriceHandle) return;

                this.draggingPriceHandle = null;
                this.syncQuery();
            },
            adjustPriceHandle(handle, event) {
                const current = handle === 'min' ? this.priceLow : this.priceHigh;
                const next = event.key === 'Home'
                    ? this.priceMin
                    : event.key === 'End'
                        ? this.priceMax
                        : current + (event.key === 'ArrowLeft' || event.key === 'ArrowDown' ? -this.priceStep : this.priceStep);

                if (handle === 'min') this.setPriceMin(next);
                else this.setPriceMax(next);

                this.syncQuery();
            },
            matchesValue(product, key, values) {
                if (!values.length) return true;
                const productValue = key === 'color'
                    ? (product.product_variants ?? []).map(product_variant => product_variant.color)
                    : product[key];
                return Array.isArray(productValue)
                    ? values.some(value => productValue.includes(value))
                    : values.includes(String(productValue));
            },
            get results() {
                let results = this.products.filter(product => {
                    const matchesBrand = this.matchesValue(product, 'brand', this.filters.brand);
                    const matchesGroups = this.groups.every(group => this.matchesValue(product, group.key, this.filters[group.key]));
                    const matchesMin = this.filters.min === null || this.filters.min === '' || product.price >= Number(this.filters.min);
                    const matchesMax = this.filters.max === null || this.filters.max === '' || product.price <= Number(this.filters.max);

                    return matchesBrand && matchesGroups && matchesMin && matchesMax;
                });

                if (this.sort === 'latest') results.sort((a, b) => b.product_id - a.product_id);
                if (this.sort === 'sales') results.sort((a, b) => (b.sales_count ?? 0) - (a.sales_count ?? 0));
                if (this.sort === 'low') results.sort((a, b) => a.price - b.price);
                if (this.sort === 'high') results.sort((a, b) => b.price - a.price);

                return results;
            },
            groupIsVisible(group) {
                if (!group.showWhen) return true;
                return this.filters[group.showWhen.key]?.includes(group.showWhen.value) ?? false;
            },
            clearHiddenFilters() {
                this.groups.forEach(group => {
                    if (group.showWhen && !this.groupIsVisible(group)) this.filters[group.key] = [];
                });
            },
            get activeFilters() {
                const selected = this.filters.brand.map(value => ({ key: 'brand', value, label: value }));

                this.groups.forEach(group => {
                    this.filters[group.key].forEach(value => selected.push({
                        key: group.key,
                        value,
                        label: group.options[value],
                    }));
                });

                if (this.filters.min !== null && this.filters.min !== '') selected.push({ key: 'min', value: this.filters.min, label: 'From ฿' + this.filters.min });
                if (this.filters.max !== null && this.filters.max !== '') selected.push({ key: 'max', value: this.filters.max, label: 'To ฿' + this.filters.max });

                return selected;
            },
            syncQuery() {
                this.clearHiddenFilters();
                const url = new URL(window.location.href);
                [...url.searchParams.keys()].filter(key => key === 'brand[]' || key.startsWith('filters[') || ['min', 'max', 'page'].includes(key)).forEach(key => url.searchParams.delete(key));
                this.filters.brand.forEach(value => url.searchParams.append('brand[]', value));
                this.groups.forEach(group => this.filters[group.key].forEach(value => url.searchParams.append('filters[' + group.key + '][]', value)));
                if (this.filters.min !== null && this.filters.min !== '') url.searchParams.set('min', this.filters.min);
                if (this.filters.max !== null && this.filters.max !== '') url.searchParams.set('max', this.filters.max);
                window.history.replaceState({}, '', url);
            },
            removeFilter(filter) {
                if (filter.key === 'min' || filter.key === 'max') {
                    this.filters[filter.key] = null;
                } else {
                    this.filters[filter.key] = this.filters[filter.key].filter(value => value !== filter.value);
                }
                this.syncQuery();
            },
            clearFilters() {
                this.filters = Object.fromEntries([
                    ['brand', []],
                    ...this.groups.map(group => [group.key, []]),
                    ['min', null],
                    ['max', null],
                ]);
                this.syncQuery();
            },
        }"
    >
        <aside x-show="products.length" class="h-fit rounded-2xl border border-mist bg-page p-4 sm:p-5" aria-label="Product filters">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-bold text-ink">Filter products</h2>
                <button
                    type="button"
                    x-show="activeFilters.length"
                    @click="clearFilters()"
                    class="text-sm text-brand underline underline-offset-2"
                >Clear all</button>
            </div>

            <div class="divide-y divide-mist">
                <section class="py-3 first:pt-0" x-data="{ open: true }">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center justify-between font-semibold text-ink">
                        <span>Brand</span>
                        <svg class="size-4 text-muted transition-transform duration-200" :class="open ? 'rotate-90' : ''" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m7 4 6 6-6 6"/>
                        </svg>
                    </button>
                    <div x-show="open" class="mt-3 space-y-2.5">
                        <template x-for="brand in brandOptions" :key="brand">
                            <label class="flex cursor-pointer items-center gap-3 text-sm text-muted hover:text-ink">
                                <input type="checkbox" name="brand[]" :value="brand" x-model="filters.brand" @change="syncQuery()" class="size-4 rounded border-muted accent-brand focus:ring-brand">
                                <span class="flex-1" x-text="brand"></span>
                                <span class="text-xs" x-text="products.filter(product => product.brand === brand).length"></span>
                            </label>
                        </template>
                    </div>
                </section>

                <template x-for="group in groups" :key="group.key">
                    <section x-show="groupIsVisible(group)" class="py-3">
                        <button type="button" @click="group.open = !group.open" :aria-expanded="group.open !== false" class="flex w-full items-center justify-between font-semibold text-ink">
                            <span x-text="group.label"></span>
                            <svg class="size-4 text-muted transition-transform duration-200" :class="group.open !== false ? 'rotate-90' : ''" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m7 4 6 6-6 6"/>
                            </svg>
                        </button>
                        <div x-show="group.open !== false" class="mt-3 space-y-2.5">
                            <template x-if="group.type === 'color'">
                                <div class="flex flex-wrap gap-3">
                                    <template x-for="(label, value) in group.options" :key="value">
                                        <label class="cursor-pointer" :aria-label="label" :title="label">
                                            <input type="checkbox" :name="'filters[' + group.key + '][]'" :value="value" x-model="filters[group.key]" @change="syncQuery()" class="peer sr-only">
                                            <span class="grid size-8 place-items-center rounded-full border border-black/10 ring-2 ring-transparent ring-offset-2 peer-checked:ring-brand peer-focus-visible:ring-brand" :style="'background: ' + swatchStyle(value)">
                                                <svg x-show="filters[group.key].includes(value)" class="size-4 text-white drop-shadow" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="m4 10 4 4 8-8"/></svg>
                                            </span>
                                            <span class="sr-only" x-text="label"></span>
                                        </label>
                                    </template>
                                </div>
                            </template>
                            <template x-if="group.type !== 'color'">
                                <template x-for="(label, value) in group.options" :key="value">
                                    <label class="flex cursor-pointer items-start gap-3 text-sm text-muted hover:text-ink">
                                        <input type="checkbox" :name="'filters[' + group.key + '][]'" :value="value" x-model="filters[group.key]" @change="syncQuery()" class="mt-0.5 size-4 rounded border-muted accent-brand focus:ring-brand">
                                        <span class="flex-1 leading-5" x-text="label"></span>
                                        <span class="text-xs" x-text="products.filter(product => String(product[group.key]) === value).length"></span>
                                    </label>
                                </template>
                            </template>
                        </div>
                    </section>
                </template>

                <section x-show="priceMax > priceMin" class="py-3" x-data="{ open: true }">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center justify-between font-semibold text-ink">
                        <span>Price range</span>
                        <svg class="size-4 text-muted transition-transform duration-200" :class="open ? 'rotate-90' : ''" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m7 4 6 6-6 6"/>
                        </svg>
                    </button>
                    <div x-show="open" class="mt-4">
                        <div
                            class="price-range-slider relative mx-2 h-5 select-none"
                            role="group"
                            aria-label="Price range slider"
                            @pointerdown="startPriceDrag($event)"
                            @pointermove="movePriceDrag($event)"
                            @pointerup="endPriceDrag()"
                            @pointercancel="endPriceDrag()"
                        >
                            <div class="absolute inset-x-0 top-1/2 h-1 -translate-y-1/2 rounded-full bg-mist"></div>
                            <div class="absolute top-1/2 h-1 -translate-y-1/2 rounded-full bg-ink" :style="priceRangeStyle"></div>
                            <button
                                type="button"
                                role="slider"
                                aria-label="Minimum price"
                                :aria-valuemin="priceMin"
                                :aria-valuemax="priceHigh - priceGap"
                                :aria-valuenow="priceLow"
                                :aria-valuetext="'฿' + priceLow"
                                :style="{ left: priceLowPosition + '%' }"
                                :class="draggingPriceHandle === 'min' ? 'cursor-grabbing' : 'cursor-grab'"
                                @pointerdown.stop="startPriceDrag($event, 'min')"
                                @keydown.left.prevent="adjustPriceHandle('min', $event)"
                                @keydown.right.prevent="adjustPriceHandle('min', $event)"
                                @keydown.down.prevent="adjustPriceHandle('min', $event)"
                                @keydown.up.prevent="adjustPriceHandle('min', $event)"
                                @keydown.home.prevent="adjustPriceHandle('min', $event)"
                                @keydown.end.prevent="adjustPriceHandle('min', $event)"
                            ></button>
                            <button
                                type="button"
                                role="slider"
                                aria-label="Maximum price"
                                :aria-valuemin="priceLow + priceGap"
                                :aria-valuemax="priceMax"
                                :aria-valuenow="priceHigh"
                                :aria-valuetext="'฿' + priceHigh"
                                :style="{ left: priceHighPosition + '%' }"
                                :class="draggingPriceHandle === 'max' ? 'cursor-grabbing' : 'cursor-grab'"
                                @pointerdown.stop="startPriceDrag($event, 'max')"
                                @keydown.left.prevent="adjustPriceHandle('max', $event)"
                                @keydown.right.prevent="adjustPriceHandle('max', $event)"
                                @keydown.down.prevent="adjustPriceHandle('max', $event)"
                                @keydown.up.prevent="adjustPriceHandle('max', $event)"
                                @keydown.home.prevent="adjustPriceHandle('max', $event)"
                                @keydown.end.prevent="adjustPriceHandle('max', $event)"
                            ></button>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-6">
                            <label class="text-xs text-muted">
                                From
                                <span class="mt-1 flex items-center rounded-full border border-mist bg-white px-3 py-1.5 text-sm text-ink focus-within:border-brand focus-within:ring-2 focus-within:ring-brand/20">
                                    <span class="mr-1">฿</span>
                                    <input
                                        type="number"
                                        :min="priceMin"
                                        :max="priceHigh - priceGap"
                                        name="min"
                                        :value="filters.min ?? priceMin"
                                        @change="updatePriceMin($event.target.value)"
                                        class="w-full min-w-0 border-0 bg-transparent p-0 text-ink outline-none"
                                    >
                                </span>
                            </label>
                            <label class="text-xs text-muted">
                                To
                                <span class="mt-1 flex items-center rounded-full border border-mist bg-white px-3 py-1.5 text-sm text-ink focus-within:border-brand focus-within:ring-2 focus-within:ring-brand/20">
                                    <span class="mr-1">฿</span>
                                    <input
                                        type="number"
                                        :min="priceLow + priceGap"
                                        :max="priceMax"
                                        name="max"
                                        :value="filters.max ?? priceMax"
                                        @change="updatePriceMax($event.target.value)"
                                        class="w-full min-w-0 border-0 bg-transparent p-0 text-ink outline-none"
                                    >
                                </span>
                            </label>
                        </div>
                    </div>
                </section>
            </div>
        </aside>

        <section aria-labelledby="shop-title">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 id="shop-title" class="text-3xl font-bold text-ink" x-text="categoryTitles[category]"></h1>
                    <p class="mt-1 text-sm text-muted">
                        Showing <b class="text-ink" x-text="results.length"></b> of <b class="text-ink" x-text="products.length"></b> products
                    </p>
                </div>

                <div class="flex items-center gap-2 text-sm text-muted">
                    <span>Sort by</span>
                    <div class="relative" @click.outside="sortOpen = false">
                        <button
                            type="button"
                            id="sort-trigger"
                            aria-haspopup="listbox"
                            aria-controls="sort-options"
                            :aria-expanded="sortOpen"
                            @click="sortOpen = !sortOpen"
                            @keydown.escape.prevent="sortOpen = false"
                            @keydown.arrow-down.prevent="sortOpen = true"
                            class="flex min-w-40 items-center justify-between gap-4 rounded-full bg-mist py-2 pl-4 pr-3 text-ink transition hover:bg-mist/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand"
                        >
                            <span x-text="sortOptions.find(option => option.value === sort)?.label"></span>
                            <svg class="size-4 shrink-0 text-ink transition-transform duration-200" :class="sortOpen ? 'rotate-90' : ''" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m7 4 6 6-6 6"/>
                            </svg>
                        </button>
                        <div
                            x-show="sortOpen"
                            x-cloak
                            x-transition.opacity
                            id="sort-options"
                            role="listbox"
                            aria-labelledby="sort-trigger"
                            class="absolute right-0 z-20 mt-2 min-w-full overflow-hidden rounded-xl border border-mist bg-white py-1 text-ink shadow-lg"
                        >
                            <template x-for="option in sortOptions" :key="option.value">
                                <button
                                    type="button"
                                    role="option"
                                    :aria-selected="sort === option.value"
                                    @click="sort = option.value; sortOpen = false"
                                    class="block w-full whitespace-nowrap px-4 py-2 text-left hover:bg-mist focus-visible:bg-mist focus-visible:outline-none"
                                    :class="sort === option.value ? 'font-semibold text-brand' : ''"
                                    x-text="option.label"
                                ></button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="activeFilters.length" x-cloak class="mt-4 flex flex-wrap items-center gap-2">
                <span class="mr-1 text-sm text-muted">Selected:</span>
                <template x-for="filter in activeFilters" :key="filter.key + filter.value">
                    <button type="button" @click="removeFilter(filter)" class="inline-flex items-center gap-2 rounded-full border border-mist bg-white px-3 py-1 text-xs text-ink transition hover:border-brand">
                        <span x-text="filter.label"></span>
                        <span aria-hidden="true" class="text-muted">×</span>
                    </button>
                </template>
                <button type="button" @click="clearFilters()" class="ml-1 text-xs text-brand underline underline-offset-2">Clear</button>
            </div>

            <div x-show="results.length" class="mt-6 grid grid-cols-2 gap-x-4 gap-y-7 sm:gap-6 md:grid-cols-3 xl:grid-cols-4">
                <template x-for="product in results" :key="product.product_id">
                    <article class="group relative w-full">
                        <div class="relative aspect-square overflow-hidden rounded-xl bg-tile ring-2 ring-transparent transition group-hover:ring-ink">
                            <template x-if="product.image">
                                <img :src="product.image" :alt="product.name" loading="lazy" class="size-full object-cover">
                            </template>
                            <template x-if="!product.image">
                                <div class="grid size-full place-items-center bg-page text-sm font-medium text-muted" x-text="categoryTitles[category]"></div>
                            </template>
                            <a :href="'/product/' + category + '/' + product.product_id" class="absolute inset-0 z-0" :aria-label="product.name"></a>
                            <span x-show="product.rating !== null && product.rating !== undefined" class="absolute bottom-2 right-2 z-10 flex items-center gap-1 rounded border border-star/60 bg-amber-50 px-1.5 text-xs font-medium">
                                <span class="text-star">★</span><span x-text="product.rating"></span>
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-muted" x-text="product.brand"></p>
                        <div class="flex items-baseline justify-between gap-2 text-sm">
                            <h2 class="truncate font-medium text-ink" x-text="product.name"></h2>
                            <span class="shrink-0 text-muted" x-text="'฿' + new Intl.NumberFormat('th-TH').format(product.price)"></span>
                        </div>
                        <template x-if="product_type === 'RACKET' && product.product_variants?.length">
                            <div class="mt-2 flex items-center gap-1.5" aria-label="Product color">
                                <template x-for="product_variant in product.product_variants" :key="product_variant.product_id + product_variant.color">
                                    <span class="size-3 rounded-full border border-black/10" :style="'background: ' + swatchStyle(product_variant.color)" :title="product_variant.color"></span>
                                </template>
                            </div>
                        </template>
                    </article>
                </template>
            </div>

            <div x-show="!results.length" x-cloak class="mt-8 rounded-2xl border border-dashed border-mist px-6 py-16 text-center">
                <p class="text-lg font-semibold text-ink" x-text="products.length ? 'No products match these filters' : 'No products are available in this category yet.'"></p>
                <p x-show="products.length" class="mt-2 text-sm text-muted">Try changing or clearing your selected filters.</p>
                <button x-show="products.length" type="button" @click="clearFilters()" class="mt-4 rounded-full bg-ink px-5 py-2 text-sm text-white">Clear filters</button>
            </div>
        </section>
    </div>
</x-layouts.shop>
