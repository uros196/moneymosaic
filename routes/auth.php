<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\TwoFactorReminderController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest', 'translations:auth'])->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('translations:auth')
    ->name('password.request');

Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    ->name('password.email');

Route::middleware(['auth', 'translations:auth,security,profile'])
    ->group(function () {
        // 2FA reminder page and actions
        Route::get('twofactor/reminder', [TwoFactorReminderController::class, 'show'])->name('twofactor.reminder');
        Route::post('twofactor/reminder/skip', [TwoFactorReminderController::class, 'skip'])->name('twofactor.reminder.skip');
        Route::post('twofactor/reminder/snooze', [TwoFactorReminderController::class, 'snooze'])->name('twofactor.reminder.snooze');

        Route::get('verify-email', EmailVerificationPromptController::class)
            ->name('verification.notice');

        Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('verification.send');

        Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
            ->name('password.confirm');

        Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])
            ->middleware('throttle:6,1');

        // Password inactivity check endpoint for client focus checks
        Route::get('password/needs-confirmation', [ConfirmablePasswordController::class, 'needsConfirmation'])
            ->name('password.needs-confirmation');

        // Two-Factor Authentication challenge routes
        Route::get('twofactor/challenge', [TwoFactorChallengeController::class, 'create'])
            ->name('twofactor.challenge');

        Route::post('twofactor/challenge', [TwoFactorChallengeController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('twofactor.store');

        Route::post('twofactor/resend', [TwoFactorChallengeController::class, 'resend'])
            ->middleware('throttle:3,1')
            ->name('twofactor.resend');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');
    });
