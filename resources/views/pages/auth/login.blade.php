<x-layouts::auth.login :title="__('Log in')">
    <div class="flex flex-col gap-8">
        <h1 class="text-center text-4xl font-bold leading-tight text-ink sm:text-5xl">
            {{ __('Log in to your account') }}
        </h1>

        <x-auth-session-status class="text-center text-sm" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="mx-auto flex w-full max-w-[26rem] flex-col gap-6">
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

            <div class="flex flex-col gap-2">
                <div class="flex items-baseline justify-between gap-3">
                    <label for="password" class="text-lg">{{ __('Password') }}</label>
                    @if (Route::has('password.request'))
                        <a class="text-base text-ink hover:text-brand hover:underline" href="{{ route('password.request', request()->query('return_to') ? ['return_to' => request()->query('return_to')] : []) }}" wire:navigate>
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="{{ __('Password') }}"
                    @if ($errors->has('password')) aria-invalid="true" @endif
                    @class([
                        'h-[53px] w-full rounded-lg border bg-page px-3 text-base text-ink placeholder:text-zinc-400 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30',
                        'border-red-600' => $errors->has('password'),
                        'border-ink' => ! $errors->has('password'),
                    ])
                />
                @error('password')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label for="remember" class="flex cursor-pointer items-center gap-2 text-base">
                <input
                    id="remember"
                    name="remember"
                    type="checkbox"
                    value="1"
                    @checked(old('remember'))
                    class="size-5 rounded-sm border border-ink text-ink accent-ink focus:ring-brand"
                >
                {{ __('Remember me') }}
            </label>

            <button
                type="submit"
                data-test="login-button"
                class="h-[53px] w-full rounded-lg bg-ink text-base text-white transition hover:bg-ink/90 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2"
            >
                {{ __('Log in') }}
            </button>
        </form>

        @if (Route::has('register'))
            <div class="text-center text-sm text-muted">
                <span>{{ __('Don\'t have an account?') }}</span>
                <a class="text-ink hover:text-brand hover:underline" href="{{ route('register', request()->query('return_to') ? ['return_to' => request()->query('return_to')] : []) }}" wire:navigate>{{ __('Sign up') }}</a>
            </div>
        @endif
    </div>
</x-layouts::auth.login>
