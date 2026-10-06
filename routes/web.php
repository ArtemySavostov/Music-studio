<?php

use App\Livewire\AuthForm;
use App\Livewire\MyBookings;
use App\Livewire\StudioBooking;
use App\Livewire\StudioCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', StudioCatalog::class)->name('home');
Route::get('/studios/{studio}', StudioBooking::class)->name('studios.show');
Route::middleware('guest')->group(function () {
    Route::get('/login', AuthForm::class)->name('login');
    Route::get('/register', AuthForm::class)->defaults('register', true)->name('register');
});
Route::get('/bookings', MyBookings::class)->middleware('auth')->name('bookings');
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');
