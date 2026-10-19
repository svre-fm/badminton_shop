@php
    $editing = request()->routeIs('address.edit');
    $delivery_address = $delivery_address ?? null;
@endphp

<x-layouts.shop :title="$editing ? 'Edit address' : 'Add address'">
    <section class="mx-auto w-full max-w-2xl px-4 pb-16 pt-8 sm:px-8 sm:pt-12">
        <a href="{{ route('checkout') }}" class="inline-flex items-center gap-2 text-sm font-medium text-brand hover:text-ink">
            <span aria-hidden="true">←</span> Back to checkout
        </a>

        <header class="mt-6">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand">Delivery details</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-ink sm:text-4xl">
                {{ $editing ? 'Edit your address' : 'Add your address' }}
            </h1>
            <p class="mt-2 text-sm leading-6 text-muted sm:text-base">
                Enter the recipient and delivery address for your order.
            </p>
        </header>

        <form method="POST" action="{{ $editing ? route('address.update', request()->route('address')) : route('address.store') }}" class="mt-6 space-y-5 rounded-2xl border border-mist bg-white p-6 shadow-sm sm:p-8">
            @csrf
            @if ($editing) @method('PUT') @endif
            <div>
                <label for="address-name" class="mb-2 block text-sm font-medium text-ink">Name</label>
                <input
                    id="address-name"
                    name="name"
                    type="text"
                    autocomplete="name"
                    required
                    value="{{ old('name', data_get($delivery_address, 'name')) }}"
                    class="w-full rounded-lg border border-muted/50 px-4 py-3 text-ink outline-none transition placeholder:text-muted/70 focus:border-brand focus:ring-2 focus:ring-brand/20"
                    placeholder="Full name"
                >
            </div>

            <div>
                <label for="address-phone" class="mb-2 block text-sm font-medium text-ink">Phone number</label>
                <input
                    id="address-phone"
                    name="phone"
                    type="tel"
                    autocomplete="tel"
                    required
                    value="{{ old('phone', data_get($delivery_address, 'phone')) }}"
                    class="w-full rounded-lg border border-muted/50 px-4 py-3 text-ink outline-none transition placeholder:text-muted/70 focus:border-brand focus:ring-2 focus:ring-brand/20"
                    placeholder="Phone number"
                >
            </div>

            <div>
                <label for="address-text" class="mb-2 block text-sm font-medium text-ink">Address</label>
                <textarea
                    id="address-text"
                    name="address"
                    rows="4"
                    autocomplete="street-address"
                    required
                    class="w-full resize-y rounded-lg border border-muted/50 px-4 py-3 text-ink outline-none transition placeholder:text-muted/70 focus:border-brand focus:ring-2 focus:ring-brand/20"
                    placeholder="House number, street, district, province, postal code"
                >{{ old('address', data_get($delivery_address, 'address')) }}</textarea>
            </div>

            @if ($errors->any())
                <p role="alert" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</p>
            @endif

            <div class="flex flex-col-reverse gap-3 border-t border-mist pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('checkout') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-mist px-5 py-2.5 text-sm font-semibold text-ink transition hover:bg-page">
                    Cancel
                </a>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2">
                    {{ $editing ? 'Save address' : 'Add address' }}
                </button>
            </div>
        </form>
    </section>
</x-layouts.shop>
