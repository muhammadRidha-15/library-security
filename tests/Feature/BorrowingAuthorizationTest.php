<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowingAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_user_can_view_own_borrowing_history(): void
    {
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->first();

        $response = $this->actingAs($userA)->get(route('peminjaman.index'));

        $response->assertOk();
        $response->assertSee('Riwayat Peminjaman');
        // Seeder: userA meminjam 'Pemrograman Web Modern'
        $response->assertSee('Pemrograman Web Modern');
    }

    public function test_user_history_list_only_shows_own_records_and_ignores_anggota_id_query_parameter(): void
    {
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $userB = Anggota::where('email', 'user_b@perpustakaan.test')->first();

        // userA mencoba manipulasi query param ?anggota_id={id_userB}
        $response = $this->actingAs($userA)->get(route('peminjaman.index', ['anggota_id' => $userB->id]));

        $response->assertOk();
        // Hanya buku milik userA ('Pemrograman Web Modern') yang tampil
        $response->assertSee('Pemrograman Web Modern');
        // Buku milik userB ('Dasar Keamanan Sistem') TIDAK boleh tampil
        $response->assertDontSee('Dasar Keamanan Sistem');
    }

    public function test_user_with_no_borrowings_sees_empty_state_screen(): void
    {
        $newUser = Anggota::create([
            'nama' => 'User Baru',
            'email' => 'new_user@perpustakaan.test',
            'password' => 'secret123456',
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->actingAs($newUser)->get(route('peminjaman.index'));

        $response->assertOk();
        $response->assertSee('Belum Ada Riwayat Peminjaman');
        $response->assertSee('Lihat Koleksi Buku');
    }

    public function test_user_can_view_own_borrowing_detail(): void
    {
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $loanA = Peminjaman::where('anggota_id', $userA->id)->first();

        $response = $this->actingAs($userA)->get(route('peminjaman.show', $loanA));

        $response->assertOk();
        $response->assertSee('Detail Transaksi Peminjaman');
        $response->assertSee($loanA->buku->judul);
        $response->assertSee($userA->nama);
    }

    public function test_user_cannot_view_other_users_borrowing_detail_and_receives_404(): void
    {
        // SEC-10 & T3 IDOR Target: Akses silang ditolak dengan 404 Not Found (denyAsNotFound)
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $userB = Anggota::where('email', 'user_b@perpustakaan.test')->first();
        $loanB = Peminjaman::where('anggota_id', $userB->id)->first();

        // User A mencoba membaca data transaksi peminjaman milik User B
        $response = $this->actingAs($userA)->get(route('peminjaman.show', $loanB));

        // Wajib 404, BUKAN 403 atau 200, agar tidak membocorkan eksistensi data transaksi
        $response->assertStatus(404);
        $response->assertDontSee($userB->nama);
    }

    public function test_nonexistent_borrowing_id_returns_404(): void
    {
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->first();

        $response = $this->actingAs($userA)->get(url('/peminjaman/999999'));

        $response->assertStatus(404);
    }

    public function test_guest_cannot_view_borrowing_history_or_detail(): void
    {
        $loan = Peminjaman::first();

        $responseIndex = $this->get(route('peminjaman.index'));
        $responseIndex->assertRedirect(route('login'));

        $responseShow = $this->get(route('peminjaman.show', $loan));
        $responseShow->assertRedirect(route('login'));
    }

    public function test_admin_can_view_any_borrowing_detail_via_policy(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $loan = Peminjaman::first();

        $response = $this->actingAs($admin)->get(route('peminjaman.show', $loan));

        $response->assertOk();
        $response->assertSee('Detail Transaksi Peminjaman');
    }
}
