<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Ulasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_active_user_can_submit_review_for_active_book(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $book = Buku::whereNull('archived_at')->first();

        $response = $this->actingAs($user)->post(route('buku.ulasan.store', $book), [
            'isi' => 'Ini ulasan yang sangat bermutu dan relevan.',
        ]);

        $response->assertRedirect(route('buku.show', $book));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ulasan', [
            'buku_id' => $book->id,
            'anggota_id' => $user->id,
            'isi' => 'Ini ulasan yang sangat bermutu dan relevan.',
        ]);
    }

    public function test_guest_cannot_submit_review_and_is_redirected_to_login(): void
    {
        $book = Buku::whereNull('archived_at')->first();

        $response = $this->post(route('buku.ulasan.store', $book), [
            'isi' => 'Ulasan dari tamu yang tidak terautentikasi.',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('ulasan', [
            'isi' => 'Ulasan dari tamu yang tidak terautentikasi.',
        ]);
    }

    public function test_inactive_user_cannot_submit_review(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $user->update(['is_active' => false]);
        $book = Buku::whereNull('archived_at')->first();

        $response = $this->actingAs($user)->post(route('buku.ulasan.store', $book), [
            'isi' => 'Ulasan dari akun nonaktif.',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('ulasan', [
            'isi' => 'Ulasan dari akun nonaktif.',
        ]);
    }

    public function test_review_author_is_always_taken_from_session_ignoring_payload_anggota_id(): void
    {
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $userB = Anggota::where('email', 'user_b@perpustakaan.test')->first();
        $book = Buku::whereNull('archived_at')->first();

        // userA mencoba inject anggota_id milik userB
        $response = $this->actingAs($userA)->post(route('buku.ulasan.store', $book), [
            'anggota_id' => $userB->id,
            'isi' => 'Ulasan dengan usaha manipulasi identitas pembuat.',
        ]);

        $response->assertRedirect(route('buku.show', $book));

        // Harus tercatat atas nama userA, BUKAN userB
        $this->assertDatabaseHas('ulasan', [
            'buku_id' => $book->id,
            'anggota_id' => $userA->id,
            'isi' => 'Ulasan dengan usaha manipulasi identitas pembuat.',
        ]);
        $this->assertDatabaseMissing('ulasan', [
            'buku_id' => $book->id,
            'anggota_id' => $userB->id,
            'isi' => 'Ulasan dengan usaha manipulasi identitas pembuat.',
        ]);
    }

    public function test_review_for_archived_book_returns_404(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $archivedBook = Buku::whereNotNull('archived_at')->first();

        $response = $this->actingAs($user)->post(route('buku.ulasan.store', $archivedBook), [
            'isi' => 'Mencoba mengulas buku yang diarsipkan.',
        ]);

        $response->assertStatus(404);
    }

    public function test_review_requires_valid_content_length_between_1_and_2000(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $book = Buku::whereNull('archived_at')->first();

        // Kosong
        $responseEmpty = $this->actingAs($user)->post(route('buku.ulasan.store', $book), [
            'isi' => '',
        ]);
        $responseEmpty->assertSessionHasErrors('isi');

        // Lebih dari 2000 karakter
        $responseTooLong = $this->actingAs($user)->post(route('buku.ulasan.store', $book), [
            'isi' => str_repeat('A', 2001),
        ]);
        $responseTooLong->assertSessionHasErrors('isi');
    }

    public function test_stored_xss_payload_in_review_is_escaped_in_html_response(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $book = Buku::whereNull('archived_at')->first();

        // Payload T2 XSS tersimpan sesuai PRD
        $xssPayload = "<script>alert('XSS-LAB')</script>";

        $this->actingAs($user)->post(route('buku.ulasan.store', $book), [
            'isi' => $xssPayload,
        ]);

        $response = $this->get(route('buku.show', $book));
        $response->assertOk();

        // Verifikasi output escaping: karakter tag harus ter-escape di response HTML
        $response->assertDontSee($xssPayload, false);
        $response->assertSee('&lt;script&gt;alert(&#039;XSS-LAB&#039;)&lt;/script&gt;', false);
    }

    public function test_csp_header_is_present_in_web_responses(): void
    {
        $book = Buku::whereNull('archived_at')->first();
        $response = $this->get(route('buku.show', $book));

        $response->assertOk();
        $response->assertHeader('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("script-src 'self'", $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("object-src 'none'", $response->headers->get('Content-Security-Policy'));
    }
}
