<x-layouts::auth.login :title="__('Register')">
    <div class="flex flex-col gap-8">
        <h1 class="text-center text-4xl font-bold leading-tight text-ink sm:text-5xl">
            {{ __('Create an account') }}
        </h1>

        <x-auth-session-status class="text-center text-sm" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="mx-auto flex w-full max-w-[26rem] flex-col gap-5">
            @csrf

            <div class="flex flex-col gap-2">
                <label for="name" class="text-lg">{{ __('Name') }}</label>
                <input
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="{{ __('Full name') }}"
                    @if ($errors->has('name')) aria-invalid="true" @endif
                    @class([
                        'h-[53px] w-full rounded-lg border bg-page px-3 text-base text-ink placeholder:text-zinc-400 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30',
                        'border-red-600' => $errors->has('name'),
                        'border-ink' => ! $errors->has('name'),
                    ])
                >
                @error('name')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="email" class="text-lg">{{ __('Email address') }}</label>
                <input
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    type="email"
                    required
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
                <label for="password" class="text-lg">{{ __('Password') }}</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="{{ __('Password') }}"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    @if ($errors->has('password')) aria-invalid="true" @endif
                    @class([
                        'h-[53px] w-full rounded-lg border bg-page px-3 text-base text-ink placeholder:text-zinc-400 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30',
                        'border-red-600' => $errors->has('password'),
                        'border-ink' => ! $errors->has('password'),
                    ])
                >
                @error('password')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="password_confirmation" class="text-lg">{{ __('Confirm password') }}</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="{{ __('Confirm password') }}"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    @if ($errors->has('password')) aria-invalid="true" @endif
                    @class([
                        'h-[53px] w-full rounded-lg border bg-page px-3 text-base text-ink placeholder:text-zinc-400 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30',
                        'border-red-600' => $errors->has('password'),
                        'border-ink' => ! $errors->has('password'),
                    ])
                >
            </div>

            <button
                type="submit"
                data-test="register-user-button"
                class="h-[53px] w-full rounded-lg bg-ink text-base text-white transition hover:bg-ink/90 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2"
            >
                {{ __('Create account') }}
            </button>
        </form>

        <div class="text-center text-sm text-muted">
            <span>{{ __('Already have an account?') }}</span>
            <a class="text-ink hover:text-brand hover:underline" href="{{ route('login', request()->query('return_to') ? ['return_to' => request()->query('return_to')] : []) }}" wire:navigate>{{ __('Log in') }}</a>
        </div>
    </div>
</x-layouts::auth.login>
