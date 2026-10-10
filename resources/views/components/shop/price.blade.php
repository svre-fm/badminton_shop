@props(['amount', 'decimals' => 0])

<span {{ $attributes }}>฿{{ number_format((float) $amount, $decimals) }}</span>
