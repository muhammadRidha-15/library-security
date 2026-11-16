@extends('layouts.admin')

@section('header', 'Edit & Kelola Buku')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <a href="{{ route('admin.buku.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali ke Kelola Buku</span>
            </a>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 mt-2">Edit Koleksi Buku</h1>
        </div>
        <div>
            @if ($buku->archived_at)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    Status: Diarsipkan
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Status: Aktif di Katalog
                </span>
            @endif
        </div>
    </div>

    {{-- Stock Status Summary Card Canvas Node 12 --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 grid grid-cols-3 gap-4 text-center">
        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
            <span class="text-xs text-slate-500 block">Stok Total</span>
            <span class="text-lg font-bold text-slate-900">{{ $buku->stok_total }}</span>
        </div>
        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
            <span class="text-xs text-slate-500 block">Sedang Dipinjam</span>
            <span class="text-lg font-bold text-amber-700">{{ $borrowedCount }}</span>
        </div>
        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
            <span class="text-xs text-slate-500 block">Stok Tersedia</span>
            <span class="text-lg font-bold text-emerald-700">{{ $buku->stok_tersedia }}</span>
        </div>
    </div>

    {{-- Form Edit Buku --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.buku.update', $buku) }}" class="space-y-5">
            @csrf
            @method('PATCH')

            {{-- Judul Buku --}}
            <div>
                <label for="judul" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Judul Buku <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="judul"
                    id="judul"
                    value="{{ old('judul', $buku->judul) }}"
                    required
                    maxlength="255"
                    class="block w-full rounded-xl border @error('judul') border-red-500 @else border-slate-300 @enderror px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
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
                    value="{{ old('penulis', $buku->penulis) }}"
                    required
                    maxlength="255"
                    class="block w-full rounded-xl border @error('penulis') border-red-500 @else border-slate-300 @enderror px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
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
                    class="block w-full rounded-xl border @error('deskripsi') border-red-500 @else border-slate-300 @enderror p-3.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                >{{ old('deskripsi', $buku->deskripsi) }}</textarea>
                @error('deskripsi')
                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Stok Total --}}
            <div>
                <label for="stok_total" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Jumlah Stok Eksemplar Total <span class="text-red-500">*</span>
                </label>
                <input
                    type="number"
                    name="stok_total"
                    id="stok_total"
                    value="{{ old('stok_total', $buku->stok_total) }}"
                    required
                    min="{{ max(1, $borrowedCount) }}"
                    max="1000"
                    class="block w-36 rounded-xl border @error('stok_total') border-red-500 @else border-slate-300 @enderror px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                >
                @error('stok_total')
                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
                @if ($borrowedCount > 0)
                    <p class="mt-1.5 text-xs text-amber-700 font-medium">
                        * Minimal stok total adalah {{ $borrowedCount }} karena sedang ada {{ $borrowedCount }} eksemplar yang aktif dipinjam.
                    </p>
                @endif
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition shadow-sm cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Perbarui Informasi Buku</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Danger Zone & Lifecycle Actions Canvas Node 12 --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
        <h3 class="text-sm font-bold text-slate-900">Aksi Status & Siklus Hidup Buku</h3>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
            <div>
                <h4 class="text-xs font-bold text-slate-800">
                    {{ $buku->archived_at ? 'Pulihkan Buku ke Katalog Publik' : 'Arsipkan Buku Ini' }}
                </h4>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $buku->archived_at
                        ? 'Buku yang dipulihkan akan kembali muncul di katalog publik perpustakaan.'
                        : 'Buku yang diarsipkan tidak akan tampil di katalog publik, namun riwayat transaksi tetap terjaga.' }}
                </p>
            </div>
            <div>
                @if ($buku->archived_at)
                    <form method="POST" action="{{ route('admin.buku.restore', $buku) }}">
                        @csrf
                        @method('PATCH')
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition shadow-sm cursor-pointer"
                        >
                            Pulihkan Buku
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.buku.archive', $buku) }}">
                        @csrf
                        @method('PATCH')
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-xl bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900 transition shadow-sm cursor-pointer"
                        >
                            Arsipkan Buku
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Hapus Permanen atau Pengalihan Arsip --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl bg-red-50 border border-red-200">
            <div>
                <h4 class="text-xs font-bold text-red-900">
                    {{ $hasHistory ? 'Arsipkan Otomatis (Memiliki Riwayat Transaksi)' : 'Hapus Buku Permanen' }}
                </h4>
                <p class="text-xs text-red-700 mt-0.5">
                    @if ($hasHistory)
                        Buku ini pernah/sedang dipinjam oleh anggota. Menghapus buku ini akan mengarsipkannya secara otomatis (Q-09) guna menjaga integritas riwayat transaksi.
                    @else
                        Buku ini belum pernah dipinjam oleh siapa pun. Buku dapat dihapus permanen dari basis data.
                    @endif
                </p>
            </div>
            <div>
                <form method="POST" action="{{ route('admin.buku.destroy', $buku) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus / mengarsipkan buku ini?');">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="px-4 py-2 rounded-xl bg-red-600 text-white text-xs font-semibold hover:bg-red-700 transition shadow-sm cursor-pointer"
                    >
                        {{ $hasHistory ? 'Arsipkan Buku' : 'Hapus Permanen' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
