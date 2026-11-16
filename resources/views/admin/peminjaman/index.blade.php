@extends('layouts.admin')

@section('header', 'Sirkulasi & Peminjaman Buku')

@section('content')
<div class="space-y-6">
    {{-- Header Metric Cards & Quick Action Canvas Node 16 --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Transaksi</span>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ $totalPinjaman }}</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sedang Dipinjam</span>
            <div class="mt-2 text-2xl font-bold text-amber-600">{{ $pinjamanAktif }}</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Telah Dikembalikan</span>
            <div class="mt-2 text-2xl font-bold text-emerald-600">{{ $pinjamanKembali }}</div>
        </div>
    </div>

    {{-- Top Action & Tab Filters Canvas Node 16 --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        {{-- Status Filter Tabs --}}
        <div class="flex items-center gap-2 p-1 bg-slate-200/70 rounded-xl text-xs font-semibold">
            <a
                href="{{ route('admin.peminjaman.index', ['status' => 'semua', 'q' => $search, 'anggota_id' => $anggotaId]) }}"
                class="px-3.5 py-1.5 rounded-lg transition {{ $status === 'semua' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
            >
                Semua ({{ $totalPinjaman }})
            </a>
            <a
                href="{{ route('admin.peminjaman.index', ['status' => 'dipinjam', 'q' => $search, 'anggota_id' => $anggotaId]) }}"
                class="px-3.5 py-1.5 rounded-lg transition {{ $status === 'dipinjam' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
            >
                Aktif Dipinjam ({{ $pinjamanAktif }})
            </a>
            <a
                href="{{ route('admin.peminjaman.index', ['status' => 'kembali', 'q' => $search, 'anggota_id' => $anggotaId]) }}"
                class="px-3.5 py-1.5 rounded-lg transition {{ $status === 'kembali' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
            >
                Sudah Kembali ({{ $pinjamanKembali }})
            </a>
        </div>

        {{-- Record Loan Button --}}
        <div>
            <a
                href="{{ route('admin.peminjaman.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition shadow-sm"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Catat Peminjaman Anggota</span>
            </a>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.peminjaman.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <input type="hidden" name="status" value="{{ $status }}">

            <div class="relative flex-1">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Cari judul buku atau nama peminjam..."
                    class="block w-full rounded-xl border border-slate-300 pl-10 pr-4 py-2 text-xs text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="w-full sm:w-64">
                <select
                    name="anggota_id"
                    class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                >
                    <option value="">Semua Anggota</option>
                    @foreach ($anggotaList as $ang)
                        <option value="{{ $ang->id }}" {{ (string)$anggotaId === (string)$ang->id ? 'selected' : '' }}>
                            {{ $ang->nama }} ({{ $ang->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <button
                type="submit"
                class="px-4 py-2 rounded-xl bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900 transition shrink-0"
            >
                Terapkan Filter
            </button>
            @if ($search || $anggotaId)
                <a
                    href="{{ route('admin.peminjaman.index', ['status' => $status]) }}"
                    class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-medium hover:bg-slate-100 transition text-center shrink-0"
                >
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Circulation Table Canvas Node 16 --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Peminjam</th>
                        <th class="px-6 py-3.5">Buku</th>
                        <th class="px-6 py-3.5">Tanggal Pinjam</th>
                        <th class="px-6 py-3.5">Jatuh Tempo</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($peminjaman as $item)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $item->anggota->nama ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $item->anggota->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">{{ $item->buku->judul ?? '-' }}</div>
                                <div class="text-xs text-slate-500">Penulis: {{ $item->buku->penulis ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                {{ $item->dipinjam_pada ? \Carbon\Carbon::parse($item->dipinjam_pada)->format('d M Y, H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                {{ $item->jatuh_tempo ? \Carbon\Carbon::parse($item->jatuh_tempo)->format('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($item->status === 'dipinjam')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Dipinjam
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Kembali
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a
                                    href="{{ route('admin.peminjaman.show', $item) }}"
                                    class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-medium text-slate-700 hover:bg-slate-100 transition"
                                >
                                    Detail & Status
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Tidak ada transaksi peminjaman yang cocok dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($peminjaman->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $peminjaman->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
