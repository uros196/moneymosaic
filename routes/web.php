<?php

use App\Http\Controllers\Incomes\IncomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified', '2fa', 'password.recent'])->group(function () {
    Route::get('dashboard', fn () => Inertia::render('dashboard'))->name('dashboard');
    Route::get('incomes', [IncomeController::class, 'index'])->name('incomes.index');
    Route::get('incomes/create', [IncomeController::class, 'create'])->name('incomes.create');
    Route::post('incomes', [IncomeController::class, 'store'])->name('incomes.store');
    Route::get('incomes/{income}/edit', [IncomeController::class, 'edit'])->name('incomes.edit');
    Route::put('incomes/{income}', [IncomeController::class, 'update'])->name('incomes.update');
    Route::post('incomes/types', [IncomeController::class, 'storeType'])->name('incomes.types.store');
    Route::get('incomes/{id}', fn ($id) => Inertia::render('incomes/show', ['id' => (int) $id]))->name('incomes.show');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
