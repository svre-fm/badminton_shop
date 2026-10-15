<?php

use Livewire\Component;

new class extends Component {}; ?>

<section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-lg font-semibold text-ink">{{ __('Delete account') }}</h2>
        <p class="mt-1 text-sm text-muted">{{ __('Permanently remove your account and its data, including all of its resources.') }}</p>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" data-test="delete-user-button">
            {{ __('Delete account') }}
        </flux:button>
    </flux:modal.trigger>

    <livewire:pages::settings.delete-user-modal />
</section>
