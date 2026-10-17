@props(['rating' => 0, 'size' => 'text-4xl'])

@php
    $percent = max(0, min(100, ((float) $rating / 5) * 100));
@endphp

<div {{ $attributes->class(['relative inline-block leading-none', $size]) }}
     role="img" aria-label="{{ $rating }} out of 5 stars">
    {{-- ชั้นล่าง: ดาวจาง --}}
    <div class="text-zinc-200" aria-hidden="true">★★★★★</div>

    {{-- ชั้นบน: ดาวเหลือง ถูกตัดตามเปอร์เซ็นต์ --}}
    <div class="absolute inset-y-0 left-0 overflow-hidden whitespace-nowrap text-star" style="width: {{ $percent }}%" aria-hidden="true">★★★★★</div>
</div>