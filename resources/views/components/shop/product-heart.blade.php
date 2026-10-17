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
    <form method="POST" action="{{ route('favorites.toggle', ['product' => $productId]) }}" @click.stop>
        @csrf
        <input type="hidden" name="favorited" value="{{ $favorited ? 0 : 1 }}">
        <button type="submit" aria-pressed="{{ $favorited ? 'true' : 'false' }}"
                aria-label="{{ $favorited ? 'Remove from favorites' : 'Add to favorites' }}"
                class="inline-flex cursor-pointer transition {{ $favorited ? 'text-heart' : 'text-heart/60 hover:text-heart' }}">
            <svg class="{{ $icon_size }}" viewBox="0 0 24 24" stroke="#ff7373" stroke-width="1.5"
                 fill="{{ $favorited ? '#ff7373' : 'none' }}"><path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/></svg>
        </button>
    </form>
@else
    <a href="{{ $login_url }}" aria-label="Log in to add this product to favorites"
       class="inline-flex cursor-pointer text-heart/60 transition hover:text-heart">
        <svg class="{{ $icon_size }}" viewBox="0 0 24 24" stroke="#ff7373" stroke-width="1.5" fill="none" aria-hidden="true">
            <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/>
        </svg>
    </a>
@endif
