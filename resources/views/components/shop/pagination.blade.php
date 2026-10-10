@props(['pages' => 4, 'current' => 1])
<nav class="mt-10 flex items-center justify-center gap-3 text-sm text-muted" aria-label="Pagination">
    @if ($current > 1)
        <a href="{{ request()->fullUrlWithQuery(['page' => $current - 1]) }}" class="grid size-7 place-items-center rounded-full border border-mist bg-white text-muted transition hover:border-brand hover:bg-page hover:text-brand focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2" aria-label="Previous page" title="Previous page">
            <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m12 4-6 6 6 6"/>
            </svg>
        </a>
    @else
        <span class="grid size-7 cursor-not-allowed place-items-center rounded-full border border-mist bg-page text-muted/50" aria-label="Previous page" aria-disabled="true">
            <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m12 4-6 6 6 6"/>
            </svg>
        </span>
    @endif
    @foreach (range(1, $pages) as $p)
        @if ($p === $current)
            <span class="grid size-7 place-items-center rounded-full bg-ink font-bold text-white" aria-current="page">{{ $p }}</span>
        @else
            <a href="{{ request()->fullUrlWithQuery(['page' => $p]) }}" class="grid size-7 place-items-center rounded-full transition hover:bg-page hover:text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2">{{ $p }}</a>
        @endif
    @endforeach
    @if ($current < $pages)
        <a href="{{ request()->fullUrlWithQuery(['page' => $current + 1]) }}" class="grid size-7 place-items-center rounded-full border border-mist bg-white text-muted transition hover:border-brand hover:bg-page hover:text-brand focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2" aria-label="Next page" title="Next page">
            <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8 4 6 6-6 6"/>
            </svg>
        </a>
    @else
        <span class="grid size-7 cursor-not-allowed place-items-center rounded-full border border-mist bg-page text-muted/50" aria-label="Next page" aria-disabled="true">
            <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8 4 6 6-6 6"/>
            </svg>
        </span>
    @endif
</nav>
