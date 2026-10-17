{{-- resources/views/components/shop/price.blade.php  →  <x-shop.price :amount="$product->price" /> --}}
@props(['amount' => 0, 'decimals' => 0])

@php
    // เปลี่ยนสัญลักษณ์สกุลเงินตรงนี้ที่เดียว (ตอนนี้ใช้บาท)
    $symbol = '฿';
@endphp

<span {{ $attributes }}>{{ $symbol }}{{ number_format((float) $amount, (int) $decimals) }}</span>