<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    /**
     * Tampilan konfirmasi peminjaman buku (Canvas Node 05).
     */
    public function confirm(Buku $buku): View
    {
        if ($buku->archived_at !== null) {
            abort(404, 'Buku tidak ditemukan.');
        }

        Gate::authorize('create', Peminjaman::class);

        return view('peminjaman.confirm', compact('buku'));
    }

    /**
     * Simpan transaksi peminjaman buku (FR-07).
     */
    public function store(Request $request, PeminjamanService $service): RedirectResponse
    {
        Gate::authorize('create', Peminjaman::class);

        $validated = $request->validate([
            'buku_id' => ['required', 'integer', 'exists:buku,id'],
        ]);

        // SEC-10: Identitas anggota diambil dari sesi user terautentikasi
        $peminjaman = $service->pinjam(auth()->user(), (int) $validated['buku_id']);

        return redirect()
            ->route('peminjaman.index')
            ->with('success', 'Buku berhasil dipinjam.');
    }

    /**
     * Riwayat peminjaman milik pengguna aktif (FR-08 - TKT-12).
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Peminjaman::class);

        // Hanya tampilkan riwayat milik anggota login
        $peminjaman = Peminjaman::with('buku')
            ->where('anggota_id', auth()->id())
            ->orderByDesc('dipinjam_pada')
            ->get();

        return view('peminjaman.index', compact('peminjaman'));
    }

    /**
     * Detail transaksi peminjaman pengguna aktif (FR-09 - TKT-12).
     */
    public function show(Peminjaman $peminjaman): View
    {
        // VULNERABLE BRANCH: IDOR vulnerability - authorization policy bypassed (TKT-25 / SEC-10)
        // Main branch enforces: Gate::authorize('view', $peminjaman); -> aborts with 404 denyAsNotFound
        // Gate::authorize('view', $peminjaman);

        $peminjaman->load(['buku', 'anggota']);

        return view('peminjaman.show', compact('peminjaman'));
    }
}
