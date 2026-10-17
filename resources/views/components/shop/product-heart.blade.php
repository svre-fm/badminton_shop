@props(['compact' => false])

@php
    $icon_size = $compact ? 'size-5' : 'size-9';
    $login_url = route('login', ['return_to' => request()->getRequestUri()]);
@endphp

@if (auth()->check())
    {{-- favorite ยังไม่เชื่อมกับ persistence; ผู้ใช้ที่ล็อกอินเห็น interaction ฝั่งหน้าเว็บได้ --}}
    <button type="button" x-data="{ on: false }" @click.stop="on = !on" :aria-pressed="on" aria-label="Favorite"
            :class="on ? 'text-heart' : 'text-heart/60'" class="cursor-pointer">
        <svg class="{{ $icon_size }}" viewBox="0 0 24 24" stroke="#ff7373" stroke-width="1.5"
             :fill="on ? '#ff7373' : 'none'"><path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/></svg>
    </button>
@else
    <a href="{{ $login_url }}" aria-label="Log in to add this product to favorites"
       class="inline-flex cursor-pointer text-heart/60 transition hover:text-heart">
        <svg class="{{ $icon_size }}" viewBox="0 0 24 24" stroke="#ff7373" stroke-width="1.5" fill="none" aria-hidden="true">
            <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/>
        </svg>
    </a>
@endif
