<?php

namespace Tests\Feature;

use App\Models\FaceDescriptor;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EnrollmentBiometricTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('public');
    }

    public function test_karyawan_can_view_enrollment_page(): void
    {
        $karyawan = User::create([
            'nik'               => 'KAR001',
            'name'              => 'Ahmad Dani',
            'email'             => 'ahmad@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'pending',
        ]);

        $response = $this->actingAs($karyawan)->get('/karyawan/enrollment');
        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Wajah');
        $response->assertSee('face-api.min.js');
    }

    public function test_karyawan_can_store_face_descriptor_and_update_status(): void
    {
        $karyawan = User::create([
            'nik'               => 'KAR002',
            'name'              => 'Bambang Sudiro',
            'email'             => 'bambang@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'pending',
        ]);

        $fakeDescriptor = array_fill(0, 128, 0.054321);
        // Base64 1x1 transparent PNG
        $fakeBase64Photo = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($karyawan)->postJson('/karyawan/enrollment', [
            'descriptor_data' => $fakeDescriptor,
            'sample_photo'    => $fakeBase64Photo,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verifikasi database & status
        $this->assertEquals('enrolled', $karyawan->fresh()->enrollment_status);
        $this->assertDatabaseHas('face_descriptors', [
            'user_id' => $karyawan->id,
        ]);

        $descriptor = FaceDescriptor::where('user_id', $karyawan->id)->first();
        $this->assertNotNull($descriptor);
        $this->assertCount(128, $descriptor->descriptor_data);
        Storage::disk('public')->assertExists($descriptor->sample_photo);
    }

    public function test_store_enrollment_validates_descriptor_format(): void
    {
        $karyawan = User::create([
            'nik'               => 'KAR003',
            'name'              => 'Citra Kirana',
            'email'             => 'citra@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'pending',
        ]);

        // Hanya 10 elemen, bukan 128
        $invalidDescriptor = array_fill(0, 10, 0.123);

        $response = $this->actingAs($karyawan)->postJson('/karyawan/enrollment', [
            'descriptor_data' => $invalidDescriptor,
            'sample_photo'    => 'data:image/jpeg;base64,abc',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('descriptor_data');
    }

    public function test_check_enrolled_middleware_blocks_pending_user(): void
    {
        // Daftarkan rute simulasi transaksi presensi yang dilindungi middleware enrolled
        Route::get('/test-attendance-check', function () {
            return response()->json(['status' => 'allowed']);
        })->middleware(['web', 'auth', 'active', 'enrolled']);

        $pendingKaryawan = User::create([
            'nik'               => 'KAR004',
            'name'              => 'Dedi Mulyadi',
            'email'             => 'dedi@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'pending',
        ]);

        // Pending user dialihkan
        $response = $this->actingAs($pendingKaryawan)->get('/test-attendance-check');
        $response->assertRedirect(route('karyawan.enrollment'));

        // Enrolled user diizinkan
        $pendingKaryawan->update(['enrollment_status' => 'enrolled']);
        $response2 = $this->actingAs($pendingKaryawan)->get('/test-attendance-check');
        $response2->assertStatus(200);
        $response2->assertJson(['status' => 'allowed']);
    }

    public function test_admin_can_view_enrollment_list(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/enrollment');
        $response->assertStatus(200);
        $response->assertSee('Status Pendaftaran Biometrik Karyawan');
    }

    public function test_admin_can_reset_employee_enrollment(): void
    {
        $admin = User::where('role', 'admin')->first();
        $karyawan = User::create([
            'nik'               => 'KAR005',
            'name'              => 'Eko Prasetyo',
            'email'             => 'eko@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        FaceDescriptor::create([
            'user_id'         => $karyawan->id,
            'descriptor_data' => array_fill(0, 128, 0.5),
            'sample_photo'    => 'faces/sample_test.jpg',
        ]);

        $response = $this->actingAs($admin)->post("/admin/enrollment/{$karyawan->id}/reset");
        $response->assertRedirect();

        // Status kembali pending dan record descriptor terhapus
        $this->assertEquals('pending', $karyawan->fresh()->enrollment_status);
        $this->assertDatabaseMissing('face_descriptors', ['user_id' => $karyawan->id]);

        // Notifikasi terkirim ke karyawan
        $this->assertDatabaseHas('notifications', [
            'user_id' => $karyawan->id,
            'type'    => 'enrollment_required',
        ]);
    }
}
