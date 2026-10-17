@php
    // $product, $stars, $reviews, $isFavorite ถูกส่งมาจาก ProductController@show

    // ชื่อสี (productvariants.color) → รหัสสี ชื่อสีผสมคั่นด้วย "/" เช่น Black/Orange จะแสดงเป็นสีครึ่งต่อครึ่ง
    $colorHex = [
        'black'      => '#000000',
        'white'      => '#ffffff',
        'blue'       => '#4072af',
        'light blue' => '#8ec5ff',
        'red'        => '#e53935',
        'orange'     => '#ff9800',
        'yellow'     => '#ffd982',
        'lime'       => '#b6e22e',
        'green'      => '#43a047',
        'pink'       => '#f9c9ff',
        'cherry'     => '#b0003a',
        'aqua'       => '#3dd6d0',
    ];
    $hex = fn ($c) => str_starts_with(trim($c), '#') ? trim($c) : ($colorHex[strtolower(trim($c))] ?? '#cccccc');
    $swatch = function ($c) use ($hex) {
        $parts = array_values(array_filter(array_map('trim', explode('/', $c))));
        if (count($parts) < 2) {
            return $hex($parts[0] ?? $c);
        }
        return 'linear-gradient(135deg, ' . $hex($parts[0]) . ' 50%, ' . $hex($parts[1]) . ' 50%)';
    };

    // สีเริ่มต้น: สีแรกที่ยังมีสต็อก ถ้าไม่มีเลยใช้สีแรก
    $defaultVariant = $product->variants->firstWhere('stock', '>', 0) ?? $product->variants->first();

    $imageUrl = \Illuminate\Support\Str::startsWith($product->image, ['http://', 'https://', '/'])
        ? $product->image
        : asset($product->image);

    $avg = (float) ($product->reviews_avg_star ?? 0);
    $reviewTotal = $stars->sum();

    // Tension (lbs): เฉพาะไม้แบด เลือกได้ตั้งแต่ $minTension ถึง badminton_rackets.max_tension ของรุ่นนั้น
    // สินค้าอื่นเก็บเป็น 0 (ตรงกับ order_items.tension)
    $minTension = 20;
    $maxTension = (int) ($product->badmintonRacket?->max_tension ?? 0);
    $hasTension = $product->product_type === 'RACKET' && $maxTension >= $minTension;
@endphp

