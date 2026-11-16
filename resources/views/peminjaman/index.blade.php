@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Riwayat Peminjaman</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar koleksi buku yang sedang Anda pinjam atau telah dikembalikan.</p>
        </div>
        <div>
            <a
                href="{{ route('buku.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition shadow-sm"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Jelajahi Katalog Buku</span>
            </a>
        </div>
    </div>

    @if ($peminjaman->isEmpty())
        {{-- Canvas Node 20: Riwayat Kosong --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-sm">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-800">Belum Ada Riwayat Peminjaman</h3>
            <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                Anda belum pernah meminjam koleksi buku. Temukan buku menarik dan ajukan peminjaman sekarang.
            </p>
            <div class="mt-6">
                <a
                    href="{{ route('buku.index') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white font-medium text-xs hover:bg-emerald-700 transition shadow-sm"
                >
                    Lihat Koleksi Buku
                </a>
            </div>
        </div>
    @else
        {{-- Canvas Node 06: Tabel Riwayat Peminjaman --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-3.5">Buku</th>
                            <th scope="col" class="px-6 py-3.5">Tanggal Pinjam</th>
                            <th scope="col" class="px-6 py-3.5">Jatuh Tempo</th>
                            <th scope="col" class="px-6 py-3.5">Status</th>
                            <th scope="col" class="px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($peminjaman as $item)
                            <tr class="hover:bg-slate-50/75 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $item->buku->judul ?? 'Buku Tidak Diketahui' }}</div>
                                    <div class="text-xs text-slate-500">{{ $item->buku->penulis ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600">
                                    {{ $item->dipinjam_pada ? \Carbon\Carbon::parse($item->dipinjam_pada)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600">
                                    {{ $item->jatuh_tempo ? \Carbon\Carbon::parse($item->jatuh_tempo)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($item->status === 'dipinjam')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Dipinjam
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Dikembalikan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a
                                        href="{{ route('peminjaman.show', $item) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition"
                                    >
                                        <span>Detail</span>
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
