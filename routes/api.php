<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\StudioController;
use Illuminate\Support\Facades\Route;

Route::get('/studios', [StudioController::class, 'index']);
Route::get('/studios/{id}', [StudioController::class, 'show']);
Route::middleware(['auth:sanctum', 'can:manage-studios'])->group(function () {
    Route::post('/studios', [StudioController::class, 'store']);
    Route::patch('/studios/{id}', [StudioController::class, 'update']);
    Route::delete('/studios/{id}', [StudioController::class, 'destroy']);
});
Route::middleware('throttle:api-auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/bookings', [BookingController::class, 'store'])
    ->middleware('auth:sanctum');
