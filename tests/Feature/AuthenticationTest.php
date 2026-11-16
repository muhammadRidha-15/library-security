<?php

namespace Tests\Feature;

use App\Models\Anggota;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TST-12 / FR-02: Login screen can be rendered.
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Akun');
        $response->assertSee('email');
        $response->assertSee('password');
        $response->assertSee(route('register'));
    }

    /**
     * TST-10b / TST-12 / FR-02 / SEC-02 / SEC-10:
     * User can login with correct credentials and session ID is regenerated.
     */
    public function test_user_can_login_with_correct_credentials_and_session_regenerated(): void
    {
        $user = Anggota::factory()->create([
            'email' => 'user.anggota@example.com',
            'password' => Hash::make('UserPassword#123'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->get('/login');
        $initialSessionId = session()->getId();

        $response = $this->post('/login', [
            'email' => ' USER.ANGGOTA@EXAMPLE.COM ',
            'password' => 'UserPassword#123',
        ]);

        $response->assertRedirect('/buku');
        $this->assertAuthenticatedAs($user);
        $this->assertNotEquals($initialSessionId, session()->getId());
    }

    /**
     * TST-12 / FR-02: Admin can login with correct credentials and redirect to admin area.
     */
    public function test_admin_can_login_with_correct_credentials(): void
    {
        $admin = Anggota::factory()->admin()->create([
            'email' => 'admin.utama@example.com',
            'password' => Hash::make('AdminPassword#123'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin.utama@example.com',
            'password' => 'AdminPassword#123',
        ]);

        $response->assertRedirect('/admin/buku');
        $this->assertAuthenticatedAs($admin);
    }

    /**
     * TST-12 / SEC-02 / SEC-10: Login fails with invalid password and returns generic error.
     */
    public function test_login_fails_with_invalid_password_returns_generic_error(): void
    {
        $user = Anggota::factory()->create([
            'email' => 'target.user@example.com',
            'password' => Hash::make('PasswordValid#123'),
            'is_active' => true,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'target.user@example.com',
            'password' => 'SalahPassword#999',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'Email atau password salah.']);
        $this->assertGuest();
    }

    /**
     * TST-12 / SEC-10: Inactive account cannot login and returns generic error without leaking status.
     */
    public function test_inactive_account_cannot_login_and_returns_generic_error(): void
    {
        $user = Anggota::factory()->inactive()->create([
            'email' => 'inactive.account@example.com',
            'password' => Hash::make('PasswordValid#123'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'inactive.account@example.com',
            'password' => 'PasswordValid#123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'Email atau password salah.']);
        $this->assertGuest();
    }

    /**
     * TST-10d / TST-12 / SEC-10: Login is rate limited to 5 attempts and returns 429 on sixth attempt.
     */
    public function test_login_is_rate_limited_and_returns_429_on_sixth_attempt(): void
    {
        $email = 'bruteforce.target@example.com';

        for ($i = 1; $i <= 5; $i++) {
            $response = $this->post('/login', [
                'email' => $email,
                'password' => 'WrongAttempt#'.$i,
            ]);
            $response->assertSessionHasErrors('email');
        }

        $sixthResponse = $this->post('/login', [
            'email' => $email,
            'password' => 'WrongAttempt#6',
        ]);

        $sixthResponse->assertStatus(429);
    }

    /**
     * TST-10c / TST-12 / FR-03 / SEC-10: User can logout and session is invalidated.
     */
    public function test_user_can_logout_and_session_is_invalidated(): void
    {
        $user = Anggota::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['custom_session_item' => 'active_secret'])
            ->post('/logout');

        $response->assertRedirect('/login');
        $response->assertSessionHas('success', 'Anda telah berhasil keluar.');
        $this->assertGuest();
        $this->assertFalse(session()->has('custom_session_item'));
    }

    /**
     * TST-10d / SEC-10: Logout requires CSRF token.
     */
    public function test_logout_requires_csrf(): void
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

        $user = Anggota::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertStatus(419);
        $this->assertAuthenticatedAs($user);
    }

    /**
     * TST-12 / FR-03: Guest cannot logout and is redirected to login.
     */
    public function test_guest_cannot_logout(): void
    {
        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
