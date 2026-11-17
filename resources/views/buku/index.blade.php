@extends('layouts.app')

@section('title', 'Katalog Buku — Perpustakaan Mini')

@section('content')
<div class="space-y-8">
    <section class="rounded-2xl bg-emerald-50 p-6 sm:p-8 lg:p-10">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Koleksi Perpustakaan</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight text-emerald-900 sm:text-4xl">Temukan bacaan berikutnya.</h1>
                <p class="mt-4 text-sm text-slate-600 sm:text-base">Jelajahi koleksi buku dan temukan referensi yang Anda butuhkan.</p>
            </div>
            <div class="rounded-xl border border-emerald-100 bg-white px-6 py-4 sm:min-w-52">
                <p class="text-3xl font-bold text-emerald-700">{{ $buku->count() }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-600">{{ !empty($searchQuery) ? 'Hasil pencarian' : 'Buku dalam katalog' }}</p>
            </div>
        </div>
    </section>

    <section aria-labelledby="daftar-buku">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 id="daftar-buku" class="text-2xl font-bold text-slate-900">{{ !empty($searchQuery) ? 'Hasil pencarian' : 'Semua buku' }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $buku->count() }} buku {{ !empty($searchQuery) ? 'ditemukan' : 'dalam koleksi' }}</p>
            </div>
            <form method="GET" action="{{ route('buku.cari') }}" class="w-full lg:max-w-lg">
                <label for="q" class="mb-2 block text-sm font-medium text-slate-700">Cari buku</label>
                <div class="flex flex-wrap gap-3 sm:flex-nowrap">
                    <input type="text" name="q" id="q" value="{{ old('q', $searchQuery ?? '') }}" maxlength="100"
                        placeholder="Cari judul atau nama penulis..."
                        @error('q') aria-invalid="true" aria-describedby="q-error" @enderror
                        class="min-w-0 flex-1 rounded-xl border @error('q') border-red-500 @else border-slate-300 @enderror bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    <button type="submit" class="rounded-xl bg-emerald-700 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">Cari</button>
                    @if (!empty($searchQuery))
                        <a href="{{ route('buku.index') }}" class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-100">Reset</a>
                    @endif
                </div>
                @error('q')
                    <p id="q-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </form>
        </div>

        @if (!empty($searchQuery))
            <p class="mt-4 text-sm text-slate-600">Hasil pencarian untuk: <strong class="text-slate-900">"{{ $searchQuery }}"</strong> &middot; <a href="{{ route('buku.index') }}" class="text-emerald-700 underline">Tampilkan semua buku</a></p>
        @endif

        @if ($buku->isEmpty())
            <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-8 text-center sm:p-12">
                <h3 class="text-base font-semibold text-slate-900">Tidak ada buku yang ditemukan</h3>
                <p class="mx-auto mt-2 max-w-sm text-sm text-slate-500">
                    @if (!empty($searchQuery))
                        Tidak ditemukan buku dengan kata kunci "{{ $searchQuery }}". Coba kata kunci lain atau bersihkan pencarian.
                    @else
                        Belum ada koleksi buku aktif yang tersedia di katalog perpustakaan saat ini.
                    @endif
                </p>
                @if (!empty($searchQuery))
                    <a href="{{ route('buku.index') }}" class="mt-4 inline-block text-sm font-semibold text-emerald-700 underline">Kembali ke Semua Buku</a>
                @endif
            </div>
        @else
            <div class="mt-6 space-y-5">
                @foreach ($buku as $item)
                    <article class="flex flex-col gap-5 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row">
                        <a href="{{ route('buku.show', $item) }}" class="block h-40 w-32 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                            <img src="{{ $item->foto_url }}" alt="Sampul {{ $item->judul }}" class="h-full w-full object-cover" loading="lazy">
                        </a>
                        <div class="min-w-0 flex-1 py-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Buku</p>
                            <h3 class="mt-2 text-xl font-bold text-slate-900"><a href="{{ route('buku.show', $item) }}" class="hover:text-emerald-700">{{ $item->judul }}</a></h3>
                            <p class="mt-1 text-sm text-slate-600">Oleh {{ $item->penulis }}</p>
                            <p class="mt-4 text-sm text-slate-600">{{ $item->deskripsi ?: 'Tidak ada deskripsi tersedia.' }}</p>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 pt-4 sm:w-56 sm:shrink-0 sm:flex-col sm:items-start sm:border-l sm:border-t-0 sm:py-1 sm:pl-6">
                            <div>
                                @if ($item->stok_tersedia > 0)
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">Tersedia</span>
                                @else
                                    <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">Habis</span>
                                @endif
                                <p class="mt-4 text-xs font-semibold uppercase text-slate-500">Stok tersedia</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $item->stok_tersedia }} / {{ $item->stok_total }}</p>
                            </div>
                            <a href="{{ route('buku.show', $item) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-emerald-700 px-4 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Lihat detail <span aria-hidden="true" class="ml-2">→</span></a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
