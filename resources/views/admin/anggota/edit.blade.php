@extends('layouts.admin')

@section('header', 'Kelola Detail Anggota')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <a href="{{ route('admin.anggota.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali ke Kelola Anggota</span>
            </a>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 mt-2">Detail & Pengaturan Akun</h1>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $anggota->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                Peran: {{ ucfirst($anggota->role) }}
            </span>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $anggota->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                {{ $anggota->is_active ? 'Status: Aktif' : 'Status: Nonaktif' }}
            </span>
        </div>
    </div>

    {{-- Form Edit Data Anggota Canvas Node 15 --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <h2 class="text-base font-bold text-slate-900 mb-4">Informasi Identitas Anggota</h2>

        <form method="POST" action="{{ route('admin.anggota.update', $anggota) }}" class="space-y-5">
            @csrf
            @method('PATCH')

            {{-- Nama --}}
            <div>
                <label for="nama" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="nama"
                    id="nama"
                    value="{{ old('nama', $anggota->nama) }}"
                    required
                    maxlength="255"
                    class="block w-full rounded-xl border @error('nama') border-red-500 @else border-slate-300 @enderror px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                >
                @error('nama')
                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Alamat Email <span class="text-red-500">*</span>
                </label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email', $anggota->email) }}"
                    required
                    maxlength="255"
                    class="block w-full rounded-xl border @error('email') border-red-500 @else border-slate-300 @enderror px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                >
                @error('email')
                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nomor Telepon --}}
            <div>
                <label for="telepon" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Nomor Telepon (AES-256-GCM Terenkripsi)
                </label>
                <input
                    type="text"
                    name="telepon"
                    id="telepon"
                    value="{{ old('telepon', $teleponPlaintext) }}"
                    placeholder="081299998888"
                    class="block w-full rounded-xl border @error('telepon') border-red-500 @else border-slate-300 @enderror px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                >
                @error('telepon')
                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-slate-400">
                    Kosongkan kolom jika ingin menghapus data kontak.
                </p>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition shadow-sm cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Aksi Penonaktifan / Aktivasi Akun Canvas Node 15 --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
        <h3 class="text-sm font-bold text-slate-900">Status Akun & Akses Sesi</h3>

        @error('status')
            <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700 font-medium">
                {{ $message }}
            </div>
        @enderror

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl {{ $anggota->is_active ? 'bg-amber-50 border border-amber-200' : 'bg-emerald-50 border border-emerald-200' }}">
            <div>
                <h4 class="text-xs font-bold {{ $anggota->is_active ? 'text-amber-900' : 'text-emerald-900' }}">
                    {{ $anggota->is_active ? 'Nonaktifkan Akun Ini' : 'Aktifkan Kembali Akun Ini' }}
                </h4>
                <p class="text-xs {{ $anggota->is_active ? 'text-amber-700' : 'text-emerald-700' }} mt-0.5">
                    {{ $anggota->is_active
                        ? 'Akun yang dinonaktifkan akan segera kehilangan akses sesi pada request berikutnya dan tidak dapat masuk.'
                        : 'Mengaktifkan kembali akun akan memulihkan izin anggota untuk masuk dan meminjam koleksi buku.' }}
                </p>
            </div>
            <div>
                <form method="POST" action="{{ route('admin.anggota.status', $anggota) }}">
                    @csrf
                    @method('PATCH')
                    <button
                        type="submit"
                        class="px-4 py-2 rounded-xl text-white text-xs font-semibold transition shadow-sm cursor-pointer {{ $anggota->is_active ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }}"
                    >
                        {{ $anggota->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Active Loans of this Member --}}
    @if ($borrowedLoans->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Buku yang Sedang Dipinjam ({{ $borrowedLoans->count() }})</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach ($borrowedLoans as $loan)
                    <div class="p-4 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold text-slate-900">{{ $loan->buku->judul ?? '-' }}</div>
                            <div class="text-slate-500">Dipinjam sejak: {{ \Carbon\Carbon::parse($loan->dipinjam_pada)->format('d M Y') }}</div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            Sedang Dipinjam
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
