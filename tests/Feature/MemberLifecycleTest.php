<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Services\AesGcm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_view_member_index_and_filter(): void
    {
        $admin = Anggota::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.anggota.index'));

        $response->assertOk();
        $response->assertSee('Kelola Anggota Perpustakaan');
        $response->assertSee('User A');
        $response->assertSee('User B');
    }

    public function test_admin_can_search_members_by_name_or_email(): void
    {
        $admin = Anggota::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.anggota.index', ['q' => 'User A']));

        $response->assertOk();
        $response->assertSee('User A');
        $response->assertDontSee('User B');
    }

    public function test_admin_can_create_new_member_with_hashed_password_and_encrypted_phone(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $phoneInput = '081298765432';

        $response = $this->actingAs($admin)->post(route('admin.anggota.store'), [
            'nama' => 'Anggota Baru Admin',
            'email' => 'baru_admin@perpustakaan.test',
            'password' => 'secret_password_123',
            'telepon' => $phoneInput,
        ]);

        $response->assertRedirect(route('admin.anggota.index'));
        $response->assertSessionHas('success');

        $newMember = Anggota::where('email', 'baru_admin@perpustakaan.test')->first();
        $this->assertNotNull($newMember);
        $this->assertSame('user', $newMember->role);
        $this->assertTrue($newMember->is_active);

        // SEC-02: Password hashed with Bcrypt
        $this->assertTrue(Hash::check('secret_password_123', $newMember->password));

        // SEC-03: Phone stored encrypted, plaintext NOT in DB
        $rawRecord = DB::table('anggota')->where('id', $newMember->id)->first();
        $this->assertStringNotContainsString($phoneInput, $rawRecord->telepon_enc);

        $aes = app(AesGcm::class);
        $decrypted = $aes->decrypt($newMember->telepon_enc, "anggota:{$newMember->id}:telepon:v1");
        $this->assertSame($phoneInput, $decrypted);
    }

    public function test_admin_create_member_rejects_password_over_72_bytes(): void
    {
        $admin = Anggota::where('role', 'admin')->first();

        // 73 ASCII characters (73 bytes)
        $password73Bytes = str_repeat('a', 73);

        $response = $this->actingAs($admin)->post(route('admin.anggota.store'), [
            'nama' => 'User Over Bytes',
            'email' => 'overbytes@perpustakaan.test',
            'password' => $password73Bytes,
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('anggota', ['email' => 'overbytes@perpustakaan.test']);

        // Multibyte characters (e.g. 20 4-byte characters = 80 bytes > 72 bytes)
        $multibytePassword = str_repeat('🔒', 19); // 19 * 4 = 76 bytes > 72 bytes
        $responseMb = $this->actingAs($admin)->post(route('admin.anggota.store'), [
            'nama' => 'User Multibyte Over',
            'email' => 'multibyte_over@perpustakaan.test',
            'password' => $multibytePassword,
        ]);

        $responseMb->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('anggota', ['email' => 'multibyte_over@perpustakaan.test']);
    }

    public function test_admin_can_update_member_info_and_phone(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->first();

        $response = $this->actingAs($admin)->patch(route('admin.anggota.update', $userA), [
            'nama' => 'User A (Nama Baru)',
            'email' => 'user_a_baru@perpustakaan.test',
            'telepon' => '087711223344',
        ]);

        $response->assertRedirect(route('admin.anggota.edit', $userA));
        $response->assertSessionHas('success');

        $userA->refresh();
        $this->assertSame('User A (Nama Baru)', $userA->nama);
        $this->assertSame('user_a_baru@perpustakaan.test', $userA->email);

        $aes = app(AesGcm::class);
        $decrypted = $aes->decrypt($userA->telepon_enc, "anggota:{$userA->id}:telepon:v1");
        $this->assertSame('087711223344', $decrypted);
    }

    public function test_admin_can_deactivate_and_reactivate_member(): void
    {
        $admin = Anggota::where('role', 'admin')->first();
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->first();

        // 1. Deaktivasi
        $deactRes = $this->actingAs($admin)->patch(route('admin.anggota.status', $userA));
        $deactRes->assertRedirect();

        $userA->refresh();
        $this->assertFalse($userA->is_active);

        // Logout admin agar status menjadi guest untuk mencoba login
        auth()->logout();

        // User A nonaktif tidak bisa login
        $loginRes = $this->post(route('login.store'), [
            'email' => 'user_a@perpustakaan.test',
            'password' => 'password123456',
        ]);
        $loginRes->assertSessionHasErrors('email');

        // 2. Aktivasi kembali
        $actRes = $this->actingAs($admin)->patch(route('admin.anggota.status', $userA));
        $actRes->assertRedirect();

        $userA->refresh();
        $this->assertTrue($userA->is_active);
    }

    public function test_last_active_admin_cannot_be_deactivated(): void
    {
        $admin = Anggota::where('role', 'admin')->first();

        // Mencoba menonaktifkan akun admin satu-satunya
        $response = $this->actingAs($admin)->patch(route('admin.anggota.status', $admin));

        $response->assertSessionHasErrors('status');

        $admin->refresh();
        $this->assertTrue($admin->is_active);
    }

    public function test_regular_user_cannot_access_member_management_and_receives_403(): void
    {
        $userA = Anggota::where('email', 'user_a@perpustakaan.test')->first();

        $this->actingAs($userA)->get(route('admin.anggota.index'))->assertStatus(403);
        $this->actingAs($userA)->get(route('admin.anggota.create'))->assertStatus(403);
        $this->actingAs($userA)->post(route('admin.anggota.store'), [
            'nama' => 'Illegal Member',
            'email' => 'illegal@perpustakaan.test',
            'password' => 'secret123456',
        ])->assertStatus(403);
        $this->actingAs($userA)->patch(route('admin.anggota.status', $userA))->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_on_admin_member_endpoints(): void
    {
        $this->get(route('admin.anggota.index'))->assertRedirect(route('login'));
        $this->get(route('admin.anggota.create'))->assertRedirect(route('login'));
    }
}
