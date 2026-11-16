<?php

namespace Tests\Feature;

use App\Models\Buku;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSearchSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TST-08 / TST-13 / FR-04 / SEC-08: Normal search returns matching active books.
     */
    public function test_normal_search_returns_matching_active_books(): void
    {
        Buku::factory()->create([
            'judul' => 'Pemrograman Laravel Modern',
            'penulis' => 'Taylor Otwell',
            'archived_at' => null,
        ]);

        Buku::factory()->create([
            'judul' => 'Algoritma dan Struktur Data',
            'penulis' => 'Donald Knuth',
            'archived_at' => null,
        ]);

        $response = $this->get('/buku/cari?q=Laravel');

        $response->assertStatus(200);
        $response->assertSee('Pemrograman Laravel Modern');
        $response->assertDontSee('Algoritma dan Struktur Data');

        $responseAuthor = $this->get('/buku/cari?q=Knuth');

        $responseAuthor->assertStatus(200);
        $responseAuthor->assertSee('Algoritma dan Struktur Data');
        $responseAuthor->assertDontSee('Pemrograman Laravel Modern');
    }

    /**
     * TST-08 / SEC-08: Search with SQL injection payload is treated as literal string
     * without query expansion or database errors.
     */
    public function test_search_with_sqli_payload_treated_as_literal_without_expansion_or_error(): void
    {
        Buku::factory()->create([
            'judul' => 'Buku Jaringan Komputer',
            'penulis' => 'Andrew Tanenbaum',
            'archived_at' => null,
        ]);

        Buku::factory()->create([
            'judul' => 'Buku Basis Data',
            'penulis' => 'C.J. Date',
            'archived_at' => null,
        ]);

        $payload = "' OR 1=1 -- ";
        $response = $this->get('/buku/cari?q=' . urlencode($payload));

        $response->assertStatus(200);
        $response->assertDontSee('Buku Jaringan Komputer');
        $response->assertDontSee('Buku Basis Data');
        $response->assertSee('Tidak ada buku yang ditemukan');
    }

    /**
     * TST-08 / SEC-08: Search query exceeding 100 characters is rejected by validation.
     */
    public function test_search_rejects_query_over_100_characters(): void
    {
        $overlengthQuery = str_repeat('a', 101);

        $response = $this->get('/buku/cari?q=' . $overlengthQuery);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['q']);
    }

    /**
     * TST-08 / FR-04: Empty or whitespace-only search redirects to catalog page.
     */
    public function test_empty_search_redirects_to_catalog(): void
    {
        $responseEmpty = $this->get('/buku/cari?q=');
        $responseEmpty->assertRedirect('/buku');

        $responseWhitespace = $this->get('/buku/cari?q=%20%20%20');
        $responseWhitespace->assertRedirect('/buku');

        $responseNoParam = $this->get('/buku/cari');
        $responseNoParam->assertRedirect('/buku');
    }

    /**
     * TST-08 / TST-13 / FR-04: Search does not return archived books even if query matches.
     */
    public function test_search_does_not_return_archived_books(): void
    {
        Buku::factory()->create([
            'judul' => 'Kriptografi Modern Aktif',
            'archived_at' => null,
        ]);

        Buku::factory()->create([
            'judul' => 'Kriptografi Kuno Diarsipkan',
            'archived_at' => now(),
        ]);

        $response = $this->get('/buku/cari?q=Kriptografi');

        $response->assertStatus(200);
        $response->assertSee('Kriptografi Modern Aktif');
        $response->assertDontSee('Kriptografi Kuno Diarsipkan');
    }
}
