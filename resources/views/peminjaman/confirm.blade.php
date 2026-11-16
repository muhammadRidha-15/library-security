@extends('layouts.app')

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 max-w-lg mx-auto">
    <div class="text-center pb-6 border-b border-slate-100">
        <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <h1 class="text-xl font-bold text-slate-900">Konfirmasi Peminjaman</h1>
        <p class="text-xs text-slate-500 mt-1">Pastikan informasi buku yang akan Anda pinjam sudah sesuai.</p>
    </div>

    <div class="py-6 space-y-4">
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
            <div class="text-xs text-slate-500">Judul Koleksi</div>
            <div class="text-base font-bold text-slate-900 leading-snug">{{ $buku->judul }}</div>
            <div class="text-xs text-slate-600">Penulis: <span class="font-medium text-slate-800">{{ $buku->penulis }}</span></div>
        </div>

        <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block">Sisa Stok</span>
                <span class="font-bold text-slate-900 text-sm">{{ $buku->stok_tersedia }} dari {{ $buku->stok_total }}</span>
            </div>
            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block">Durasi Peminjaman</span>
                <span class="font-bold text-emerald-700 text-sm">7 Hari</span>
            </div>
        </div>

        <p class="text-xs text-slate-500 italic">
            * Peminjaman akan dicatat atas nama akun Anda. Harap mengembalikan buku tepat waktu.
        </p>
    </div>

    <form method="POST" action="{{ route('peminjaman.store') }}" class="space-y-3">
        @csrf
        <input type="hidden" name="buku_id" value="{{ $buku->id }}">

        <button
            type="submit"
            @if ($buku->stok_tersedia <= 0) disabled @endif
            class="w-full justify-center py-2.5 px-4 rounded-xl text-white font-semibold text-sm transition shadow-sm {{ $buku->stok_tersedia > 0 ? 'bg-emerald-600 hover:bg-emerald-700 cursor-pointer' : 'bg-slate-400 cursor-not-allowed opacity-60' }}"
        >
            Konfirmasi Pinjam Buku
        </button>

        <a
            href="{{ route('buku.show', $buku) }}"
            class="block w-full text-center py-2 px-4 rounded-xl text-slate-600 font-medium text-xs hover:bg-slate-100 transition"
        >
            Batalkan dan Kembali
        </a>
    </form>
</div>
@endsection
