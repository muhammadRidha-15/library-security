@extends('layouts.app')

@section('title', $buku->judul . ' — Perpustakaan Mini')

@section('container-class', 'max-w-4xl')

@section('content')
{{-- Canvas Node: 02 · Detail Buku dan Ulasan (ID: d4131952-b7bc-4a84-bfc4-3edb6a3c0185) --}}
<div class="space-y-6">
    {{-- Back to catalog button --}}
    <div>
        <a href="{{ route('buku.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-emerald-700 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Katalog</span>
        </a>
    </div>

    {{-- Book Detail Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row items-start gap-6 pb-6 border-b border-slate-100">
            {{-- Book Cover --}}
            <div class="w-full sm:w-44 md:w-48 shrink-0 aspect-[3/4] rounded-xl overflow-hidden shadow-md border border-slate-200 bg-slate-100 self-center sm:self-start">
                <img
                    src="{{ $buku->foto_url }}"
                    alt="Sampul {{ $buku->judul }}"
                    class="w-full h-full object-cover"
                >
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md">
                                Buku Perpustakaan
                            </span>
                            @if ($buku->stok_tersedia > 0)
                                <span class="text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                                    Tersedia
                                </span>
                            @else
                                <span class="text-xs font-medium text-red-700 bg-red-50 border border-red-200 px-2.5 py-0.5 rounded-full">
                                    Stok Habis
                                </span>
                            @endif
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
                            {{ $buku->judul }}
                        </h1>
                        <p class="mt-1 text-sm text-slate-600">
                            Penulis: <span class="font-medium text-slate-800">{{ $buku->penulis }}</span>
                        </p>
                    </div>

                    {{-- Stock summary box --}}
                    <div class="sm:text-right bg-slate-50 border border-slate-200 rounded-xl p-4 shrink-0">
                        <span class="text-xs text-slate-500 font-medium block">Informasi Stok</span>
                        <div class="mt-1 flex items-baseline gap-1 sm:justify-end">
                            <span class="text-2xl font-bold text-slate-900">{{ $buku->stok_tersedia }}</span>
                            <span class="text-xs text-slate-500">/ {{ $buku->stok_total }} eksemplar</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Description --}}
        <div class="py-6 border-b border-slate-100">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-2">Deskripsi Buku</h2>
            <div class="text-slate-700 text-sm leading-relaxed whitespace-pre-line">
                {{ $buku->deskripsi ?: 'Tidak ada deskripsi tersedia untuk buku ini.' }}
            </div>
        </div>

        {{-- Borrow Action Section --}}
        <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-slate-500">
                @if ($buku->stok_tersedia > 0)
                    <span>Buku ini dapat dipinjam oleh anggota terdaftar.</span>
                @else
                    <span class="text-red-600 font-medium">Buku ini sedang habis dipinjam oleh anggota lain.</span>
                @endif
            </div>

            <div>
                @guest
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700 text-sm transition shadow-sm"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        <span>Masuk untuk Meminjam</span>
                    </a>
                @else
                    @if (auth()->user()->role === 'user')
                        <a
                            href="{{ route('buku.pinjam.confirm', $buku) }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white font-medium text-sm transition shadow-sm {{ $buku->stok_tersedia > 0 ? 'bg-emerald-600 hover:bg-emerald-700 cursor-pointer' : 'bg-slate-400 cursor-not-allowed opacity-60 pointer-events-none' }}"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Pinjam Buku</span>
                        </a>
                    @else
                        <span class="text-xs font-medium text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
                            Mode Administrator (Peminjaman hanya untuk akun Anggota)
                        </span>
                    @endif
                @endguest
            </div>
        </div>
    </div>

    {{-- Reviews Section --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Ulasan Pembaca</h3>
                <p class="text-xs text-slate-500 mt-0.5">Ulasan dan tanggapan dari para anggota perpustakaan.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-700 rounded-full">
                {{ $buku->ulasan->count() }} Ulasan
            </span>
        </div>

        {{-- Form Tulis Ulasan (Canvas Node 02) --}}
        @auth
            @if (auth()->user()->is_active)
                <div class="p-5 rounded-xl bg-slate-50 border border-slate-200">
                    <h4 class="text-sm font-semibold text-slate-900 mb-2">Tulis Ulasan Anda</h4>
                    <form method="POST" action="{{ route('buku.ulasan.store', $buku) }}" class="space-y-3">
                        @csrf
                        <div>
                            <textarea
                                name="isi"
                                id="isi"
                                rows="3"
                                minlength="1"
                                maxlength="2000"
                                required
                                placeholder="Bagikan pendapat Anda mengenai buku ini (maksimal 2.000 karakter)..."
                                class="block w-full rounded-lg border @error('isi') border-red-500 @else border-slate-300 @enderror p-3 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                            >{{ old('isi') }}</textarea>
                            @error('isi')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                <span>Kirim Ulasan</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        @else
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center text-sm text-slate-600">
                Ingin memberikan ulasan?
                <a href="{{ route('login') }}" class="font-medium text-emerald-600 hover:text-emerald-700">Masuk ke akun</a>
                atau
                <a href="{{ route('register') }}" class="font-medium text-emerald-600 hover:text-emerald-700">daftar sekarang</a>.
            </div>
        @endauth

        @if ($buku->ulasan->isEmpty())
            <div class="text-center py-8">
                <p class="text-sm text-slate-500">Belum ada ulasan untuk buku ini.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($buku->ulasan as $ulasan)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs uppercase">
                                    {{ substr($ulasan->anggota->nama ?? 'A', 0, 1) }}
                                </div>
                                <span class="font-semibold text-slate-800">
                                    {{ $ulasan->anggota->nama ?? 'Anggota Perpustakaan' }}
                                </span>
                            </div>
                            <span class="text-slate-500">
                                {{ $ulasan->created_at ? $ulasan->created_at->format('d M Y, H:i') : '' }}
                            </span>
                        </div>
                        <div class="text-sm text-slate-700 leading-relaxed break-words">
                            {{-- VULNERABLE BRANCH: Raw HTML render without Blade escaping for stored XSS demo (TKT-24 / SEC-09) --}}
                            {!! $ulasan->isi !!}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
