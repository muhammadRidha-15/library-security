<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Ulasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReviewModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_view_review_moderation_index(): void
    {
        $admin = Anggota::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.ulasan.index'));

        $response->assertOk();
        $response->assertSee('Moderasi Ulasan Anggota');
        $response->assertSee('Total Ulasan Terbit:');
    }

    public function test_admin_can_filter_reviews_by_book_and_search_query(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $user = Anggota::where('role', 'user')->first();
        $book = Buku::whereNull('archived_at')->first();

        $review = Ulasan::create([
            'buku_id' => $book->id,
            'anggota_id' => $user->id,
            'isi' => 'KataUnikFilterDalamUlasanYangSangatSpesifik',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.ulasan.index', [
            'q' => 'KataUnikFilterDalamUlasanYangSangatSpesifik',
            'buku_id' => $book->id,
        ]));

        $response->assertOk();
        $response->assertSee('KataUnikFilterDalamUlasanYangSangatSpesifik');
    }

    public function test_admin_can_delete_review(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $user = Anggota::where('role', 'user')->first();
        $book = Buku::whereNull('archived_at')->first();

        $review = Ulasan::create([
            'buku_id' => $book->id,
            'anggota_id' => $user->id,
            'isi' => 'Ulasan yang melanggar aturan dan harus dimoderasi.',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.ulasan.destroy', $review));

        $response->assertRedirect(route('admin.ulasan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('ulasan', [
            'id' => $review->id,
        ]);

        // Review also no longer visible on the book detail page
        $bookResponse = $this->get(route('buku.show', $book));
        $bookResponse->assertDontSee('Ulasan yang melanggar aturan dan harus dimoderasi.');
    }

    public function test_regular_user_cannot_delete_review_and_receives_403(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $book = Buku::whereNull('archived_at')->first();

        $review = Ulasan::create([
            'buku_id' => $book->id,
            'anggota_id' => $user->id,
            'isi' => 'Ulasan normal.',
        ]);

        $this->actingAs($user)->get(route('admin.ulasan.index'))->assertStatus(403);

        $response = $this->actingAs($user)->delete(route('admin.ulasan.destroy', $review));
        $response->assertStatus(403);

        $this->assertDatabaseHas('ulasan', [
            'id' => $review->id,
        ]);
    }

    public function test_guest_is_redirected_to_login_on_admin_review_routes(): void
    {
        $review = Ulasan::create([
            'buku_id' => 1,
            'anggota_id' => 2,
            'isi' => 'Ulasan contoh untuk uji tamu.',
        ]);

        $this->get(route('admin.ulasan.index'))->assertRedirect(route('login'));
        $this->delete(route('admin.ulasan.destroy', $review))->assertRedirect(route('login'));
    }
}
