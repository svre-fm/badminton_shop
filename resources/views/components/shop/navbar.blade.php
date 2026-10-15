@props(['active' => null, 'cartCount' => 0])

@php
    $categories = ['Badminton racket', 'Badminton string', 'Grip', 'Shuttlecock'];
    $returnTo = request()->query('return_to');

    if (! is_string($returnTo) || $returnTo === '') {
        $returnTo = request()->routeIs('login', 'register', 'password.request', 'password.reset')
            ? null
            : request()->getRequestUri();
    }

    $returnQuery = $returnTo ? ['return_to' => $returnTo] : [];
@endphp

<header class="relative z-30 border-b border-mist bg-white shadow-sm">
    <div class="mx-auto grid min-h-20 w-full max-w-screen-2xl grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-2 px-4 py-3 sm:min-h-24 sm:gap-4 sm:px-8 lg:px-12">
        {{-- Left: nav --}}
        <nav class="flex items-center gap-1 text-sm font-medium sm:gap-7 sm:text-base lg:gap-9" aria-label="Main navigation">
            <a href="{{ route('home') }}"
               @class([
                   'hidden rounded-full px-3 py-2 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 sm:inline-flex sm:px-4',
                   'bg-page text-ink' => $active === 'home',
                   'text-muted hover:bg-page hover:text-ink' => $active !== 'home',
               ])>Home</a>

            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                <button
                    type="button"
                    @click="open = !open"
                    @class([
                        'flex cursor-pointer items-center gap-1 rounded-full px-2.5 py-2 font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 sm:gap-1.5 sm:px-4',
                        'bg-page text-ink' => $active === 'shop',
                        'text-muted hover:bg-page hover:text-ink' => $active !== 'shop',
                    ])
                    :aria-expanded="open"
                >
                    Shop
                    <svg class="size-4 text-muted transition-transform duration-200" :class="open ? 'rotate-90' : ''" viewBox="0 0 20 20" fill="none"
                         stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m7 4 6 6-6 6"/></svg>
                </button>

                <ul x-show="open" x-cloak x-transition.opacity
                    class="absolute left-0 top-full z-30 mt-2 w-56 divide-y divide-mist overflow-hidden rounded-xl border border-mist bg-white text-sm shadow-xl">
                    @foreach ($categories as $c)
                        <li>
                            <a href="{{ route('shop.index', ['category' => Str::slug($c)]) }}"
                               class="block px-4 py-3 text-muted transition hover:bg-page hover:text-ink">{{ $c }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </nav>

        {{-- Center: logo --}}
        <a href="{{ route('home') }}" class="flex min-w-0 justify-self-center rounded-lg p-1 transition hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2" aria-label="Badminton store">
            <img src="{{ asset('images/store-logo.png') }}" alt="Badminton store" class="h-12 w-auto max-w-32 object-contain sm:h-14 sm:max-w-48 lg:h-16 lg:max-w-56">
        </a>

        {{-- Right: cart + account --}}
        <div class="flex items-center justify-end gap-2 sm:gap-4">
            <a href="{{ auth()->check() ? route('cart') : route('login', $returnQuery) }}"
               class="flex min-h-10 cursor-pointer items-center gap-1.5 rounded-full border border-mist bg-white px-3 py-2 text-xs font-medium text-ink shadow-sm transition hover:border-brand hover:bg-page focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 sm:gap-2 sm:px-4 sm:text-sm">
                <span class="max-[380px]:hidden">My cart</span>
                <svg class="size-4 sm:size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path d="M3 4h2l2 11h11l2-8H6"/><circle cx="9" cy="19" r="1.2"/><circle cx="17" cy="19" r="1.2"/>
                </svg>
                @if ($cartCount)
                    <span class="grid min-w-5 place-items-center rounded-full bg-brand px-1.5 text-xs font-semibold text-white">{{ $cartCount }}</span>
                @endif
            </a>

            <span class="h-8 w-px bg-mist" aria-hidden="true"></span>

            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                <button type="button" @click="open = !open"
                        @class([
                            'grid size-10 shrink-0 cursor-pointer place-items-center overflow-hidden rounded-full ring-2 ring-white shadow-sm transition hover:scale-105 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 sm:size-11',
                            'bg-brand text-white' => auth()->check(),
                            'bg-slate-200 text-slate-500' => ! auth()->check(),
                        ])
                        aria-label="Account menu" :aria-expanded="open">
                    @auth
                        <span class="text-sm font-semibold text-white">{{ auth()->user()->initials() }}</span>
                    @else
                        <svg viewBox="0 0 24 24" class="size-full p-1.5 text-slate-500" fill="currentColor">
                            <circle cx="12" cy="9" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/>
                        </svg>
                    @endauth
                </button>

                <ul x-show="open" x-cloak x-transition.opacity
                    class="absolute right-0 top-full z-30 mt-2 w-64 divide-y divide-mist overflow-hidden rounded-xl border border-mist bg-white text-muted shadow-xl">
                    @auth
                        <li class="flex items-center gap-3 px-3 py-3">
                            <span class="grid size-10 shrink-0 place-items-center rounded-full bg-brand text-sm font-semibold text-white">
                                {{ auth()->user()->initials() }}
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate font-medium text-ink">{{ auth()->user()->name }}</span>
                                <span class="block truncate text-xs">{{ auth()->user()->email }}</span>
                            </span>
                        </li>
                        <li>
                            <a href="{{ route('profile.edit') }}"
                               @class([
                                   'block px-3 py-2 font-medium transition',
                                   'bg-page text-brand' => request()->routeIs('profile.edit'),
                                   'text-muted hover:bg-mist hover:text-ink' => ! request()->routeIs('profile.edit'),
                               ])>
                                Member information
                            </a>
                        </li>
                        <li><a href="{{ route('favorites') }}" class="block px-3 py-2 hover:bg-mist hover:text-ink">Favorites</a></li>
                        <li><a href="{{ route('orders') }}" class="block px-3 py-2 hover:bg-mist hover:text-ink">Order status</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="block w-full px-3 py-2 text-left hover:bg-mist hover:text-ink">Log out</button>
                            </form>
                        </li>
                    @else
                        <li><a href="{{ route('login', $returnQuery) }}" class="block px-3 py-2 hover:bg-mist hover:text-ink">Log in</a></li>
                        @if (Route::has('register'))
                            <li><a href="{{ route('register', $returnQuery) }}" class="block px-3 py-2 hover:bg-mist hover:text-ink">Register</a></li>
                        @endif
                    @endauth
                </ul>
            </div>
        </div>
    </div>
</header>
