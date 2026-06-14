<?php

use App\Http\Controllers\ConvertDraftController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DigitalProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RunController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SeriesController;
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
    Route::get('/runs/{pipelineRun}/export/{format}', [RunController::class, 'exportEbook'])->name('runs.export');

    // Sales
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');
    Route::delete('/sales/{sale}', [SalesController::class, 'destroy'])->name('sales.destroy');

    // Digital Products Factory
    Route::prefix('digital-products')->name('digital-products.')->group(function () {
        Route::get('/', [DigitalProductController::class, 'index'])->name('index');
        Route::get('/create/{type}', [DigitalProductController::class, 'create'])->name('create');
        Route::post('/', [DigitalProductController::class, 'store'])->name('store');
        Route::get('/{digitalProduct}', [DigitalProductController::class, 'show'])->name('show');
        Route::get('/{digitalProduct}/status', [DigitalProductController::class, 'status'])->name('status');
        Route::post('/{digitalProduct}/advance', [DigitalProductController::class, 'advance'])->name('advance');
        Route::post('/{digitalProduct}/regenerate/{stage}', [DigitalProductController::class, 'regenerate'])->name('regenerate');
        Route::patch('/{digitalProduct}/edit-field', [DigitalProductController::class, 'editField'])->name('edit-field');
        Route::get('/{digitalProduct}/export/{format}', [DigitalProductController::class, 'export'])->name('export');
        Route::delete('/{digitalProduct}', [DigitalProductController::class, 'destroy'])->name('destroy');
    });

    // Convert Draft
    Route::prefix('convert')->name('convert.')->group(function () {
        Route::get('/', [ConvertDraftController::class, 'index'])->name('index');
        Route::post('/upload', [ConvertDraftController::class, 'upload'])->name('upload');
        Route::get('/{pipelineRun}/order', [ConvertDraftController::class, 'orderPage'])->name('order');
        Route::post('/{pipelineRun}/order', [ConvertDraftController::class, 'confirmOrder'])->name('order.confirm');
        Route::get('/{pipelineRun}/review', [ConvertDraftController::class, 'review'])->name('review');
        Route::get('/{pipelineRun}/status', [ConvertDraftController::class, 'status'])->name('status');
        Route::post('/{pipelineRun}/approve', [ConvertDraftController::class, 'approve'])->name('approve');
        Route::get('/{pipelineRun}/listing', [ConvertDraftController::class, 'listing'])->name('listing');
        Route::get('/{pipelineRun}/launch', [ConvertDraftController::class, 'launch'])->name('launch');
        Route::get('/{pipelineRun}/export/{format}', [ConvertDraftController::class, 'export'])->name('export');
    });

    // My Series
    Route::get('/series', [SeriesController::class, 'index'])->name('series.index');
    Route::get('/series/create', [SeriesController::class, 'create'])->name('series.create');
    Route::post('/series', [SeriesController::class, 'store'])->name('series.store');
    Route::get('/series/{series}', [SeriesController::class, 'show'])->name('series.show');
    Route::get('/series/{series}/edit', [SeriesController::class, 'edit'])->name('series.edit');
    Route::patch('/series/{series}', [SeriesController::class, 'update'])->name('series.update');
    Route::get('/series/{series}/plan', [SeriesController::class, 'planNext'])->name('series.plan');
    Route::post('/series/{series}/plan', [SeriesController::class, 'storeNext'])->name('series.plan.store');
    Route::post('/series/{series}/attach-run/{pipelineRun}', [SeriesController::class, 'attachRun'])->name('series.attach-run');
    Route::delete('/series/{series}/detach-run/{pipelineRun}', [SeriesController::class, 'detachRun'])->name('series.detach-run');
    Route::delete('/series/{series}', [SeriesController::class, 'destroy'])->name('series.destroy');

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
