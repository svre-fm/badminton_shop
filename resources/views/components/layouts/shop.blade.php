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
        @if (session('status'))
            <div class="mx-auto mt-4 w-full max-w-7xl rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mx-auto mt-4 w-full max-w-7xl rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                {{ session('error') }}
            </div>
        @endif
        {{ $slot }}
    </main>

    @livewireScripts
    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist
    @if (session('toast'))
        <div
            x-data
            x-init="$nextTick(() => $dispatch('toast-show', {
                duration: 5000,
                slots: { text: @js(session('toast.text')) },
                dataset: { variant: @js(session('toast.variant')) },
            }))"
        ></div>
    @endif
    @fluxScripts
</body>
</html>
