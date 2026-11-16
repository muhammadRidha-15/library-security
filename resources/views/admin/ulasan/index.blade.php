@extends('layouts.admin')

@section('header', 'Moderasi Ulasan Anggota')

@section('content')
<div class="space-y-6">
    {{-- Header Metric Canvas Node 19 --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Ulasan &amp; Opini Buku</h2>
            <p class="text-xs text-slate-500 mt-1">Moderasi konten komentar yang dikirimkan oleh anggota perpustakaan.</p>
        </div>
        <div class="bg-white px-4 py-2.5 rounded-xl border border-slate-200 shadow-sm text-xs font-semibold text-slate-700">
            Total Ulasan Terbit: <span class="text-emerald-600 font-bold ml-1">{{ $totalUlasan }}</span>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.ulasan.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative flex-1">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Cari kata dalam ulasan, judul buku, atau nama penulis ulasan..."
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
                    name="buku_id"
                    class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                >
                    <option value="">Semua Buku</option>
                    @foreach ($bukuList as $b)
                        <option value="{{ $b->id }}" {{ (string)$bukuId === (string)$b->id ? 'selected' : '' }}>
                            {{ $b->judul }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button
                type="submit"
                class="px-4 py-2 rounded-xl bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900 transition shrink-0"
            >
                Filter Ulasan
            </button>
            @if ($search || $bukuId)
                <a
                    href="{{ route('admin.ulasan.index') }}"
                    class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-medium hover:bg-slate-100 transition text-center shrink-0"
                >
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Review List Table Canvas Node 19 --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Penulis Ulasan</th>
                        <th class="px-6 py-3.5">Buku</th>
                        <th class="px-6 py-3.5">Isi Ulasan (Escaped)</th>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5 text-right">Moderasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($ulasan as $item)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="px-6 py-4 align-top">
                                <div class="font-bold text-slate-900">{{ $item->anggota->nama ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $item->anggota->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <a href="{{ route('buku.show', $item->buku_id) }}" class="font-semibold text-emerald-700 hover:underline">
                                    {{ $item->buku->judul ?? '-' }}
                                </a>
                            </td>
                            <td class="px-6 py-4 align-top max-w-md">
                                {{-- SEC-09: Contextual escaping bawaan {{ }} --}}
                                <p class="text-xs text-slate-800 leading-relaxed bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                                    {{ $item->isi }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500 align-top whitespace-nowrap">
                                {{ $item->created_at ? $item->created_at->format('d M Y, H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right align-top whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.ulasan.destroy', $item) }}" onsubmit="return confirm('Hapus ulasan ini secara permanen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold border border-red-200 transition cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Tidak ada ulasan yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($ulasan->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $ulasan->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
