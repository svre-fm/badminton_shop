<x-layouts::auth.login :title="__('Forgot password')">
    <div class="flex flex-col gap-8">
        <div class="flex flex-col gap-3 text-center">
            <h1 class="text-4xl font-bold leading-tight text-ink sm:text-5xl">
                {{ __('Forgot password?') }}
            </h1>
            <p class="text-base text-muted">
                {{ __('Enter your email to receive a password reset link') }}
            </p>
        </div>

        <x-auth-session-status class="text-center text-sm" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="mx-auto flex w-full max-w-[26rem] flex-col gap-6">
            @csrf

            <div class="flex flex-col gap-2">
                <label for="email" class="text-lg">{{ __('Email address') }}</label>
                <input
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="email@example.com"
                    @if ($errors->has('email')) aria-invalid="true" @endif
                    @class([
                        'h-[53px] w-full rounded-lg border bg-page px-3 text-base text-ink placeholder:text-zinc-400 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30',
                        'border-red-600' => $errors->has('email'),
                        'border-ink' => ! $errors->has('email'),
                    ])
                >
                @error('email')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                data-test="email-password-reset-link-button"
                class="h-[53px] w-full rounded-lg bg-ink text-base text-white transition hover:bg-ink/90 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2"
            >
                {{ __('Email password reset link') }}
            </button>
        </form>

        <div class="text-center text-sm text-muted">
            <span>{{ __('Or, return to') }}</span>
            <a class="text-ink hover:text-brand hover:underline" href="{{ route('login', request()->query('return_to') ? ['return_to' => request()->query('return_to')] : []) }}" wire:navigate>{{ __('log in') }}</a>
        </div>
    </div>
</x-layouts::auth.login>
