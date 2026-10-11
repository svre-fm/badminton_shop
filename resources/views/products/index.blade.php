{{-- resources/views/products/index.blade.php --}}
<x-layouts.shop title="Shop" active="shop">
    <p>{{ $products->total() }} / {{ $totalInCategory }}</p>
    @foreach ($products as $product)
        <div>{{ $product->name }} — {{ $product->price }}</div>
    @endforeach
</x-layouts.shop>