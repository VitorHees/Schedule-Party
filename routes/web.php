<?php

use App\Livewire\Homepage;
use App\Livewire\PersonalCalendar;
use App\Livewire\SharedCalendar;
use App\Livewire\Dashboard;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\AcceptInvitation; // Import the new component
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Artisan;

// Homepage
Route::get('/', Homepage::class)->name('home');

// Dashboard
Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('calendar/personal', PersonalCalendar::class)->name('calendar.personal');

    Route::get('calendar/shared/{calendar}', SharedCalendar::class)
        ->name('calendar.shared');

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');

    Route::get('/user/confirm-password', function () {
        return view('livewire.auth.confirm-password');
    })->name('password.confirm');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
                ? ['password.confirm']
                : []
        )
        ->name('settings.two-factor');
});

Route::get('/symlink', function () {
    Artisan::call('storage:link');
});

// REPLACED: Point directly to the Livewire component
Route::get('/invite/{token}', AcceptInvitation::class)->name('invitations.accept'); //

require __DIR__.'/auth.php';
