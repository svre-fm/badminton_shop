<x-layouts.shop title="My cart">
    <div
        class="mt-6"
        x-data="{
            cart_items: @js($cartItems ?? []),
            notice: '',
            updating: {},
            init() {
                this.cart_items = this.cart_items.map(cart_item => ({ ...cart_item, checked: true }));
            },
            get all() { return this.cart_items.length > 0 && this.cart_items.every(cart_item => cart_item.checked) },
            set all(value) { this.cart_items.forEach(cart_item => cart_item.checked = value) },
            get picked() { return this.cart_items.filter(cart_item => cart_item.checked) },
            get total() { return this.picked.reduce((sum, cart_item) => sum + Number(cart_item.product.price) * Number(cart_item.quantity), 0) },
            get totalQuantity() { return this.picked.reduce((sum, cart_item) => sum + Number(cart_item.quantity), 0) },
            key(cart_item) {
                return [cart_item.customer_id, cart_item.product_id, cart_item.color, cart_item.racket_tension ?? cart_item.tension ?? 0].join('|');
            },
            async changeQuantity(cart_item, amount) {
                const itemKey = this.key(cart_item);
                if (this.updating[itemKey]) return;

                const previousQuantity = Number(cart_item.quantity);
                const quantity = Math.max(1, previousQuantity + amount);
                if (quantity === previousQuantity) return;

                this.updating[itemKey] = true;
                this.notice = '';

                try {
                    const response = await fetch(@js(url('/cart_item')) + '/' + encodeURIComponent(cart_item.product_id), {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': @js(csrf_token()),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            quantity: quantity,
                            color: cart_item.color,
                        }),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(data.message || Object.values(data.errors ?? {}).flat()[0] || 'Could not update the cart.');
                    }

                    cart_item.quantity = data.quantity ?? quantity;
                } catch (error) {
                    this.notice = error.message || 'Could not update the cart.';
                } finally {
                    this.updating[itemKey] = false;
                }
            },
            async remove(cart_item) {
                const itemKey = this.key(cart_item);
                if (this.updating[itemKey]) return;

                this.updating[itemKey] = true;
                this.notice = '';

                try {
                    const response = await fetch(@js(url('/cart_item')) + '/' + encodeURIComponent(cart_item.product_id), {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': @js(csrf_token()),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            color: cart_item.color,
                        }),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(data.message || 'Could not remove the item.');
                    }

                    this.cart_items = this.cart_items.filter(item => this.key(item) !== itemKey);
                } catch (error) {
                    this.notice = error.message || 'Could not remove the item.';
                } finally {
                    this.updating[itemKey] = false;
                }
            },
            purchase() {
                if (!this.picked.length) return;

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = @js(route('checkout.items'));

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = @js(csrf_token());
                form.appendChild(csrf);

                this.picked.forEach((item, index) => {
                    for (const [field, value] of Object.entries({
                        product_id: item.product_id,
                        color: item.color,
                    })) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `items[${index}][${field}]`;
                        input.value = value;
                        form.appendChild(input);
                    }
                });

                document.body.appendChild(form);
                form.submit();
            }
        }"
    >
        <h1 class="mt-4 text-3xl font-bold text-ink">My cart</h1>

        <button
            type="button"
            @click="all = !all"
            :aria-pressed="all"
            :disabled="!cart_items.length"
            class="mt-5 inline-flex items-center rounded-full border border-ink px-5 py-2 text-sm font-medium text-ink transition hover:bg-ink hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-40"
        >
            <span x-text="all ? 'Deselect all' : 'Select all'"></span>
        </button>

        <p x-show="notice" x-text="notice" role="status" class="mt-4 text-sm font-medium text-red-700"></p>

        <template x-if="cart_items.length">
            <ul class="mt-4 divide-y divide-mist border-y border-mist">
                <template x-for="cart_item in cart_items" :key="key(cart_item)">
                    <li class="flex flex-wrap items-center gap-4 py-6 sm:gap-6">
                        <input type="checkbox" x-model="cart_item.checked" class="size-5 shrink-0 rounded border-ink text-ink sm:size-6" :aria-label="'Select ' + cart_item.product.name">
                        <div class="grid size-24 shrink-0 place-items-center rounded-lg bg-tile sm:size-32" aria-hidden="true">
                            <template x-if="cart_item.variantRecord?.image">
                                <img :src="cart_item.variantRecord.image" :alt="cart_item.product.name" class="size-full rounded-lg object-cover border-1">
                            </template>
                        </div>
                        <div class="min-w-40 flex-1">
                            <h2 class="text-xl font-bold sm:text-2xl" x-text="cart_item.product.name"></h2>
                            <p class="mt-2 text-xs text-muted sm:text-sm" x-text="'Color: ' + cart_item.color + ((cart_item.racket_tension || cart_item.tension) ? ' · Tension: ' + (cart_item.racket_tension || cart_item.tension) + ' lbs' : '')"></p>
                            <div class="mt-3 flex items-center gap-3">
                                <button type="button" @click="changeQuantity(cart_item, -1)" :disabled="updating[key(cart_item)]" class="grid size-7 place-items-center rounded-full bg-brand text-white disabled:opacity-50" aria-label="Decrease">−</button>
                                <span class="w-6 text-center text-lg" x-text="cart_item.quantity"></span>
                                <button type="button" @click="changeQuantity(cart_item, 1)" :disabled="updating[key(cart_item)]" class="grid size-7 place-items-center rounded-full bg-brand text-white disabled:opacity-50" aria-label="Increase">+</button>
                                <button type="button" @click="remove(cart_item)" :disabled="updating[key(cart_item)]" class="ml-2 text-sm text-muted hover:text-ink sm:hidden">Remove</button>
                            </div>
                        </div>
                        <div class="ml-auto min-w-28 text-right">
                            <p class="text-xs text-muted sm:text-sm">
                                <span x-text="'฿' + new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(cart_item.product.price)"></span>
                                <span>each</span>
                            </p>
                            <p class="text-lg font-bold text-brand sm:text-xl" x-text="'฿' + new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(cart_item.product.price * cart_item.quantity)"></p>
                            <button type="button" @click="remove(cart_item)" :disabled="updating[key(cart_item)]" class="mt-2 hidden text-sm text-muted hover:text-ink sm:inline">Remove</button>
                        </div>
                    </li>
                </template>
            </ul>
        </template>

        <p x-show="!cart_items.length" x-cloak class="py-10 text-muted">
            Your cart is empty. <a href="{{ route('shop.index') }}" class="text-brand underline">Browse products</a>
        </p>

        <div x-show="cart_items.length" x-cloak class="mt-8 flex flex-wrap items-center justify-end gap-5">
            <span class="text-xl text-muted">(Total <span x-text="totalQuantity"></span> items)</span>
            <span class="text-3xl font-bold text-brand" x-text="'฿' + total.toFixed(2)"></span>
            <button type="button" @click="purchase()" :disabled="!picked.length" class="rounded-lg bg-ink px-8 py-3 text-white transition hover:bg-brand disabled:cursor-not-allowed disabled:opacity-40">
                Purchase
            </button>
        </div>
    </div>
</x-layouts.shop>