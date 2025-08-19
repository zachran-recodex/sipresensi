<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->prefix('dashboard')->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');

    // Employee routes
    Route::middleware(['role:super admin|karyawan'])->group(function () {
        Route::get('face/enrollment', function () {
            return view('face.enrollment');
        })->name('face.enrollment');
        Route::get('attendance/check-in', function () {
            return view('attendance.check-in');
        })->name('attendance.check-in');
        Route::get('attendance/check-out', function () {
            return view('attendance.check-out');
        })->name('attendance.check-out');
    });

    // Administrator routes
    Route::middleware(['role:super admin|admin'])->group(function () {
        Route::get('administrator/manage-users', \App\Livewire\Administrator\ManageUsers::class)->name('administrator.manage-users');
        Route::get('administrator/manage-locations', \App\Livewire\Administrator\ManageLocations::class)->name('administrator.manage-locations');
        Route::get('administrator/manage-attendances', \App\Livewire\Administrator\ManageAttendances::class)->name('administrator.manage-attendances');
        Route::get('administrator/attendance-reports', \App\Livewire\Administrator\AttendanceReports::class)->name('administrator.attendance-reports');
    });
});

require __DIR__.'/auth.php';
