<?php

use App\Http\Controllers\Api\ProcessorCameraController;
use App\Http\Controllers\Api\ProcessorVideoController;
use Illuminate\Support\Facades\Route;

Route::get('/processor/cameras', [ProcessorCameraController::class, 'index']);
Route::post('/processor/videos', [ProcessorVideoController::class, 'store']);
