@php
    $tensions = ['20–23 lbs', '24–26 lbs', '> 27 lbs'];
    $stars = [5 => 120, 4 => 30, 3 => 6, 2 => 2, 1 => 1];
    $reviews = collect(range(1, 4))->map(fn ($i) => ['name' => 'Name', 'when' => 'today', 'rating' => [5, 5, 4, 3][$i - 1], 'text' => 'Review text goes here.']);
@endphp

<x-layouts.shop title="Badminton racket" active="shop">
    <div class="mt-8 grid gap-8 md:grid-cols-[6rem_1fr_1fr]">
        {{-- Thumbs --}}
        <div class="order-2 flex gap-3 md:order-1 md:flex-col">
            @foreach (range(1, 3) as $t)
                <button type="button" class="aspect-square w-20 overflow-hidden rounded-lg bg-tile md:w-full" aria-label="Image {{ $t }}">
                    <img src="{{ asset('images/badminton-racket.jpg') }}" alt="" class="size-full object-cover">
                </button>
            @endforeach
        </div>
        <div class="order-1 aspect-square overflow-hidden rounded-xl bg-tile md:order-2">
            <img src="{{ asset('images/badminton-racket.jpg') }}" alt="Badminton racket" class="size-full object-cover">
        </div>

        {{-- Info --}}
        <div
            class="order-3"
            x-data="{
                tension: '20–23 lbs',
                color: '#000000',
                quantity: 1,
                notice: '',
                get item() {
                    const category = @js(request()->route('category', 'badminton-racket'));
                    const slug = @js(request()->route('slug', 'badminton-racket-1'));
                    const requestedQuantity = Number(this.quantity);
                    return {
                        id: [slug, this.color, this.tension].join('|'),
                        slug,
                        category,
                        name: 'Badminton racket',
                        price: 100,
                        color: this.color,
                        tension: this.tension,
                        variant: this.color + ', ' + this.tension,
                        qty: Number.isFinite(requestedQuantity) ? Math.max(1, Math.floor(requestedQuantity)) : 1,
                    };
                },
                readCart() {
                    const value = JSON.parse(localStorage.getItem('badminton-shop-cart') || '[]');
                    if (!Array.isArray(value) || !value.every(entry =>
                        entry && typeof entry.id === 'string' && typeof entry.name === 'string' &&
                        typeof entry.variant === 'string' && Number.isFinite(entry.price) && entry.price >= 0 &&
                        Number.isInteger(entry.qty) && entry.qty > 0
                    )) throw new Error('Saved cart data is invalid.');
                    return value;
                },
                addToCart() {
                    try {
                        const cart = this.readCart();
                        const item = this.item;
                        const existing = cart.find(entry => entry.id === item.id);
                        if (existing) existing.qty += item.qty;
                        else cart.push(item);
                        localStorage.setItem('badminton-shop-cart', JSON.stringify(cart));
                        this.notice = 'Added to cart.';
                    } catch (error) {
                        this.notice = 'Could not update your cart on this device. Please try again.';
                    }
                },
                buyNow() {
                    try {
                        localStorage.setItem('badminton-shop-buy-now', JSON.stringify([this.item]));
                        window.location.href = @js(route('checkout') . '?buy_now=1');
                    } catch (error) {
                        this.notice = 'Could not prepare checkout on this device. Please try again.';
                    }
                }
            }"
        >
            <div class="flex items-center gap-3">
                <h1 class="text-4xl font-bold">Badminton racket</h1>
                <span class="rounded border border-star/60 bg-amber-50 px-2 text-lg"><span class="text-star">★</span> 4.9</span>
            </div>
            <x-shop.price :amount="100" :decimals="2" class="mt-2 text-4xl font-bold text-brand" />

            <h2 class="mt-6 text-2xl">Tension</h2>
            <div class="mt-2 flex flex-wrap gap-3">
                @foreach ($tensions as $i => $t)
                    <button type="button" @click="tension = @js($t)" :aria-pressed="tension === @js($t)"
                            :class="tension === @js($t) ? 'bg-ink text-white' : 'border border-ink'"
                            class="rounded-full px-4 py-1">{{ $t }}</button>
                @endforeach
            </div>

            <h2 class="mt-6 text-2xl">Color</h2>
            <div class="mt-2 flex gap-2">
                @foreach (['#000000', '#f9c9ff', '#ffd982', '#4072af'] as $color)
                    <button
                        type="button"
                        @click="color = @js($color)"
                        :aria-pressed="color === @js($color)"
                        aria-label="Select color {{ $color }}"
                        :class="color === @js($color) ? 'ring-brand' : 'ring-transparent'"
                        class="grid size-7 place-items-center rounded-full border border-black/10 ring-2 ring-offset-2"
                        style="background-color: {{ $color }}"
                    >
                        <svg x-show="color === @js($color)" class="size-4 text-white mix-blend-difference" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="m4 10 4 4 8-8"/></svg>
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
                <button type="button" @click="addToCart()" class="rounded bg-mist px-5 py-2 transition hover:bg-brand hover:text-white">Add to cart</button>
                <button type="button" @click="buyNow()" class="rounded bg-ink px-8 py-2 text-white transition hover:bg-brand">Buy</button>
                <x-shop.product-heart />
            </div>
            <p x-cloak x-show="notice" role="status" class="mt-4 text-sm font-medium text-brand">
                <span x-text="notice"></span>
                <a x-show="notice === 'Added to cart.'" href="{{ route('cart') }}" class="ml-2 underline underline-offset-2">View cart</a>
            </p>
        </div>
    </div>

    {{-- Tabs --}}
    <section class="mt-12" x-data="{ tab: 'detail' }">
        <div class="flex gap-8 border-b-2 border-mist text-xl" role="tablist">
            <button role="tab" @click="tab = 'detail'" :aria-selected="tab === 'detail'"
                    :class="tab === 'detail' ? 'border-b-2 border-ink text-ink' : 'text-muted'" class="-mb-0.5 pb-2">Product Detail</button>
            <button role="tab" @click="tab = 'reviews'" :aria-selected="tab === 'reviews'"
                    :class="tab === 'reviews' ? 'border-b-2 border-ink text-ink' : 'text-muted'" class="-mb-0.5 pb-2">Rating &amp; Reviews ({{ array_sum($stars) }})</button>
        </div>

        <div x-show="tab === 'detail'" class="py-6 text-lg">Product description goes here.</div>

        <div x-show="tab === 'reviews'" x-cloak class="py-6">
            <div class="flex flex-wrap items-center gap-10">
                <div>
                    <p class="text-6xl font-bold">4.9 <span class="text-2xl font-normal text-muted">out of 5</span></p>
                    <p class="text-3xl text-star">★★★★★</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    @foreach ($stars as $s => $n)
                        <button type="button" class="rounded-full border border-ink px-4 py-1">{{ $s }} star ({{ $n }})</button>
                    @endforeach
                </div>
            </div>

            <ul class="mt-8 grid gap-x-12 gap-y-6 md:grid-cols-2">
                @foreach ($reviews as $r)
                    <li class="border-b border-muted/50 pb-4">
                        <div class="flex items-center gap-3">
                            <span class="size-14 rounded-full bg-zinc-300"></span>
                            <div>
                                <p class="text-xl">{{ $r['name'] }} <span class="text-xs text-muted">{{ $r['when'] }}</span></p>
                                <p class="text-star" aria-label="{{ $r['rating'] }} stars">{{ str_repeat('★', $r['rating']) }}<span class="text-zinc-200">{{ str_repeat('★', 5 - $r['rating']) }}</span></p>
                            </div>
                        </div>
                        <p class="mt-3 text-muted">{{ $r['text'] }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
</x-layouts.shop>
