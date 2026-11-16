<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/buku');

// Public catalog routes
Route::get('/buku', [BookController::class, 'index'])->name('buku.index');
Route::get('/buku/cari', [BookController::class, 'search'])->name('buku.cari');
Route::get('/buku/{buku}', [BookController::class, 'show'])
    ->whereNumber('buku')
    ->name('buku.show');

// Authentication routes
Route::get('/registrasi', [RegisterController::class, 'create'])
    ->middleware('guest')
    ->name('register');

Route::post('/registrasi', [RegisterController::class, 'store'])
    ->middleware(['guest', 'throttle:10,1'])
    ->name('register.store');

Route::get('/login', [LoginController::class, 'create'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [LoginController::class, 'store'])
    ->middleware('guest')
    ->name('login.store');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
