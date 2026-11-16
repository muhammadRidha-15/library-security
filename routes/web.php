<?php

use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Route::get('/registrasi', [RegisterController::class, 'create'])
    ->middleware('guest')
    ->name('register');

Route::post('/registrasi', [RegisterController::class, 'store'])
    ->middleware(['guest', 'throttle:10,1'])
    ->name('register.store');

// Dummy login route until TKT-07 implements full login
Route::get('/login', function () {
    return 'Login Page';
})->middleware('guest')->name('login');
