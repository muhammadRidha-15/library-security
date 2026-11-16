<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Services\AesGcm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MemberPhoneEncryptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_user_can_view_profile_page(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();

        $response = $this->actingAs($user)->get(route('profil.show'));

        $response->assertOk();
        $response->assertSee('Profil Saya');
        $response->assertSee($user->nama);
        $response->assertSee($user->email);
    }

    public function test_user_can_update_phone_number_and_stored_encrypted(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $phoneInput = '081234567890';

        $response = $this->actingAs($user)->patch(route('profil.telepon.update'), [
            'telepon' => $phoneInput,
        ]);

        $response->assertRedirect(route('profil.show'));
        $response->assertSessionHas('success');

        $user->refresh();

        // 1. Telepon plaintext TIDAK boleh tersimpan mentah di database
        $rawRecord = DB::table('anggota')->where('id', $user->id)->first();
        $this->assertStringNotContainsString($phoneInput, $rawRecord->telepon_enc);

        // 2. Format penyimpanan berupa envelope JSON versioned
        $envelope = json_decode($rawRecord->telepon_enc, true);
        $this->assertIsArray($envelope);
        $this->assertSame(1, $envelope['v']);
        $this->assertArrayHasKey('nonce', $envelope);
        $this->assertArrayHasKey('ct', $envelope);
        $this->assertArrayHasKey('tag', $envelope);

        // 3. Round-trip verifikasi: dapat didekripsi kembali dengan AAD yang sah
        $aes = app(AesGcm::class);
        $decrypted = $aes->decrypt($envelope, "anggota:{$user->id}:telepon:v1");
        $this->assertSame($phoneInput, $decrypted);
    }

    public function test_two_updates_with_same_phone_produce_different_nonces(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $phone = '081234567890';

        $this->actingAs($user)->patch(route('profil.telepon.update'), ['telepon' => $phone]);
        $user->refresh();
        $envelope1 = $user->telepon_enc;

        $this->actingAs($user)->patch(route('profil.telepon.update'), ['telepon' => $phone]);
        $user->refresh();
        $envelope2 = $user->telepon_enc;

        $this->assertNotSame($envelope1['nonce'], $envelope2['nonce']);
        $this->assertNotSame($envelope1['ct'], $envelope2['ct']);
    }

    public function test_phone_number_cannot_be_decrypted_with_other_members_aad(): void
    {
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $userB = Anggota::where('email', 'user_b@perpustakaan.test')->first();

        $this->actingAs($userA)->patch(route('profil.telepon.update'), ['telepon' => '081234567890']);
        $userA->refresh();

        $aes = app(AesGcm::class);
        // Membuka ciphertext User A menggunakan identitas User B harus gagal
        $tamperedDecryption = $aes->decrypt($userA->telepon_enc, "anggota:{$userB->id}:telepon:v1");
        $this->assertNull($tamperedDecryption);
    }

    public function test_tampered_envelope_in_db_fails_gracefully_on_profile_view(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $this->actingAs($user)->patch(route('profil.telepon.update'), ['telepon' => '081234567890']);
        $user->refresh();

        // Rusak ciphertext di database
        $envelope = $user->telepon_enc;
        $envelope['ct'] = base64_encode('corrupted_ciphertext');
        $user->update(['telepon_enc' => $envelope]);

        // Halaman profil tetap terbuka tanpa 500 error, input telepon menjadi kosong
        $response = $this->actingAs($user)->get(route('profil.show'));
        $response->assertOk();
        $response->assertDontSee('081234567890');
    }

    public function test_phone_validation_rejects_invalid_characters_or_length(): void
    {
        $user = Anggota::where('email', 'user_a@perpustakaan.test')->first();

        // Huruf
        $response1 = $this->actingAs($user)->patch(route('profil.telepon.update'), [
            'telepon' => 'notanumber',
        ]);
        $response1->assertSessionHasErrors('telepon');

        // Kurang dari 8 digit
        $response2 = $this->actingAs($user)->patch(route('profil.telepon.update'), [
            'telepon' => '12345',
        ]);
        $response2->assertSessionHasErrors('telepon');

        // Lebih dari 15 digit
        $response3 = $this->actingAs($user)->patch(route('profil.telepon.update'), [
            'telepon' => '12345678901234567',
        ]);
        $response3->assertSessionHasErrors('telepon');
    }

    public function test_user_cannot_update_phone_of_another_user_via_parameter_injection(): void
    {
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->first();
        $userB = Anggota::where('email', 'user_b@perpustakaan.test')->first();

        // User A mencoba inject user_id/anggota_id milik User B
        $response = $this->actingAs($userA)->patch(route('profil.telepon.update'), [
            'anggota_id' => $userB->id,
            'user_id' => $userB->id,
            'telepon' => '089999999999',
        ]);

        $response->assertRedirect(route('profil.show'));

        $userA->refresh();
        $userB->refresh();

        // Nomor tersimpan di userA
        $this->assertNotNull($userA->telepon_enc);
        // Telepon userB tidak berubah
        $this->assertNull($userB->telepon_enc);
    }
}
