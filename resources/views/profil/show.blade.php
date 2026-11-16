@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="pb-4 border-b border-slate-200">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Profil Saya</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola informasi akun dan data kontak terenkripsi Anda.</p>
    </div>

    {{-- Canvas Node 08: Ringkasan Akun --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="flex items-center gap-4 pb-6 border-b border-slate-100">
            <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xl uppercase">
                {{ substr($user->nama, 0, 1) }}
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900">{{ $user->nama }}</h2>
                <p class="text-xs text-slate-500">{{ $user->email }}</p>
                <div class="mt-2 flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                        Peran: {{ ucfirst($user->role) }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ $user->is_active ? 'Akun Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Form Pembaruan Telepon Terenkripsi --}}
        <div>
            <div class="mb-4">
                <h3 class="text-base font-bold text-slate-900">Kontak Nomor Telepon</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Data nomor telepon Anda diamankan menggunakan enkripsi simetris <strong>AES-256-GCM</strong> dengan Nonce 96-bit dan Authentication Tag 128-bit yang terikat identitas akun.
                </p>
            </div>

            <form method="POST" action="{{ route('profil.telepon.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="telepon" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nomor Telepon (8 - 15 Digit)
                    </label>
                    <input
                        type="text"
                        name="telepon"
                        id="telepon"
                        value="{{ old('telepon', $teleponPlaintext) }}"
                        placeholder="Contoh: 081299998888 atau +6281299998888"
                        class="block w-full rounded-xl border @error('telepon') border-red-500 @else border-slate-300 @enderror px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                    >
                    @error('telepon')
                        <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-slate-400">
                        Kosongkan kolom dan simpan jika ingin menghapus nomor telepon.
                    </p>
                </div>

                {{-- Indikator Enkripsi Database --}}
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-1">
                    <div class="flex items-center gap-1.5 font-semibold text-slate-700">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Status Penyimpanan Database:</span>
                    </div>
                    @if ($user->telepon_enc)
                        <p class="text-emerald-700 font-medium">
                            Tersimpan dalam bentuk ciphertext envelope terenkripsi (v1, GCM tag, IV nonce unik).
                        </p>
                    @else
                        <p class="text-slate-500">
                            Belum ada nomor telepon tersimpan.
                        </p>
                    @endif
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition shadow-sm cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        <span>Simpan Nomor Telepon</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
