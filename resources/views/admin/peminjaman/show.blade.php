@extends('layouts.admin')

@section('header', 'Detail Transaksi Peminjaman')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <a href="{{ route('admin.peminjaman.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali ke Sirkulasi</span>
            </a>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 mt-2">Detail Peminjaman #{{ $peminjaman->id }}</h1>
        </div>
        <div>
            @if ($peminjaman->status === 'dipinjam')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    Status: Sedang Dipinjam
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Status: Sudah Dikembalikan
                </span>
            @endif
        </div>
    </div>

    {{-- Detail Anggota & Koleksi Canvas Node 18 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Kartu Anggota --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-3">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Identitas Peminjam</h3>
            <div class="font-bold text-slate-900 text-base">{{ $peminjaman->anggota->nama ?? '-' }}</div>
            <div class="text-xs text-slate-500">Email: {{ $peminjaman->anggota->email ?? '-' }}</div>
            <div class="pt-2 flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ ($peminjaman->anggota->is_active ?? false) ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                    {{ ($peminjaman->anggota->is_active ?? false) ? 'Akun Aktif' : 'Akun Nonaktif' }}
                </span>
                <a href="{{ route('admin.anggota.edit', $peminjaman->anggota_id) }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">
                    Lihat Akun &rarr;
                </a>
            </div>
        </div>

        {{-- Kartu Buku --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-3">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Koleksi Buku</h3>
            <div class="font-bold text-slate-900 text-base">{{ $peminjaman->buku->judul ?? '-' }}</div>
            <div class="text-xs text-slate-500">Penulis: {{ $peminjaman->buku->penulis ?? '-' }}</div>
            <div class="pt-2 text-xs text-slate-600 flex items-center gap-3">
                <span>Stok Total: <strong>{{ $peminjaman->buku->stok_total ?? '-' }}</strong></span>
                <span>•</span>
                <span>Stok Tersedia: <strong>{{ $peminjaman->buku->stok_tersedia ?? '-' }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Tanggal Sirkulasi --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Waktu dan Status Transaksi</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-slate-500 block">Waktu Dipinjam</span>
                <span class="text-sm font-bold text-slate-900 mt-1 block">
                    {{ $peminjaman->dipinjam_pada ? \Carbon\Carbon::parse($peminjaman->dipinjam_pada)->format('d M Y, H:i') : '-' }}
                </span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-slate-500 block">Batas Jatuh Tempo</span>
                <span class="text-sm font-bold text-slate-900 mt-1 block">
                    {{ $peminjaman->jatuh_tempo ? \Carbon\Carbon::parse($peminjaman->jatuh_tempo)->format('d M Y') : '-' }}
                </span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-slate-500 block">Waktu Dikembalikan</span>
                <span class="text-sm font-bold {{ $peminjaman->dikembalikan_pada ? 'text-emerald-700' : 'text-slate-400' }} mt-1 block">
                    {{ $peminjaman->dikembalikan_pada ? \Carbon\Carbon::parse($peminjaman->dikembalikan_pada)->format('d M Y, H:i') : 'Belum dikembalikan' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Aksi Pengembalian Canvas Node 18 --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
        <h3 class="text-sm font-bold text-slate-900">Aksi Pemrosesan Sirkulasi</h3>

        @if ($peminjaman->status === 'dipinjam')
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                <div>
                    <h4 class="text-xs font-bold text-emerald-900">Proses Pengembalian Buku</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">
                        Menandai transaksi selesai, mencatat waktu pengembalian, dan memulihkan stok eksemplar buku (+1) secara otomatis.
                    </p>
                </div>
                <div>
                    <form method="POST" action="{{ route('admin.peminjaman.pengembalian', $peminjaman) }}">
                        @csrf
                        @method('PATCH')
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition shadow-sm cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Proses Pengembalian</span>
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Buku ini telah dikembalikan pada {{ \Carbon\Carbon::parse($peminjaman->dikembalikan_pada)->format('d M Y, H:i') }}. Tidak ada tindakan sirkulasi lanjutan yang diperlukan.</span>
            </div>
        @endif
    </div>
</div>
@endsection
