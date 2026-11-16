@extends('layouts.app')

@section('title', 'Katalog Buku — Perpustakaan Mini')

@section('content')
{{-- Canvas Node: 01 · Katalog Buku (ID: 29bc24c3-85bf-4afa-8b13-4cf4d9bf8f09) --}}
<div class="space-y-8">
    {{-- Search bar section --}}
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <div class="max-w-2xl">
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Katalog Buku Perpustakaan</h1>
            <p class="mt-2 text-sm text-slate-600">Temukan buku yang Anda butuhkan melalui pencarian judul atau nama penulis di bawah ini.</p>
        </div>

        <form method="GET" action="{{ route('buku.cari') }}" class="mt-6 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="text"
                    name="q"
                    id="q"
                    value="{{ old('q', $searchQuery ?? '') }}"
                    maxlength="100"
                    placeholder="Cari berdasarkan judul atau penulis..."
                    class="block w-full pl-10 pr-4 py-2.5 rounded-xl border @error('q') border-red-500 @else border-slate-300 @enderror text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 text-sm transition"
                >
            </div>
            <div class="flex items-center gap-2">
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition text-sm flex items-center justify-center gap-2 shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Cari</span>
                </button>
                @if (!empty($searchQuery))
                    <a
                        href="{{ route('buku.index') }}"
                        class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 transition text-sm font-medium"
                    >
                        Reset
                    </a>
                @endif
            </div>
        </form>

        @error('q')
            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
        @enderror

        @if (!empty($searchQuery))
            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-slate-600 bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg">
                <span>Hasil pencarian untuk: <strong class="text-slate-900">"{{ $searchQuery }}"</strong></span>
                <span class="text-slate-400">&bull;</span>
                <span>{{ $buku->count() }} buku ditemukan</span>
                <span class="text-slate-400">&bull;</span>
                <a href="{{ route('buku.index') }}" class="text-emerald-700 hover:underline">Tampilkan semua buku</a>
            </div>
        @endif
    </div>

    {{-- Books Grid / List --}}
    @if ($buku->isEmpty())
        <div class="bg-white p-12 text-center rounded-2xl border border-slate-200 shadow-sm">
            <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-slate-900">Tidak ada buku yang ditemukan</h3>
            <p class="mt-1 text-sm text-slate-500 max-w-sm mx-auto">
                @if (!empty($searchQuery))
                    Tidak ditemukan buku dengan kata kunci "{{ $searchQuery }}". Coba kata kunci lain atau bersihkan pencarian.
                @else
                    Belum ada koleksi buku aktif yang tersedia di katalog perpustakaan saat ini.
                @endif
            </p>
            @if (!empty($searchQuery))
                <div class="mt-4">
                    <a href="{{ route('buku.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-sm font-medium transition">
                        Kembali ke Semua Buku
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($buku as $item)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col overflow-hidden">
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md">
                                Buku
                            </span>
                            @if ($item->stok_tersedia > 0)
                                <span class="text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                                    Tersedia
                                </span>
                            @else
                                <span class="text-xs font-medium text-red-700 bg-red-50 border border-red-200 px-2.5 py-0.5 rounded-full">
                                    Habis
                                </span>
                            @endif
                        </div>

                        <h2 class="text-lg font-bold text-slate-900 line-clamp-2 hover:text-emerald-700 transition">
                            <a href="{{ route('buku.show', $item) }}">
                                {{ $item->judul }}
                            </a>
                        </h2>

                        <p class="mt-1 text-xs text-slate-500 font-medium">
                            Penulis: <span class="text-slate-700">{{ $item->penulis }}</span>
                        </p>

                        <p class="mt-3 text-sm text-slate-600 line-clamp-3 flex-1">
                            {{ $item->deskripsi ?: 'Tidak ada deskripsi tersedia.' }}
                        </p>

                        <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                            <div>
                                <span>Stok: </span>
                                <strong class="text-slate-800">{{ $item->stok_tersedia }}</strong>
                                <span> / {{ $item->stok_total }}</span>
                            </div>
                            <a
                                href="{{ route('buku.show', $item) }}"
                                class="inline-flex items-center gap-1 font-semibold text-emerald-700 hover:text-emerald-800 transition"
                            >
                                <span>Detail</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
