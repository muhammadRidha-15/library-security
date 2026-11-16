<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUlasanRequest;
use App\Models\Buku;
use App\Models\Ulasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class UlasanController extends Controller
{
    /**
     * Store a newly created review in storage.
     */
    public function store(StoreUlasanRequest $request, Buku $buku): RedirectResponse
    {
        // Tolak jika buku diarsipkan (404)
        if ($buku->archived_at !== null) {
            abort(404, 'Buku tidak ditemukan.');
        }

        Gate::authorize('create', [Ulasan::class, $buku]);

        // SEC-10: anggota_id diambil secara ketat dari auth/sesi, bukan dari payload request
        Ulasan::create([
            'buku_id' => $buku->id,
            'anggota_id' => auth()->id(),
            'isi' => $request->validated('isi'),
        ]);

        return redirect()
            ->route('buku.show', $buku)
            ->with('success', 'Ulasan Anda berhasil dikirim.');
    }
}
