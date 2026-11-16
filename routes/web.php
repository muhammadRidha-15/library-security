<?php

use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\CirculationController as AdminCirculationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UlasanController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/buku');

// Admin panel routes (auth, active, role:admin)
Route::prefix('admin')
    ->middleware(['auth', 'active', 'role:admin'])
    ->group(function () {
        Route::redirect('/', '/admin/ringkasan');
        Route::get('/ringkasan', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        // CRUD Buku
        Route::get('/buku', [AdminBookController::class, 'index'])->name('admin.buku.index');
        Route::get('/buku/create', [AdminBookController::class, 'create'])->name('admin.buku.create');
        Route::post('/buku', [AdminBookController::class, 'store'])->name('admin.buku.store');
        Route::get('/buku/{buku}', [AdminBookController::class, 'show'])->whereNumber('buku')->name('admin.buku.show');
        Route::get('/buku/{buku}/edit', [AdminBookController::class, 'edit'])->whereNumber('buku')->name('admin.buku.edit');
        Route::patch('/buku/{buku}', [AdminBookController::class, 'update'])->whereNumber('buku')->name('admin.buku.update');
        Route::patch('/buku/{buku}/arsip', [AdminBookController::class, 'archive'])->whereNumber('buku')->name('admin.buku.archive');
        Route::patch('/buku/{buku}/pulihkan', [AdminBookController::class, 'restore'])->whereNumber('buku')->name('admin.buku.restore');
        Route::delete('/buku/{buku}', [AdminBookController::class, 'destroy'])->whereNumber('buku')->name('admin.buku.destroy');

        // Manajemen Anggota (FR-12)
        Route::get('/anggota', [AdminMemberController::class, 'index'])->name('admin.anggota.index');
        Route::get('/anggota/create', [AdminMemberController::class, 'create'])->name('admin.anggota.create');
        Route::post('/anggota', [AdminMemberController::class, 'store'])->name('admin.anggota.store');
        Route::get('/anggota/{anggota}', [AdminMemberController::class, 'show'])->whereNumber('anggota')->name('admin.anggota.show');
        Route::get('/anggota/{anggota}/edit', [AdminMemberController::class, 'edit'])->whereNumber('anggota')->name('admin.anggota.edit');
        Route::patch('/anggota/{anggota}', [AdminMemberController::class, 'update'])->whereNumber('anggota')->name('admin.anggota.update');
        Route::patch('/anggota/{anggota}/status', [AdminMemberController::class, 'toggleStatus'])->whereNumber('anggota')->name('admin.anggota.status');

        // Sirkulasi Peminjaman & Pengembalian (FR-13, FR-14)
        Route::get('/peminjaman', [AdminCirculationController::class, 'index'])->name('admin.peminjaman.index');
        Route::get('/peminjaman/create', [AdminCirculationController::class, 'create'])->name('admin.peminjaman.create');
        Route::post('/peminjaman', [AdminCirculationController::class, 'store'])->name('admin.peminjaman.store');
        Route::get('/peminjaman/{peminjaman}', [AdminCirculationController::class, 'show'])->whereNumber('peminjaman')->name('admin.peminjaman.show');
        Route::patch('/peminjaman/{peminjaman}/pengembalian', [AdminCirculationController::class, 'returnBook'])->whereNumber('peminjaman')->name('admin.peminjaman.pengembalian');
    });

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
