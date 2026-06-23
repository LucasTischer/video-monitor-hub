<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CameraController;
use App\Http\Controllers\CameraShareController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('cameras', CameraController::class);
    Route::post('/cameras/{camera}/shares', [CameraShareController::class, 'store'])->name('cameras.shares.store');
    Route::patch('/cameras/{camera}/shares/{user}', [CameraShareController::class, 'update'])->name('cameras.shares.update');
    Route::delete('/cameras/{camera}/shares/{user}', [CameraShareController::class, 'destroy'])->name('cameras.shares.destroy');
    Route::resource('videos', VideoController::class)->only(['index', 'show', 'destroy']);
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
