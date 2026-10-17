@php
    $imageUrl = fn ($path) => \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/']) ? $path : asset($path);
@endphp

<x-layouts.shop title="Order Status" active="orders">
    <section class="py-8">
        <h1 class="text-center text-4xl font-bold">Order Status</h1>

        <nav aria-label="Filter orders by status" class="mx-auto mt-6 flex max-w-4xl flex-wrap justify-center gap-3">
            @foreach ($statusGroups as $group)
                <a
                    href="{{ route('orders.status', ['status' => $group['key']]) }}"
                    @class([
                        'min-w-32 rounded-full border border-ink px-6 py-2 text-center',
                        'bg-ink text-white' => $selectedStatus === $group['key'],
                        'bg-white text-ink hover:bg-mist' => $selectedStatus !== $group['key'],
                    ])
                    @if ($selectedStatus === $group['key']) aria-current="page" @endif
                >
                    {{ $group['label'] }}
                    <span class="ml-1 text-sm">({{ $group['count'] }})</span>
                </a>
            @endforeach
        </nav>

        @if ($orders->isEmpty())
            <div class="mt-8 rounded-xl border border-ink/20 px-6 py-12 text-center">
                <p class="text-lg text-muted">No orders in this category.</p>
                <a href="{{ route('products.index') }}" class="mt-4 inline-block rounded-full bg-ink px-5 py-2 text-white">
                    Browse products
                </a>
            </div>
        @else
            <div class="mt-8 space-y-6">
                @foreach ($orders as $order)
                    <article class="overflow-hidden rounded-lg border border-ink/40 shadow-sm">
                        <header class="flex flex-wrap items-center justify-between gap-2 bg-blue-100 px-4 py-3">
                            <h2 class="text-lg font-medium">
                                Order no: <span class="font-semibold">{{ $order->order_no }}</span>
                            </h2>
                            <span class="text-lg font-bold text-blue-700">{{ $order->status }}</span>
                        </header>

                        <div class="divide-y divide-ink/20 px-4">
                            @foreach ($order->items as $item)
                                @php
                                    $product = $item->product;
                                    $itemTotal = (float) $item->unit_price * $item->quantity;
                                @endphp
                                <div class="grid grid-cols-[5rem_1fr_auto] items-center gap-4 py-4 sm:grid-cols-[7rem_1fr_auto]">
                                    @if ($product)
                                        <a href="{{ route('products.show', $product) }}" class="aspect-square overflow-hidden rounded-lg bg-mist">
                                            <img src="{{ $imageUrl($product->image) }}" alt="{{ $product->name }}" loading="lazy" class="size-full object-cover">
                                        </a>
                                        <div class="min-w-0">
                                            <a href="{{ route('products.show', $product) }}" class="font-semibold hover:underline">
                                                {{ $product->name }}
                                            </a>
                                            <p class="mt-1 text-sm text-muted">
                                                {{ $item->color }}
                                                @if ($item->tension)
                                                    · {{ $item->tension }} lbs
                                                @endif
                                            </p>
                                            <p class="text-sm text-muted">
                                                {{ number_format((float) $item->unit_price, 2) }} × {{ $item->quantity }}
                                            </p>
                                        </div>
                                    @else
                                        <div class="aspect-square rounded-lg bg-mist"></div>
                                        <p class="text-muted">Product no longer available</p>
                                    @endif
                                    <p class="whitespace-nowrap text-right font-medium">
                                        {{ number_format($itemTotal, 2) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        <footer class="flex justify-end px-4 py-4 text-lg">
                            <span class="font-medium">Total: {{ number_format((float) $order->total_price, 2) }}</span>
                        </footer>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.shop>
