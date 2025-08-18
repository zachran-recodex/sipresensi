<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Administrator routes
    Route::middleware(['role:super admin|admin'])->group(function () {
        Route::get('administrator/manage-users', \App\Livewire\Administrator\ManageUsers::class)->name('administrator.manage-users');
        Route::get('administrator/manage-locations', \App\Livewire\Administrator\ManageLocations::class)->name('administrator.manage-locations');
    });
});

require __DIR__.'/auth.php';
