<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\VoiceProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-connection', [SettingsController::class, 'testConnection'])->name('settings.test');

    // My Voice
    Route::get('/voice', [VoiceProfileController::class, 'index'])->name('voice.index');
    Route::get('/voice/create', [VoiceProfileController::class, 'create'])->name('voice.create');
    Route::post('/voice', [VoiceProfileController::class, 'store'])->name('voice.store');
    Route::get('/voice/{voiceProfile}/train', [VoiceProfileController::class, 'train'])->name('voice.train');
    Route::post('/voice/{voiceProfile}/upload', [VoiceProfileController::class, 'upload'])->name('voice.upload');
    Route::post('/voice/{voiceProfile}/approve', [VoiceProfileController::class, 'approve'])->name('voice.approve');
    Route::post('/voice/{voiceProfile}/default', [VoiceProfileController::class, 'setDefault'])->name('voice.default');
    Route::delete('/voice/{voiceProfile}', [VoiceProfileController::class, 'destroy'])->name('voice.destroy');
});

require __DIR__.'/auth.php';
