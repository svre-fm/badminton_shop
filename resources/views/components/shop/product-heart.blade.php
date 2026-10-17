{{-- resources/views/components/shop/product-heart.blade.php --}}
@props(['productId' => null, 'active' => false])

<button
    type="button"
    x-data="{
        active: @js((bool) $active),
        busy: false,
        async toggle() {
            const id = @js($productId);
            if (this.busy || !id) return;
            this.busy = true;
            try {
                const res = await fetch(@js($productId ? route('favorites.toggle', $productId) : '#'), {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()) },
                });
                if (res.status === 401 || res.status === 419) {
                    window.location.href = @js(Route::has('login') ? route('login') : url('/'));
                    return;
                }
                if (res.ok) {
                    this.active = (await res.json()).favorited;
                    window.dispatchEvent(new CustomEvent('favorite-toggled', {
                        detail: { productId: id, favorited: this.active },
                    }));
                }
            } finally {
                this.busy = false;
            }
        }
    }"
    @click.prevent="toggle()"
    :aria-pressed="active"
    aria-label="Toggle favorite"
    {{ $attributes->merge(['class' => 'grid size-8 place-items-center rounded-full bg-white/80']) }}
>
    <svg viewBox="0 0 24 24" class="size-5" stroke="currentColor" stroke-width="2" aria-hidden="true"
         :class="active ? 'fill-red-500 text-red-500' : 'fill-none text-ink'">
        <path stroke-linejoin="round" d="M12 20.5s-7.5-4.6-9.3-9.3C1.5 8 3.4 5 6.5 5c1.9 0 3.5 1 5.5 3.2C14 6 15.6 5 17.5 5c3.1 0 5 3 3.8 6.2-1.8 4.7-9.3 9.3-9.3 9.3z"/>
    </svg>
</button>