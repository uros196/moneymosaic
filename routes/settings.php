<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SessionsController;
use App\Http\Controllers\Settings\TwoFactorController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', '2fa', 'password.recent', 'translations:settings'])
    ->prefix('settings')
    ->group(function () {
        Route::redirect('', '/settings/profile');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit')
            ->middleware('translations:profile');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('password', [PasswordController::class, 'edit'])->name('password.edit')
            ->middleware('translations:password');

        Route::put('password', [PasswordController::class, 'update'])->name('password.update')
            ->middleware('throttle:6,1');

        Route::get('appearance', fn () => Inertia::render('settings/appearance'))->name('appearance')
            ->middleware('translations:appearance');

        // Sessions management
        Route::get('sessions', [SessionsController::class, 'index'])->name('settings.sessions')
            ->middleware('translations:sessions');
        Route::delete('sessions/{id}', [SessionsController::class, 'destroy'])->name('settings.sessions.destroy');
        Route::post('sessions/others', [SessionsController::class, 'destroyOthers'])->name('settings.sessions.others');
        Route::post('sessions/all', [SessionsController::class, 'destroyAll'])->name('settings.sessions.all');

        // Security / Two-Factor
        Route::get('security', [TwoFactorController::class, 'edit'])->name('settings.security')
            ->middleware('translations:security');
        Route::post('security/email/enable', [TwoFactorController::class, 'enableEmail'])->name('settings.security.email.enable');
        Route::post('security/email/confirm', [TwoFactorController::class, 'confirmEmail'])->name('settings.security.email.confirm')
            ->middleware('throttle:6,1');
        Route::post('security/email/resend', [TwoFactorController::class, 'resendEmail'])->name('settings.security.email.resend')
            ->middleware('throttle:3,1');
        Route::post('security/totp/begin', [TwoFactorController::class, 'beginTotp'])->name('settings.security.totp.begin');
        Route::post('security/totp/confirm', [TwoFactorController::class, 'confirmTotp'])->name('settings.security.totp.confirm')
            ->middleware('throttle:6,1');
        Route::post('security/disable', [TwoFactorController::class, 'disable'])->name('settings.security.disable');
    });
