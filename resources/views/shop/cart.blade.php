<x-layouts.shop title="My cart">
    <div
        class="mt-6"
        x-data="{
            items: [],
            notice: '',
            storageError: false,
            init() {
                try {
                    const saved = JSON.parse(localStorage.getItem('badminton-shop-cart') || '[]');
                    if (!Array.isArray(saved) || !saved.every(item =>
                        item && typeof item.id === 'string' && typeof item.name === 'string' &&
                        typeof item.variant === 'string' && Number.isFinite(item.price) && item.price >= 0 &&
                        Number.isInteger(item.qty) && item.qty > 0
                    )) throw new Error('Saved cart data is invalid.');
                    this.items = saved.map(item => ({ ...item, checked: true }));
                } catch (error) {
                    this.storageError = true;
                    this.notice = 'Could not load your saved cart on this device.';
                }
            },
            get all() { return this.items.length > 0 && this.items.every(item => item.checked) },
            set all(value) { this.items.forEach(item => item.checked = value) },
            get picked() { return this.items.filter(item => item.checked) },
            get total() { return this.picked.reduce((sum, item) => sum + item.price * item.qty, 0) },
            get totalQuantity() { return this.picked.reduce((sum, item) => sum + item.qty, 0) },
            save() {
                try {
                    localStorage.setItem('badminton-shop-cart', JSON.stringify(this.items.map(({ checked, ...item }) => item)));
                    this.notice = '';
                } catch (error) {
                    this.notice = 'Could not save your cart changes on this device.';
                }
            },
            remove(id) {
                this.items = this.items.filter(item => item.id !== id);
                this.save();
            },
            changeQuantity(item, amount) {
                item.qty = Math.max(1, Number(item.qty) + amount);
                this.save();
            },
            purchase() {
                if (!this.picked.length) return;
                try {
                    localStorage.setItem('badminton-shop-checkout-items', JSON.stringify(this.picked.map(({ checked, ...item }) => item)));
                    window.location.href = @js(route('checkout'));
                } catch (error) {
                    this.notice = 'Could not prepare checkout on this device.';
                }
            }
        }"
    >
        <h1 class="mt-4 text-3xl font-bold text-ink">My cart</h1>

        <button
            type="button"
            @click="all = !all"
            :aria-pressed="all"
            :disabled="!items.length"
            class="mt-5 inline-flex items-center rounded-full border border-ink px-5 py-2 text-sm font-medium text-ink transition hover:bg-ink hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-40"
        >
            <span x-text="all ? 'Deselect all' : 'Select all'"></span>
        </button>

        <p x-show="notice" x-text="notice" role="status" class="mt-4 text-sm font-medium text-red-700"></p>

        <template x-if="items.length">
            <ul class="mt-4 divide-y divide-mist border-y border-mist">
                <template x-for="item in items" :key="item.id">
                    <li class="flex flex-wrap items-center gap-4 py-6 sm:gap-6">
                        <input type="checkbox" x-model="item.checked" class="size-5 shrink-0 rounded border-ink text-ink sm:size-6" :aria-label="'Select ' + item.name">
                        <div class="grid size-24 shrink-0 place-items-center rounded-lg bg-tile sm:size-32" aria-hidden="true">
                            <img src="{{ asset('images/badminton-racket.jpg') }}" alt="" class="size-full rounded-lg object-cover">
                        </div>
                        <div class="min-w-40 flex-1">
                            <h2 class="text-xl font-bold sm:text-2xl" x-text="item.name"></h2>
                            <p class="mt-2 text-xs text-muted sm:text-sm" x-text="item.variant"></p>
                            <div class="mt-3 flex items-center gap-3">
                                <button type="button" @click="changeQuantity(item, -1)" class="grid size-7 place-items-center rounded-full bg-brand text-white" aria-label="Decrease">−</button>
                                <span class="w-6 text-center text-lg" x-text="item.qty"></span>
                                <button type="button" @click="changeQuantity(item, 1)" class="grid size-7 place-items-center rounded-full bg-brand text-white" aria-label="Increase">+</button>
                                <button type="button" @click="remove(item.id)" class="ml-2 text-sm text-muted hover:text-ink sm:hidden">Remove</button>
                            </div>
                        </div>
                        <div class="ml-auto min-w-28 text-right">
                            <p class="text-xs text-muted sm:text-sm">
                                <span x-text="'฿' + new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(item.price)"></span>
                                <span>each</span>
                            </p>
                            <p class="text-lg font-bold text-brand sm:text-xl" x-text="'฿' + new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(item.price * item.qty)"></p>
                            <button type="button" @click="remove(item.id)" class="mt-2 hidden text-sm text-muted hover:text-ink sm:inline">Remove</button>
                        </div>
                    </li>
                </template>
            </ul>
        </template>

        <p x-show="!items.length && !storageError" x-cloak class="py-10 text-muted">
            Your cart is empty. <a href="{{ route('shop.index') }}" class="text-brand underline">Browse products</a>
        </p>

        <div x-show="items.length" x-cloak class="mt-8 flex flex-wrap items-center justify-end gap-5">
            <span class="text-xl text-muted">(Total <span x-text="totalQuantity"></span> items)</span>
            <span class="text-3xl font-bold text-brand" x-text="'฿' + total.toFixed(2)"></span>
            <button type="button" @click="purchase()" :disabled="!picked.length || storageError" class="rounded-lg bg-ink px-8 py-3 text-white transition hover:bg-brand disabled:cursor-not-allowed disabled:opacity-40">
                Purchase
            </button>
        </div>
    </div>
</x-layouts.shop>
