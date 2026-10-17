{{-- Quantity stepper. Usage: <x-shop.qty :value="1" /> --}}
@props(['value' => 1])
<div x-data="{ n: {{ $value }} }" class="flex items-center gap-3">
    <button type="button" @click="n = Math.max(1, n - 1)" aria-label="Decrease"
            class="grid size-7 place-items-center rounded-full bg-brand text-white">−</button>
    <input type="number" min="1" x-model.number="n" class="w-10 border-0 bg-transparent p-0 text-center text-lg [appearance:textfield]">
    <button type="button" @click="n++" aria-label="Increase"
            class="grid size-7 place-items-center rounded-full bg-brand text-white">+</button>
</div>
