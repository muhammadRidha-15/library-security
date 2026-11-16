<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * Display a listing of active books.
     */
    public function index(Request $request): View
    {
        $buku = Buku::query()
            ->whereNull('archived_at')
            ->orderBy('judul')
            ->get();

        return view('buku.index', compact('buku'));
    }

    /**
     * Search active books by title or author using bound parameters.
     */
    public function search(Request $request): View|RedirectResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = trim($request->input('q', ''));

        if ($query === '') {
            return redirect()->route('buku.index');
        }

        $buku = Buku::query()
            ->whereNull('archived_at')
            ->where(function ($qb) use ($query) {
                $qb->where('judul', 'like', "%{$query}%")
                   ->orWhere('penulis', 'like', "%{$query}%");
            })
            ->orderBy('judul')
            ->get();

        return view('buku.index', [
            'buku' => $buku,
            'searchQuery' => $query,
        ]);
    }

    /**
     * Display the specified active book and its reviews.
     */
    public function show(Buku $buku): View
    {
        if ($buku->archived_at !== null) {
            abort(404);
        }

        $buku->load(['ulasan.anggota']);

        return view('buku.show', compact('buku'));
    }
}
