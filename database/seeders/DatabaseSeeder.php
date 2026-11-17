<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Ulasan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Password konstan untuk akun user demonstrasi/pengujian lokal.
     */
    public const USER_PASSWORD = 'P@ssword1234!';
    public const ADMIN_PASSWORD = 'AdminPassword123!';

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Bersihkan data lama dengan urutan relasi foreign key untuk memastikan idempoten
        Ulasan::query()->delete();
        Peminjaman::query()->delete();
        Buku::query()->delete();
        Anggota::query()->delete();

        // 1. Tepat 1 Admin
        Anggota::create([
            'nama' => 'Admin Perpustakaan',
            'email' => 'admin@perpustakaan.test',
            'password' => Hash::make(self::ADMIN_PASSWORD),
            'role' => 'admin',
            'is_active' => true,
            'telepon_enc' => null,
        ]);

        // 2. Tepat 2 User dengan password identik tetapi menghasilkan hash berbeda (random salt)
        $userA = Anggota::create([
            'nama' => 'User A',
            'email' => 'user_a@perpustakaan.test',
            'password' => Hash::make(self::USER_PASSWORD),
            'role' => 'user',
            'is_active' => true,
            'telepon_enc' => null,
        ]);

        $userB = Anggota::create([
            'nama' => 'User B',
            'email' => 'user_b@perpustakaan.test',
            'password' => Hash::make(self::USER_PASSWORD),
            'role' => 'user',
            'is_active' => true,
            'telepon_enc' => null,
        ]);

        // 3. 2 Buku aktif berbeda dan 1 buku arsip
        $buku1 = Buku::create([
            'judul' => 'Pemrograman Web Modern',
            'penulis' => 'John Doe',
            'deskripsi' => 'Panduan komprehensif pengembangan aplikasi web berbasis arsitektur modern.',
            'foto' => 'images/books/pemrograman-web.svg',
            'stok_total' => 5,
            'stok_tersedia' => 5,
            'archived_at' => null,
        ]);

        $buku2 = Buku::create([
            'judul' => 'Dasar Keamanan Sistem',
            'penulis' => 'Jane Smith',
            'deskripsi' => 'Prinsip pertahanan berlapis, kriptografi terapan, dan mitigasi risiko perangkat lunak.',
            'foto' => 'images/books/keamanan-sistem.svg',
            'stok_total' => 3,
            'stok_tersedia' => 3,
            'archived_at' => null,
        ]);

        $bukuArsip = Buku::create([
            'judul' => 'Buku Sejarah Lama',
            'penulis' => 'Arsiparis',
            'deskripsi' => 'Koleksi naskah sejarah khusus yang telah dialihkan ke status arsip.',
            'foto' => null, // Menguji fallback ke default cover
            'stok_total' => 0,
            'stok_tersedia' => 0,
            'archived_at' => now(),
        ]);

        // 4. Fixture kepemilikan transaksi peminjaman & ulasan untuk User A dan User B
        Peminjaman::create([
            'anggota_id' => $userA->id,
            'buku_id' => $buku1->id,
            'dipinjam_pada' => now()->subDays(2),
            'jatuh_tempo' => now()->addDays(5),
            'dikembalikan_pada' => null,
            'status' => 'dipinjam',
        ]);

        Peminjaman::create([
            'anggota_id' => $userB->id,
            'buku_id' => $buku2->id,
            'dipinjam_pada' => now()->subDays(1),
            'jatuh_tempo' => now()->addDays(6),
            'dikembalikan_pada' => null,
            'status' => 'dipinjam',
        ]);

        Ulasan::create([
            'anggota_id' => $userA->id,
            'buku_id' => $buku1->id,
            'isi' => 'Buku sangat aplikatif dan memberikan pemahaman mendalam tentang pengembangan web.',
        ]);

        Ulasan::create([
            'anggota_id' => $userB->id,
            'buku_id' => $buku2->id,
            'isi' => 'Penjelasan konsep keamanan sistem sangat terstruktur dan mudah diikuti.',
        ]);
    }
}
