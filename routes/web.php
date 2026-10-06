<?php

use App\Http\Controllers\StudioController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
// Route::get('/studios', [StudioController::class, 'index']);
// Route::get('/studios/{id}', [StudioController::class, 'show']);
// Route::post('/studios', [StudioController::class, 'store']);