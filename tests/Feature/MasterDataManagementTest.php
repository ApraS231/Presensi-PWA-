<?php

namespace Tests\Feature;

use App\Models\FaceDescriptor;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterDataManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $admin;
    protected User $karyawan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->superadmin = User::where('role', 'superadmin')->first();
        $this->admin      = User::where('role', 'admin')->first();

        $this->karyawan = User::create([
            'nik'               => 'KAR101',
            'name'              => 'Sample Karyawan',
            'email'             => 'sample@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'department'        => 'Operasional',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);
    }

    public function test_superadmin_can_access_dashboard_and_master_modules(): void
    {
        $this->actingAs($this->superadmin)->get('/superadmin/dashboard')->assertStatus(200);
        $this->actingAs($this->superadmin)->get('/superadmin/users')->assertStatus(200);
        $this->actingAs($this->superadmin)->get('/superadmin/locations')->assertStatus(200);
        $this->actingAs($this->superadmin)->get('/superadmin/settings')->assertStatus(200);
    }

    public function test_superadmin_can_create_and_update_employee_user(): void
    {
        // 1. Create User
        $responseCreate = $this->actingAs($this->superadmin)->post('/superadmin/users', [
            'nik'        => 'KAR200',
            'name'       => 'Budi Baru',
            'email'      => 'budibaru@ptcak.com',
            'role'       => 'karyawan',
            'department' => 'Logistik',
            'jabatan'    => 'Staff Gudang',
            'no_telp'    => '0811223344',
        ]);

        $responseCreate->assertRedirect('/superadmin/users');
        $this->assertDatabaseHas('users', [
            'nik'               => 'KAR200',
            'name'              => 'Budi Baru',
            'email'             => 'budibaru@ptcak.com',
            'role'              => 'karyawan',
            'department'        => 'Logistik',
            'jabatan'           => 'Staff Gudang',
            'is_active'         => true,
            'enrollment_status' => 'pending',
        ]);

        $newUser = User::where('nik', 'KAR200')->first();
        $this->assertTrue(Hash::check('ptcak123', $newUser->password));

        // 2. Update User
        $responseUpdate = $this->actingAs($this->superadmin)->put("/superadmin/users/{$newUser->id}", [
            'nik'        => 'KAR200',
            'name'       => 'Budi Baru Senior',
            'email'      => 'budibaru@ptcak.com',
            'role'       => 'karyawan',
            'department' => 'Logistik & Distribusi',
            'jabatan'    => 'Supervisor',
            'no_telp'    => '0811223344',
        ]);

        $responseUpdate->assertRedirect('/superadmin/users');
        $this->assertDatabaseHas('users', [
            'id'         => $newUser->id,
            'name'       => 'Budi Baru Senior',
            'department' => 'Logistik & Distribusi',
            'jabatan'    => 'Supervisor',
        ]);
    }

    public function test_user_store_validates_unique_nik_and_email(): void
    {
        // Duplicate NIK
        $responseDuplicateNik = $this->actingAs($this->superadmin)->post('/superadmin/users', [
            'nik'        => 'KAR101', // Already belongs to sample karyawan
            'name'       => 'Duplicate NIK User',
            'email'      => 'unique@ptcak.com',
            'role'       => 'karyawan',
        ]);
        $responseDuplicateNik->assertSessionHasErrors('nik');

        // Duplicate Email
        $responseDuplicateEmail = $this->actingAs($this->superadmin)->post('/superadmin/users', [
            'nik'        => 'KAR999',
            'name'       => 'Duplicate Email User',
            'email'      => 'sample@ptcak.com', // Already belongs to sample karyawan
            'role'       => 'karyawan',
        ]);
        $responseDuplicateEmail->assertSessionHasErrors('email');
    }

    public function test_superadmin_can_toggle_user_active_status(): void
    {
        $this->assertTrue($this->karyawan->is_active);

        // Toggle to Inactive
        $this->actingAs($this->superadmin)->patch("/superadmin/users/{$this->karyawan->id}/toggle")
            ->assertRedirect();

        $this->karyawan->refresh();
        $this->assertFalse($this->karyawan->is_active);

        // Toggle back to Active
        $this->actingAs($this->superadmin)->patch("/superadmin/users/{$this->karyawan->id}/toggle")
            ->assertRedirect();

        $this->karyawan->refresh();
        $this->assertTrue($this->karyawan->is_active);

        // Superadmin cannot deactivate self
        $this->actingAs($this->superadmin)->patch("/superadmin/users/{$this->superadmin->id}/toggle")
            ->assertSessionHas('error');
    }

    public function test_superadmin_can_reset_employee_face_descriptor(): void
    {
        // Create descriptor for user
        FaceDescriptor::create([
            'user_id'         => $this->karyawan->id,
            'descriptor_data' => json_encode(array_fill(0, 128, 0.123)),
        ]);

        $this->assertEquals('enrolled', $this->karyawan->enrollment_status);
        $this->assertNotNull($this->karyawan->faceDescriptor);

        // Reset
        $this->actingAs($this->superadmin)->post("/superadmin/users/{$this->karyawan->id}/reset-face")
            ->assertRedirect();

        $this->karyawan->refresh();
        $this->assertEquals('pending', $this->karyawan->enrollment_status);
        $this->assertNull($this->karyawan->faceDescriptor);
    }

    public function test_superadmin_can_create_and_manage_locations_with_geofence(): void
    {
        // 1. Create Location
        $responseCreate = $this->actingAs($this->superadmin)->post('/superadmin/locations', [
            'name'          => 'Site Tambang Sangatta C',
            'latitude'      => 0.4900000,
            'longitude'     => 117.5400000,
            'radius_meters' => 150,
            'is_active'     => 1,
        ]);
        $responseCreate->assertRedirect('/superadmin/locations');

        $this->assertDatabaseHas('locations', [
            'name'          => 'Site Tambang Sangatta C',
            'radius_meters' => 150,
        ]);

        $location = Location::where('name', 'Site Tambang Sangatta C')->first();

        // 2. Update Location
        $this->actingAs($this->superadmin)->put("/superadmin/locations/{$location->id}", [
            'name'          => 'Site Tambang Sangatta C Revised',
            'latitude'      => 0.4910000,
            'longitude'     => 117.5410000,
            'radius_meters' => 200,
            'is_active'     => 1,
        ])->assertRedirect('/superadmin/locations');

        $this->assertDatabaseHas('locations', [
            'id'            => $location->id,
            'name'          => 'Site Tambang Sangatta C Revised',
            'radius_meters' => 200,
        ]);

        // 3. Toggle Status
        $this->actingAs($this->superadmin)->patch("/superadmin/locations/{$location->id}/toggle")
            ->assertRedirect();
        $location->refresh();
        $this->assertFalse($location->is_active);

        // 4. Delete
        $this->actingAs($this->superadmin)->delete("/superadmin/locations/{$location->id}")
            ->assertRedirect('/superadmin/locations');
        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    public function test_superadmin_can_update_system_settings_batch(): void
    {
        $response = $this->actingAs($this->superadmin)->post('/superadmin/settings/batch', [
            'settings' => [
                'jam_masuk'           => '07:30',
                'jam_pulang'          => '16:30',
                'toleransi_terlambat' => 20,
                'jam_istirahat'       => 45,
                'hari_kerja'          => 'senin,selasa,rabu,kamis,jumat,sabtu',
                'max_retroaktif_izin' => 5,
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals('07:30', Setting::getValue('jam_masuk'));
        $this->assertEquals('16:30', Setting::getValue('jam_pulang'));
        $this->assertEquals('20', Setting::getValue('toleransi_terlambat'));
        $this->assertEquals('45', Setting::getValue('jam_istirahat'));
        $this->assertEquals('senin,selasa,rabu,kamis,jumat,sabtu', Setting::getValue('hari_kerja'));
        $this->assertEquals('5', Setting::getValue('max_retroaktif_izin'));
    }

    public function test_non_superadmin_cannot_access_superadmin_routes(): void
    {
        // Admin biasa mencoba akses superadmin
        $this->actingAs($this->admin)->get('/superadmin/dashboard')->assertStatus(403);
        $this->actingAs($this->admin)->get('/superadmin/users')->assertStatus(403);
        $this->actingAs($this->admin)->get('/superadmin/settings')->assertStatus(403);

        // Karyawan mencoba akses superadmin
        $this->actingAs($this->karyawan)->get('/superadmin/dashboard')->assertStatus(403);
        $this->actingAs($this->karyawan)->get('/superadmin/users')->assertStatus(403);
        $this->actingAs($this->karyawan)->get('/superadmin/settings')->assertStatus(403);
    }
}
