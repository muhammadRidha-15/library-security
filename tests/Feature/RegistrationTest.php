<?php

namespace Tests\Feature;

use App\Models\Anggota;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TST-11 / FR-01: Registration screen can be rendered.
     */
    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/registrasi');

        $response->assertStatus(200);
        $response->assertSee('Daftar Anggota Baru');
        $response->assertSee('nama');
        $response->assertSee('email');
        $response->assertSee('password');
        $response->assertSee('password_confirmation');
    }

    /**
     * TST-11 / FR-01 / SEC-02: Guest can register with valid data.
     */
    public function test_guest_can_register_with_valid_data(): void
    {
        $response = $this->post('/registrasi', [
            'nama' => 'Muhammad Ridha',
            'email' => 'Ridha.Lab@Example.COM',
            'password' => 'S4ngatKuat!2026#Perpustakaan',
            'password_confirmation' => 'S4ngatKuat!2026#Perpustakaan',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success', 'Registrasi berhasil. Silakan masuk.');

        $this->assertDatabaseHas('anggota', [
            'nama' => 'Muhammad Ridha',
            'email' => 'ridha.lab@example.com',
            'role' => 'user',
            'is_active' => true,
        ]);

        $member = Anggota::where('email', 'ridha.lab@example.com')->first();
        $this->assertNotNull($member);
        $this->assertTrue(Hash::check('S4ngatKuat!2026#Perpustakaan', $member->password));
        $this->assertNotEquals('S4ngatKuat!2026#Perpustakaan', $member->password);
    }

    /**
     * TST-11 / FR-01 / SEC-02: Registration ignores role injection from request.
     */
    public function test_registration_ignores_role_injection(): void
    {
        $response = $this->post('/registrasi', [
            'nama' => 'Attacker User',
            'email' => 'attacker@example.com',
            'password' => 'InjectedRole#Pass1234',
            'password_confirmation' => 'InjectedRole#Pass1234',
            'role' => 'admin',
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('anggota', [
            'email' => 'attacker@example.com',
            'role' => 'user',
        ]);

        $this->assertDatabaseMissing('anggota', [
            'email' => 'attacker@example.com',
            'role' => 'admin',
        ]);
    }

    /**
     * TST-11 / FR-01: Duplicate email is rejected.
     */
    public function test_duplicate_email_is_rejected(): void
    {
        Anggota::create([
            'nama' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => Hash::make('ExistingPassword#123'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->from('/registrasi')->post('/registrasi', [
            'nama' => 'Duplicate Email Attempt',
            'email' => 'EXISTING@example.com',
            'password' => 'DifferentPassword#123',
            'password_confirmation' => 'DifferentPassword#123',
        ]);

        $response->assertRedirect('/registrasi');
        $response->assertSessionHasErrors('email');
        $this->assertEquals(1, Anggota::where('email', 'existing@example.com')->count());
    }

    /**
     * TST-11 / SEC-02: Password under 12 characters is rejected.
     */
    public function test_password_under_12_characters_is_rejected(): void
    {
        $response = $this->from('/registrasi')->post('/registrasi', [
            'nama' => 'Short Pass User',
            'email' => 'shortpass@example.com',
            'password' => 'Short12345!', // 11 characters
            'password_confirmation' => 'Short12345!',
        ]);

        $response->assertRedirect('/registrasi');
        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('anggota', [
            'email' => 'shortpass@example.com',
        ]);
    }

    /**
     * TST-11 / SEC-02: Password over 72 bytes is rejected.
     */
    public function test_password_over_72_bytes_is_rejected(): void
    {
        // 73 ASCII characters = 73 bytes
        $asciiOver72Bytes = str_repeat('A', 73);

        $responseAscii = $this->from('/registrasi')->post('/registrasi', [
            'nama' => 'Long ASCII User',
            'email' => 'longascii@example.com',
            'password' => $asciiOver72Bytes,
            'password_confirmation' => $asciiOver72Bytes,
        ]);

        $responseAscii->assertRedirect('/registrasi');
        $responseAscii->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('anggota', [
            'email' => 'longascii@example.com',
        ]);

        // UTF-8 multibyte characters: 19 x 4 bytes = 76 bytes (> 72 byte limit)
        $utf8Over72Bytes = str_repeat('🔒', 19);

        $responseUtf8 = $this->from('/registrasi')->post('/registrasi', [
            'nama' => 'Long UTF8 User',
            'email' => 'longutf8@example.com',
            'password' => $utf8Over72Bytes,
            'password_confirmation' => $utf8Over72Bytes,
        ]);

        $responseUtf8->assertRedirect('/registrasi');
        $responseUtf8->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('anggota', [
            'email' => 'longutf8@example.com',
        ]);
    }

    /**
     * TST-11 / FR-01: Password confirmation must match.
     */
    public function test_password_confirmation_must_match(): void
    {
        $response = $this->from('/registrasi')->post('/registrasi', [
            'nama' => 'Mismatch Pass User',
            'email' => 'mismatch@example.com',
            'password' => 'PasswordSatu#1234',
            'password_confirmation' => 'PasswordDua#5678',
        ]);

        $response->assertRedirect('/registrasi');
        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('anggota', [
            'email' => 'mismatch@example.com',
        ]);
    }

    /**
     * TST-11 / SEC-02 / SEC-10: Registration requires CSRF token.
     */
    public function test_registration_requires_csrf(): void
    {
        $this->app->instance(
            ValidateCsrfToken::class,
            new class($this->app, $this->app->make('encrypter')) extends ValidateCsrfToken
            {
                protected function runningUnitTests(): bool
                {
                    return false;
                }
            }
        );

        $response = $this->post('/registrasi', [
            'nama' => 'No CSRF User',
            'email' => 'nocsrf@example.com',
            'password' => 'ValidPassword#1234',
            'password_confirmation' => 'ValidPassword#1234',
        ]);

        $response->assertStatus(419);
        $this->assertDatabaseMissing('anggota', [
            'email' => 'nocsrf@example.com',
        ]);
    }
}