<x-layouts.shop :title="$product->name" active="shop">
    <div class="mt-8 grid gap-8 md:grid-cols-2">
        {{-- Image --}}
        <div class="aspect-square overflow-hidden rounded-xl bg-tile">
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="size-full object-cover">
        </div>

        {{-- Info --}}
        <div
            x-data="{
                tension: @js($hasTension ? $minTension : 0),
                color: @js($defaultVariant?->color),
                stocks: @js($product->variants->pluck('stock', 'color')),
                quantity: 1,
                notice: '',
                get maxQty() { return Number(this.stocks[this.color] ?? 0); },
                clampQty() {
                    const n = Math.floor(Number(this.quantity));
                    this.quantity = Number.isFinite(n) ? Math.min(Math.max(1, n), Math.max(1, this.maxQty)) : 1;
                },
                get item() {
                    this.clampQty();
                    const slug = @js((string) $product->product_id);
                    return {
                        id: [slug, this.color, this.tension].filter(Boolean).join('|'),
                        slug,
                        category: @js(strtolower($product->product_type)),
                        name: @js($product->name),
                        price: @js((float) $product->price),
                        color: this.color,
                        tension: this.tension,
                        variant: [this.color, this.tension ? this.tension + ' lbs' : null].filter(Boolean).join(', '),
                        qty: this.quantity,
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
                    if (this.maxQty < 1) { this.notice = 'This color is out of stock.'; return; }
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
                    if (this.maxQty < 1) { this.notice = 'This color is out of stock.'; return; }
                    try {
                        localStorage.setItem('badminton-shop-buy-now', JSON.stringify([this.item]));
                        window.location.href = @js(Route::has('checkout') ? route('checkout') . '?buy_now=1' : '#');
                    } catch (error) {
                        this.notice = 'Could not prepare checkout on this device. Please try again.';
                    }
                }
            }"
        >
            <div class="flex items-center gap-3">
                <h1 class="text-4xl font-bold">{{ $product->name }}</h1>
                <span class="rounded border border-star/60 bg-amber-50 px-2 text-lg">
                    <span class="text-star">★</span> {{ number_format($avg, 1) }}
                </span>
            </div>
            <p class="mt-1 text-muted">{{ $product->brand }}</p>
            <x-shop.price :amount="$product->price" :decimals="2" class="mt-2 text-4xl font-bold text-brand" />

            {{-- Tension (lbs): เฉพาะไม้แบด --}}
            @if ($hasTension)
                <h2 class="mt-6 text-2xl">Tension</h2>
                <div class="mt-2 flex items-center gap-3">
                    <select x-model.number="tension" aria-label="String tension in lbs"
                            class="rounded-full border border-ink bg-transparent px-4 py-1">
                        @foreach (range($minTension, $maxTension) as $lbs)
                            <option value="{{ $lbs }}">{{ $lbs }} lbs</option>
                        @endforeach
                    </select>
                    <span class="text-sm text-muted">Max {{ $maxTension }} lbs</span>
                </div>
            @endif

            {{-- Color: จาก product_variant --}}
            @if ($product->variants->isNotEmpty())
                <h2 class="mt-6 text-2xl">Color</h2>
                <div class="mt-2 flex gap-2">
                    @foreach ($product->variants as $v)
                        <button
                            type="button"
                            @disabled($v->stock < 1)
                            @click="color = @js($v->color); clampQty()"
                            :aria-pressed="color === @js($v->color)"
                            aria-label="Select color {{ $v->color }}{{ $v->stock < 1 ? ' (out of stock)' : '' }}"
                            title="{{ $v->color }}{{ $v->stock < 1 ? ' – out of stock' : '' }}"
                            :class="color === @js($v->color) ? 'ring-brand' : 'ring-transparent'"
                            class="grid size-7 place-items-center rounded-full border border-black/10 ring-2 ring-offset-2 {{ $v->stock < 1 ? 'cursor-not-allowed opacity-40' : '' }}"
                            style="background: {{ $swatch($v->color) }}"
                        >
                            <svg x-show="color === @js($v->color)" class="size-4 text-white mix-blend-difference" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="m4 10 4 4 8-8"/></svg>
                        </button>
                    @endforeach
                </div>
                <p class="mt-2 text-sm text-muted" x-show="color" x-cloak>
                    <span x-show="maxQty > 0" x-text="maxQty + ' in stock'"></span>
                    <span x-show="maxQty < 1" class="text-red-600">Out of stock</span>
                </p>
            @else
                <p class="mt-6 text-red-600">Out of stock</p>
            @endif

            <div class="mt-8 flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-3">
                    <button type="button" @click="quantity = Number(quantity) - 1; clampQty()" aria-label="Decrease quantity"
                            class="grid size-8 place-items-center rounded-full bg-brand text-white">−</button>
                    <input type="number" min="1" step="1" x-model.number="quantity" @change="clampQty()" aria-label="Quantity"
                           class="w-12 border-0 bg-transparent p-0 text-center text-lg [appearance:textfield]">
                    <button type="button" @click="quantity = Number(quantity) + 1; clampQty()" aria-label="Increase quantity"
                            class="grid size-8 place-items-center rounded-full bg-brand text-white">+</button>
                </div>
                <button type="button" @click="addToCart()" :disabled="maxQty < 1"
                        class="rounded bg-mist px-5 py-2 transition hover:bg-brand hover:text-white disabled:cursor-not-allowed disabled:opacity-50">Add to cart</button>
                <button type="button" @click="buyNow()" :disabled="maxQty < 1"
                        class="rounded bg-ink px-8 py-2 text-white transition hover:bg-brand disabled:cursor-not-allowed disabled:opacity-50">Buy</button>
                <x-shop.product-heart :product-id="$product->product_id" :active="$isFavorite" />
            </div>
            <p x-cloak x-show="notice" role="status" class="mt-4 text-sm font-medium text-brand">
                <span x-text="notice"></span>
                @if (Route::has('cart'))
                    <a x-show="notice === 'Added to cart.'" href="{{ route('cart') }}" class="ml-2 underline underline-offset-2">View cart</a>
                @endif
            </p>
        </div>
    </div>

    {{-- Tabs --}}
    <section class="mt-12" x-data="{ tab: 'detail', starFilter: null }">
        <div class="flex gap-8 border-b-2 border-mist text-xl" role="tablist">
            <button role="tab" @click="tab = 'detail'" :aria-selected="tab === 'detail'"
                    :class="tab === 'detail' ? 'border-b-2 border-ink text-ink' : 'text-muted'" class="-mb-0.5 pb-2">Product Detail</button>
            <button role="tab" @click="tab = 'reviews'" :aria-selected="tab === 'reviews'"
                    :class="tab === 'reviews' ? 'border-b-2 border-ink text-ink' : 'text-muted'" class="-mb-0.5 pb-2">Rating &amp; Reviews ({{ $reviewTotal }})</button>
        </div>

        <div x-show="tab === 'detail'" class="py-6 text-lg">
            @if (filled($product->detail))
                {!! nl2br(e($product->detail)) !!}
            @else
                <span class="text-muted">No product details yet.</span>
            @endif
        </div>

        <div x-show="tab === 'reviews'" x-cloak class="py-6">
            <div class="flex flex-wrap items-center gap-10">
                <div>
                    <p class="text-6xl font-bold">{{ number_format($avg, 1) }} <span class="text-2xl font-normal text-muted">out of 5</span></p>
                    <p class="text-3xl text-star" aria-label="{{ number_format($avg, 1) }} out of 5 stars">
                        {{ str_repeat('★', (int) round($avg)) }}<span class="text-zinc-200">{{ str_repeat('★', 5 - (int) round($avg)) }}</span>
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    @foreach ($stars as $s => $n)
                        <button type="button"
                                @click="starFilter = starFilter === {{ $s }} ? null : {{ $s }}"
                                :aria-pressed="starFilter === {{ $s }}"
                                :class="starFilter === {{ $s }} ? 'bg-ink text-white' : ''"
                                class="rounded-full border border-ink px-4 py-1">{{ $s }} star ({{ $n }})</button>
                    @endforeach
                </div>
            </div>

            @if ($reviews->isEmpty())
                <p class="mt-8 text-muted">No reviews yet.</p>
            @else
                <ul class="mt-8 grid gap-x-12 gap-y-6 md:grid-cols-2">
                    @foreach ($reviews as $r)
                        <li class="border-b border-muted/50 pb-4" x-show="starFilter === null || starFilter === {{ $r->star }}">
                            <div class="flex items-center gap-3">
                                <span class="size-14 rounded-full bg-zinc-300"></span>
                                <div>
                                    <p class="text-xl">{{ $r->customer?->name ?? 'Customer' }}
                                        <span class="text-xs text-muted">{{ \Illuminate\Support\Carbon::parse($r->date)->diffForHumans() }}</span>
                                    </p>
                                    <p class="text-star" aria-label="{{ $r->star }} stars">{{ str_repeat('★', $r->star) }}<span class="text-zinc-200">{{ str_repeat('★', 5 - $r->star) }}</span></p>
                                </div>
                            </div>
                            @if (filled($r->comment))
                                <p class="mt-3 text-muted">{{ $r->comment }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
</x-layouts.shop>