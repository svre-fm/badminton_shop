{{-- resources/views/components/layouts/shop.blade.php  →  ใช้เป็น <x-layouts.shop title="..." active="shop"> --}}
@props(['title' => null, 'active' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' | ' : '' }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-ink antialiased">
    <header class="border-b border-ink/20">
        <nav class="mx-auto flex max-w-screen-2xl items-center justify-between gap-4 px-6 py-4">
            <div class="flex items-center gap-8 text-xl">
                <a href="{{ route('home') }}" @class(['font-bold text-ink' => $active === 'home', 'text-muted' => $active !== 'home'])>HOME</a>
                <a href="{{ route('products.index') }}" @class(['font-bold text-ink' => $active === 'shop', 'text-muted' => $active !== 'shop'])>SHOP</a>
            </div>

            <a href="{{ route('home') }}" class="text-2xl font-bold tracking-wide">{{ config('app.name') }}</a>

            <div class="flex items-center gap-4">
                @if (Route::has('cart'))
                    <a href="{{ route('cart') }}" class="rounded-full border border-ink px-4 py-1">MY CART</a>
                @endif

                @auth
                    <a href="{{ route('favorites.index') }}" @class(['font-bold text-ink' => $active === 'favorites', 'text-muted' => $active !== 'favorites'])>FAVORITES</a>
                    <a href="{{ route('orders.status') }}" @class(['font-bold text-ink' => $active === 'orders', 'text-muted' => $active !== 'orders'])>MY ORDERS</a>
                    <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" class="rounded-full bg-mist px-4 py-1">{{ auth()->user()->name }}</a>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="underline underline-offset-2">Log in</a>
                    @endif
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-full bg-ink px-4 py-1 text-white">Register</a>
                    @endif
                @endauth
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-screen-2xl px-6 pb-16">
        {{ $slot }}
    </main>

    {{-- สคริปต์ของ starter kit (Flux/Alpine) — หน้า show ใช้ Alpine (x-data) ต้องโหลดจากตรงนี้หรือจาก app.js --}}
    @fluxScripts
</body>
</html>