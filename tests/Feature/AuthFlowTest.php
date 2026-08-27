<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->karyawan = User::create([
            'nik'               => 'KAR991',
            'name'              => 'Auth Test Karyawan',
            'email'             => 'authtest@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'department'        => 'Operasional',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);
    }

    public function test_user_can_logout_via_post(): void
    {
        $response = $this->actingAs($this->karyawan)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_user_can_logout_via_get(): void
    {
        $response = $this->actingAs($this->karyawan)->get('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_unauthenticated_user_can_access_logout_cleanly(): void
    {
        $response = $this->get('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
