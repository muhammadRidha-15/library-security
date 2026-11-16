@extends('layouts.admin')

@section('header', 'Tambah Koleksi Buku')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <a href="{{ route('admin.buku.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali ke Daftar Buku</span>
            </a>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 mt-2">Formulir Tambah Buku Baru</h1>
        </div>
    </div>

    {{-- Form Tambah Buku Canvas Node 11 --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.buku.store') }}" class="space-y-5">
            @csrf

            {{-- Judul Buku --}}
            <div>
                <label for="judul" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Judul Buku <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="judul"
                    id="judul"
                    value="{{ old('judul') }}"
                    required
                    maxlength="255"
                    placeholder="Contoh: Pemrograman Berorientasi Objek dengan PHP 8"
                    class="block w-full rounded-xl border @error('judul') border-red-500 @else border-slate-300 @enderror px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                >
                @error('judul')
                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Penulis --}}
            <div>
                <label for="penulis" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Nama Penulis <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="penulis"
                    id="penulis"
                    value="{{ old('penulis') }}"
                    required
                    maxlength="255"
                    placeholder="Contoh: John Doe, M.Kom"
                    class="block w-full rounded-xl border @error('penulis') border-red-500 @else border-slate-300 @enderror px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                >
                @error('penulis')
                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div>
                <label for="deskripsi" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Deskripsi Ringkas (Opsional)
                </label>
                <textarea
                    name="deskripsi"
                    id="deskripsi"
                    rows="4"
                    maxlength="2000"
                    placeholder="Tuliskan sinopsis atau deskripsi buku (maksimal 2.000 karakter)..."
                    class="block w-full rounded-xl border @error('deskripsi') border-red-500 @else border-slate-300 @enderror p-3.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                >{{ old('deskripsi') }}</textarea>
                @error('deskripsi')
                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Stok Total --}}
            <div>
                <label for="stok_total" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Jumlah Stok Eksemplar <span class="text-red-500">*</span>
                </label>
                <input
                    type="number"
                    name="stok_total"
                    id="stok_total"
                    value="{{ old('stok_total', 1) }}"
                    required
                    min="1"
                    max="1000"
                    class="block w-36 rounded-xl border @error('stok_total') border-red-500 @else border-slate-300 @enderror px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                >
                @error('stok_total')
                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-slate-400">
                    Stok yang tersedia untuk dipinjam otomatis diatur sama dengan stok total.
                </p>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a
                    href="{{ route('admin.buku.index') }}"
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
                    <span>Simpan Buku Baru</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
