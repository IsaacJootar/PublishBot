<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RunController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\VoiceProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/runs', [DashboardController::class, 'run'])->name('runs.create');

    // Run routes
    Route::get('/runs', [RunController::class, 'index'])->name('runs.index');
    Route::get('/runs/{pipelineRun}', [RunController::class, 'show'])->name('runs.show');
    Route::get('/runs/{pipelineRun}/status', [RunController::class, 'status'])->name('runs.status');
    Route::post('/runs/{pipelineRun}/continue', [RunController::class, 'continue'])->name('runs.continue');
    Route::post('/runs/{pipelineRun}/retry/{stageNumber}', [RunController::class, 'retry'])->name('runs.retry');
    Route::post('/runs/{pipelineRun}/rerun', [RunController::class, 'rerun'])->name('runs.rerun');
    Route::delete('/runs/{pipelineRun}', [RunController::class, 'destroy'])->name('runs.destroy');
    Route::get('/runs/{pipelineRun}/files/{filename}', [RunController::class, 'file'])->name('runs.file');
    Route::get('/runs/{pipelineRun}/download-all', [RunController::class, 'downloadAll'])->name('runs.download-all');

    // Sales
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');
    Route::delete('/sales/{sale}', [SalesController::class, 'destroy'])->name('sales.destroy');

    // My Voice
    Route::get('/voice', [VoiceProfileController::class, 'index'])->name('voice.index');
    Route::post('/voice', [VoiceProfileController::class, 'store'])->name('voice.store');
    Route::get('/voice/{voiceProfile}', [VoiceProfileController::class, 'show'])->name('voice.show');
    Route::patch('/voice/{voiceProfile}', [VoiceProfileController::class, 'update'])->name('voice.update');
    Route::post('/voice/{voiceProfile}/train', [VoiceProfileController::class, 'train'])->name('voice.train');
    Route::post('/voice/{voiceProfile}/extract', [VoiceProfileController::class, 'extract'])->name('voice.extract');
    Route::get('/voice/{voiceProfile}/status', [VoiceProfileController::class, 'status'])->name('voice.status');
    Route::post('/voice/{voiceProfile}/default', [VoiceProfileController::class, 'setDefault'])->name('voice.default');
    Route::delete('/voice/{voiceProfile}', [VoiceProfileController::class, 'destroy'])->name('voice.destroy');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-connection', [SettingsController::class, 'testConnection'])->name('settings.test');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
