<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $karyawan;
    protected User $karyawanB;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->karyawan = User::factory()->create([
            'nik'               => 'KAR001',
            'name'              => 'Budi Santoso',
            'email'             => 'budi.santoso@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'department'        => 'Operasional',
            'jabatan'           => 'Staff Lapangan',
            'no_telp'           => '081234567890',
            'enrollment_status' => 'enrolled',
            'is_active'         => true,
        ]);

        $this->karyawanB = User::factory()->create([
            'nik'               => 'KAR002',
            'name'              => 'Siti Rahma',
            'email'             => 'siti.rahma@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
        ]);

        $this->admin = User::factory()->create([
            'nik'               => 'ADM001',
            'name'              => 'HRD Admin',
            'email'             => 'hrd@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'admin',
            'is_active'         => true,
        ]);
    }

    public function test_employee_can_access_profile_page(): void
    {
        $response = $this->actingAs($this->karyawan)->get('/karyawan/profile');

        $response->assertStatus(200);
        $response->assertSee('Profil Karyawan');
        $response->assertSee('Budi Santoso');
        $response->assertSee('KAR001');
        $response->assertSee('Operasional');
        $response->assertSee('Staff Lapangan');
        $response->assertSee('081234567890');
    }

    public function test_employee_can_update_personal_information(): void
    {
        $response = $this->actingAs($this->karyawan)->put('/karyawan/profile', [
            'name'    => 'Budi Santoso S.T.',
            'email'   => 'budi.santoso.new@ptcak.com',
            'no_telp' => '089876543210',
        ]);

        $response->assertRedirect('/karyawan/profile');
        $response->assertSessionHas('success');

        $this->karyawan->refresh();
        $this->assertEquals('Budi Santoso S.T.', $this->karyawan->name);
        $this->assertEquals('budi.santoso.new@ptcak.com', $this->karyawan->email);
        $this->assertEquals('089876543210', $this->karyawan->no_telp);
    }

    public function test_employee_cannot_update_with_duplicate_email(): void
    {
        $response = $this->actingAs($this->karyawan)->put('/karyawan/profile', [
            'name'    => 'Budi Santoso',
            'email'   => 'siti.rahma@ptcak.com', // Email milik Karyawan B
            'no_telp' => '081234567890',
        ]);

        $response->assertSessionHasErrors('email');

        $this->karyawan->refresh();
        $this->assertEquals('budi.santoso@ptcak.com', $this->karyawan->email);
    }

    public function test_employee_can_keep_own_email_when_updating(): void
    {
        $response = $this->actingAs($this->karyawan)->put('/karyawan/profile', [
            'name'    => 'Budi Santoso Update',
            'email'   => 'budi.santoso@ptcak.com', // Email sendiri
            'no_telp' => '08111222333',
        ]);

        $response->assertRedirect('/karyawan/profile');
        $response->assertSessionHas('success');

        $this->karyawan->refresh();
        $this->assertEquals('Budi Santoso Update', $this->karyawan->name);
    }

    public function test_employee_cannot_modify_protected_fields(): void
    {
        $response = $this->actingAs($this->karyawan)->put('/karyawan/profile', [
            'name'              => 'Budi Hack',
            'email'             => 'budi.santoso@ptcak.com',
            'nik'               => 'HACKED_NIK',
            'role'              => 'superadmin',
            'department'        => 'Direksi',
            'enrollment_status' => 'pending',
        ]);

        $this->karyawan->refresh();
        $this->assertEquals('KAR001', $this->karyawan->nik);
        $this->assertEquals('karyawan', $this->karyawan->role);
        $this->assertEquals('Operasional', $this->karyawan->department);
        $this->assertEquals('enrolled', $this->karyawan->enrollment_status);
    }

    public function test_employee_can_upload_and_replace_avatar(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('my_avatar.jpg', 300, 300);

        $response = $this->actingAs($this->karyawan)->put('/karyawan/profile', [
            'name'   => 'Budi Santoso',
            'email'  => 'budi.santoso@ptcak.com',
            'avatar' => $file,
        ]);

        $response->assertRedirect('/karyawan/profile');
        $response->assertSessionHas('success');

        $this->karyawan->refresh();
        $this->assertNotNull($this->karyawan->avatar);
        Storage::disk('public')->assertExists($this->karyawan->avatar);

        // Upload avatar kedua untuk memastikan avatar lama terhapus
        $oldAvatar = $this->karyawan->avatar;
        $newFile = UploadedFile::fake()->image('my_avatar_2.png', 400, 400);

        $this->actingAs($this->karyawan)->put('/karyawan/profile', [
            'name'   => 'Budi Santoso',
            'email'  => 'budi.santoso@ptcak.com',
            'avatar' => $newFile,
        ]);

        $this->karyawan->refresh();
        Storage::disk('public')->assertMissing($oldAvatar);
        Storage::disk('public')->assertExists($this->karyawan->avatar);
    }

    public function test_employee_can_remove_avatar(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('test_avatar.jpg')->store('avatars', 'public');
        $this->karyawan->update(['avatar' => $path]);

        $response = $this->actingAs($this->karyawan)->put('/karyawan/profile', [
            'name'          => 'Budi Santoso',
            'email'         => 'budi.santoso@ptcak.com',
            'remove_avatar' => 1,
        ]);

        $response->assertRedirect('/karyawan/profile');
        $this->karyawan->refresh();
        $this->assertNull($this->karyawan->avatar);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_employee_can_change_password_with_valid_current_password(): void
    {
        $response = $this->actingAs($this->karyawan)->put('/karyawan/profile/password', [
            'current_password'      => 'password',
            'password'              => 'new_password123',
            'password_confirmation' => 'new_password123',
        ]);

        $response->assertRedirect('/karyawan/profile');
        $response->assertSessionHas('success');

        $this->karyawan->refresh();
        $this->assertTrue(Hash::check('new_password123', $this->karyawan->password));
    }

    public function test_employee_cannot_change_password_with_invalid_current_password(): void
    {
        $response = $this->actingAs($this->karyawan)->put('/karyawan/profile/password', [
            'current_password'      => 'wrong_old_password',
            'password'              => 'new_password123',
            'password_confirmation' => 'new_password123',
        ]);

        $response->assertSessionHasErrors('current_password');

        $this->karyawan->refresh();
        $this->assertTrue(Hash::check('password', $this->karyawan->password));
    }

    public function test_guest_cannot_access_profile(): void
    {
        $response = $this->get('/karyawan/profile');
        $response->assertRedirect('/login');
    }

    public function test_non_karyawan_cannot_access_karyawan_profile(): void
    {
        $response = $this->actingAs($this->admin)->get('/karyawan/profile');
        $response->assertStatus(403);
    }
}
