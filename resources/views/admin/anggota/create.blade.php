@extends('layouts.admin')

@section('header', 'Tambah Akun Anggota')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <a href="{{ route('admin.anggota.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali ke Kelola Anggota</span>
            </a>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 mt-2">Pendaftaran Anggota Baru</h1>
        </div>
    </div>

    {{-- Form Tambah Akun Anggota Canvas Node 14 --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.anggota.store') }}" class="space-y-5">
            @csrf

            {{-- Nama Lengkap --}}
            <div>
                <label for="nama" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="nama"
                    id="nama"
                    value="{{ old('nama') }}"
                    required
                    maxlength="255"
                    placeholder="Contoh: Budi Prakoso"
                    class="block w-full rounded-xl border @error('nama') border-red-500 @else border-slate-300 @enderror px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                >
                @error('nama')
                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Alamat Email --}}
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Alamat Email (Digunakan untuk Login) <span class="text-red-500">*</span>
                </label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    required
                    maxlength="255"
                    placeholder="budi@perpustakaan.test"
                    class="block w-full rounded-xl border @error('email') border-red-500 @else border-slate-300 @enderror px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                >
                @error('email')
                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Awal --}}
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Kata Sandi Awal (12 - 72 Karakter) <span class="text-red-500">*</span>
                </label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    required
                    minlength="12"
                    maxlength="72"
                    placeholder="Minimal 12 karakter aman..."
                    class="block w-full rounded-xl border @error('password') border-red-500 @else border-slate-300 @enderror px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                >
                @error('password')
                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-slate-400">
                    Sandi akan otomatis di-hash menggunakan Bcrypt (cost 12) dengan salt unik otomatis.
                </p>
            </div>

            {{-- Nomor Telepon --}}
            <div>
                <label for="telepon" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Nomor Telepon Kontak (Opsional)
                </label>
                <input
                    type="text"
                    name="telepon"
                    id="telepon"
                    value="{{ old('telepon') }}"
                    placeholder="081299998888"
                    class="block w-full rounded-xl border @error('telepon') border-red-500 @else border-slate-300 @enderror px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                >
                @error('telepon')
                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-slate-400">
                    Nomor telepon langsung dienkripsi menggunakan AES-256-GCM terikat identitas akun anggota.
                </p>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a
                    href="{{ route('admin.anggota.index') }}"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-medium hover:bg-slate-50 transition"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition shadow-sm cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Akun Anggota</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
