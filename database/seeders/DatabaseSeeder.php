<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Ulasan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new \RuntimeException('DatabaseSeeder tidak boleh dijalankan di production.');
        }

        $userPassword = config('security.seed_user_password');
        $adminPassword = config('security.seed_admin_password');
        foreach ([$userPassword, $adminPassword] as $password) {
            if (! is_string($password) || strlen($password) < 8 || strlen($password) > 72) {
                throw new \RuntimeException('Password fixture harus disediakan dan berukuran 8-72 byte.');
            }
        }

        DB::transaction(function () use ($userPassword, $adminPassword) {
            // 1. Tepat 1 Admin
            Anggota::firstOrCreate(['email' => 'admin@perpustakaan.test'], [
                'nama' => 'Admin Perpustakaan',
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
                'is_active' => true,
                'telepon_enc' => null,
            ]);

            // 2. Tepat 2 User dengan password identik tetapi menghasilkan hash berbeda (random salt)
            $userA = Anggota::firstOrCreate(['email' => 'user_a@perpustakaan.test'], [
                'nama' => 'User A',
                'password' => Hash::make($userPassword),
                'role' => 'user',
                'is_active' => true,
                'telepon_enc' => null,
            ]);

            $userB = Anggota::firstOrCreate(['email' => 'user_b@perpustakaan.test'], [
                'nama' => 'User B',
                'password' => Hash::make($userPassword),
                'role' => 'user',
                'is_active' => true,
                'telepon_enc' => null,
            ]);

            // 3. 2 Buku aktif berbeda dan 1 buku arsip
            $buku1 = Buku::firstOrCreate(['judul' => 'Pemrograman Web Modern'], [
                'penulis' => 'John Doe',
                'deskripsi' => 'Panduan komprehensif pengembangan aplikasi web berbasis arsitektur modern.',
                'foto' => 'images/books/pemrograman-web.svg',
                'stok_total' => 5,
                'stok_tersedia' => 5,
                'archived_at' => null,
            ]);

            $buku2 = Buku::firstOrCreate(['judul' => 'Dasar Keamanan Sistem'], [
                'penulis' => 'Jane Smith',
                'deskripsi' => 'Prinsip pertahanan berlapis, kriptografi terapan, dan mitigasi risiko perangkat lunak.',
                'foto' => 'images/books/keamanan-sistem.svg',
                'stok_total' => 3,
                'stok_tersedia' => 3,
                'archived_at' => null,
            ]);

            Buku::firstOrCreate(['judul' => 'Buku Sejarah Lama'], [
                'penulis' => 'Arsiparis',
                'deskripsi' => 'Koleksi naskah sejarah khusus yang telah dialihkan ke status arsip.',
                'foto' => null, // Menguji fallback ke default cover
                'stok_total' => 0,
                'stok_tersedia' => 0,
                'archived_at' => now(),
            ]);

            // 4. Fixture kepemilikan transaksi peminjaman & ulasan untuk User A dan User B
            if ($userA->wasRecentlyCreated && $buku1->wasRecentlyCreated) {
                $buku1->decrement('stok_tersedia');
                Peminjaman::create([
                    'anggota_id' => $userA->id,
                    'buku_id' => $buku1->id,
                    'dipinjam_pada' => now()->subDays(2),
                    'jatuh_tempo' => now()->addDays(5),
                    'dikembalikan_pada' => null,
                    'status' => 'dipinjam',
                ]);

            }
            if ($userB->wasRecentlyCreated && $buku2->wasRecentlyCreated) {
                $buku2->decrement('stok_tersedia');
                Peminjaman::create([
                    'anggota_id' => $userB->id,
                    'buku_id' => $buku2->id,
                    'dipinjam_pada' => now()->subDays(1),
                    'jatuh_tempo' => now()->addDays(6),
                    'dikembalikan_pada' => null,
                    'status' => 'dipinjam',
                ]);

            }
            if ($userA->wasRecentlyCreated && $buku1->wasRecentlyCreated) {
                Ulasan::create([
                    'anggota_id' => $userA->id,
                    'buku_id' => $buku1->id,
                    'isi' => 'Buku sangat aplikatif dan memberikan pemahaman mendalam tentang pengembangan web.',
                ]);

            }
            if ($userB->wasRecentlyCreated && $buku2->wasRecentlyCreated) {
                Ulasan::create([
                    'anggota_id' => $userB->id,
                    'buku_id' => $buku2->id,
                    'isi' => 'Penjelasan konsep keamanan sistem sangat terstruktur dan mudah diikuti.',
                ]);
            }
        });
    }
}
