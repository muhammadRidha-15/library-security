<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VulnerableProofTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TKT-23 / SEC-08: Demonstrasi SQL Injection pada pencarian buku.
     * Payload ' OR 1=1 -- berhasil membocorkan seluruh katalog buku karena string concatenation raw query.
     */
    public function test_vulnerable_search_sqli_expands_to_all_books(): void
    {
        Buku::factory()->create([
            'judul' => 'Buku Rahasia A',
            'penulis' => 'Penulis A',
            'archived_at' => null,
        ]);

        Buku::factory()->create([
            'judul' => 'Buku Rahasia B',
            'penulis' => 'Penulis B',
            'archived_at' => null,
        ]);

        $payload = "' OR 1=1 -- ";
        $response = $this->get('/buku/cari?q=' . urlencode($payload));

        $response->assertStatus(200);
        // Membuktikan SQLi berhasil mengekspansi query dan menampilkan kedua buku
        $response->assertSee('Buku Rahasia A');
        $response->assertSee('Buku Rahasia B');
    }

    /**
     * TKT-24 / SEC-09: Demonstrasi Stored XSS pada ulasan buku.
     * Payload <script>alert('XSS-LAB')</script> dirender mentah ({!! !!}) tanpa escaping Blade.
     */
    public function test_vulnerable_review_renders_raw_xss_script_tag(): void
    {
        $user = Anggota::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $buku = Buku::factory()->create(['archived_at' => null]);

        $xssPayload = "<script>alert('XSS-LAB')</script>";

        $this->actingAs($user)->post("/buku/{$buku->id}/ulasan", [
            'isi' => $xssPayload,
        ]);

        $response = $this->get("/buku/{$buku->id}");

        $response->assertStatus(200);
        // Membuktikan payload dirender mentah sebagai tag script aktif, bukan &lt;script&gt;
        $response->assertSee($xssPayload, false);
    }

    /**
     * TKT-25 / SEC-10: Demonstrasi IDOR pada detail peminjaman.
     * User A dapat mengakses dan membaca detail peminjaman User B via IDOR (/peminjaman/{id}) tanpa 404.
     */
    public function test_vulnerable_idor_allows_cross_user_borrowing_view(): void
    {
        $userA = Anggota::factory()->create([
            'nama' => 'User A',
            'email' => 'user_a@test.com',
            'role' => 'user',
            'is_active' => true,
        ]);

        $userB = Anggota::factory()->create([
            'nama' => 'User B',
            'email' => 'user_b@test.com',
            'role' => 'user',
            'is_active' => true,
        ]);

        $buku = Buku::factory()->create(['judul' => 'Buku Pinjaman Privat User B']);

        $pinjamanUserB = Peminjaman::create([
            'anggota_id' => $userB->id,
            'buku_id' => $buku->id,
            'dipinjam_pada' => now(),
            'status' => 'dipinjam',
        ]);

        // User A mengakses peminjaman User B langsung via IDOR
        $response = $this->actingAs($userA)->get("/peminjaman/{$pinjamanUserB->id}");

        // Pada versi rentan, request berhasil (HTTP 200) alih-alih ditolak 404
        $response->assertStatus(200);
        $response->assertSee('Buku Pinjaman Privat User B');
        $response->assertSee('User B');
    }

    /**
     * TKT-25 / SEC-10: Demonstrasi Atribut Cookie Lemah & Isolasi Nama Sesi.
     * Cookie sesi diisolasi dengan nama mini_library_vuln_session tanpa flag HttpOnly/Secure/SameSite.
     */
    public function test_vulnerable_session_configuration_has_weak_attributes_and_isolated_cookie_name(): void
    {
        $this->assertSame('mini_library_vuln_session', config('session.cookie'));
        $this->assertFalse(config('session.http_only'));
        $this->assertFalse(config('session.secure'));
        $this->assertNull(config('session.same_site'));
    }
}
