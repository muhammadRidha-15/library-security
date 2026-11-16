<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Ulasan;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji keberadaan tabel domain dan ketiadaan tabel users default.
     */
    public function test_database_schema_has_expected_domain_tables(): void
    {
        $this->assertTrue(Schema::hasTable('anggota'));
        $this->assertTrue(Schema::hasTable('buku'));
        $this->assertTrue(Schema::hasTable('peminjaman'));
        $this->assertTrue(Schema::hasTable('ulasan'));
        $this->assertTrue(Schema::hasTable('sessions'));

        // Tidak boleh ada tabel users kedua yang menduplikasi identitas anggota
        $this->assertFalse(Schema::hasTable('users'));
    }

    /**
     * Uji kolom-kolom tabel anggota sesuai spesifikasi kamus data.
     */
    public function test_anggota_table_has_correct_columns(): void
    {
        $expectedColumns = [
            'id',
            'nama',
            'email',
            'password',
            'role',
            'is_active',
            'telepon_enc',
            'created_at',
            'updated_at',
        ];

        $this->assertTrue(Schema::hasColumns('anggota', $expectedColumns));

        // Memastikan tidak ada kolom plaintext atau salt terpisah
        $this->assertFalse(Schema::hasColumn('anggota', 'telepon'));
        $this->assertFalse(Schema::hasColumn('anggota', 'salt'));
        $this->assertFalse(Schema::hasColumn('anggota', 'remember_token'));
    }

    /**
     * Uji kolom-kolom tabel buku, peminjaman, dan ulasan.
     */
    public function test_buku_peminjaman_ulasan_tables_have_correct_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('buku', [
            'id',
            'judul',
            'penulis',
            'deskripsi',
            'stok_total',
            'stok_tersedia',
            'archived_at',
            'created_at',
            'updated_at',
        ]));

        $this->assertTrue(Schema::hasColumns('peminjaman', [
            'id',
            'anggota_id',
            'buku_id',
            'dipinjam_pada',
            'jatuh_tempo',
            'dikembalikan_pada',
            'status',
            'created_at',
            'updated_at',
        ]));

        $this->assertTrue(Schema::hasColumns('ulasan', [
            'id',
            'anggota_id',
            'buku_id',
            'isi',
            'created_at',
            'updated_at',
        ]));
    }

    /**
     * Uji konfigurasi autentikasi diarahkan ke model Anggota.
     */
    public function test_auth_provider_is_configured_to_use_anggota_model(): void
    {
        $this->assertSame(Anggota::class, config('auth.providers.users.model'));
        $this->assertSame('anggota', config('auth.providers.users.table'));
        $this->assertSame(Anggota::class, Auth::getProvider()->getModel());
    }

    /**
     * Uji foreign key constraints aktif pada koneksi SQLite.
     */
    public function test_foreign_key_constraints_are_enabled_in_sqlite(): void
    {
        $result = DB::select('PRAGMA foreign_keys');
        $fkStatus = $result[0]->foreign_keys ?? array_values((array) $result[0])[0];

        $this->assertEquals(1, (int) $fkStatus);
    }

    /**
     * Uji penolakan foreign key pada relasi peminjaman yatim.
     */
    public function test_foreign_key_rejects_orphan_peminjaman(): void
    {
        $buku = Buku::create([
            'judul' => 'Pemrograman Web',
            'penulis' => 'John Doe',
            'stok_total' => 5,
            'stok_tersedia' => 5,
        ]);

        $this->expectException(QueryException::class);

        Peminjaman::create([
            'anggota_id' => 999999, // Anggota tidak ada
            'buku_id' => $buku->id,
            'dipinjam_pada' => now(),
            'status' => 'dipinjam',
        ]);
    }

    /**
     * Uji penolakan foreign key pada ulasan yatim.
     */
    public function test_foreign_key_rejects_orphan_ulasan(): void
    {
        $anggota = Anggota::create([
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $this->expectException(QueryException::class);

        Ulasan::create([
            'anggota_id' => $anggota->id,
            'buku_id' => 999999, // Buku tidak ada
            'isi' => 'Buku sangat informatif.',
        ]);
    }

    /**
     * Uji penolakan penghapusan induk (ON DELETE RESTRICT) saat masih ada peminjaman.
     */
    public function test_foreign_key_restrict_prevents_deleting_referenced_anggota_or_buku(): void
    {
        $anggota = Anggota::create([
            'nama' => 'Siti Rahma',
            'email' => 'siti@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $buku = Buku::create([
            'judul' => 'Keamanan Sistem Komputer',
            'penulis' => 'Jane Smith',
            'stok_total' => 3,
            'stok_tersedia' => 2,
        ]);

        Peminjaman::create([
            'anggota_id' => $anggota->id,
            'buku_id' => $buku->id,
            'dipinjam_pada' => now(),
            'status' => 'dipinjam',
        ]);

        // Coba hapus anggota yang memiliki peminjaman aktif -> wajib ditolak RESTRICT
        $this->expectException(QueryException::class);
        $anggota->delete();
    }

    /**
     * Uji penolakan duplikasi email pada tabel anggota (UNIQUE constraint).
     */
    public function test_unique_email_constraint_on_anggota(): void
    {
        Anggota::create([
            'nama' => 'User Pertama',
            'email' => 'duplikat@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'user',
        ]);

        $this->expectException(QueryException::class);

        Anggota::create([
            'nama' => 'User Kedua',
            'email' => 'duplikat@example.com',
            'password' => Hash::make('secret456'),
            'role' => 'user',
        ]);
    }

    /**
     * Uji check constraint role pada SQLite.
     */
    public function test_role_check_constraint_rejects_invalid_role(): void
    {
        $this->expectException(QueryException::class);

        DB::table('anggota')->insert([
            'nama' => 'Invalid Role User',
            'email' => 'invalidrole@example.com',
            'password' => Hash::make('password123'),
            'role' => 'moderator', // Bukan admin atau user
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Uji check constraint status peminjaman pada SQLite.
     */
    public function test_status_check_constraint_rejects_invalid_peminjaman_status(): void
    {
        $anggota = Anggota::create([
            'nama' => 'Test User',
            'email' => 'statuscheck@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $buku = Buku::create([
            'judul' => 'Basis Data Lanjut',
            'penulis' => 'Penulis DB',
            'stok_total' => 5,
            'stok_tersedia' => 5,
        ]);

        $this->expectException(QueryException::class);

        DB::table('peminjaman')->insert([
            'anggota_id' => $anggota->id,
            'buku_id' => $buku->id,
            'dipinjam_pada' => now(),
            'status' => 'hilang', // Bukan dipinjam atau kembali
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Uji relasi Eloquent dua arah antar entitas.
     */
    public function test_eloquent_relationships_between_entities(): void
    {
        $anggota = Anggota::create([
            'nama' => 'Muhammad Ridha',
            'email' => 'ridha@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $buku = Buku::create([
            'judul' => 'Struktur Data & Algoritma',
            'penulis' => 'Clifford A.',
            'deskripsi' => 'Panduan algoritma.',
            'stok_total' => 10,
            'stok_tersedia' => 9,
        ]);

        $peminjaman = Peminjaman::create([
            'anggota_id' => $anggota->id,
            'buku_id' => $buku->id,
            'dipinjam_pada' => now(),
            'jatuh_tempo' => now()->addDays(7),
            'status' => 'dipinjam',
        ]);

        $ulasan = Ulasan::create([
            'anggota_id' => $anggota->id,
            'buku_id' => $buku->id,
            'isi' => 'Buku sangat komprehensif.',
        ]);

        // Relasi Anggota
        $this->assertCount(1, $anggota->peminjaman);
        $this->assertTrue($anggota->peminjaman->first()->is($peminjaman));
        $this->assertCount(1, $anggota->ulasan);
        $this->assertTrue($anggota->ulasan->first()->is($ulasan));

        // Relasi Buku
        $this->assertCount(1, $buku->peminjaman);
        $this->assertTrue($buku->peminjaman->first()->is($peminjaman));
        $this->assertCount(1, $buku->ulasan);
        $this->assertTrue($buku->ulasan->first()->is($ulasan));

        // Relasi Peminjaman
        $this->assertTrue($peminjaman->anggota->is($anggota));
        $this->assertTrue($peminjaman->buku->is($buku));

        // Relasi Ulasan
        $this->assertTrue($ulasan->anggota->is($anggota));
        $this->assertTrue($ulasan->buku->is($buku));
    }

    /**
     * Uji password tersembunyi dari serialisasi JSON dan array.
     */
    public function test_password_is_hidden_from_serialization(): void
    {
        $anggota = Anggota::create([
            'nama' => 'Secret User',
            'email' => 'secret@example.com',
            'password' => 'RahasiaSuper123',
            'role' => 'user',
        ]);

        $array = $anggota->toArray();
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);

        $json = $anggota->toJson();
        $this->assertStringNotContainsString('password', $json);
        $this->assertStringNotContainsString('RahasiaSuper123', $json);
    }

    /**
     * Uji attribute casting pada model.
     */
    public function test_attribute_casting_works_as_expected(): void
    {
        $anggota = Anggota::create([
            'nama' => 'Cast User',
            'email' => 'cast@example.com',
            'password' => 'plainpassword',
            'is_active' => 1,
        ]);

        $this->assertIsBool($anggota->is_active);
        $this->assertTrue($anggota->is_active);
        $this->assertTrue(Hash::check('plainpassword', $anggota->password));

        $buku = Buku::create([
            'judul' => 'Casting Test Book',
            'penulis' => 'Tester',
            'stok_total' => '15',
            'stok_tersedia' => '12',
        ]);

        $this->assertIsInt($buku->stok_total);
        $this->assertSame(15, $buku->stok_total);
        $this->assertIsInt($buku->stok_tersedia);
        $this->assertSame(12, $buku->stok_tersedia);

        $peminjaman = Peminjaman::create([
            'anggota_id' => $anggota->id,
            'buku_id' => $buku->id,
            'dipinjam_pada' => '2026-11-16 10:00:00',
            'jatuh_tempo' => '2026-11-23',
            'status' => 'dipinjam',
        ]);

        $this->assertInstanceOf(\DateTimeInterface::class, $peminjaman->dipinjam_pada);
        $this->assertInstanceOf(\DateTimeInterface::class, $peminjaman->jatuh_tempo);
    }
}
