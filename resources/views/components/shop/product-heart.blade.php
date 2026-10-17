@props([
    'compact' => false,
    'productId' => null,
    'favorited' => false,
])

@php
    $icon_size = $compact ? 'size-5' : 'size-9';
    $login_url = route('login', ['return_to' => request()->getRequestUri()]);
@endphp

@if (auth()->check())
    <div
        x-data="{
            liked: @js((bool) $favorited),
            busy: false,
            async toggle() {
                if (this.busy) return;
                this.busy = true;
                const was = this.liked;
                this.liked = !was;
                try {
                    const res = await fetch(@js(route('favorites.toggle', ['product' => $productId])), {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': @js(csrf_token()),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    if (res.status === 401 || res.status === 419) {
                        window.location.assign(@js($login_url));
                        return;
                    }
                    if (!res.ok) throw new Error(res.status);
                    this.liked = (await res.json()).favorited;
                } catch (e) {
                    this.liked = was;
                } finally {
                    this.busy = false;
                }
            }
        }"
        class="inline-flex"
        @click.stop
    >
        <button type="button"
                @click.stop.prevent="toggle()"
                :aria-pressed="liked"
                :aria-label="liked ? 'Remove from favorites' : 'Add to favorites'"
                class="inline-flex cursor-pointer transition hover:scale-110">
            <svg class="{{ $icon_size }}" viewBox="0 0 24 24" stroke="#ff7373" stroke-width="1.5"
                 :fill="liked ? '#ff7373' : 'none'" aria-hidden="true">
                <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/>
            </svg>
        </button>
    </div>
@else
    <a href="{{ $login_url }}" aria-label="Log in to add this product to favorites"
       class="inline-flex cursor-pointer text-heart/60 transition hover:text-heart">
        <svg class="{{ $icon_size }}" viewBox="0 0 24 24" stroke="#ff7373" stroke-width="1.5" fill="none" aria-hidden="true">
            <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/>
        </svg>
    </a>
@endif