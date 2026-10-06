<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudioController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

Route::get('/studios', [StudioController::class, 'index']);
Route::get('/studios/{id}', [StudioController::class, 'show']);
Route::post('/studios', [StudioController::class, 'store']);
Route::patch('/studios/{id}', [StudioController::class, 'update']);
Route::delete('/studios/{id}', [StudioController::class, 'destroy']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/bookings', [BookingController::class, 'store'])
    ->middleware('auth:sanctum');