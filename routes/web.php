<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/runs', [DashboardController::class, 'run'])->name('runs.create');

    // Placeholder routes so sidebar links resolve without errors
    Route::get('/runs', fn () => view('placeholder', ['page' => 'History']))->name('runs.index');
    Route::get('/runs/{run}', fn () => view('placeholder', ['page' => 'Run Detail']))->name('runs.show');
    Route::get('/sales', fn () => view('placeholder', ['page' => 'Sales']))->name('sales.index');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-connection', [SettingsController::class, 'testConnection'])->name('settings.test');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
