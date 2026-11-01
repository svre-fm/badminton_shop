@php
    // OrderController ส่ง orders.order_no/order_date/status/shipping พร้อม relation order_items และ product
    // order_item.tension ยังไม่มีใน diagram ล่าสุด แม้ต้องเพิ่มตามข้อตกลงเรื่อง tension ของไม้
    $orders = collect($orders ?? []);
    $status_labels = ['To ship' => 'To ship', 'To Recieve' => 'To receive', 'Complete' => 'Completed', 'Cancelled' => 'Cancelled'];
    $status_groups = collect($statusGroups ?? [
        ['key' => 'all', 'label' => 'All', 'count' => $orders->count()],
        ['key' => 'to_ship', 'label' => 'To ship', 'count' => 0],
        ['key' => 'to_receive', 'label' => 'To receive', 'count' => 0],
        ['key' => 'completed', 'label' => 'Completed', 'count' => 0],
        ['key' => 'cancelled', 'label' => 'Cancelled', 'count' => 0],
    ]);
    $reviewed_product_keys = $reviewedProductKeys ?? [];
    $variant_images = $variantImages ?? [];
@endphp

<x-layouts.shop title="Status Delivery">
    <div x-data="{
        filter: @js($selectedStatus ?? 'all'),
        expanded: {},
        statusKey(status) {
            return ({ 'To ship': 'to_ship', 'To Recieve': 'to_receive', 'Complete': 'completed', 'Cancelled': 'cancelled' })[status] ?? status;
        }
    }" class="mx-auto max-w-4xl">
        <h1 class="mt-8 text-center text-3xl font-bold">Status Delivery</h1>

        <div class="mt-6 flex flex-wrap justify-center gap-3 sm:gap-4" role="tablist" aria-label="Filter orders by status">
            @foreach ($status_groups as $group)
                <button
                    type="button"
                    role="tab"
                    @click="filter = @js($group['key'])"
                    :aria-selected="filter === @js($group['key'])"
                    :class="filter === @js($group['key']) ? 'bg-ink text-white' : 'border border-muted/70 bg-white text-ink hover:bg-mist'"
                    class="min-w-28 rounded-full px-6 py-1.5 text-base transition"
                >{{ $group['label'] }} ({{ $group['count'] }})</button>
            @endforeach
        </div>

        @if ($orders->isEmpty())
            <p class="mt-8 rounded-xl border border-dashed border-mist px-6 py-12 text-center text-muted">
                Your order history will appear here when you place an order.
            </p>
        @else
        <div class="mt-6 space-y-4">
            @foreach ($orders as $order)
                @php $review_button_products = []; @endphp
                <article
                    x-show="filter === 'all' || filter === statusKey(@js(data_get($order, 'status')))"
                    class="overflow-hidden rounded-md border border-slate-300 bg-[#faf9f8]"
                >
                    <header class="flex items-center justify-between gap-4 border-b border-slate-300 bg-[#d5e7ff] px-3 py-1.5 text-sm sm:px-4 sm:text-base">
                        <h2 class="font-medium text-slate-700">Order_no : {{ data_get($order, 'order_no') }}</h2>
                        <span class="shrink-0 text-base font-bold text-blue-700 sm:text-lg">{{ $status_labels[data_get($order, 'status')] ?? data_get($order, 'status') }}</span>
                        <span class="text-xs text-slate-500">{{ data_get($order, 'order_date') ? \Illuminate\Support\Carbon::parse(data_get($order, 'order_date'))->toDateString() : '' }}</span>
                    </header>

                    <div class="px-4 pb-3 pt-2 sm:px-5">
                        <ul class="space-y-3">
                            @foreach (data_get($order, 'items', []) as $index => $order_item)
                                <li
                                    x-show="expanded['{{ data_get($order, 'order_no') }}'] || {{ $index === 0 ? 'true' : 'false' }}"
                                    class="flex items-center gap-3 sm:gap-4"
                                >
                                    <div class="size-16 shrink-0 overflow-hidden rounded-md bg-white sm:size-20">
                                        @php
                                            $product_id = (int) data_get($order_item, 'product_id');
                                            $review_button_available = data_get($order, 'status') === 'Complete'
                                                && ! in_array(data_get($order, 'order_no').'|'.$product_id, $reviewed_product_keys, true)
                                                && ! isset($review_button_products[$product_id]);
                                            $product_image = ($variant_images[$product_id.'|'.data_get($order_item, 'color')] ?? null)
                                                ?: data_get($order_item, 'product.image');
                                        @endphp
                                        @if ($product_image)
                                            <img src="{{ $product_image }}" alt="{{ data_get($order_item, 'product.name') }} - {{ data_get($order_item, 'color') }}" class="size-full object-cover">
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <h3 class="font-bold text-slate-800 sm:text-lg">{{ data_get($order_item, 'product.name') }}</h3>
                                            @if ($review_button_available)
                                                @php $review_button_products[$product_id] = true; @endphp
                                                <a
                                                    href="{{ route('review.create', ['product_id' => data_get($order_item, 'product_id'), 'order_no' => data_get($order, 'order_no')]) }}"
                                                    class="rounded border border-blue-400 px-1.5 py-0.5 text-[10px] leading-tight text-blue-700 transition hover:bg-blue-50 sm:text-xs"
                                                >Review</a>
                                            @endif
                                        </div>
                                        <ul class="mt-0.5 space-y-0.5 text-[11px] leading-snug text-slate-400 sm:text-xs">
                                            <li>
                                                {{ data_get($order_item, 'color') }}
                                                @if (data_get($order_item, 'tension'))
                                                    · {{ data_get($order_item, 'tension') }} lbs
                                                @endif
                                                · {{ data_get($order_item, 'quantity') }} × ฿{{ number_format(data_get($order_item, 'unit_price'), 2) }}
                                            </li>
                                        </ul>
                                    </div>
                                    <p class="shrink-0 text-sm text-blue-700 sm:text-base">฿{{ number_format(data_get($order_item, 'unit_price') * data_get($order_item, 'quantity'), 2) }}</p>
                                </li>
                            @endforeach
                        </ul>

                        @if (count(data_get($order, 'items', [])) > 1)
                            <button
                                type="button"
                                @click="expanded['{{ data_get($order, 'order_no') }}'] = !expanded['{{ data_get($order, 'order_no') }}']"
                                class="mt-2 text-left text-xs text-slate-500 transition hover:text-blue-700"
                                :aria-expanded="Boolean(expanded['{{ data_get($order, 'order_no') }}'])"
                            >
                                <span x-text="expanded['{{ data_get($order, 'order_no') }}'] ? 'View Less' : 'View More'"></span>
                            </button>
                        @endif

                        <div class="mt-1 text-right text-sm text-blue-700 sm:text-base">
                            Total in {{ count(data_get($order, 'items', [])) }} item{{ count(data_get($order, 'items', [])) === 1 ? '' : 's' }} :
                            <span class="font-medium">฿{{ number_format(data_get($order, 'total_price'), 2) }}</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        @endif
    </div>
</x-layouts.shop>
