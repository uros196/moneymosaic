<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SessionsController;
use App\Http\Controllers\Settings\TwoFactorController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', '2fa', 'password.recent'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/appearance');
    })->name('appearance');

    // Sessions management
    Route::get('settings/sessions', [SessionsController::class, 'index'])->name('settings.sessions');
    Route::delete('settings/sessions/{id}', [SessionsController::class, 'destroy'])->name('settings.sessions.destroy');
    Route::post('settings/sessions/others', [SessionsController::class, 'destroyOthers'])->name('settings.sessions.others');
    Route::post('settings/sessions/all', [SessionsController::class, 'destroyAll'])->name('settings.sessions.all');

    // Security / Two-Factor
    Route::get('settings/security', [TwoFactorController::class, 'edit'])->name('settings.security');
    Route::post('settings/security/email/enable', [TwoFactorController::class, 'enableEmail'])->name('settings.security.email.enable');
    Route::post('settings/security/totp/begin', [TwoFactorController::class, 'beginTotp'])->name('settings.security.totp.begin');
    Route::post('settings/security/totp/confirm', [TwoFactorController::class, 'confirmTotp'])->middleware('throttle:6,1')->name('settings.security.totp.confirm');
    Route::post('settings/security/disable', [TwoFactorController::class, 'disable'])->name('settings.security.disable');
});
