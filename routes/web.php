<?php

use App\Http\Controllers\LocalizationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// Quick locale switch (guest or authenticated)
Route::post('locale', [LocalizationController::class, 'store'])->name('locale.set');
