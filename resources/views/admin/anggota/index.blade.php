@extends('layouts.admin')

@section('header', 'Kelola Anggota Perpustakaan')

@section('content')
<div class="space-y-6">
    {{-- Header Metric & Actions Canvas Node 13 --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        {{-- Status Filter Tabs --}}
        <div class="flex items-center gap-2 p-1 bg-slate-200/70 rounded-xl text-xs font-semibold">
            <a
                href="{{ route('admin.anggota.index', ['status' => 'semua', 'q' => $search]) }}"
                class="px-3.5 py-1.5 rounded-lg transition {{ $status === 'semua' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
            >
                Semua User ({{ $totalUser }})
            </a>
            <a
                href="{{ route('admin.anggota.index', ['status' => 'aktif', 'q' => $search]) }}"
                class="px-3.5 py-1.5 rounded-lg transition {{ $status === 'aktif' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
            >
                Aktif ({{ $totalUserAktif }})
            </a>
            <a
                href="{{ route('admin.anggota.index', ['status' => 'nonaktif', 'q' => $search]) }}"
                class="px-3.5 py-1.5 rounded-lg transition {{ $status === 'nonaktif' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
            >
                Nonaktif ({{ $totalUserNonaktif }})
            </a>
        </div>

        {{-- Add Member Button --}}
        <div>
            <a
                href="{{ route('admin.anggota.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition shadow-sm"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>Tambah Anggota Baru</span>
            </a>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.anggota.index') }}" class="flex items-center gap-3">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative flex-1">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Cari berdasarkan nama anggota atau email..."
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
                    href="{{ route('admin.anggota.index', ['status' => $status]) }}"
                    class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-medium hover:bg-slate-100 transition"
                >
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Member Table Canvas Node 13 --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Nama & Email</th>
                        <th class="px-6 py-3.5 text-center">Peran</th>
                        <th class="px-6 py-3.5 text-center">Status Akun</th>
                        <th class="px-6 py-3.5 text-center">Pinjaman Aktif</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($anggota as $item)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $item->nama }}</div>
                                <div class="text-xs text-slate-500">{{ $item->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $item->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ ucfirst($item->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($item->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-xs font-semibold {{ ($item->pinjaman_aktif_count ?? 0) > 0 ? 'text-amber-700' : 'text-slate-400' }}">
                                {{ $item->pinjaman_aktif_count ?? 0 }} Buku
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a
                                    href="{{ route('admin.anggota.edit', $item) }}"
                                    class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-medium text-slate-700 hover:bg-slate-100 transition"
                                >
                                    Kelola Akun
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Tidak ada akun anggota yang sesuai kriteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($anggota->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $anggota->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
