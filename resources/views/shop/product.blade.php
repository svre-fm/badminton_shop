@php
    // ProductController ส่งคอลัมน์ product และความสัมพันธ์ product_variant, racket_tension, review ตาม EER
    // $max_tension เป็นค่าคำนวณจาก racket_tension.tension; schema ต้องเพิ่ม tension ใน cart_item/order_item ตามข้อตกลง
    $minimum_tension = 18;
    $product = $product ?? null;
    $product_type_by_route = [
        'badminton-racket' => 'RACKET',
        'badminton-string' => 'STRING',
        'grip' => 'GRIP',
        'shuttlecock' => 'SHUTTLECOCK',
    ];
    $product_type = data_get($product, 'product_type', $product_type_by_route[request()->route('category')] ?? null);
    $requires_tension = $product_type === 'RACKET';
    $max_tension = isset($max_tension) && (int) $max_tension >= $minimum_tension ? (int) $max_tension : null;
    $reviews = collect($reviews ?? []);
    $star_counts = collect(range(1, 5))->mapWithKeys(fn ($star) => [$star => $reviews->where('star', $star)->count()]);
@endphp

<x-layouts.shop :title="data_get($product, 'name', 'Product')" active="shop">
    @if ($product)
    <div class="mt-8 grid gap-8 md:grid-cols-[6rem_1fr_1fr]">
        {{-- Thumbs --}}
        <div class="order-2 flex gap-3 md:order-1 md:flex-col">
            @foreach (data_get($product, 'images', []) as $image)
                <button type="button" class="aspect-square w-20 overflow-hidden rounded-lg bg-tile md:w-full" aria-label="Product image">
                    <img src="{{ $image }}" alt="" class="size-full object-cover">
                </button>
            @endforeach
        </div>
        <div class="order-1 aspect-square overflow-hidden rounded-xl bg-tile md:order-2">
            @if (data_get($product, 'image'))
                <img src="{{ data_get($product, 'image') }}" alt="{{ data_get($product, 'name') }}" class="size-full object-cover">
            @endif
        </div>

        {{-- Info --}}
        <div
            class="order-3"
            x-data="{
                tension: '',
                minimumTension: @js($minimum_tension),
                maxTension: @js($max_tension),
                tensionError: '',
                requiresTension: @js($requires_tension),
                color: @js(data_get($product, 'product_variants.0.color')),
                quantity: 1,
                notice: '',
                validateTension() {
                    if (!this.requiresTension) {
                        this.tension = 0;
                        this.tensionError = '';
                        return true;
                    }

                    const enteredTension = String(this.tension).trim();
                    const numericTension = Number(enteredTension);

                    if (!enteredTension || !Number.isInteger(numericTension)) {
                        this.tensionError = 'Please enter a whole-number tension.';
                        return false;
                    }

                    if (!Number.isInteger(this.maxTension) || this.maxTension < this.minimumTension) {
                        this.tensionError = 'Tension limits are unavailable for this product. Please try again later.';
                        return false;
                    }

                    if (numericTension < this.minimumTension || numericTension > this.maxTension) {
                        this.tensionError = `Enter a tension from ${this.minimumTension} to ${this.maxTension} lbs.`;
                        return false;
                    }

                    this.tension = numericTension;
                    this.tensionError = '';
                    return true;
                },
                addToCart() {
                    if (!this.validateTension()) return;
                    // ให้ CartController บันทึก product/variant/tension/quantity โดยตรวจราคาและ stock จากฐานข้อมูล
                    this.notice = 'Cart service is not connected yet.';
                },
                buyNow() {
                    if (!this.validateTension()) return;
                    // ให้ CheckoutController สร้าง checkout จากข้อมูลสินค้าฝั่ง server ไม่รับราคา/รายการจาก browser
                    this.notice = 'Checkout service is not connected yet.';
                }
            }"
        >
            <div class="flex items-center gap-3">
                <h1 class="text-4xl font-bold">{{ data_get($product, 'name') }}</h1>
                @if (data_get($product, 'rating'))
                    <span class="rounded border border-star/60 bg-amber-50 px-2 text-lg"><span class="text-star">★</span> {{ data_get($product, 'rating') }}</span>
                @endif
            </div>
            <x-shop.price :amount="data_get($product, 'price')" :decimals="2" class="mt-2 text-4xl font-bold text-brand" />

            @if ($requires_tension)
            <h2 class="mt-6 text-2xl">Tension (lbs)</h2>
            <div class="mt-2 max-w-sm">
                <label for="product-tension" class="mb-2 block text-sm text-muted">
                    @if ($max_tension)
                        Enter a value from {{ $minimum_tension }} to {{ $max_tension }} lbs
                    @else
                        Tension limit is not available for this product yet.
                    @endif
                </label>
                <input
                    id="product-tension"
                    type="number"
                    min="{{ $minimum_tension }}"
                    @if ($max_tension) max="{{ $max_tension }}" @endif
                    step="1"
                    required
                    x-model="tension"
                    @input="tensionError = ''"
                    :aria-invalid="tensionError ? 'true' : 'false'"
                    aria-describedby="tension-help tension-error"
                    class="w-full rounded-lg border border-muted/50 px-4 py-3 text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20"
                    placeholder="e.g. 24"
                >
                <p id="tension-help" class="mt-2 text-sm text-muted">
                    Minimum {{ $minimum_tension }} lbs. Maximum is derived from racket_tension.tension for this racket.
                </p>
                <p id="tension-error" x-cloak x-show="tensionError" x-text="tensionError" role="alert" class="mt-2 text-sm font-medium text-red-700"></p>
            </div>
            @endif

            <h2 class="mt-6 text-2xl">Color</h2>
            <div class="mt-2 flex gap-2">
                @foreach (data_get($product, 'product_variants', []) as $product_variant)
                    <button
                        type="button"
                        @click="color = @js(data_get($product_variant, 'color'))"
                        :aria-pressed="color === @js(data_get($product_variant, 'color'))"
                        aria-label="Select color {{ data_get($product_variant, 'color') }}"
                        :class="color === @js(data_get($product_variant, 'color')) ? 'ring-brand' : 'ring-transparent'"
                        class="grid size-7 place-items-center rounded-full border border-black/10 ring-2 ring-offset-2"
                        style="background-color: {{ data_get($product_variant, 'color') }}"
                    >
                        <svg x-show="color === @js(data_get($product_variant, 'color'))" class="size-4 text-white mix-blend-difference" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="m4 10 4 4 8-8"/></svg>
                    </button>
                @endforeach
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-3">
                    <button type="button" @click="quantity = Math.max(1, Number(quantity) - 1)" aria-label="Decrease quantity"
                            class="grid size-8 place-items-center rounded-full bg-brand text-white">−</button>
                    <input type="number" min="1" step="1" x-model.number="quantity" aria-label="Quantity"
                           class="w-12 border-0 bg-transparent p-0 text-center text-lg [appearance:textfield]">
                    <button type="button" @click="quantity = Math.max(1, Number(quantity) + 1)" aria-label="Increase quantity"
                            class="grid size-8 place-items-center rounded-full bg-brand text-white">+</button>
                </div>
                
                <form
                    method="POST"
                    action="{{ route('cart.store') }}"
                    @submit="if (!validateTension()) $event.preventDefault()"
                >
                    @csrf
                    <input
                        type="hidden"
                        name="product_id"
                        value="{{ data_get($product, 'product_id') }}"
                    >
                    <input type="hidden" name="color" x-model="color">
                    <input type="hidden" name="quantity" x-model="quantity">
                    <input type="hidden" name="tension" x-model="tension">
                    <button
                        type="submit"
                        class="rounded bg-mist px-5 py-2 transition hover:bg-brand hover:text-white"
                    >
                        Add to cart
                    </button>
                </form>
                
                <button type="button" @click="buyNow()" class="rounded bg-ink px-8 py-2 text-white transition hover:bg-brand">Buy</button>
                <x-shop.product-heart />
            </div>
            <p x-cloak x-show="notice" role="status" class="mt-4 text-sm font-medium text-brand">
                <span x-text="notice"></span>
            </p>
        </div>
    </div>

    {{-- Tabs --}}
    <section class="mt-12" x-data="{ tab: 'detail', ratingFilter: null }">
        <div class="flex gap-8 border-b-2 border-mist text-xl" role="tablist">
            <button role="tab" @click="tab = 'detail'" :aria-selected="tab === 'detail'"
                    :class="tab === 'detail' ? 'border-b-2 border-ink text-ink' : 'text-muted'" class="-mb-0.5 pb-2">Product Detail</button>
            <button role="tab" @click="tab = 'reviews'" :aria-selected="tab === 'reviews'"
                    :class="tab === 'reviews' ? 'border-b-2 border-ink text-ink' : 'text-muted'" class="-mb-0.5 pb-2">Rating &amp; Reviews ({{ $reviews->count() }})</button>
        </div>

        <div x-show="tab === 'detail'" class="py-6 text-lg">{{ data_get($product, 'detail') }}</div>

        <div x-show="tab === 'reviews'" x-cloak class="py-6">
            {{-- ใช้ review.star/date/comment ตาม EER; rating เป็นค่าคำนวณจาก review.star ไม่ใช่คอลัมน์ product --}}
            <div class="flex flex-wrap items-center gap-10">
                <div>
                    @if (data_get($product, 'rating'))
                        <p class="text-6xl font-bold">{{ data_get($product, 'rating') }} <span class="text-2xl font-normal text-muted">out of 5</span></p>
                    @endif
                </div>
                <div class="flex flex-wrap gap-3">
                    <button
                        type="button"
                        @click="ratingFilter = null"
                        :aria-pressed="ratingFilter === null"
                        :class="ratingFilter === null ? 'bg-ink text-white' : 'border-ink text-ink'"
                        class="cursor-pointer rounded-full border px-4 py-1 transition hover:bg-ink hover:text-white"
                    >All reviews ({{ $reviews->count() }})</button>
                    @foreach ($star_counts as $star => $count)
                        <button
                            type="button"
                            @click="ratingFilter = {{ $star }}"
                            :aria-pressed="ratingFilter === {{ $star }}"
                            :class="ratingFilter === {{ $star }} ? 'bg-ink text-white' : 'border-ink text-ink'"
                            class="cursor-pointer rounded-full border px-4 py-1 transition hover:bg-ink hover:text-white"
                        >{{ $star }} star ({{ $count }})</button>
                    @endforeach
                </div>
            </div>

            <ul class="mt-8 grid gap-x-12 gap-y-6 md:grid-cols-2">
                @foreach ($reviews as $review)
                    <li x-show="ratingFilter === null || ratingFilter === {{ data_get($review, 'star') }}" class="border-b border-muted/50 pb-4">
                        <div class="flex items-center gap-3">
                            <div>
                                <p class="text-xl">{{ data_get($review, 'customer.name') }} <span class="text-xs text-muted">{{ data_get($review, 'date') }}</span></p>
                                <p class="text-star" aria-label="{{ data_get($review, 'star') }} stars">{{ str_repeat('★', data_get($review, 'star')) }}<span class="text-zinc-200">{{ str_repeat('★', 5 - data_get($review, 'star')) }}</span></p>
                            </div>
                        </div>
                        <p class="mt-3 text-muted">{{ data_get($review, 'comment') }}</p>
                    </li>
                @endforeach
            </ul>
            <p
                x-cloak
                x-show="ratingFilter !== null && ![{{ $reviews->pluck('star')->unique()->implode(',') }}].includes(ratingFilter)"
                class="mt-6 text-center text-muted"
                role="status"
            >No reviews with this rating yet.</p>
        </div>
    </section>
    @else
        <section class="mx-auto mt-12 max-w-3xl rounded-2xl border border-mist bg-white p-8 text-center" role="status">
            <h1 class="text-2xl font-bold text-ink">Product information is not available yet.</h1>
            <p class="mt-2 text-muted">Product details will appear here when connected to the product catalog.</p>
            <a href="{{ route('shop.index') }}" class="mt-5 inline-flex rounded-lg bg-ink px-5 py-3 font-semibold text-white transition hover:bg-brand">Browse products</a>
        </section>
    @endif
</x-layouts.shop>


@if (session('status'))
    <p role="status" class="mt-4 text-sm font-medium text-green-700">
        {{ session('status') }}
    </p>
@endif
@if (session('error'))
    <p role="alert" class="mt-4 text-sm font-medium text-red-700">
        {{ session('error') }}
    </p>
@endif
@if ($errors->any())
    <ul role="alert" class="mt-4 text-sm text-red-700">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif