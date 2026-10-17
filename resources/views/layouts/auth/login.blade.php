@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white font-sans text-ink antialiased">
        <x-shop.navbar />

        <main class="mx-auto flex w-full max-w-2xl flex-col px-5 pb-12 pt-16 sm:pt-28 md:pt-[7.75rem]">
            {{ $slot }}
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
