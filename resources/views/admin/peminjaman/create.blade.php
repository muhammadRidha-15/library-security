@extends('layouts.admin')

@section('header', 'Catat Peminjaman Anggota')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <a href="{{ route('admin.peminjaman.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali ke Sirkulasi</span>
            </a>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 mt-2">Formulir Peminjaman Koleksi</h1>
        </div>
    </div>

    {{-- Form Peminjaman Canvas Node 17 --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.peminjaman.store') }}" class="space-y-5">
            @csrf

            {{-- Pilih Anggota Peminjam --}}
            <div>
                <label for="anggota_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Anggota Peminjam (Hanya Akun Aktif) <span class="text-red-500">*</span>
                </label>
                <select
                    name="anggota_id"
                    id="anggota_id"
                    required
                    class="block w-full rounded-xl border @error('anggota_id') border-red-500 @else border-slate-300 @enderror px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                >
                    <option value="">-- Pilih Anggota --</option>
                    @foreach ($anggotaList as $member)
                        <option value="{{ $member->id }}" {{ old('anggota_id') == $member->id ? 'selected' : '' }}>
                            {{ $member->nama }} &mdash; {{ $member->email }}
                        </option>
                    @endforeach
                </select>
                @error('anggota_id')
                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Pilih Buku --}}
            <div>
                <label for="buku_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Buku yang Dipinjam <span class="text-red-500">*</span>
                </label>
                <select
                    name="buku_id"
                    id="buku_id"
                    required
                    class="block w-full rounded-xl border @error('buku_id') border-red-500 @else border-slate-300 @enderror px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                >
                    <option value="">-- Pilih Koleksi Buku --</option>
                    @foreach ($bukuList as $book)
                        <option value="{{ $book->id }}" {{ old('buku_id') == $book->id ? 'selected' : '' }}>
                            {{ $book->judul }} (Penulis: {{ $book->penulis }} | Stok Tersedia: {{ $book->stok_tersedia }})
                        </option>
                    @endforeach
                </select>
                @error('buku_id')
                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-slate-400">
                    Hanya menampilkan buku katalog aktif yang memiliki ketersediaan stok &gt; 0.
                </p>
            </div>

            {{-- Kebijakan Peminjaman --}}
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1">
                <div class="font-semibold text-slate-800">Ketentuan Sirkulasi Peminjaman:</div>
                <ul class="list-disc list-inside space-y-0.5 text-slate-500">
                    <li>Durasi peminjaman standar berlaku selama 7 hari kalender sejak dicatat.</li>
                    <li>Stok eksemplar buku akan berkurang otomatis secara atomik 1 unit.</li>
                </ul>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a
                    href="{{ route('admin.peminjaman.index') }}"
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
                    <span>Catat Transaksi Peminjaman</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
