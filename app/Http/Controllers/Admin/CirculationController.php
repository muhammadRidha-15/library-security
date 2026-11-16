<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CirculationController extends Controller
{
    /**
     * Display a listing of circulation loans (Canvas Node 16).
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAnyAdmin', Peminjaman::class);

        $status = $request->query('status', 'semua'); // 'dipinjam' | 'kembali' | 'semua'
        $search = $request->query('q');
        $anggotaId = $request->query('anggota_id');

        $query = Peminjaman::with(['buku', 'anggota']);

        if ($status === 'dipinjam') {
            $query->where('status', 'dipinjam');
        } elseif ($status === 'kembali') {
            $query->where('status', 'kembali');
        }

        if (! empty($anggotaId)) {
            $query->where('anggota_id', $anggotaId);
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('buku', function ($bq) use ($search) {
                    $bq->where('judul', 'like', "%{$search}%");
                })->orWhereHas('anggota', function ($aq) use ($search) {
                    $aq->where('nama', 'like', "%{$search}%");
                });
            });
        }

        $peminjaman = $query->orderByDesc('dipinjam_pada')->paginate(15)->withQueryString();

        $pinjamanAktif = Peminjaman::where('status', 'dipinjam')->count();
        $pinjamanKembali = Peminjaman::where('status', 'kembali')->count();
        $totalPinjaman = Peminjaman::count();

        $anggotaList = Anggota::where('role', 'user')->orderBy('nama')->get();

        return view('admin.peminjaman.index', compact(
            'peminjaman',
            'status',
            'search',
            'anggotaId',
            'pinjamanAktif',
            'pinjamanKembali',
            'totalPinjaman',
            'anggotaList'
        ));
    }

    /**
     * Show the form for creating a new loan manually by admin (Canvas Node 17).
     */
    public function create(): View
    {
        Gate::authorize('createAdmin', Peminjaman::class);

        $anggotaList = Anggota::where('role', 'user')
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        $bukuList = Buku::whereNull('archived_at')
            ->where('stok_tersedia', '>', 0)
            ->orderBy('judul')
            ->get();

        return view('admin.peminjaman.create', compact('anggotaList', 'bukuList'));
    }

    /**
     * Store a newly created loan in storage.
     */
    public function store(Request $request, PeminjamanService $service): RedirectResponse
    {
        Gate::authorize('createAdmin', Peminjaman::class);

        $validated = $request->validate([
            'anggota_id' => ['required', 'integer', 'exists:anggota,id'],
            'buku_id' => ['required', 'integer', 'exists:buku,id'],
        ], [
            'anggota_id.required' => 'Pilih anggota perpustakaan peminjam.',
            'buku_id.required' => 'Pilih buku yang hendak dipinjam.',
        ]);

        $targetMember = Anggota::findOrFail($validated['anggota_id']);
        if (! $targetMember->is_active) {
            return back()->withErrors([
                'anggota_id' => 'Anggota yang dipilih berstatus nonaktif dan tidak diizinkan meminjam buku.',
            ])->withInput();
        }

        $service->pinjam($targetMember, (int) $validated['buku_id']);

        return redirect()
            ->route('admin.peminjaman.index')
            ->with('success', 'Transaksi peminjaman berhasil dicatat.');
    }

    /**
     * Display the specified loan detail (Canvas Node 18).
     */
    public function show(Peminjaman $peminjaman): View
    {
        Gate::authorize('view', $peminjaman);

        $peminjaman->load(['buku', 'anggota']);

        return view('admin.peminjaman.show', compact('peminjaman'));
    }

    /**
     * Process return of the borrowed book (FR-14).
     */
    public function returnBook(Peminjaman $peminjaman, PeminjamanService $service): RedirectResponse
    {
        Gate::authorize('returnBook', $peminjaman);

        $service->kembalikan($peminjaman);

        return redirect()
            ->route('admin.peminjaman.show', $peminjaman)
            ->with('success', 'Buku berhasil dikembalikan dan stok eksemplar telah dipulihkan.');
    }
}
