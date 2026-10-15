<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'member-information');
    Route::redirect('settings/profile', 'member-information')->name('profile.legacy');

    Route::livewire('member-information', 'pages::settings.profile')->name('profile.edit');
});

Route::middleware(['auth'])->group(function () {
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');

    Route::livewire('settings/security', 'pages::settings.security')
        ->middleware([
            'password.confirm',
        ])
        ->name('security.edit');
});
