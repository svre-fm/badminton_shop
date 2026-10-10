<?php

use App\Concerns\ProfileValidationRules;
use Flux\Flux;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('components.layouts.shop')] #[Title('My profile')] class extends Component
{
    use ProfileValidationRules;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->phone = Auth::user()->phone ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('home', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="mx-auto w-full max-w-6xl px-4 pb-16 pt-8 sm:px-8 sm:pt-12">
    <header class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand">Account</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-ink sm:text-4xl">Member information</h1>
        <p class="mt-2 max-w-xl text-sm leading-6 text-muted sm:text-base">
            View and update your name, email address, and phone number.
        </p>
    </header>

    <div class="overflow-hidden rounded-2xl border border-mist bg-white shadow-sm">
        <div class="grid md:grid-cols-[minmax(15rem,0.8fr)_1.4fr]">
            <aside class="flex flex-col justify-between bg-page p-6 sm:p-8">
                <div>
                    <div class="grid size-16 place-items-center rounded-full border-2 border-white bg-brand text-xl font-semibold text-white shadow-sm">
                        {{ Auth::user()->initials() }}
                    </div>
                    <h2 class="mt-5 break-words text-xl font-semibold text-ink">{{ Auth::user()->name }}</h2>
                    <dl class="mt-4 space-y-3 border-t border-mist pt-4 text-sm">
                        <div>
                            <dt class="font-medium text-ink">Email</dt>
                            <dd class="mt-0.5 break-all text-muted">{{ Auth::user()->email }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-ink">Phone</dt>
                            <dd class="mt-0.5 text-muted">{{ Auth::user()->phone ?: 'Not provided' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-8 border-t border-mist pt-5">
                    <p class="text-sm font-semibold text-ink">Member profile</p>
                    <p class="mt-1 text-sm leading-6 text-muted">
                        This page contains your account details. Addresses and delivery history are managed separately.
                    </p>
                </div>
            </aside>

            <div class="p-6 sm:p-8 lg:p-10">
                <div class="mb-7 border-b border-mist pb-5">
                    <h2 class="text-xl font-semibold text-ink">Your details</h2>
                    <p class="mt-1 text-sm text-muted">Keep your contact information up to date.</p>
                </div>

                <form wire:submit="updateProfileInformation" class="space-y-6">
                    <div>
                        <label for="profile-name" class="mb-2 block text-sm font-medium text-ink">Full name</label>
                        <input
                            id="profile-name"
                            wire:model="name"
                            type="text"
                            required
                            autofocus
                            autocomplete="name"
                            class="w-full rounded-lg border border-muted/50 bg-white px-4 py-3 text-ink outline-none transition placeholder:text-muted/70 focus:border-brand focus:ring-2 focus:ring-brand/20"
                            placeholder="Your name"
                        >
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="profile-phone" class="mb-2 block text-sm font-medium text-ink">Phone number <span class="font-normal text-muted">(optional)</span></label>
                        <input
                            id="profile-phone"
                            wire:model="phone"
                            type="tel"
                            autocomplete="tel"
                            maxlength="32"
                            class="w-full rounded-lg border border-muted/50 bg-white px-4 py-3 text-ink outline-none transition placeholder:text-muted/70 focus:border-brand focus:ring-2 focus:ring-brand/20"
                            placeholder="Add a phone number"
                        >
                        @error('phone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="profile-email" class="mb-2 block text-sm font-medium text-ink">Email address</label>
                        <input
                            id="profile-email"
                            wire:model="email"
                            type="email"
                            required
                            autocomplete="email"
                            class="w-full rounded-lg border border-muted/50 bg-white px-4 py-3 text-ink outline-none transition placeholder:text-muted/70 focus:border-brand focus:ring-2 focus:ring-brand/20"
                            placeholder="you@example.com"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        @if ($this->hasUnverifiedEmail)
                            <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                                <p class="font-medium">Your email address is not verified yet.</p>
                                <button
                                    type="button"
                                    wire:click="resendVerificationNotification"
                                    class="mt-1 font-medium text-brand underline decoration-brand/40 underline-offset-2 hover:text-ink"
                                >
                                    Send a new verification email
                                </button>

                                @if (session('status') === 'verification-link-sent')
                                    <p role="status" class="mt-2 text-green-700">
                                        A new verification link has been sent to your email address.
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-mist pt-5 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-muted">Your email may need to be verified again after changing it.</p>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="updateProfileInformation"
                            data-test="update-profile-button"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-brand px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="updateProfileInformation">Save changes</span>
                            <span wire:loading wire:target="updateProfileInformation">Saving...</span>
                            <svg wire:loading.remove wire:target="updateProfileInformation" viewBox="0 0 20 20" fill="none" class="size-4" aria-hidden="true">
                                <path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($this->showDeleteUser)
        <div class="mt-8 rounded-2xl border border-red-200 bg-white p-6 sm:p-8">
            <livewire:pages::settings.delete-user-form />
        </div>
    @endif
</section>
