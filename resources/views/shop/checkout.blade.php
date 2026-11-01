@php
    $delivery_addresses = collect($deliveryAddresses ?? [])->values()->all();
    $cart_items = collect($cartItems ?? [])->values()->all();
    $credit_debit_cards = collect($creditCards ?? [])->values()->all();
    $card_tokens = $cardTokens ?? [];
@endphp

<x-layouts.shop title="Checkout" :fullWidth="true">
    <div
        class="mx-auto w-full max-w-screen-2xl px-4 pb-16 pt-8 sm:px-8 xl:px-12"
        x-data="{
            pay: 'qr',
            modal: false,
            delivery_addresses: @js($delivery_addresses),
            credit_debit_cards: @js($credit_debit_cards),
            cardTokens: @js($card_tokens),
            csrf: @js(csrf_token()),
            addressDeleteUrl: @js(route('address.destroy', ['address' => '__ADDRESS__'])),
            addressCreateUrl: @js(route('address.store')),
            addressUpdateUrl: @js(route('address.update', ['address' => '__ADDRESS__'])),
            selectedAddressId: null,
            selectedCardToken: null,
            draftAddressId: null,
            cart_items: @js($cart_items),
            notice: '',
            addressFormOpen: false,
            addressSaving: false,
            addressEditingId: null,
            addressForm: { name: '', phone: '', address: '' },
            addressErrors: {},
            init() {
                // EER ไม่มี default flag จึงเลือก address แรก; เปลี่ยนเมื่อ schema มีข้อมูลที่อยู่หลัก
                this.selectedAddressId = this.delivery_addresses[0]?.delivery_id ?? null;
                this.draftAddressId = this.selectedAddressId;
                this.selectedCardToken = this.credit_debit_cards[0] ? this.cardTokens[this.credit_debit_cards[0].card_no] : null;
            },
            get selectedAddress() {
                return this.delivery_addresses.find(delivery_address => delivery_address.delivery_id === this.selectedAddressId) ?? null;
            },
            openAddressPicker() {
                this.draftAddressId = this.selectedAddressId;
                this.modal = true;
                this.addressFormOpen = false;
                this.notice = '';
            },
            openNewAddressForm() {
                this.addressEditingId = null;
                this.addressForm = { name: '', phone: '', address: '' };
                this.addressErrors = {};
                this.notice = '';
                this.addressFormOpen = true;
            },
            openEditAddressForm(address) {
                this.addressEditingId = address.delivery_id;
                this.addressForm = {
                    name: address.name,
                    phone: address.phone,
                    address: address.address,
                };
                this.addressErrors = {};
                this.notice = '';
                this.addressFormOpen = true;
            },
            async saveAddress() {
                if (this.addressSaving) return;

                this.addressSaving = true;
                this.addressErrors = {};
                this.notice = '';

                try {
                    const editing = this.addressEditingId !== null;
                    const response = await fetch(
                        editing
                            ? this.addressUpdateUrl.replace('__ADDRESS__', this.addressEditingId)
                            : this.addressCreateUrl,
                        {
                            method: editing ? 'PUT' : 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(this.addressForm),
                        }
                    );
                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            this.addressErrors = data.errors;
                            return;
                        }

                        throw new Error(data.message || 'Could not save this address.');
                    }

                    const savedAddress = data.address;
                    const index = this.delivery_addresses.findIndex(
                        address => address.delivery_id === savedAddress.delivery_id
                    );

                    if (index === -1) {
                        this.delivery_addresses.push(savedAddress);
                    } else {
                        this.delivery_addresses.splice(index, 1, savedAddress);
                    }

                    this.selectedAddressId = savedAddress.delivery_id;
                    this.draftAddressId = savedAddress.delivery_id;
                    this.addressFormOpen = false;
                } catch (error) {
                    this.notice = error.message || 'Could not save this address.';
                } finally {
                    this.addressSaving = false;
                }
            },
            confirmAddress() {
                if (!this.delivery_addresses.some(delivery_address => delivery_address.delivery_id === this.draftAddressId)) return;
                this.selectedAddressId = this.draftAddressId;
                this.modal = false;
            },
            async removeAddress(id) {
                if (!window.confirm('Delete this delivery address?')) return;
                this.notice = '';

                try {
                    const response = await fetch(this.addressDeleteUrl.replace('__ADDRESS__', id), {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Could not delete this address.');
                    }

                    this.delivery_addresses = this.delivery_addresses.filter(address => address.delivery_id !== id);
                    if (this.selectedAddressId === id) {
                        this.selectedAddressId = this.delivery_addresses[0]?.delivery_id ?? null;
                    }
                    this.draftAddressId = this.selectedAddressId;
                } catch (error) {
                    this.notice = error.message || 'Could not delete this address.';
                }
            },
            get subtotal() { return this.cart_items.reduce((sum, cart_item) => sum + cart_item.product.price * cart_item.quantity, 0) },
            get totalQuantity() { return this.cart_items.reduce((sum, cart_item) => sum + cart_item.quantity, 0) },
            placeOrder() {
                if (!this.selectedAddress || (this.pay === 'card' && !this.selectedCardToken)) {
                    this.notice = 'Select a delivery address and, when paying by card, a payment card to continue.';
                    return;
                }
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = @js(route('checkout.place'));
                const methods = { card: 'CREDIT_CARD', qr: 'PROMPTPAY', cod: 'CASH_ON_DELIVERY' };
                const fields = { _token: @js(csrf_token()), delivery_id: this.selectedAddressId, payment_method: methods[this.pay], card_token: this.selectedCardToken ?? '' };
                Object.entries(fields).forEach(([name, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden'; input.name = name; input.value = value; form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
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
                <template x-if="cart_items.length">
                    <ul class="divide-y divide-mist">
                        <template x-for="cart_item in cart_items" :key="[cart_item.product_id, cart_item.color, cart_item.racket_tension ?? cart_item.tension ?? 0].join('|')">
                            <li class="flex gap-4 py-5 sm:gap-6">
                                <div class="size-28 shrink-0 overflow-hidden rounded-xl bg-white sm:size-36 xl:size-40">
                                    <template x-if="cart_item.variantRecord?.image">
                                        <img :src="cart_item.variantRecord.image" :alt="cart_item.product.name" class="size-full object-cover">
                                    </template>
                                </div>
                                <div class="flex min-w-0 flex-1 flex-col justify-center">
                                    <p class="text-xl font-semibold text-ink sm:text-2xl" x-text="cart_item.product.name"></p>
                                    <p class="mt-2 text-sm text-muted sm:text-base" x-text="'Color: ' + cart_item.color + (cart_item.racket_tension ? ' · Tension: ' + cart_item.racket_tension + ' lbs' : '')"></p>
                                    <p class="mt-1 text-sm text-muted sm:text-base">Quantity: <span x-text="cart_item.quantity"></span></p>
                                    <p class="mt-3 text-lg font-bold text-brand sm:text-xl" x-text="'฿' + (cart_item.product.price * cart_item.quantity).toFixed(2)"></p>
                                </div>
                            </li>
                        </template>
                    </ul>
                </template>
                <p x-show="!cart_items.length" x-cloak class="mt-5 text-base text-muted">
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
                        <p class="text-muted sm:border-l sm:border-mist sm:pl-5" x-text="selectedAddress?.address"></p>
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
                        <template x-for="credit_debit_card in credit_debit_cards" :key="credit_debit_card.user_id + ':' + credit_debit_card.card_no">
                            <label class="flex cursor-pointer items-center gap-3 text-base sm:text-lg">
                                <input type="radio" name="card_token" :value="cardTokens[credit_debit_card.card_no]" x-model="selectedCardToken" class="size-5 cursor-pointer text-ink">
                                <span x-text="String(credit_debit_card.card_no).slice(0, 4) + ' xxxx xxxx ' + String(credit_debit_card.card_no).slice(-4) + ' · Expires ' + credit_debit_card.expiry_date.slice(5, 7) + '/' + credit_debit_card.expiry_date.slice(2, 4)"></span>
                            </label>
                        </template>
                        <p x-show="!credit_debit_cards.length" class="text-sm text-muted">No saved payment methods.</p>
                        <a href="{{ route('payment-method.create') }}" class="inline-flex items-center gap-2 rounded-lg border border-ink bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-mist">+ Pay with new card</a>
                    </div>
                    <p x-show="pay === 'qr'" x-cloak class="mt-5 rounded-xl bg-page p-4 text-sm text-muted sm:text-base">Your PromptPay payment reference will be recorded when the order is placed.</p>
                    <p x-show="pay === 'cod'" x-cloak class="mt-5 rounded-xl bg-page p-4 text-sm text-muted sm:text-base">Pay the courier when your order arrives.</p>
                </section>

                <section class="rounded-2xl border border-mist bg-page p-5 sm:p-7">
                    <h2 class="text-xl font-bold text-ink sm:text-2xl">Order Summary</h2>
                    <dl class="mt-4 space-y-3 text-base sm:text-lg">
                        <div class="flex justify-between gap-4 text-muted">
                            <dt>Merchandise Subtotal (<span x-text="totalQuantity"></span> items)</dt>
                            <dd class="shrink-0 font-semibold text-ink" x-text="'฿' + subtotal.toFixed(2)"></dd>
                        </div>
                        <div class="flex justify-between gap-4 border-t border-mist pt-4 text-lg font-bold text-ink sm:text-xl">
                            <dt>Total Payment</dt>
                            <dd class="shrink-0 text-brand" x-text="'฿' + subtotal.toFixed(2)"></dd>
                        </div>
                    </dl>

                    <p x-show="notice" x-text="notice" role="status" class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"></p>
                    <button type="button" @click="placeOrder()" :disabled="!cart_items.length || !selectedAddress || (pay === 'card' && !selectedCardToken)" class="mt-6 w-full cursor-pointer rounded-xl bg-ink px-8 py-4 text-lg font-semibold text-white transition hover:bg-brand disabled:cursor-not-allowed disabled:opacity-40 sm:text-xl">
                        Place Order
                    </button>
                </section>
            </div>
        </div>

        {{-- ใช้ delivery_address.id/customer_id/name/phone/address ตาม EER; ตรวจสิทธิ์เจ้าของทุก request --}}
        <div x-show="modal" x-cloak @keydown.escape.window="modal = false; addressFormOpen = false"
             class="fixed inset-0 z-40 flex justify-end bg-black/40" role="dialog" aria-modal="true" aria-labelledby="addr-title">
            <div @click.outside="modal = false" class="flex h-full max-h-screen w-full max-w-md flex-col bg-white shadow-xl">
                <div class="flex shrink-0 items-center justify-between border-b border-muted/50 px-5 py-4">
                    <h2 id="addr-title" class="text-2xl font-bold" x-text="addressFormOpen ? (addressEditingId === null ? 'Add address' : 'Edit address') : 'My Address'"></h2>
                    <button type="button" @click="addressFormOpen ? addressFormOpen = false : modal = false" aria-label="Close" class="text-3xl leading-none">×</button>
                </div>
                <ul x-show="!addressFormOpen && delivery_addresses.length" class="min-h-0 flex-1 space-y-3 overflow-y-auto p-4 sm:p-5">
                    <template x-for="delivery_address in delivery_addresses" :key="delivery_address.delivery_id">
                        <li>
                            <div
                                class="flex items-start gap-3 rounded-xl border p-4 transition"
                                :class="draftAddressId === delivery_address.delivery_id ? 'border-brand bg-sky-50 ring-1 ring-brand' : 'border-mist'"
                            >
                                <label class="flex min-w-0 flex-1 cursor-pointer gap-3">
                                    <input type="radio" name="delivery_address_id" :value="delivery_address.delivery_id" x-model.number="draftAddressId" class="mt-1 shrink-0 cursor-pointer text-brand">
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center gap-x-2 text-base font-semibold text-ink">
                                            <span x-text="delivery_address.name"></span>
                                            <span class="font-normal text-muted">| <span x-text="delivery_address.phone"></span></span>
                                        </span>
                                        <span class="mt-2 block break-words text-sm leading-relaxed text-muted" x-text="delivery_address.address"></span>
                                    </span>
                                </label>
                                <div class="flex shrink-0 flex-col items-end gap-2">
                                    <button type="button" @click="openEditAddressForm(delivery_address)" class="cursor-pointer text-sm font-medium text-brand underline underline-offset-2 hover:text-ink">
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        @click="removeAddress(delivery_address.delivery_id)"
                                        :aria-label="'Delete address for ' + delivery_address.name"
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
                <p x-show="!addressFormOpen && !delivery_addresses.length" class="flex-1 p-5 text-sm text-muted">No saved addresses yet. Add one to continue.</p>
                <form x-show="addressFormOpen" x-cloak @submit.prevent="saveAddress()" class="min-h-0 flex-1 space-y-4 overflow-y-auto p-4 sm:p-5">
                    <div>
                        <label for="checkout-address-name" class="mb-2 block text-sm font-medium text-ink">Name</label>
                        <input id="checkout-address-name" x-model="addressForm.name" type="text" maxlength="100" required class="w-full rounded-lg border border-muted/50 px-4 py-3 text-ink outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
                        <p x-show="addressErrors.name" x-text="addressErrors.name?.[0]" role="alert" class="mt-1 text-sm text-red-700"></p>
                    </div>
                    <div>
                        <label for="checkout-address-phone" class="mb-2 block text-sm font-medium text-ink">Phone number</label>
                        <input id="checkout-address-phone" x-model="addressForm.phone" type="tel" maxlength="10" pattern="[0]{1}[0-9]{9}" required class="w-full rounded-lg border border-muted/50 px-4 py-3 text-ink outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
                        <p x-show="addressErrors.phone" x-text="addressErrors.phone?.[0]" role="alert" class="mt-1 text-sm text-red-700"></p>
                    </div>
                    <div>
                        <label for="checkout-address-text" class="mb-2 block text-sm font-medium text-ink">Address</label>
                        <textarea id="checkout-address-text" x-model="addressForm.address" rows="4" maxlength="2000" required class="w-full resize-y rounded-lg border border-muted/50 px-4 py-3 text-ink outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"></textarea>
                        <p x-show="addressErrors.address" x-text="addressErrors.address?.[0]" role="alert" class="mt-1 text-sm text-red-700"></p>
                    </div>
                </form>
                <p x-show="notice" x-text="notice" role="alert" class="mx-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800 sm:mx-5"></p>
                <div x-show="!addressFormOpen" class="flex shrink-0 flex-col gap-3 border-t border-muted/50 p-4 sm:p-5">
                    <button type="button" @click="openNewAddressForm()" class="inline-flex items-center justify-center gap-2 rounded-lg border border-ink px-4 py-3 font-medium text-ink transition hover:bg-mist">
                        <span class="text-xl leading-none" aria-hidden="true">+</span>
                        Add new address
                    </button>
                    <button type="button" @click="confirmAddress()" :disabled="!delivery_addresses.some(delivery_address => delivery_address.delivery_id === draftAddressId)" class="rounded-lg bg-ink px-4 py-3 font-semibold text-white transition hover:bg-brand disabled:cursor-not-allowed disabled:opacity-40">
                        Save
                    </button>
                </div>
                <div x-show="addressFormOpen" class="flex shrink-0 gap-3 border-t border-muted/50 p-4 sm:p-5">
                    <button type="button" @click="addressFormOpen = false" class="flex-1 rounded-lg border border-mist px-4 py-3 font-semibold text-ink transition hover:bg-page">Cancel</button>
                    <button type="button" @click="saveAddress()" :disabled="addressSaving" class="flex-1 rounded-lg bg-ink px-4 py-3 font-semibold text-white transition hover:bg-brand disabled:cursor-wait disabled:opacity-50">
                        <span x-text="addressSaving ? 'Saving...' : 'Save address'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.shop>   