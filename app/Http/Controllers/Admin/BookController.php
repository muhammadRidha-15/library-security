<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBookRequest;
use App\Http\Requests\Admin\UpdateBookRequest;
use App\Models\Buku;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * Display a listing of books in admin panel (Canvas Node 10).
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Buku::class);

        $status = $request->query('status', 'aktif'); // 'aktif' | 'arsip' | 'semua'
        $search = $request->query('q');

        $query = Buku::query()->withCount([
            'peminjaman as pinjaman_aktif_count' => function ($q) {
                $q->where('status', 'dipinjam');
            },
        ]);

        if ($status === 'aktif') {
            $query->whereNull('archived_at');
        } elseif ($status === 'arsip') {
            $query->whereNotNull('archived_at');
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('penulis', 'like', "%{$search}%");
            });
        }

        $buku = $query->orderBy('judul')->paginate(15)->withQueryString();

        $totalAktif = Buku::whereNull('archived_at')->count();
        $totalArsip = Buku::whereNotNull('archived_at')->count();

        return view('admin.buku.index', compact('buku', 'status', 'search', 'totalAktif', 'totalArsip'));
    }

    /**
     * Show the form for creating a new book (Canvas Node 11).
     */
    public function create(): View
    {
        Gate::authorize('create', Buku::class);

        return view('admin.buku.create');
    }

    /**
     * Store a newly created book in storage.
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        Gate::authorize('create', Buku::class);

        $validated = $request->validated();

        $buku = Buku::create([
            'judul' => $validated['judul'],
            'penulis' => $validated['penulis'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'stok_total' => (int) $validated['stok_total'],
            'stok_tersedia' => (int) $validated['stok_total'],
            'archived_at' => null,
        ]);

        return redirect()
            ->route('admin.buku.index')
            ->with('success', "Buku \"{$buku->judul}\" berhasil ditambahkan ke katalog.");
    }

    /**
     * Display the specified book in admin area.
     */
    public function show(Buku $buku): RedirectResponse
    {
        Gate::authorize('viewAdmin', $buku);

        return redirect()->route('admin.buku.edit', $buku);
    }

    /**
     * Show the form for editing the specified book (Canvas Node 12).
     */
    public function edit(Buku $buku): View
    {
        Gate::authorize('update', $buku);

        $borrowedCount = $buku->peminjaman()->where('status', 'dipinjam')->count();
        $hasHistory = $buku->peminjaman()->exists();

        return view('admin.buku.edit', compact('buku', 'borrowedCount', 'hasHistory'));
    }

    /**
     * Update the specified book in storage.
     */
    public function update(UpdateBookRequest $request, Buku $buku): RedirectResponse
    {
        Gate::authorize('update', $buku);

        $validated = $request->validated();
        $newTotal = (int) $validated['stok_total'];

        $borrowedCount = $buku->peminjaman()->where('status', 'dipinjam')->count();
        $newAvailable = $newTotal - $borrowedCount;

        $buku->update([
            'judul' => $validated['judul'],
            'penulis' => $validated['penulis'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'stok_total' => $newTotal,
            'stok_tersedia' => $newAvailable,
        ]);

        return redirect()
            ->route('admin.buku.edit', $buku)
            ->with('success', "Informasi buku \"{$buku->judul}\" berhasil diperbarui.");
    }

    /**
     * Archive the specified book.
     */
    public function archive(Buku $buku): RedirectResponse
    {
        Gate::authorize('archive', $buku);

        $buku->update(['archived_at' => now()]);

        return redirect()
            ->route('admin.buku.index')
            ->with('success', "Buku \"{$buku->judul}\" berhasil dipindahkan ke arsip.");
    }

    /**
     * Restore the specified archived book.
     */
    public function restore(Buku $buku): RedirectResponse
    {
        Gate::authorize('archive', $buku);

        $buku->update(['archived_at' => null]);

        return redirect()
            ->route('admin.buku.index', ['status' => 'aktif'])
            ->with('success', "Buku \"{$buku->judul}\" berhasil dipulihkan dari arsip.");
    }

    /**
     * Remove or archive the book based on borrowing history (Q-09).
     */
    public function destroy(Buku $buku): RedirectResponse
    {
        Gate::authorize('delete', $buku);

        if ($buku->peminjaman()->exists()) {
            // Memiliki riwayat peminjaman: arsipkan untuk menjaga integritas data historis
            $buku->update(['archived_at' => now()]);

            return redirect()
                ->route('admin.buku.index')
                ->with('success', "Buku \"{$buku->judul}\" memiliki riwayat peminjaman sehingga dialihkan ke arsip.");
        }

        // Tidak ada riwayat: hapus fisik permanen
        $judul = $buku->judul;
        $buku->delete();

        return redirect()
            ->route('admin.buku.index')
            ->with('success', "Buku \"{$judul}\" berhasil dihapus permanen.");
    }
}
