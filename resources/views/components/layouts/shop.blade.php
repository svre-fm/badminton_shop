<!DOCTYPE html>
@props(['title' => null, 'active' => null, 'fullWidth' => false])

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Badminton Store' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-white font-sans text-ink antialiased">
    <x-shop.navbar :active="$active ?? null" />

    <main @class([
        'w-full pb-16' => $fullWidth,
        'mx-auto w-full max-w-7xl px-4 pb-16 sm:px-8' => ! $fullWidth,
    ])>
        {{ $slot }}
    </main>

    @livewireScripts
    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist
    @fluxScripts
</body>
</html>
