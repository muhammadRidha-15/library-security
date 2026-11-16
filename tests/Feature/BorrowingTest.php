<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class BorrowingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_active_user_can_view_borrowing_confirmation_screen(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $book = Buku::whereNull('archived_at')->where('stok_tersedia', '>', 0)->first();

        $response = $this->actingAs($user)->get(route('buku.pinjam.confirm', $book));

        $response->assertOk();
        $response->assertSee('Konfirmasi Peminjaman');
        $response->assertSee($book->judul);
    }

    public function test_active_user_can_borrow_available_book_and_reduces_stock(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $book = Buku::whereNull('archived_at')->where('stok_tersedia', '>', 0)->first();
        $initialStock = $book->stok_tersedia;

        $response = $this->actingAs($user)->post(route('peminjaman.store'), [
            'buku_id' => $book->id,
        ]);

        $response->assertRedirect(route('peminjaman.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('peminjaman', [
            'anggota_id' => $user->id,
            'buku_id' => $book->id,
            'status' => 'dipinjam',
        ]);

        $book->refresh();
        $this->assertSame($initialStock - 1, $book->stok_tersedia);
    }

    public function test_borrowing_fails_with_409_when_stock_is_zero(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $book = Buku::whereNull('archived_at')->first();
        $book->update(['stok_tersedia' => 0]);

        $response = $this->actingAs($user)->post(route('peminjaman.store'), [
            'buku_id' => $book->id,
        ]);

        $response->assertStatus(409);
    }

    public function test_borrowing_author_is_always_authenticated_user_ignoring_client_anggota_id(): void
    {
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $userB = Anggota::where('email', 'user_b@perpustakaan.test')->first();
        $book = Buku::whereNull('archived_at')->where('stok_tersedia', '>', 0)->first();

        // userA mencoba inject anggota_id milik userB
        $response = $this->actingAs($userA)->post(route('peminjaman.store'), [
            'buku_id' => $book->id,
            'anggota_id' => $userB->id,
        ]);

        $response->assertRedirect(route('peminjaman.index'));

        // Peminjaman harus tercatat atas nama userA, BUKAN userB
        $this->assertDatabaseHas('peminjaman', [
            'anggota_id' => $userA->id,
            'buku_id' => $book->id,
            'status' => 'dipinjam',
        ]);
    }

    public function test_guest_cannot_borrow_book_and_is_redirected_to_login(): void
    {
        $book = Buku::whereNull('archived_at')->first();

        $response = $this->post(route('peminjaman.store'), [
            'buku_id' => $book->id,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_admin_cannot_borrow_book_via_user_route_and_receives_403(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $book = Buku::whereNull('archived_at')->first();

        $response = $this->actingAs($admin)->post(route('peminjaman.store'), [
            'buku_id' => $book->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_inactive_user_cannot_borrow_book(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $user->update(['is_active' => false]);
        $book = Buku::whereNull('archived_at')->first();

        $response = $this->actingAs($user)->post(route('peminjaman.store'), [
            'buku_id' => $book->id,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_borrowing_archived_book_returns_404(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $archivedBook = Buku::whereNotNull('archived_at')->first();

        $response = $this->actingAs($user)->post(route('peminjaman.store'), [
            'buku_id' => $archivedBook->id,
        ]);

        $response->assertStatus(404);
    }

    public function test_atomic_stock_decrement_prevents_negative_stock(): void
    {
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $userB = Anggota::where('email', 'user_b@perpustakaan.test')->first();

        $book = Buku::create([
            'judul' => 'Buku Stok Terakhir',
            'penulis' => 'Penulis Khusus',
            'stok_total' => 1,
            'stok_tersedia' => 1,
            'archived_at' => null,
        ]);

        $service = app(PeminjamanService::class);

        // Transaksi 1 berhasil
        $loan1 = $service->pinjam($userA, $book->id);
        $this->assertNotNull($loan1);

        $book->refresh();
        $this->assertSame(0, $book->stok_tersedia);

        // Transaksi 2 langsung gagal 409 dan stok tetap 0 (tidak negatif)
        try {
            $service->pinjam($userB, $book->id);
            $this->fail('Harus gagal karena stok habis.');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }

        $book->refresh();
        $this->assertSame(0, $book->stok_tersedia);
    }
}
