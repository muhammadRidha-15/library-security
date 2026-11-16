<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard Ringkasan Admin (Canvas Node 09).
     */
    public function index(): View
    {
        $totalBuku = Buku::count();
        $bukuAktif = Buku::whereNull('archived_at')->count();
        $bukuArsip = Buku::whereNotNull('archived_at')->count();

        $pinjamanAktif = Peminjaman::where('status', 'dipinjam')->count();
        $pinjamanSelesai = Peminjaman::where('status', 'kembali')->count();

        $totalAnggota = Anggota::where('role', 'user')->count();
        $anggotaAktif = Anggota::where('role', 'user')->where('is_active', true)->count();

        $peminjamanTerbaru = Peminjaman::with(['buku', 'anggota'])
            ->orderByDesc('dipinjam_pada')
            ->limit(5)
            ->get();

        return view('admin.ringkasan', compact(
            'totalBuku',
            'bukuAktif',
            'bukuArsip',
            'pinjamanAktif',
            'pinjamanSelesai',
            'totalAnggota',
            'anggotaAktif',
            'peminjamanTerbaru'
        ));
    }
}
