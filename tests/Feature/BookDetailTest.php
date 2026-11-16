<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Ulasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TST-13 / FR-05: Guest can view active book detail.
     */
    public function test_guest_can_view_active_book_detail(): void
    {
        $book = Buku::factory()->create([
            'judul' => 'Sistem Operasi Modern',
            'penulis' => 'Andrew S. Tanenbaum',
            'deskripsi' => 'Buku teks komprehensif tentang prinsip dan desain sistem operasi.',
            'stok_total' => 5,
            'stok_tersedia' => 3,
            'archived_at' => null,
        ]);

        $response = $this->get('/buku/' . $book->id);

        $response->assertStatus(200);
        $response->assertSee('Sistem Operasi Modern');
        $response->assertSee('Andrew S. Tanenbaum');
        $response->assertSee('Buku teks komprehensif tentang prinsip dan desain sistem operasi.');
        $response->assertSee('Masuk untuk Meminjam');
    }

    /**
     * TST-13 / FR-05: Viewing archived book returns 404.
     */
    public function test_viewing_archived_book_returns_404(): void
    {
        $book = Buku::factory()->archived()->create([
            'judul' => 'Buku Edisi Usang',
        ]);

        $response = $this->get('/buku/' . $book->id);

        $response->assertStatus(404);
    }

    /**
     * TST-13 / FR-05: Viewing nonexistent book returns 404.
     */
    public function test_viewing_nonexistent_book_returns_404(): void
    {
        $response = $this->get('/buku/999999');

        $response->assertStatus(404);
    }

    /**
     * TST-13 / FR-05 / SEC-09: Book detail escapes XSS payload in reviews.
     */
    public function test_book_detail_escapes_xss_payload_in_reviews(): void
    {
        $book = Buku::factory()->create([
            'judul' => 'Keamanan Jaringan Komputer',
            'archived_at' => null,
        ]);

        $member = Anggota::factory()->create([
            'nama' => 'User Pengulas',
        ]);

        $xssPayload = "<script>alert('XSS')</script>";

        Ulasan::create([
            'anggota_id' => $member->id,
            'buku_id' => $book->id,
            'isi' => $xssPayload,
        ]);

        $response = $this->get('/buku/' . $book->id);

        $response->assertStatus(200);
        $response->assertSee('User Pengulas');
        // Unescaped script tag MUST NOT appear in the response
        $response->assertDontSee('<script>alert(\'XSS\')</script>', false);
        // Escaped HTML entities MUST appear
        $response->assertSee(e($xssPayload), false);
    }
}
