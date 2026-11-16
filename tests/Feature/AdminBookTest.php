<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_view_dashboard_summary(): void
    {
        $admin = Anggota::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Ringkasan Sistem Perpustakaan');
        $response->assertSee('Koleksi Buku');
        $response->assertSee('Pinjaman Berjalan');
    }

    public function test_admin_can_view_book_management_index(): void
    {
        $admin = Anggota::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.buku.index'));

        $response->assertOk();
        $response->assertSee('Kelola Koleksi Buku');
        $response->assertSee('Buku Aktif');
        $response->assertSee('Diarsipkan');
    }

    public function test_admin_can_filter_archived_books(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $archivedBook = Buku::whereNotNull('archived_at')->first();

        $response = $this->actingAs($admin)->get(route('admin.buku.index', ['status' => 'arsip']));

        $response->assertOk();
        $response->assertSee($archivedBook->judul);
    }

    public function test_admin_can_create_new_book(): void
    {
        $admin = Anggota::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->post(route('admin.buku.store'), [
            'judul' => 'Sistem Terdistribusi Lanjutan',
            'penulis' => 'Andrew S. Tanenbaum',
            'deskripsi' => 'Buku pegangan arsitektur sistem terdistribusi modern.',
            'stok_total' => 5,
        ]);

        $response->assertRedirect(route('admin.buku.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('buku', [
            'judul' => 'Sistem Terdistribusi Lanjutan',
            'penulis' => 'Andrew S. Tanenbaum',
            'stok_total' => 5,
            'stok_tersedia' => 5,
            'archived_at' => null,
        ]);
    }

    public function test_admin_can_update_book_info_and_stock(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $book = Buku::whereNull('archived_at')->first();

        $response = $this->actingAs($admin)->patch(route('admin.buku.update', $book), [
            'judul' => $book->judul . ' (Edisi Revisi)',
            'penulis' => $book->penulis,
            'deskripsi' => 'Deskripsi yang diperbarui oleh admin.',
            'stok_total' => $book->stok_total + 2,
        ]);

        $response->assertRedirect(route('admin.buku.edit', $book));
        $response->assertSessionHas('success');

        $book->refresh();
        $this->assertStringContainsString('(Edisi Revisi)', $book->judul);
    }

    public function test_total_stock_cannot_be_set_lower_than_active_borrowed_count(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        // Cari buku yang memiliki pinjaman aktif
        $loan = Peminjaman::where('status', 'dipinjam')->first();
        $book = $loan->buku;

        // Mencoba menurunkan stok total ke 0 padahal ada 1 buku aktif dipinjam
        $response = $this->actingAs($admin)->patch(route('admin.buku.update', $book), [
            'judul' => $book->judul,
            'penulis' => $book->penulis,
            'deskripsi' => $book->deskripsi,
            'stok_total' => 0,
        ]);

        $response->assertSessionHasErrors('stok_total');
    }

    public function test_admin_can_archive_and_restore_book(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $book = Buku::whereNull('archived_at')->first();

        // Arsipkan
        $archiveRes = $this->actingAs($admin)->patch(route('admin.buku.archive', $book));
        $archiveRes->assertRedirect(route('admin.buku.index'));

        $book->refresh();
        $this->assertNotNull($book->archived_at);

        // Pulihkan
        $restoreRes = $this->actingAs($admin)->patch(route('admin.buku.restore', $book));
        $restoreRes->assertRedirect(route('admin.buku.index', ['status' => 'aktif']));

        $book->refresh();
        $this->assertNull($book->archived_at);
    }

    public function test_destroy_book_with_borrowing_history_archives_instead_of_physical_delete(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        // Buku ini memiliki transaksi di seeder
        $loan = Peminjaman::first();
        $book = $loan->buku;

        $response = $this->actingAs($admin)->delete(route('admin.buku.destroy', $book));

        $response->assertRedirect(route('admin.buku.index'));

        // Buku tetap ada di DB (tidak terhapus fisik), namun archived_at terisi
        $this->assertDatabaseHas('buku', ['id' => $book->id]);
        $book->refresh();
        $this->assertNotNull($book->archived_at);
    }

    public function test_destroy_book_without_borrowing_history_is_permanently_deleted(): void
    {
        $admin = Anggota::where('role', 'admin')->first();

        $freshBook = Buku::create([
            'judul' => 'Buku Baru Belum Ada Transaksi',
            'penulis' => 'Penulis Bebas',
            'stok_total' => 2,
            'stok_tersedia' => 2,
            'archived_at' => null,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.buku.destroy', $freshBook));

        $response->assertRedirect(route('admin.buku.index'));
        $this->assertDatabaseMissing('buku', ['id' => $freshBook->id]);
    }

    public function test_regular_user_cannot_access_admin_book_endpoints_and_receives_403(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $book = Buku::first();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertStatus(403);
        $this->actingAs($user)->get(route('admin.buku.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('admin.buku.create'))->assertStatus(403);
        $this->actingAs($user)->post(route('admin.buku.store'), [
            'judul' => 'Illegal Injection',
            'penulis' => 'Hacker',
            'stok_total' => 10,
        ])->assertStatus(403);
        $this->actingAs($user)->patch(route('admin.buku.update', $book), [
            'judul' => 'Modified',
            'penulis' => 'Modified',
            'stok_total' => 10,
        ])->assertStatus(403);
        $this->actingAs($user)->delete(route('admin.buku.destroy', $book))->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_on_admin_book_endpoints(): void
    {
        $this->get(route('admin.buku.index'))->assertRedirect(route('login'));
        $this->get(route('admin.buku.create'))->assertRedirect(route('login'));
    }
}
