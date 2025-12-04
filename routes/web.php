<?php

use App\Livewire\Homepage;
use App\Livewire\PersonalCalendar;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

// Homepage
Route::get('/', Homepage::class)->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('calendar/personal', PersonalCalendar::class)->name('calendar.personal');

    // Settings Redirect
    Route::redirect('settings', 'settings/profile');

    // Settings Routes
    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');

    Route::get('/user/confirm-password', function () {
        return view('livewire.auth.confirm-password');
    })->name('password.confirm');

    // Fixed Two-Factor Route
    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
        // Logic: If 'confirmPassword' option is on, use the confirmation middleware
            Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
                ? ['password.confirm']
                : []
        )
        ->name('settings.two-factor');
});

require __DIR__.'/auth.php';
