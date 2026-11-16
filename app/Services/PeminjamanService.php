<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PeminjamanService
{
    /**
     * Meminjam buku secara atomik dengan pengurangan stok yang aman dari race condition.
     *
     * @throws HttpException
     */
    public function pinjam(Anggota $user, int $bukuId): Peminjaman
    {
        return DB::transaction(function () use ($user, $bukuId) {
            // Periksa keberadaan buku
            $buku = Buku::find($bukuId);
            if (! $buku || $buku->archived_at !== null) {
                abort(404, 'Buku tidak ditemukan atau telah diarsipkan.');
            }

            // Atomic conditional update pada SQLite/DB
            // Mengurangi stok_tersedia hanya jika stok_tersedia > 0
            $affected = DB::table('buku')
                ->where('id', $bukuId)
                ->whereNull('archived_at')
                ->where('stok_tersedia', '>', 0)
                ->decrement('stok_tersedia', 1);

            if ($affected === 0) {
                abort(409, 'Stok buku ini sedang tidak tersedia.');
            }

            // Catat transaksi peminjaman terikat anggota dari sesi
            return Peminjaman::create([
                'anggota_id' => $user->id,
                'buku_id' => $bukuId,
                'dipinjam_pada' => now(),
                'jatuh_tempo' => now()->addDays(7),
                'dikembalikan_pada' => null,
                'status' => 'dipinjam',
            ]);
        });
    }
}
