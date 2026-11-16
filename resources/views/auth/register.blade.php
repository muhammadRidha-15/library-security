@extends('layouts.app')

@section('content')
<div class="bg-white py-8 px-6 shadow-sm rounded-xl border border-slate-200 sm:px-8">
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Daftar Anggota Baru</h1>
        <p class="mt-1 text-sm text-slate-600">Lengkapi formulir di bawah ini untuk membuat akun perpustakaan.</p>
    </div>

    @if ($errors->any())
        <div class="mb-5 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm" role="alert">
            <p class="font-medium">Terdapat kesalahan pada isian formulir:</p>
            <ul class="mt-1 list-disc list-inside space-y-1 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-5" novalidate>
        @csrf

        <div>
            <label for="nama" class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
            <div class="mt-1">
                <input
                    type="text"
                    id="nama"
                    name="nama"
                    value="{{ old('nama') }}"
                    required
                    maxlength="255"
                    autocomplete="name"
                    class="block w-full rounded-lg border @error('nama') border-red-500 @else border-slate-300 @enderror px-3.5 py-2.5 text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 text-sm transition"
                    placeholder="Contoh: Muhammad Ridha"
                >
            </div>
            @error('nama')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Alamat Email</label>
            <div class="mt-1">
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    maxlength="255"
                    autocomplete="email"
                    class="block w-full rounded-lg border @error('email') border-red-500 @else border-slate-300 @enderror px-3.5 py-2.5 text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 text-sm transition"
                    placeholder="nama@domain.com"
                >
            </div>
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <div class="mt-1">
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    minlength="12"
                    maxlength="72"
                    autocomplete="new-password"
                    class="block w-full rounded-lg border @error('password') border-red-500 @else border-slate-300 @enderror px-3.5 py-2.5 text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 text-sm transition"
                    placeholder="••••••••••••"
                >
            </div>
            <p class="mt-1 text-xs text-slate-500">Minimal 12 karakter, maksimal 72 byte.</p>
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi Password</label>
            <div class="mt-1">
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    minlength="12"
                    maxlength="72"
                    autocomplete="new-password"
                    class="block w-full rounded-lg border @error('password_confirmation') border-red-500 @else border-slate-300 @enderror px-3.5 py-2.5 text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 text-sm transition"
                    placeholder="••••••••••••"
                >
            </div>
            @error('password_confirmation')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-2">
            <button
                type="submit"
                class="w-full justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition cursor-pointer"
            >
                Daftar Sebagai Anggota
            </button>
        </div>
    </form>

    <div class="mt-6 border-t border-slate-200 pt-4 text-center">
        <p class="text-sm text-slate-600">
            Sudah memiliki akun?
            <a href="{{ route('login') }}" class="font-medium text-emerald-600 hover:text-emerald-500 transition">
                Masuk ke akun
            </a>
        </p>
    </div>
</div>
@endsection
