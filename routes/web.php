<?php

use App\Http\Controllers\Auth\PasswordVerifyController;
use App\Http\Controllers\Incomes\IncomeController;
use App\Http\Controllers\LocalizationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// Quick locale switch (guest or authenticated)
Route::post('locale', [LocalizationController::class, 'store'])->name('locale.set');

// Password verify API (no password.recent, but requires auth + 2FA/verified)
Route::middleware(['auth', 'verified', '2fa', 'throttle:6,1'])->group(function () {
    Route::post('auth/password/verify', [PasswordVerifyController::class, 'store'])->name('auth.password.verify');
});

Route::middleware(['auth', 'verified', '2fa', 'password.recent'])->group(function () {
    Route::get('dashboard', fn () => Inertia::render('dashboard'))->name('dashboard');

    // Income resource
    Route::controller(IncomeController::class)
        ->middleware(['paging:incomes', 'translations:incomes'])
        ->group(function () {
            Route::get('incomes', 'index')->name('incomes.index');
            Route::get('incomes/create', 'create')->name('incomes.create');
            Route::post('incomes', 'store')->name('incomes.store');
            Route::get('incomes/{income}/edit', 'edit')->name('incomes.edit');
            Route::put('incomes/{income}', 'update')->name('incomes.update');
            Route::delete('incomes/{income}', 'destroy')->name('incomes.destroy');
            Route::post('incomes/types', 'storeType')->name('incomes.types.store');
        });

    Route::get('incomes/{id}', fn ($id) => Inertia::render('incomes/show', ['id' => (int) $id]))->name('incomes.show');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
