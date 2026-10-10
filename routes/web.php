<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/',          [ProfileController::class, 'show'])->name('show');
    Route::get('/edit',      [ProfileController::class, 'edit'])->name('edit');
    Route::put('/',          [ProfileController::class, 'update'])->name('update');
    Route::resource('/cards', CardController::class)->except(['show'])->parameters(['cards' => 'card_no']);

});

Route::get('/profile', [ProfileController::class, 'show']);

require __DIR__.'/settings.php';
