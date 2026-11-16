<?php

namespace Tests\Unit;

use App\Services\AesGcm;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AesGcmTest extends TestCase
{
    private string $validKeyBase64;

    protected function setUp(): void
    {
        parent::setUp();
        // 32-byte key base64 encoded
        $this->validKeyBase64 = base64_encode(random_bytes(32));
    }

    public function test_encrypt_and_decrypt_round_trip(): void
    {
        $service = new AesGcm($this->validKeyBase64);
        $plaintext = '081234567890';
        $aad = 'anggota:1:telepon:v1';

        $envelope = $service->encrypt($plaintext, $aad);

        $this->assertSame(1, $envelope['v']);
        $this->assertNotEmpty($envelope['nonce']);
        $this->assertNotEmpty($envelope['ct']);
        $this->assertNotEmpty($envelope['tag']);

        $decrypted = $service->decrypt($envelope, $aad);
        $this->assertSame($plaintext, $decrypted);
    }

    public function test_two_encryptions_of_same_input_have_distinct_nonces_and_ciphertexts(): void
    {
        $service = new AesGcm($this->validKeyBase64);
        $plaintext = '081234567890';
        $aad = 'anggota:1:telepon:v1';

        $envelope1 = $service->encrypt($plaintext, $aad);
        $envelope2 = $service->encrypt($plaintext, $aad);

        $this->assertNotSame($envelope1['nonce'], $envelope2['nonce']);
        $this->assertNotSame($envelope1['ct'], $envelope2['ct']);
    }

    public function test_tampered_ciphertext_fails_decryption(): void
    {
        $service = new AesGcm($this->validKeyBase64);
        $envelope = $service->encrypt('081234567890', 'anggota:1:telepon:v1');

        // Tamper ciphertext
        $rawCt = base64_decode($envelope['ct']);
        $rawCt[0] = chr(ord($rawCt[0]) ^ 0xFF);
        $envelope['ct'] = base64_encode($rawCt);

        $this->assertNull($service->decrypt($envelope, 'anggota:1:telepon:v1'));
    }

    public function test_tampered_tag_fails_decryption(): void
    {
        $service = new AesGcm($this->validKeyBase64);
        $envelope = $service->encrypt('081234567890', 'anggota:1:telepon:v1');

        // Tamper authentication tag
        $rawTag = base64_decode($envelope['tag']);
        $rawTag[0] = chr(ord($rawTag[0]) ^ 0xFF);
        $envelope['tag'] = base64_encode($rawTag);

        $this->assertNull($service->decrypt($envelope, 'anggota:1:telepon:v1'));
    }

    public function test_tampered_nonce_fails_decryption(): void
    {
        $service = new AesGcm($this->validKeyBase64);
        $envelope = $service->encrypt('081234567890', 'anggota:1:telepon:v1');

        // Tamper nonce
        $rawNonce = base64_decode($envelope['nonce']);
        $rawNonce[0] = chr(ord($rawNonce[0]) ^ 0xFF);
        $envelope['nonce'] = base64_encode($rawNonce);

        $this->assertNull($service->decrypt($envelope, 'anggota:1:telepon:v1'));
    }

    public function test_wrong_aad_fails_decryption(): void
    {
        $service = new AesGcm($this->validKeyBase64);
        $envelope = $service->encrypt('081234567890', 'anggota:1:telepon:v1');

        // Mencoba dekripsi menggunakan AAD anggota lain (anggota:2)
        $this->assertNull($service->decrypt($envelope, 'anggota:2:telepon:v1'));
    }

    public function test_wrong_key_fails_decryption(): void
    {
        $service1 = new AesGcm($this->validKeyBase64);
        $otherKeyBase64 = base64_encode(random_bytes(32));
        $service2 = new AesGcm($otherKeyBase64);

        $envelope = $service1->encrypt('081234567890', 'anggota:1:telepon:v1');
        $this->assertNull($service2->decrypt($envelope, 'anggota:1:telepon:v1'));
    }

    public function test_invalid_key_length_throws_or_returns_null(): void
    {
        $shortKeyBase64 = base64_encode(random_bytes(16)); // 16 bytes bukan 32 bytes
        $service = new AesGcm($shortKeyBase64);

        $this->expectException(RuntimeException::class);
        $service->encrypt('081234567890', 'anggota:1:telepon:v1');
    }
}
