<x-layouts.shop title="Write a review">
    <section class="mx-auto w-full max-w-2xl px-4 pb-16 pt-8 sm:px-8 sm:pt-12">
        <a href="{{ route('orders') }}" class="inline-flex items-center gap-2 text-sm font-medium text-brand hover:text-ink">
            <span aria-hidden="true">←</span> Back to order status
        </a>

        <div class="mt-8 rounded-2xl border border-mist bg-white p-6 shadow-sm sm:p-10">
            <header class="text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand">Your feedback</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-ink">Write your review</h1>
                <p class="mt-2 text-sm text-muted">
                    {{ request()->query('product', 'Share your experience with this product') }}
                </p>
            </header>

            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">
                This is a design preview. Reviews are not submitted until the review backend is connected.
            </div>

            <form x-data="{ rating: 0, attempted: false }" @submit.prevent="attempted = true" class="mt-7 space-y-6">
                <fieldset>
                    <legend class="mb-3 text-sm font-medium text-ink">Your rating</legend>
                    <div class="flex justify-center gap-2" role="radiogroup" aria-label="Rating from 1 to 5 stars">
                        @foreach (range(1, 5) as $star)
                            <label class="cursor-pointer">
                                <input
                                    type="radio"
                                    name="rating"
                                    value="{{ $star }}"
                                    x-model.number="rating"
                                    required
                                    class="peer sr-only"
                                >
                                <span
                                    class="pointer-events-none text-4xl text-zinc-300 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-4 peer-focus-visible:outline-brand"
                                    :style="{ color: rating >= {{ $star }} ? '#ffd24d' : '#d4d4d4' }"
                                    aria-hidden="true"
                                >★</span>
                                <span class="sr-only">{{ $star }} {{ $star === 1 ? 'star' : 'stars' }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="mt-2 text-center text-sm font-medium text-muted" aria-live="polite" x-text="rating ? rating + ' out of 5 stars' : 'Select a rating'"></p>
                </fieldset>

                <div>
                    <label for="review-comment" class="mb-2 block text-sm font-medium text-ink">Comment</label>
                    <textarea
                        id="review-comment"
                        name="comment"
                        rows="5"
                        required
                        maxlength="2000"
                        class="w-full resize-y rounded-lg border border-muted/50 px-4 py-3 text-ink outline-none transition placeholder:text-muted/70 focus:border-brand focus:ring-2 focus:ring-brand/20"
                        placeholder="Write your review..."
                    ></textarea>
                </div>

                <p x-cloak x-show="attempted" role="status" class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                    Preview only: your review was not submitted. Backend persistence is not connected yet.
                </p>

                <div class="flex justify-center border-t border-mist pt-5">
                    <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-ink px-8 py-3 text-sm font-semibold text-white transition hover:bg-brand focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 sm:w-auto sm:min-w-44">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </section>
</x-layouts.shop>
