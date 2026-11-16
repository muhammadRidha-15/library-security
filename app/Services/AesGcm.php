<?php

namespace App\Services;

use RuntimeException;

class AesGcm
{
    private const CIPHER = 'aes-256-gcm';
    private const NONCE_LENGTH = 12; // 96-bit recommended for GCM
    private const TAG_LENGTH = 16;   // 128-bit authentication tag
    private const KEY_LENGTH = 32;   // 256-bit key

    private ?string $keyBytes = null;

    public function __construct(?string $keyBase64 = null)
    {
        $rawKey = $keyBase64 ?? config('security.telepon_key');
        if (is_string($rawKey) && ! empty($rawKey)) {
            $this->keyBytes = $this->parseKey($rawKey);
        }
    }

    /**
     * Parse and validate base64 key string (supports optional 'base64:' prefix).
     */
    private function parseKey(string $keyString): ?string
    {
        if (str_starts_with($keyString, 'base64:')) {
            $keyString = substr($keyString, 7);
        }

        $decoded = base64_decode($keyString, true);
        if ($decoded === false || strlen($decoded) !== self::KEY_LENGTH) {
            return null;
        }

        return $decoded;
    }

    /**
     * Encrypt plaintext string with AES-256-GCM using authenticated additional data (AAD).
     *
     * @param string $plaintext
     * @param string $aad e.g. "anggota:{id}:telepon:v1"
     * @return array{v: int, nonce: string, ct: string, tag: string} Versioned envelope
     * @throws RuntimeException
     */
    public function encrypt(string $plaintext, string $aad): array
    {
        if ($this->keyBytes === null || strlen($this->keyBytes) !== self::KEY_LENGTH) {
            throw new RuntimeException('Kunci enkripsi tidak valid atau belum dikonfigurasi.');
        }

        $nonce = random_bytes(self::NONCE_LENGTH);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $this->keyBytes,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            $aad,
            self::TAG_LENGTH
        );

        if ($ciphertext === false || strlen($tag) !== self::TAG_LENGTH) {
            throw new RuntimeException('Gagal mengenkripsi data kontak.');
        }

        return [
            'v' => 1,
            'nonce' => base64_encode($nonce),
            'ct' => base64_encode($ciphertext),
            'tag' => base64_encode($tag),
        ];
    }

    /**
     * Decrypt envelope array or JSON string using authenticated additional data (AAD).
     * Returns null if tampering detected, key invalid, or decryption fails.
     *
     * @param array{v?: int, nonce?: string, ct?: string, tag?: string}|string $envelope
     * @param string $aad
     * @return string|null
     */
    public function decrypt(array|string $envelope, string $aad): ?string
    {
        if ($this->keyBytes === null || strlen($this->keyBytes) !== self::KEY_LENGTH) {
            return null;
        }

        if (is_string($envelope)) {
            $envelope = json_decode($envelope, true);
        }

        if (! is_array($envelope)) {
            return null;
        }

        // Strict envelope schema validation
        if (! isset($envelope['v'], $envelope['nonce'], $envelope['ct'], $envelope['tag'])) {
            return null;
        }

        if ($envelope['v'] !== 1) {
            return null;
        }

        $nonce = base64_decode($envelope['nonce'], true);
        $ciphertext = base64_decode($envelope['ct'], true);
        $tag = base64_decode($envelope['tag'], true);

        if ($nonce === false || strlen($nonce) !== self::NONCE_LENGTH) {
            return null;
        }

        if ($ciphertext === false) {
            return null;
        }

        if ($tag === false || strlen($tag) !== self::TAG_LENGTH) {
            return null;
        }

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->keyBytes,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            $aad
        );

        if ($plaintext === false) {
            return null;
        }

        return $plaintext;
    }
}
