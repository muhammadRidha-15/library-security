@extends('layouts.admin')

@section('header', 'Ringkasan Sistem Perpustakaan')

@section('content')
<div class="space-y-6">
    {{-- Metric Cards Canvas Node 09 --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Koleksi --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Koleksi Buku</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold text-slate-900">{{ $totalBuku }}</span>
                <div class="text-xs text-slate-500 mt-1 flex gap-2">
                    <span class="text-emerald-700 font-medium">{{ $bukuAktif }} Aktif</span>
                    <span>•</span>
                    <span class="text-slate-400">{{ $bukuArsip }} Arsip</span>
                </div>
            </div>
        </div>

        {{-- Pinjaman Aktif --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pinjaman Berjalan</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold text-amber-600">{{ $pinjamanAktif }}</span>
                <div class="text-xs text-slate-500 mt-1">
                    <span>Sedang dipinjam oleh anggota</span>
                </div>
            </div>
        </div>

        {{-- Pinjaman Selesai --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pengembalian</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold text-blue-600">{{ $pinjamanSelesai }}</span>
                <div class="text-xs text-slate-500 mt-1">
                    <span>Total riwayat selesai</span>
                </div>
            </div>
        </div>

        {{-- Total Anggota --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Anggota Perpustakaan</span>
                <span class="p-2 rounded-xl bg-purple-50 text-purple-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold text-slate-900">{{ $totalAnggota }}</span>
                <div class="text-xs text-slate-500 mt-1">
                    <span class="text-emerald-700 font-medium">{{ $anggotaAktif }} Akun Aktif</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Action Section --}}
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.buku.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Buku Baru</span>
        </a>
        <a href="{{ route('admin.buku.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition shadow-sm">
            <span>Kelola Katalog Buku</span>
        </a>
        <a href="{{ url('/admin/peminjaman') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition shadow-sm">
            <span>Sirkulasi Peminjaman</span>
        </a>
    </div>

    {{-- Recent Loans Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Peminjaman Terbaru</h2>
            <a href="{{ url('/admin/peminjaman') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Lihat Semua &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Peminjam</th>
                        <th class="px-6 py-3">Buku</th>
                        <th class="px-6 py-3">Tanggal Pinjam</th>
                        <th class="px-6 py-3">Jatuh Tempo</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($peminjamanTerbaru as $loan)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="px-6 py-3.5 font-semibold text-slate-900">
                                {{ $loan->anggota->nama ?? '-' }}
                            </td>
                            <td class="px-6 py-3.5 text-slate-800">
                                {{ $loan->buku->judul ?? '-' }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-600">
                                {{ $loan->dipinjam_pada ? \Carbon\Carbon::parse($loan->dipinjam_pada)->format('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-600">
                                {{ $loan->jatuh_tempo ? \Carbon\Carbon::parse($loan->jatuh_tempo)->format('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-3.5">
                                @if ($loan->status === 'dipinjam')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Dipinjam
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Kembali
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-xs text-slate-400">
                                Belum ada aktivitas sirkulasi peminjaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
