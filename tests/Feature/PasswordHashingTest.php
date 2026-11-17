<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Ulasan;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordHashingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * TST-19 / SYR-2: Seeder menghasilkan tepat 3 akun dengan role dan atribut yang tepat.
     */
    public function test_seeder_creates_exact_three_accounts_with_correct_roles(): void
    {
        $this->assertSame(3, Anggota::count());
        $this->assertSame(1, Anggota::where('role', 'admin')->count());
        $this->assertSame(2, Anggota::where('role', 'user')->count());

        $admin = Anggota::where('email', 'admin@perpustakaan.test')->first();
        $this->assertNotNull($admin);
        $this->assertSame('Admin Perpustakaan', $admin->nama);
        $this->assertSame('admin', $admin->role);
        $this->assertTrue($admin->is_active);

        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $this->assertNotNull($userA);
        $this->assertSame('User A', $userA->nama);
        $this->assertSame('user', $userA->role);
        $this->assertTrue($userA->is_active);

        $userB = Anggota::where('email', 'user_b@perpustakaan.test')->first();
        $this->assertNotNull($userB);
        $this->assertSame('User B', $userB->nama);
        $this->assertSame('user', $userB->role);
        $this->assertTrue($userB->is_active);
    }

    /**
     * TST-02 / SYR-2: Input password identik menghasilkan hash berbeda karena salt acak unik per hash.
     */
    public function test_user_a_and_user_b_have_different_hashes_for_identical_password(): void
    {
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->firstOrFail();
        $userB = Anggota::where('email', 'user_b@perpustakaan.test')->firstOrFail();

        $this->assertNotEmpty($userA->password);
        $this->assertNotEmpty($userB->password);
        $this->assertNotSame($userA->password, $userB->password);
    }

    /**
     * TST-02 / SYR-2: Hash::check memverifikasi password benar dan menolak password salah.
     */
    public function test_hash_check_verifies_correct_password_and_rejects_wrong_password(): void
    {
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->firstOrFail();
        $userB = Anggota::where('email', 'user_b@perpustakaan.test')->firstOrFail();

        // Password benar harus lolos
        $this->assertTrue(Hash::check(config('security.seed_user_password'), $userA->password));
        $this->assertTrue(Hash::check(config('security.seed_user_password'), $userB->password));

        // Password salah harus ditolak
        $this->assertFalse(Hash::check('WrongPassword999!', $userA->password));
        $this->assertFalse(Hash::check('WrongPassword999!', $userB->password));
        $this->assertFalse(Hash::check('p@ssword1234!', $userA->password)); // Case sensitivity check
    }

    /**
     * TST-02 / SYR-2: Password di-hash menggunakan algoritma Bcrypt dengan cost yang valid.
     */
    public function test_password_is_hashed_with_bcrypt_and_valid_cost(): void
    {
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->firstOrFail();
        $userB = Anggota::where('email', 'user_b@perpustakaan.test')->firstOrFail();
        $admin = Anggota::where('email', 'admin@perpustakaan.test')->firstOrFail();

        $infoA = Hash::info($userA->password);
        $infoB = Hash::info($userB->password);
        $infoAdmin = Hash::info($admin->password);

        // Algoritma Bcrypt
        $this->assertSame('bcrypt', $infoA['algoName']);
        $this->assertSame('bcrypt', $infoB['algoName']);
        $this->assertSame('bcrypt', $infoAdmin['algoName']);

        // Cost sesuai environment (BCRYPT_ROUNDS di testing env bernilai 4, produksi >= 10 / 12)
        $expectedCost = (int) env('BCRYPT_ROUNDS', 12);
        $this->assertSame($expectedCost, $infoA['options']['cost']);
        $this->assertSame($expectedCost, $infoB['options']['cost']);
        $this->assertGreaterThanOrEqual(4, $infoA['options']['cost']);
    }

    /**
     * TST-02 / SYR-2: Password disembunyikan dari serialisasi array dan JSON model.
     */
    public function test_password_is_hidden_from_serialization(): void
    {
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->firstOrFail();

        $array = $userA->toArray();
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);

        $json = $userA->toJson();
        $this->assertStringNotContainsString('password', $json);
        $this->assertStringNotContainsString(config('security.seed_user_password'), $json);
        $this->assertStringNotContainsString($userA->password, $json);
    }

    /**
     * TST-19: Seeder membuat 2 buku aktif, 1 buku arsip, dan transaksi peminjaman serta ulasan terpisah.
     */
    public function test_seeder_creates_active_books_archived_book_and_distinct_user_transactions(): void
    {
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->firstOrFail();
        $userB = Anggota::where('email', 'user_b@perpustakaan.test')->firstOrFail();

        // 2 Buku aktif
        $activeBooks = Buku::whereNull('archived_at')->get();
        $this->assertCount(2, $activeBooks);
        $this->assertTrue($activeBooks->contains('judul', 'Pemrograman Web Modern'));
        $this->assertTrue($activeBooks->contains('judul', 'Dasar Keamanan Sistem'));

        // 1 Buku arsip
        $archivedBooks = Buku::whereNotNull('archived_at')->get();
        $this->assertCount(1, $archivedBooks);
        $this->assertSame('Buku Sejarah Lama', $archivedBooks->first()->judul);
        $this->assertSame(0, $archivedBooks->first()->stok_total);

        // Peminjaman User A dan User B
        $loanA = Peminjaman::where('anggota_id', $userA->id)->first();
        $loanB = Peminjaman::where('anggota_id', $userB->id)->first();

        $this->assertNotNull($loanA);
        $this->assertNotNull($loanB);
        $this->assertNotSame($loanA->id, $loanB->id);
        $this->assertNotSame($loanA->buku_id, $loanB->buku_id);

        // Ulasan User A dan User B
        $reviewA = Ulasan::where('anggota_id', $userA->id)->first();
        $reviewB = Ulasan::where('anggota_id', $userB->id)->first();

        $this->assertNotNull($reviewA);
        $this->assertNotNull($reviewB);
        $this->assertNotSame($reviewA->id, $reviewB->id);
        $this->assertNotSame($reviewA->isi, $reviewB->isi);
    }

    /**
     * Seeder bersifat idempoten saat dijalankan berulang kali.
     */
    public function test_seeder_is_idempotent(): void
    {
        // Jalankan seeder kedua kalinya
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(3, Anggota::count());
        $this->assertSame(3, Buku::count());
        $this->assertSame(2, Peminjaman::count());
        $this->assertSame(2, Ulasan::count());
    }

    public function test_reseeding_preserves_existing_accounts_and_transactions(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->firstOrFail();
        $loan = Peminjaman::where('anggota_id', $user->id)->firstOrFail();
        $book = $loan->buku;
        $hash = $user->password;
        $user->update(['nama' => 'Nama yang diubah']);
        $book->update(['stok_tersedia' => 1]);
        $loan->update(['status' => 'kembali', 'dikembalikan_pada' => now()]);

        $this->seed(DatabaseSeeder::class);

        $this->assertSame('Nama yang diubah', $user->fresh()->nama);
        $this->assertSame($hash, $user->fresh()->password);
        $this->assertSame(1, $book->fresh()->stok_tersedia);
        $this->assertSame('kembali', $loan->fresh()->status);
        $this->assertSame(2, Peminjaman::count());
    }

    public function test_seeder_refuses_production_before_modifying_data(): void
    {
        $count = Anggota::count();
        $environment = app()->environment();
        app()->detectEnvironment(fn () => 'production');
        try {
            (new DatabaseSeeder)->run();
            $this->fail('Seeder harus menolak production.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('production', $exception->getMessage());
            $this->assertSame($count, Anggota::count());
        } finally {
            app()->detectEnvironment(fn () => $environment);
        }
    }

    public function test_seeder_rejects_missing_password_without_modifying_data(): void
    {
        $count = Anggota::count();
        config(['security.seed_user_password' => null]);
        try {
            $this->seed(DatabaseSeeder::class);
            $this->fail('Seeder harus menolak password yang kosong.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Password fixture', $exception->getMessage());
            $this->assertSame($count, Anggota::count());
        }
    }
}
