<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');
    }

    public function test_book_defaults_to_default_cover_when_photo_is_null(): void
    {
        $book = Buku::create([
            'judul' => 'Buku Tanpa Foto',
            'penulis' => 'Penulis Uji',
            'deskripsi' => 'Deskripsi uji',
            'foto' => null,
            'stok_total' => 2,
            'stok_tersedia' => 2,
        ]);

        $this->assertStringContainsString('images/default-book-cover.svg', $book->foto_url);
    }

    public function test_admin_can_upload_photo_when_creating_book(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $fakeImage = UploadedFile::fake()->image('sampul-buku.jpg', 600, 800)->size(500);

        $response = $this->actingAs($admin)->post(route('admin.buku.store'), [
            'judul' => 'Buku Baru Berfoto',
            'penulis' => 'Penulis Hebat',
            'deskripsi' => 'Deskripsi buku baru',
            'stok_total' => 5,
            'foto' => $fakeImage,
        ]);

        $response->assertRedirect(route('admin.buku.index'));
        $response->assertSessionHas('success');

        $buku = Buku::where('judul', 'Buku Baru Berfoto')->first();
        $this->assertNotNull($buku);
        $this->assertNotNull($buku->foto);

        Storage::disk('public')->assertExists($buku->foto);
        $this->assertStringContainsString('storage/' . $buku->foto, $buku->foto_url);
    }

    public function test_admin_can_update_and_replace_book_photo(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $initialImage = UploadedFile::fake()->image('awal.jpg', 400, 600);

        // Buat buku dengan foto awal
        $storedPath = $initialImage->store('covers', 'public');
        $buku = Buku::create([
            'judul' => 'Buku Ganti Foto',
            'penulis' => 'Penulis Uji',
            'foto' => $storedPath,
            'stok_total' => 3,
            'stok_tersedia' => 3,
        ]);

        Storage::disk('public')->assertExists($storedPath);

        // Upload foto baru pengganti
        $newImage = UploadedFile::fake()->image('baru.png', 500, 700);

        $response = $this->actingAs($admin)->patch(route('admin.buku.update', $buku), [
            'judul' => 'Buku Ganti Foto',
            'penulis' => 'Penulis Uji',
            'stok_total' => 3,
            'foto' => $newImage,
        ]);

        $response->assertRedirect(route('admin.buku.edit', $buku));
        $buku->refresh();

        $this->assertNotEquals($storedPath, $buku->foto);
        Storage::disk('public')->assertMissing($storedPath);
        Storage::disk('public')->assertExists($buku->foto);
    }

    public function test_admin_can_remove_book_photo_via_hapus_foto(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $image = UploadedFile::fake()->image('hapus-saya.jpg', 400, 600);
        $path = $image->store('covers', 'public');

        $buku = Buku::create([
            'judul' => 'Buku Hapus Foto',
            'penulis' => 'Penulis Uji',
            'foto' => $path,
            'stok_total' => 3,
            'stok_tersedia' => 3,
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($admin)->patch(route('admin.buku.update', $buku), [
            'judul' => 'Buku Hapus Foto',
            'penulis' => 'Penulis Uji',
            'stok_total' => 3,
            'hapus_foto' => '1',
        ]);

        $response->assertRedirect(route('admin.buku.edit', $buku));
        $buku->refresh();

        $this->assertNull($buku->foto);
        Storage::disk('public')->assertMissing($path);
        $this->assertStringContainsString('images/default-book-cover.svg', $buku->foto_url);
    }

    public function test_photo_upload_rejects_non_image_files(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $fakeScript = UploadedFile::fake()->create('malicious.php', 10, 'application/x-php');

        $response = $this->actingAs($admin)->post(route('admin.buku.store'), [
            'judul' => 'Buku Malicious',
            'penulis' => 'Attacker',
            'stok_total' => 1,
            'foto' => $fakeScript,
        ]);

        $response->assertSessionHasErrors('foto');
        $this->assertDatabaseMissing('buku', ['judul' => 'Buku Malicious']);
    }

    public function test_photo_upload_rejects_file_over_2mb(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $oversizedImage = UploadedFile::fake()->image('huge.jpg')->size(3000); // 3 MB

        $response = $this->actingAs($admin)->post(route('admin.buku.store'), [
            'judul' => 'Buku Huge Image',
            'penulis' => 'Penulis',
            'stok_total' => 1,
            'foto' => $oversizedImage,
        ]);

        $response->assertSessionHasErrors('foto');
    }

    public function test_catalog_and_detail_view_renders_book_covers(): void
    {
        $buku = Buku::whereNull('archived_at')->first();

        $catalogResponse = $this->get(route('buku.index'));
        $catalogResponse->assertOk();
        $catalogResponse->assertSee($buku->foto_url, false);

        $detailResponse = $this->get(route('buku.show', $buku));
        $detailResponse->assertOk();
        $detailResponse->assertSee($buku->foto_url, false);
    }
}
