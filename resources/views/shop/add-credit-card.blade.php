<x-layouts.shop title="Add credit card">
    <section class="mx-auto w-full max-w-xl px-4 pb-16 pt-8 sm:px-8 sm:pt-12">
        <a href="{{ route('checkout') }}" class="inline-flex items-center gap-2 text-sm font-medium text-brand hover:text-ink">
            <span aria-hidden="true">←</span> Back to checkout
        </a>

        <header class="mt-6 text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand">Payment method</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-ink">Add Credit Card</h1>
            <p class="mt-2 text-sm leading-6 text-muted">Add a card for a faster checkout experience.</p>
        </header>

        <form method="POST" action="{{ route('payment-method.store') }}" class="mt-6 space-y-5 rounded-2xl border border-mist bg-white p-6 shadow-sm sm:p-8">
            @csrf
            <div>
                <label for="card-number" class="mb-2 block text-sm font-medium text-ink">Card Number</label>
                <input
                    id="card-number"
                    name="card_no"
                    type="text"
                    inputmode="numeric"
                    autocomplete="cc-number"
                    required
                    maxlength="19"
                    class="w-full rounded-lg border border-muted/50 px-4 py-3 text-ink outline-none transition placeholder:text-muted/70 focus:border-brand focus:ring-2 focus:ring-brand/20"
                    placeholder="0000 0000 0000 0000"
                >
            </div>

            <div>
                <label for="card-expiry" class="mb-2 block text-sm font-medium text-ink">Expiry Date</label>
                <input
                    id="card-expiry"
                    name="expiry_date"
                    type="month"
                    autocomplete="cc-exp"
                    required
                    class="w-full rounded-lg border border-muted/50 px-4 py-3 text-ink outline-none transition placeholder:text-muted/70 focus:border-brand focus:ring-2 focus:ring-brand/20"
                    value="{{ old('expiry_date') }}"
                >
            </div>

            @if ($errors->any())
                <p role="alert" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</p>
            @endif

            <div class="flex flex-col-reverse gap-3 border-t border-mist pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('checkout') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-mist px-5 py-2.5 text-sm font-semibold text-ink transition hover:bg-page">
                    Cancel
                </a>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2">
                    Submit
                </button>
            </div>
        </form>
    </section>
</x-layouts.shop>
