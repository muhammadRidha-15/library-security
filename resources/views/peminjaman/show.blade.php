@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('peminjaman.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Kembali ke Riwayat</span>
                </a>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 mt-2">Detail Transaksi Peminjaman</h1>
        </div>
        <div>
            @if ($peminjaman->status === 'dipinjam')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    Sedang Dipinjam
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Sudah Dikembalikan
                </span>
            @endif
        </div>
    </div>

    {{-- Detail Card Canvas Node 07 --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-6 border-b border-slate-100 text-xs">
            <div>
                <span class="text-slate-400 block font-medium">Nomor Referensi Transaksi</span>
                <span class="font-mono text-sm font-bold text-slate-800">#TRX-{{ str_pad($peminjaman->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Peminjam</span>
                <span class="text-sm font-semibold text-slate-800">{{ $peminjaman->anggota->nama ?? '-' }}</span>
            </div>
        </div>

        <div class="space-y-3 pb-6 border-b border-slate-100">
            <span class="text-xs text-slate-400 block font-medium">Informasi Buku</span>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                <h3 class="text-base font-bold text-slate-900">{{ $peminjaman->buku->judul ?? 'Buku Tidak Diketahui' }}</h3>
                <p class="text-xs text-slate-600">Penulis: <span class="font-medium text-slate-800">{{ $peminjaman->buku->penulis ?? '-' }}</span></p>
                <div class="pt-2">
                    <a href="{{ route('buku.show', $peminjaman->buku_id) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 inline-flex items-center gap-1">
                        <span>Buka Halaman Koleksi Buku</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block mb-1">Tanggal Peminjaman</span>
                <span class="font-bold text-slate-800 text-sm">
                    {{ $peminjaman->dipinjam_pada ? \Carbon\Carbon::parse($peminjaman->dipinjam_pada)->format('d M Y') : '-' }}
                </span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block mb-1">Batas Jatuh Tempo</span>
                <span class="font-bold text-amber-700 text-sm">
                    {{ $peminjaman->jatuh_tempo ? \Carbon\Carbon::parse($peminjaman->jatuh_tempo)->format('d M Y') : '-' }}
                </span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block mb-1">Tanggal Dikembalikan</span>
                <span class="font-bold text-slate-800 text-sm">
                    {{ $peminjaman->dikembalikan_pada ? \Carbon\Carbon::parse($peminjaman->dikembalikan_pada)->format('d M Y') : 'Belum Kembali' }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
