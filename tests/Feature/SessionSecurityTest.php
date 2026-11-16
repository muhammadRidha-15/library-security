<?php

namespace Tests\Feature;

use App\Models\Anggota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SessionSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->group(function () {
            Route::get('/_test/admin', fn () => 'admin area')->middleware(['auth', 'active', 'role:admin']);
            Route::get('/_test/user', fn () => 'user area')->middleware(['auth', 'active', 'role:user']);
            Route::get('/_test/protected', fn () => 'protected area')->middleware(['auth', 'active']);
        });
    }

    /**
     * TST-10c / TST-12: Guest is redirected to login when accessing protected route.
     */
    public function test_guest_redirected_to_login_when_accessing_protected_route(): void
    {
        $response = $this->get('/_test/protected');

        $response->assertRedirect('/login');
    }

    /**
     * TST-10c / TST-12: User cannot access admin route and receives 403 Forbidden.
     */
    public function test_user_cannot_access_admin_route_and_receives_403(): void
    {
        $user = Anggota::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/_test/admin');

        $response->assertStatus(403);
    }

    /**
     * TST-10c: Admin can access admin route.
     */
    public function test_admin_can_access_admin_route(): void
    {
        $admin = Anggota::factory()->admin()->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/_test/admin');

        $response->assertStatus(200);
        $response->assertSee('admin area');
    }

    /**
     * TST-10c: User can access user route.
     */
    public function test_user_can_access_user_route(): void
    {
        $user = Anggota::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/_test/user');

        $response->assertStatus(200);
        $response->assertSee('user area');
    }

    /**
     * TST-10d / FR-12 / SEC-10: Inactive account with session is logged out and redirected.
     */
    public function test_inactive_account_with_session_is_logged_out_and_redirected(): void
    {
        $user = Anggota::factory()->create([
            'role' => 'user',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get('/_test/protected');

        $response->assertRedirect('/login');
        $response->assertSessionHas('error', 'Akun Anda telah dinonaktifkan.');
        $this->assertGuest();
    }

    /**
     * TST-17 / SEC-10: Session configuration has HttpOnly=true and SameSite=lax.
     */
    public function test_session_configuration_has_httponly_and_samesite_lax(): void
    {
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', strtolower(config('session.same_site')));

        $response = $this->get('/login');
        $response->assertStatus(200);

        $cookies = $response->headers->getCookies();
        $sessionCookie = null;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === config('session.cookie')) {
                $sessionCookie = $cookie;
                break;
            }
        }

        if ($sessionCookie) {
            $this->assertTrue($sessionCookie->isHttpOnly());
            $this->assertSame('lax', strtolower($sessionCookie->getSameSite()));
        }
    }
}
