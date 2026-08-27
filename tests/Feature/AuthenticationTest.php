<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Presensi PT. CAK');
    }

    public function test_user_can_login_using_nik(): void
    {
        $response = $this->post('/login', [
            'login'    => 'SA001',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('superadmin.dashboard'));
    }

    public function test_admin_hrd_can_login_and_redirects_to_admin_dashboard(): void
    {
        $response = $this->post('/login', [
            'login'    => 'ADM001',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_user_can_login_using_email(): void
    {
        $response = $this->post('/login', [
            'login'    => 'superadmin@ptcak.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('superadmin.dashboard'));
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'login'    => 'SA001',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::create([
            'nik'               => 'KAR099',
            'name'              => 'Karyawan Nonaktif',
            'email'             => 'nonaktif@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => false,
            'enrollment_status' => 'pending',
        ]);

        $response = $this->post('/login', [
            'login'    => 'KAR099',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_inactive_user_is_logged_out_by_middleware_on_subsequent_request(): void
    {
        $user = User::create([
            'nik'               => 'KAR001',
            'name'              => 'Karyawan Test',
            'email'             => 'karyawan@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        // Login saat masih aktif
        $this->actingAs($user);

        // Nonaktifkan user di database
        $user->update(['is_active' => false]);

        // Request ke rute terproteksi
        $response = $this->get('/karyawan/dashboard');

        // Harus dialihkan ke login dan sesi di-logout
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_karyawan_cannot_access_admin_or_superadmin_routes(): void
    {
        $karyawan = User::create([
            'nik'               => 'KAR002',
            'name'              => 'Staff Karyawan',
            'email'             => 'staff@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        $this->actingAs($karyawan);

        $adminResponse = $this->get('/admin/dashboard');
        $adminResponse->assertStatus(403);

        $superadminResponse = $this->get('/superadmin/dashboard');
        $superadminResponse->assertStatus(403);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::where('nik', 'SA001')->first();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_update_password(): void
    {
        $user = User::where('nik', 'SA001')->first();
        $this->actingAs($user);

        $response = $this->put('/password/update', [
            'current_password'      => 'password',
            'password'              => 'new-secret-password-123',
            'password_confirmation' => 'new-secret-password-123',
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('new-secret-password-123', $user->fresh()->password));
    }
}
