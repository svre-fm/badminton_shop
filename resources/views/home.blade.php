@php
    // TODO: replace with real data from the controller / Livewire component
    $categories = [
        ['name' => 'Badminton racket', 'slug' => 'badminton-racket', 'image' => 'badminton-racket-icon.png'],
        ['name' => 'Badminton string', 'slug' => 'badminton-string', 'image' => 'badminton-string-icon.png'],
        ['name' => 'Grip',             'slug' => 'grip',             'image' => 'grip-icon.png'],
        ['name' => 'Shuttlecock',      'slug' => 'shuttlecock',      'image' => 'shuttlecock-icon.png'],
    ];
    $bestSellers = collect(range(1, 8))->map(fn ($i) => [
        'name' => "Racket $i",
        'price' => 100,
        'image' => asset('images/badminton-racket.jpg'),
        'href' => route('shop.product', ['category' => 'badminton-racket', 'slug' => 'sample']),
    ]);
@endphp

<x-layouts.shop title="Home" active="home" :full-width="true">
    {{-- Hero --}}
    <section class="mx-auto flex min-h-[clamp(13.5rem,26vw,15rem)] w-[92%] items-center justify-center rounded-b-3xl bg-brand px-5 py-12 text-center sm:w-[87%] sm:px-10 sm:py-16 lg:w-[82%]">
        <h1 class="max-w-5xl text-4xl font-bold uppercase leading-tight text-white sm:text-[3.25rem] lg:text-[4rem]">Welcome to our store!!</h1>
    </section>

    <div class="mx-auto w-full max-w-screen-2xl px-4 sm:px-8 lg:px-12">
        {{-- Categories --}}
        <section class="mt-10 text-center sm:mt-14" aria-labelledby="cat-title">
            <h2 id="cat-title" class="text-3xl font-bold sm:text-4xl">Category</h2>

            <ul class="mt-6 flex flex-wrap justify-center gap-4 sm:mt-8 sm:gap-8">
                @foreach ($categories as $c)
                    <li>
                        <a href="{{ route('shop.index', ['category' => $c['slug']]) }}"
                           class="flex size-28 flex-col items-center justify-center gap-2 rounded-2xl border-[3px] border-brand bg-white
                                  text-xs font-semibold shadow-md transition hover:scale-105 hover:shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 sm:size-32 sm:text-sm">
                            <span class="grid size-12 place-items-center sm:size-14" aria-hidden="true">
                                <img src="{{ asset('images/'.$c['image']) }}" alt="" class="size-full object-contain">
                            </span>
                            {{ $c['name'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Best seller carousel --}}
        <section class="mt-12 sm:mt-16" x-data="{
            el: null, canPrev: false, canNext: true,
            init() { this.el = this.$refs.track; this.update(); },
            update() {
                this.canPrev = this.el.scrollLeft > 4;
                this.canNext = this.el.scrollLeft + this.el.clientWidth < this.el.scrollWidth - 4;
            },
            go(dir) { this.el.scrollBy({ left: dir * this.el.clientWidth * 0.8, behavior: 'smooth' }); }
        }">
        <div class="flex items-center gap-4">
            <h2 class="text-3xl font-bold sm:text-4xl">Best seller</h2>
            <hr class="flex-1 border-brand/50">
            <div class="flex gap-2">
                <button
                    type="button"
                    @click="go(-1)"
                    :disabled="!canPrev"
                    aria-label="Previous best sellers"
                    title="Previous best sellers"
                    class="group grid size-11 cursor-pointer place-items-center rounded-full border-2 border-brand bg-white text-brand shadow-sm transition duration-200 hover:-translate-x-0.5 hover:bg-brand hover:text-white hover:shadow-md active:scale-90 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-300 disabled:shadow-none disabled:hover:translate-x-0 disabled:hover:bg-slate-100 disabled:hover:text-slate-300 disabled:active:scale-100 sm:size-12"
                    :class="canPrev && 'ring-2 ring-brand/15'"
                >
                    <svg class="size-5 transition-transform duration-200 group-hover:-translate-x-0.5 group-disabled:translate-x-0 sm:size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                </button>
                <button
                    type="button"
                    @click="go(1)"
                    :disabled="!canNext"
                    aria-label="Next best sellers"
                    title="Next best sellers"
                    class="group grid size-11 cursor-pointer place-items-center rounded-full border-2 border-brand bg-brand text-white shadow-md transition duration-200 hover:translate-x-0.5 hover:shadow-lg active:scale-90 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-300 disabled:shadow-none disabled:hover:translate-x-0 disabled:active:scale-100 sm:size-12"
                    :class="canNext && 'ring-2 ring-brand/20'"
                >
                    <svg class="size-5 transition-transform duration-200 group-hover:translate-x-0.5 group-disabled:translate-x-0 sm:size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </button>
            </div>
        </div>

            <div x-ref="track" @scroll.passive="update()"
                 class="mt-5 flex snap-x snap-mandatory gap-5 overflow-x-auto pb-3 sm:gap-7 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach ($bestSellers as $p)
                    <div class="w-44 shrink-0 snap-start sm:w-56 lg:w-64">
                        <x-shop.product-card :name="$p['name']" :price="$p['price']" :image="$p['image']" :href="$p['href']" />
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.shop>
