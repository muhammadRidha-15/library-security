<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\Ulasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * Display a listing of user reviews for moderation (Canvas Node 19).
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Ulasan::class);

        $search = $request->query('q');
        $bukuId = $request->query('buku_id');

        $query = Ulasan::with(['anggota', 'buku']);

        if (! empty($bukuId)) {
            $query->where('buku_id', $bukuId);
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('isi', 'like', "%{$search}%")
                    ->orWhereHas('anggota', function ($aq) use ($search) {
                        $aq->where('nama', 'like', "%{$search}%");
                    })
                    ->orWhereHas('buku', function ($bq) use ($search) {
                        $bq->where('judul', 'like', "%{$search}%");
                    });
            });
        }

        $ulasan = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $totalUlasan = Ulasan::count();
        $bukuList = Buku::orderBy('judul')->get();

        return view('admin.ulasan.index', compact('ulasan', 'search', 'bukuId', 'totalUlasan', 'bukuList'));
    }

    /**
     * Remove the specified review from storage (FR-15).
     */
    public function destroy(Ulasan $ulasan): RedirectResponse
    {
        Gate::authorize('delete', $ulasan);

        $ulasan->delete();

        return redirect()
            ->route('admin.ulasan.index')
            ->with('success', 'Ulasan anggota berhasil dihapus.');
    }
}
