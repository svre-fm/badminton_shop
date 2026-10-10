@php
    $orders = [
        [
            'no' => 'ORD-20261003-0001',
            'status' => 'completed',
            'items' => [
                ['name' => 'Badminton racket', 'variants' => ['black, 20 - 23 lbs · ฿100.00 × 1', 'pink, 20 - 23 lbs · ฿100.00 × 1'], 'total' => 200],
                ['name' => 'Grip', 'variants' => ['white · ฿100.00 × 1'], 'total' => 100],
            ],
        ],
        [
            'no' => 'ORD-20261003-0002',
            'status' => 'to_receive',
            'items' => [
                ['name' => 'Badminton racket', 'variants' => ['black, 20 - 23 lbs · ฿100.00 × 1', 'pink, 20 - 23 lbs · ฿100.00 × 1'], 'total' => 200],
                ['name' => 'Grip', 'variants' => ['white · ฿100.00 × 1'], 'total' => 100],
            ],
        ],
        [
            'no' => 'ORD-20261003-0003',
            'status' => 'to_ship',
            'items' => [
                ['name' => 'Grip', 'variants' => ['white · ฿100.00 × 1'], 'total' => 100],
            ],
        ],
    ];
    $labels = ['to_ship' => 'To ship', 'to_receive' => 'To receive', 'completed' => 'Completed'];
@endphp

<x-layouts.shop title="Status Delivery">
    <div x-data="{ filter: 'all', expanded: {} }" class="mx-auto max-w-4xl">
        <h1 class="mt-8 text-center text-3xl font-bold">Status Delivery</h1>

        <div class="mt-6 flex flex-wrap justify-center gap-3 sm:gap-4" role="tablist" aria-label="Filter orders by status">
            @foreach (['all' => 'All'] + $labels as $key => $label)
                <button
                    type="button"
                    role="tab"
                    @click="filter = '{{ $key }}'"
                    :aria-selected="filter === '{{ $key }}'"
                    :class="filter === '{{ $key }}' ? 'bg-ink text-white' : 'border border-muted/70 bg-white text-ink hover:bg-mist'"
                    class="min-w-28 rounded-full px-6 py-1.5 text-base transition"
                >{{ $label }}</button>
            @endforeach
        </div>

        <div class="mt-6 space-y-4">
            @foreach ($orders as $order)
                <article
                    x-show="filter === 'all' || filter === '{{ $order['status'] }}'"
                    class="overflow-hidden rounded-md border border-slate-300 bg-[#faf9f8]"
                >
                    <header class="flex items-center justify-between gap-4 border-b border-slate-300 bg-[#d5e7ff] px-3 py-1.5 text-sm sm:px-4 sm:text-base">
                        <h2 class="font-medium text-slate-700">Order_no : {{ $order['no'] }}</h2>
                        <span class="shrink-0 text-base font-bold text-blue-700 sm:text-lg">{{ $labels[$order['status']] }}</span>
                    </header>

                    <div class="px-4 pb-3 pt-2 sm:px-5">
                        <ul class="space-y-3">
                            @foreach ($order['items'] as $index => $item)
                                <li
                                    x-show="expanded['{{ $order['no'] }}'] || {{ $index === 0 ? 'true' : 'false' }}"
                                    class="flex items-center gap-3 sm:gap-4"
                                >
                                    <div class="size-16 shrink-0 overflow-hidden rounded-md bg-white sm:size-20">
                                        <img src="{{ asset('images/badminton-racket.jpg') }}" alt="" class="size-full object-cover">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <h3 class="font-bold text-slate-800 sm:text-lg">{{ $item['name'] }}</h3>
                                            @if ($order['status'] === 'completed')
                                                <a
                                                    href="{{ route('review.create', ['product' => $item['name']]) }}"
                                                    class="rounded border border-blue-400 px-1.5 py-0.5 text-[10px] leading-tight text-blue-700 transition hover:bg-blue-50 sm:text-xs"
                                                >Review</a>
                                            @endif
                                        </div>
                                        <ul class="mt-0.5 space-y-0.5 text-[11px] leading-snug text-slate-400 sm:text-xs">
                                            @foreach ($item['variants'] as $variant)
                                                <li>{{ $variant }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <p class="shrink-0 text-sm text-blue-700 sm:text-base">฿{{ number_format($item['total'], 2) }}</p>
                                </li>
                            @endforeach
                        </ul>

                        @if (count($order['items']) > 1)
                            <button
                                type="button"
                                @click="expanded['{{ $order['no'] }}'] = !expanded['{{ $order['no'] }}']"
                                class="mt-2 text-left text-xs text-slate-500 transition hover:text-blue-700"
                                :aria-expanded="Boolean(expanded['{{ $order['no'] }}'])"
                            >
                                <span x-text="expanded['{{ $order['no'] }}'] ? 'View Less' : 'View More'"></span>
                            </button>
                        @endif

                        <div class="mt-1 text-right text-sm text-blue-700 sm:text-base">
                            Total in {{ count($order['items']) }} item{{ count($order['items']) === 1 ? '' : 's' }} :
                            <span class="font-medium">฿{{ number_format(collect($order['items'])->sum('total'), 2) }}</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</x-layouts.shop>
