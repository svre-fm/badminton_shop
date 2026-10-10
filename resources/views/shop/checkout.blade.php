@php
    $shipping = 30;
    $addresses = [
        ['id' => 1, 'name' => 'Name', 'phone' => '0812345678', 'text' => 'Address line 1, District, Province 50000', 'default' => true],
        ['id' => 2, 'name' => 'Name', 'phone' => '0898765432', 'text' => 'Address line 2, District, Province 50000', 'default' => false],
    ];
@endphp

<x-layouts.shop title="Checkout" :fullWidth="true">
    <div
        class="mx-auto w-full max-w-screen-2xl px-4 pb-16 pt-8 sm:px-8 xl:px-12"
        x-data="{
            pay: 'qr',
            modal: false,
            addresses: @js($addresses),
            selectedAddressId: null,
            draftAddressId: null,
            items: [],
            notice: '',
            init() {
                this.selectedAddressId = this.addresses.find(address => address.default)?.id ?? this.addresses[0]?.id ?? null;
                this.draftAddressId = this.selectedAddressId;
                this.loadCheckoutItems();
            },
            loadCheckoutItems() {
                try {
                    const buyNow = new URLSearchParams(window.location.search).get('buy_now') === '1';
                    const key = buyNow ? 'badminton-shop-buy-now' : 'badminton-shop-checkout-items';
                    const saved = JSON.parse(localStorage.getItem(key) || '[]');
                    if (!Array.isArray(saved) || !saved.every(item =>
                        item && typeof item.id === 'string' && typeof item.name === 'string' &&
                        typeof item.variant === 'string' && Number.isFinite(item.price) && item.price >= 0 &&
                        Number.isInteger(item.qty) && item.qty > 0
                    )) throw new Error('Checkout item data is invalid.');
                    this.items = saved;
                    localStorage.removeItem(key);
                } catch (error) {
                    this.notice = 'Could not load checkout items from this device.';
                }
            },
            get selectedAddress() {
                return this.addresses.find(address => address.id === this.selectedAddressId) ?? null;
            },
            openAddressPicker() {
                this.draftAddressId = this.selectedAddressId;
                this.modal = true;
            },
            confirmAddress() {
                if (!this.addresses.some(address => address.id === this.draftAddressId)) return;
                this.selectedAddressId = this.draftAddressId;
                this.modal = false;
            },
            get subtotal() { return this.items.reduce((sum, item) => sum + item.price * item.qty, 0) },
            get totalQuantity() { return this.items.reduce((sum, item) => sum + item.qty, 0) },
            get shipping() { return this.items.length ? {{ $shipping }} : 0 },
            placeOrder() {
                this.notice = 'Checkout preview only: order placement and payment are not connected yet.';
            }
        }"
    >
        <header class="mb-8 border-b border-mist pb-6">
            <h1 class="mt-2 text-4xl font-bold text-ink sm:text-5xl">Checkout</h1>
            <p class="mt-2 text-base text-muted sm:text-lg">Review your items, delivery address, and payment method.</p>
        </header>

        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)] xl:gap-8">
            {{-- Order --}}
            <section class="h-full rounded-2xl border border-mist bg-page p-5 sm:p-7 xl:p-8">
                <div class="flex items-baseline justify-between gap-4 border-b border-mist pb-4">
                    <h2 class="text-2xl font-bold text-ink sm:text-3xl">Product Order</h2>
                    <span class="text-sm text-muted"><span x-text="totalQuantity"></span> items</span>
                </div>
                <template x-if="items.length">
                    <ul class="divide-y divide-mist">
                        <template x-for="item in items" :key="item.id">
                            <li class="flex gap-4 py-5 sm:gap-6">
                                <div class="size-28 shrink-0 overflow-hidden rounded-xl bg-white sm:size-36 xl:size-40">
                                    <img src="{{ asset('images/badminton-racket.jpg') }}" alt="" class="size-full object-cover">
                                </div>
                                <div class="flex min-w-0 flex-1 flex-col justify-center">
                                    <p class="text-xl font-semibold text-ink sm:text-2xl" x-text="item.name"></p>
                                    <p class="mt-2 text-sm text-muted sm:text-base"><span x-text="item.variant"></span></p>
                                    <p class="mt-1 text-sm text-muted sm:text-base">Quantity: <span x-text="item.qty"></span></p>
                                    <p class="mt-3 text-lg font-bold text-brand sm:text-xl" x-text="'฿' + (item.price * item.qty).toFixed(2)"></p>
                                </div>
                            </li>
                        </template>
                    </ul>
                </template>
                <p x-show="!items.length" x-cloak class="mt-5 text-base text-muted">
                    No items selected. <a href="{{ route('cart') }}" class="font-medium text-brand underline">Return to cart</a>
                </p>
            </section>

            <div class="space-y-6">
                {{-- Address --}}
                <section class="rounded-2xl border border-mist bg-white p-5 sm:p-7">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wide text-muted">Step 1</p>
                            <h2 class="mt-1 text-2xl font-bold text-brand sm:text-3xl">Delivery Address</h2>
                        </div>
                        <button type="button" @click="openAddressPicker()" class="cursor-pointer rounded-full border border-ink px-5 py-2 text-sm font-medium text-ink transition hover:bg-ink hover:text-white sm:text-base">Change address</button>
                    </div>
                    <div x-show="selectedAddress" class="mt-5 grid gap-3 rounded-xl bg-page p-4 text-base sm:grid-cols-[12rem_1fr] sm:p-5 sm:text-lg">
                        <div class="font-semibold text-ink">
                            <p x-text="selectedAddress?.name"></p>
                            <p class="mt-1 font-normal text-muted" x-text="selectedAddress?.phone"></p>
                        </div>
                        <p class="text-muted sm:border-l sm:border-mist sm:pl-5" x-text="selectedAddress?.text"></p>
                    </div>
                    <p x-show="!selectedAddress" class="mt-4 text-sm text-muted">Choose or add a delivery address to continue.</p>
                </section>

                {{-- Payment --}}
                <section class="rounded-2xl border border-mist bg-white p-5 sm:p-7">
                    <p class="text-sm font-semibold uppercase tracking-wide text-muted">Step 2</p>
                    <h2 class="mt-1 text-2xl font-bold text-brand sm:text-3xl">Payment Method</h2>
                    <div class="mt-5 flex flex-wrap gap-3" role="tablist">
                        @foreach (['qr' => 'QR PromptPay', 'card' => 'Credit / Debit Card', 'cod' => 'Cash on Delivery'] as $k => $label)
                            <button type="button" role="tab" @click="pay = '{{ $k }}'" :aria-selected="pay === '{{ $k }}'"
                                    :class="pay === '{{ $k }}' ? 'bg-ink text-white shadow-sm' : 'border border-mist bg-white text-muted hover:border-ink hover:text-ink'"
                                    class="cursor-pointer rounded-full px-4 py-2 text-sm transition sm:text-base">{{ $label }}</button>
                        @endforeach
                    </div>

                    <div x-show="pay === 'card'" x-cloak class="mt-5 space-y-3 rounded-xl bg-page p-4">
                        <label class="flex cursor-pointer items-center gap-3 text-base sm:text-lg"><input type="radio" name="card" checked class="size-5 cursor-pointer text-ink"> Credit Card 1 **** 1234</label>
                        <a href="{{ route('payment-method.create') }}" class="inline-flex items-center gap-2 rounded-lg border border-ink bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-mist">+ Pay with new card</a>
                    </div>
                    <p x-show="pay === 'qr'" x-cloak class="mt-5 rounded-xl bg-page p-4 text-sm text-muted sm:text-base">A PromptPay QR code will be shown after you place the order.</p>
                    <p x-show="pay === 'cod'" x-cloak class="mt-5 rounded-xl bg-page p-4 text-sm text-muted sm:text-base">Pay the courier when your order arrives.</p>
                </section>

                <section class="rounded-2xl border border-mist bg-page p-5 sm:p-7">
                    <h2 class="text-xl font-bold text-ink sm:text-2xl">Order Summary</h2>
                    <dl class="mt-4 space-y-3 text-base sm:text-lg">
                        <div class="flex justify-between gap-4 text-muted"><dt>Merchandise Subtotal (<span x-text="totalQuantity"></span> items)</dt><dd class="shrink-0 font-semibold text-ink" x-text="'฿' + subtotal.toFixed(2)"></dd></div>
                        <div class="flex justify-between gap-4 text-muted"><dt>Shipping</dt><dd class="shrink-0 font-semibold text-ink" x-text="'฿' + shipping.toFixed(2)"></dd></div>
                        <div class="flex justify-between gap-4 border-t border-mist pt-4 text-lg font-bold text-ink sm:text-xl"><dt>Total Payment</dt><dd class="shrink-0 text-brand" x-text="'฿' + (subtotal + shipping).toFixed(2)"></dd></div>
                    </dl>

                    <p x-show="notice" x-text="notice" role="status" class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"></p>
                    <button type="button" @click="placeOrder()" :disabled="!items.length" class="mt-6 w-full cursor-pointer rounded-xl bg-ink px-8 py-4 text-lg font-semibold text-white transition hover:bg-brand disabled:cursor-not-allowed disabled:opacity-40 sm:text-xl">
                        Place Order
                    </button>
                </section>
            </div>
        </div>

        {{-- Address modal --}}
        <div x-show="modal" x-cloak @keydown.escape.window="modal = false"
             class="fixed inset-0 z-40 flex justify-end bg-black/40" role="dialog" aria-modal="true" aria-labelledby="addr-title">
            <div @click.outside="modal = false" class="flex h-full max-h-screen w-full max-w-md flex-col bg-white shadow-xl">
                <div class="flex shrink-0 items-center justify-between border-b border-muted/50 px-5 py-4">
                    <h2 id="addr-title" class="text-2xl font-bold">My Address</h2>
                    <button type="button" @click="modal = false" aria-label="Close" class="text-3xl leading-none">×</button>
                </div>
                <ul x-show="addresses.length" class="min-h-0 flex-1 space-y-3 overflow-y-auto p-4 sm:p-5">
                    <template x-for="address in addresses" :key="address.id">
                        <li>
                            <div
                                class="flex items-start gap-3 rounded-xl border p-4 transition"
                                :class="draftAddressId === address.id ? 'border-brand bg-sky-50 ring-1 ring-brand' : 'border-mist'"
                            >
                                <label class="flex min-w-0 flex-1 cursor-pointer gap-3">
                                    <input type="radio" name="address" :value="address.id" x-model.number="draftAddressId" class="mt-1 shrink-0 cursor-pointer text-brand">
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center gap-x-2 text-base font-semibold text-ink">
                                            <span x-text="address.name"></span>
                                            <span class="font-normal text-muted">| <span x-text="address.phone"></span></span>
                                            <span x-show="address.default" class="rounded border border-brand px-1.5 py-0.5 text-xs font-normal text-brand">Default</span>
                                        </span>
                                        <span class="mt-2 block break-words text-sm leading-relaxed text-muted" x-text="address.text"></span>
                                    </span>
                                </label>
                                <div class="flex shrink-0 flex-col items-end gap-2">
                                    <a :href="'{{ url('/edit-address') }}/' + address.id" class="cursor-pointer text-sm font-medium text-brand underline underline-offset-2 hover:text-ink">
                                        Edit
                                    </a>
                                    <button
                                        type="button"
                                        @click="removeAddress(address.id)"
                                        :aria-label="'Delete address for ' + address.name"
                                        title="Delete this address"
                                        class="group grid size-8 cursor-pointer place-items-center rounded-lg text-red-400 transition hover:bg-red-50 hover:text-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-1"
                                    >
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M4 7h16M10 11v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </li>
                    </template>
                </ul>
                <p x-show="!addresses.length" class="flex-1 p-5 text-sm text-muted">No saved addresses yet. Add one to continue.</p>
                <div class="flex shrink-0 flex-col gap-3 border-t border-muted/50 p-4 sm:p-5">
                    <a href="{{ route('address.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-ink px-4 py-3 font-medium text-ink transition hover:bg-mist">
                        <span class="text-xl leading-none" aria-hidden="true">+</span>
                        Add new address
                    </a>
                    <button type="button" @click="confirmAddress()" :disabled="!addresses.some(address => address.id === draftAddressId)" class="rounded-lg bg-ink px-4 py-3 font-semibold text-white transition hover:bg-brand disabled:cursor-not-allowed disabled:opacity-40">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.shop>
