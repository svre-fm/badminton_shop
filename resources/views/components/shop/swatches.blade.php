{{-- Usage: <x-shop.swatches :colors="['#000000','#f9c9ff','#ffd982','#4072af']" /> --}}
@props(['colors' => [], 'selected' => 0])
<div x-data="{ s: {{ $selected }} }" class="flex gap-2">
    @foreach ($colors as $i => $hex)
        <button type="button" @click="s = {{ $i }}" aria-label="Color {{ $hex }}" :aria-pressed="s === {{ $i }}"
                class="grid size-6 place-items-center rounded-full border border-black/10" style="background: {{ $hex }}">
            <svg x-show="s === {{ $i }}" class="size-3.5 text-white mix-blend-difference" viewBox="0 0 20 20" fill="none"
                 stroke="currentColor" stroke-width="3"><path d="m4 10 4 4 8-8"/></svg>
        </button>
    @endforeach
</div>
