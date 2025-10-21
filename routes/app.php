<?php

use App\Http\Controllers\Incomes\IncomeController;
use Inertia\Inertia;

// Dashboard
Route::get('dashboard', fn () => Inertia::render('dashboard'))->name('dashboard');

// Income resource
Route::controller(IncomeController::class)
    ->middleware(['paging:incomes', 'translations:incomes'])
    ->group(function () {
        Route::get('incomes', 'index')->name('incomes.index');
        Route::get('incomes/create', 'create')->name('incomes.create');
        Route::post('incomes', 'store')->name('incomes.store');
        Route::get('incomes/{income}', 'show')->name('incomes.show');
        Route::get('incomes/{income}/edit', 'edit')->name('incomes.edit');
        Route::put('incomes/{income}', 'update')->name('incomes.update');
        Route::delete('incomes/{income}', 'destroy')->name('incomes.destroy');
    });
