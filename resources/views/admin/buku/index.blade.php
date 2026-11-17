@extends('layouts.admin')

@section('header', 'Kelola Koleksi Buku')

@section('content')
<div class="space-y-6">
    {{-- Top Action & Tab Filters Canvas Node 10 --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        {{-- Status Tabs --}}
        <div class="flex items-center gap-2 p-1 bg-slate-200/70 rounded-xl text-xs font-semibold">
            <a
                href="{{ route('admin.buku.index', ['status' => 'aktif', 'q' => $search]) }}"
                class="px-3.5 py-1.5 rounded-lg transition {{ $status === 'aktif' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
            >
                Buku Aktif ({{ $totalAktif }})
            </a>
            <a
                href="{{ route('admin.buku.index', ['status' => 'arsip', 'q' => $search]) }}"
                class="px-3.5 py-1.5 rounded-lg transition {{ $status === 'arsip' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
            >
                Diarsipkan ({{ $totalArsip }})
            </a>
            <a
                href="{{ route('admin.buku.index', ['status' => 'semua', 'q' => $search]) }}"
                class="px-3.5 py-1.5 rounded-lg transition {{ $status === 'semua' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
            >
                Semua
            </a>
        </div>

        {{-- Add Book Button --}}
        <div>
            <a
                href="{{ route('admin.buku.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition shadow-sm"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Buku Baru</span>
            </a>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.buku.index') }}" class="flex items-center gap-3">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative flex-1">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Cari berdasarkan judul atau nama penulis..."
                    class="block w-full rounded-xl border border-slate-300 pl-10 pr-4 py-2 text-xs text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <button
                type="submit"
                class="px-4 py-2 rounded-xl bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900 transition"
            >
                Cari
            </button>
            @if ($search)
                <a
                    href="{{ route('admin.buku.index', ['status' => $status]) }}"
                    class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-medium hover:bg-slate-100 transition"
                >
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Book Table Canvas Node 10 --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Judul & Penulis</th>
                        <th class="px-6 py-3.5 text-center">Stok Total</th>
                        <th class="px-6 py-3.5 text-center">Stok Tersedia</th>
                        <th class="px-6 py-3.5 text-center">Dipinjam</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($buku as $item)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img
                                        src="{{ $item->foto_url }}"
                                        alt=""
                                        class="w-10 h-14 object-cover rounded-md border border-slate-200 shrink-0 bg-slate-100 shadow-xs"
                                        loading="lazy"
                                    >
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $item->judul }}</div>
                                        <div class="text-xs text-slate-500">Penulis: {{ $item->penulis }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-slate-800 text-xs">
                                {{ $item->stok_total }}
                            </td>
                            <td class="px-6 py-4 text-center text-xs">
                                <span class="font-bold {{ $item->stok_tersedia > 0 ? 'text-emerald-700' : 'text-red-600' }}">
                                    {{ $item->stok_tersedia }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-xs font-medium text-amber-700">
                                {{ $item->pinjaman_aktif_count ?? 0 }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($item->archived_at)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        Diarsipkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <a
                                        href="{{ route('admin.buku.edit', $item) }}"
                                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-medium text-slate-700 hover:bg-slate-100 transition"
                                    >
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Tidak ada koleksi buku yang sesuai dengan kriteria filter atau pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($buku->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $buku->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
