<?php

use App\Livewire\{Homepage, Dashboard, PersonalCalendar, SharedCalendar, AcceptInvitation, Notifications};
use App\Livewire\Settings\{Profile, Password, TwoFactor, Appearance};
use Illuminate\Support\Facades\{Route, Artisan};
use Laravel\Fortify\Features;

// Publieke routes
Route::get('/', Homepage::class)->name('home');
Route::get('/invite/{token}', AcceptInvitation::class)->name('invitations.accept');

// Beveiligde routes (Ingelogd & Geverifieerd)
Route::middleware(['auth', 'verified'])->group(function () {

    // Hoofdpagina's
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('notifications', Notifications::class)->name('notifications');

    // Kalender beheer
    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('personal', PersonalCalendar::class)->name('personal');
        Route::get('shared/{calendar}', SharedCalendar::class)->name('shared');
    });

    // Instellingen
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::redirect('/', 'settings/profile');
        Route::get('profile', Profile::class)->name('profile');
        Route::get('password', Password::class)->name('password');
        Route::get('appearance', Appearance::class)->name('appearance');

        // Two Factor Authentication met optionele wachtwoordbevestiging
        $twoFactorMiddleware = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
            ? ['password.confirm']
            : [];

        Route::get('two-factor', TwoFactor::class)
            ->middleware($twoFactorMiddleware)
            ->name('two-factor');
    });

    // Systeem hulpmiddelen
    Route::get('user/confirm-password', fn() => view('livewire.auth.confirm-password'))->name('password.confirm');
});

// Deployment hulpmiddelen
Route::get('/symlink', fn() => Artisan::call('storage:link'));

require __DIR__.'/auth.php';
