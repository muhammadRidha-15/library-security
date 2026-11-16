<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UlasanController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/buku');

// Public catalog routes
Route::get('/buku', [BookController::class, 'index'])->name('buku.index');
Route::get('/buku/cari', [BookController::class, 'search'])->name('buku.cari');
Route::get('/buku/{buku}', [BookController::class, 'show'])
    ->whereNumber('buku')
    ->name('buku.show');

// Reviews route (auth, active, csrf)
Route::post('/buku/{buku}/ulasan', [UlasanController::class, 'store'])
    ->whereNumber('buku')
    ->middleware(['auth', 'active'])
    ->name('buku.ulasan.store');

// Profile routes (auth, active)
Route::get('/profil', [ProfileController::class, 'show'])
    ->middleware(['auth', 'active'])
    ->name('profil.show');

Route::patch('/profil/telepon', [ProfileController::class, 'updateTelepon'])
    ->middleware(['auth', 'active'])
    ->name('profil.telepon.update');

// Borrowing routes for users (auth, active, role:user)
Route::get('/buku/{buku}/pinjam', [PeminjamanController::class, 'confirm'])
    ->whereNumber('buku')
    ->middleware(['auth', 'active', 'role:user'])
    ->name('buku.pinjam.confirm');

Route::post('/peminjaman', [PeminjamanController::class, 'store'])
    ->middleware(['auth', 'active', 'role:user'])
    ->name('peminjaman.store');

Route::get('/peminjaman', [PeminjamanController::class, 'index'])
    ->middleware(['auth', 'active', 'role:user'])
    ->name('peminjaman.index');

Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show'])
    ->whereNumber('peminjaman')
    ->middleware(['auth', 'active'])
    ->name('peminjaman.show');

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
