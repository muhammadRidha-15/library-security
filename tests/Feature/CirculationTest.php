<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CirculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_return_rejects_a_stale_loan_snapshot(): void
    {
        $loan = Peminjaman::where('status', 'dipinjam')->first();
        $stale = Peminjaman::findOrFail($loan->id);
        $book = $loan->buku;
        $stock = $book->stok_tersedia;
        $service = app(PeminjamanService::class);
        $service->kembalikan($loan);

        try {
            $service->kembalikan($stale);
            $this->fail('Pengembalian kedua harus ditolak.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }
        $this->assertSame($stock + 1, $book->fresh()->stok_tersedia);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_view_circulation_index(): void
    {
        $admin = Anggota::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.peminjaman.index'));

        $response->assertOk();
        $response->assertSee('Sirkulasi &amp; Peminjaman Buku', false);
        $response->assertSee('Aktif Dipinjam');
        $response->assertSee('Sudah Kembali');
    }

    public function test_admin_can_filter_circulation_by_status_and_member(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $activeLoan = Peminjaman::where('status', 'dipinjam')->first();

        $response = $this->actingAs($admin)->get(route('admin.peminjaman.index', [
            'status' => 'dipinjam',
            'anggota_id' => $activeLoan->anggota_id,
        ]));

        $response->assertOk();
        $response->assertSee($activeLoan->buku->judul);
    }

    public function test_admin_can_view_loan_detail(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $loan = Peminjaman::first();

        $response = $this->actingAs($admin)->get(route('admin.peminjaman.show', $loan));

        $response->assertOk();
        $response->assertSee('Detail Peminjaman #'.$loan->id);
        $response->assertSee($loan->buku->judul);
        $response->assertSee($loan->anggota->nama);
    }

    public function test_admin_can_record_loan_for_active_member(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $book = Buku::whereNull('archived_at')->where('stok_tersedia', '>', 0)->first();
        $initialStock = $book->stok_tersedia;

        $response = $this->actingAs($admin)->post(route('admin.peminjaman.store'), [
            'anggota_id' => $userA->id,
            'buku_id' => $book->id,
        ]);

        $response->assertRedirect(route('admin.peminjaman.index'));
        $response->assertSessionHas('success');

        $book->refresh();
        $this->assertSame($initialStock - 1, $book->stok_tersedia);

        $this->assertDatabaseHas('peminjaman', [
            'anggota_id' => $userA->id,
            'buku_id' => $book->id,
            'status' => 'dipinjam',
        ]);
    }

    public function test_admin_cannot_record_loan_for_inactive_member(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $userA->update(['is_active' => false]);

        $book = Buku::whereNull('archived_at')->where('stok_tersedia', '>', 0)->first();
        $initialStock = $book->stok_tersedia;

        $response = $this->actingAs($admin)->post(route('admin.peminjaman.store'), [
            'anggota_id' => $userA->id,
            'buku_id' => $book->id,
        ]);

        $response->assertSessionHasErrors('anggota_id');

        $book->refresh();
        $this->assertSame($initialStock, $book->stok_tersedia);
    }

    public function test_admin_can_process_book_return_and_stock_is_incremented(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $loan = Peminjaman::where('status', 'dipinjam')->first();
        $book = $loan->buku;
        $initialStock = $book->stok_tersedia;

        $response = $this->actingAs($admin)->patch(route('admin.peminjaman.pengembalian', $loan));

        $response->assertRedirect(route('admin.peminjaman.show', $loan));
        $response->assertSessionHas('success');

        $loan->refresh();
        $this->assertSame('kembali', $loan->status);
        $this->assertNotNull($loan->dikembalikan_pada);

        $book->refresh();
        $this->assertSame($initialStock + 1, $book->stok_tersedia);
    }

    public function test_duplicate_book_return_fails_idempotently_with_409(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $loan = Peminjaman::where('status', 'dipinjam')->first();

        // First return succeeds
        $this->actingAs($admin)->patch(route('admin.peminjaman.pengembalian', $loan));
        $loan->refresh();
        $stockAfterFirstReturn = $loan->buku->stok_tersedia;

        // Second return attempt must fail with 409 Conflict
        $response = $this->actingAs($admin)->patch(route('admin.peminjaman.pengembalian', $loan));
        $response->assertStatus(409);

        // Stock must remain unchanged
        $loan->buku->refresh();
        $this->assertSame($stockAfterFirstReturn, $loan->buku->stok_tersedia);
    }

    public function test_book_return_is_allowed_for_deactivated_member(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $loan = Peminjaman::where('status', 'dipinjam')->first();

        // Deactivate the member who holds the book
        $loan->anggota->update(['is_active' => false]);

        $response = $this->actingAs($admin)->patch(route('admin.peminjaman.pengembalian', $loan));

        $response->assertRedirect(route('admin.peminjaman.show', $loan));
        $response->assertSessionHas('success');

        $loan->refresh();
        $this->assertSame('kembali', $loan->status);
    }

    public function test_regular_user_cannot_access_circulation_and_receives_403(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $loan = Peminjaman::first();

        $this->actingAs($user)->get(route('admin.peminjaman.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('admin.peminjaman.create'))->assertStatus(403);
        $this->actingAs($user)->post(route('admin.peminjaman.store'), [
            'anggota_id' => $user->id,
            'buku_id' => 1,
        ])->assertStatus(403);
        $this->actingAs($user)->patch(route('admin.peminjaman.pengembalian', $loan))->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_on_circulation_routes(): void
    {
        $this->get(route('admin.peminjaman.index'))->assertRedirect(route('login'));
        $this->get(route('admin.peminjaman.create'))->assertRedirect(route('login'));
    }
}
