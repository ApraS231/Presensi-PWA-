<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\FaceDescriptor;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $enrolledKaryawan;
    protected User $pendingKaryawan;
    protected string $fakeBase64Photo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('public');

        $this->fakeBase64Photo = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        // 1. Karyawan Terdaftar
        $this->enrolledKaryawan = User::create([
            'nik'               => 'KAR001',
            'name'              => 'Budi Santoso',
            'email'             => 'budi@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        FaceDescriptor::create([
            'user_id'         => $this->enrolledKaryawan->id,
            'descriptor_data' => array_fill(0, 128, 0.1),
            'sample_photo'    => 'faces/sample_budi.jpg',
        ]);

        // 2. Karyawan Belum Terdaftar
        $this->pendingKaryawan = User::create([
            'nik'               => 'KAR002',
            'name'              => 'Siti Rahma',
            'email'             => 'siti@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'pending',
        ]);
    }

    public function test_pending_employee_cannot_access_presensi_page(): void
    {
        $response = $this->actingAs($this->pendingKaryawan)->get('/karyawan/presensi');
        $response->assertRedirect(route('karyawan.enrollment'));
    }

    public function test_enrolled_employee_can_view_presensi_page(): void
    {
        $response = $this->actingAs($this->enrolledKaryawan)->get('/karyawan/presensi');
        $response->assertStatus(200);
        $response->assertSee('Perekaman Kehadiran');
        $response->assertSee('face-api.min.js');
    }

    public function test_employee_can_check_in_on_time_within_geofence(): void
    {
        // Set waktu simulasi ke 08:05 WITA (Tepat waktu, toleransi s/d 08:15)
        Carbon::setTestNow(Carbon::create(2026, 8, 25, 8, 5, 0, 'Asia/Makassar'));

        $response = $this->actingAs($this->enrolledKaryawan)->postJson('/karyawan/presensi/check-in', [
            'latitude'  => -0.1333000,
            'longitude' => 117.4833000,
            'photo'     => $this->fakeBase64Photo,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status'  => 'tepat_waktu',
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->enrolledKaryawan->id,
            'status'  => 'tepat_waktu',
        ]);

        $attendance = Attendance::where('user_id', $this->enrolledKaryawan->id)->first();
        $this->assertNotNull($attendance->photo_in);
        Storage::disk('public')->assertExists($attendance->photo_in);

        // Verifikasi notifikasi terkirim
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->enrolledKaryawan->id,
            'title'   => 'Presensi Masuk Berhasil',
        ]);
    }

    public function test_employee_check_in_marked_as_late_when_past_tolerance(): void
    {
        // Set waktu simulasi ke 08:25 WITA (Terlambat, toleransi berakhir 08:15)
        Carbon::setTestNow(Carbon::create(2026, 8, 25, 8, 25, 0, 'Asia/Makassar'));

        $response = $this->actingAs($this->enrolledKaryawan)->postJson('/karyawan/presensi/check-in', [
            'latitude'  => -0.1333000,
            'longitude' => 117.4833000,
            'photo'     => $this->fakeBase64Photo,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status'  => 'terlambat',
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->enrolledKaryawan->id,
            'status'  => 'terlambat',
        ]);
    }

    public function test_check_in_rejected_when_outside_geofence_radius(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 25, 8, 0, 0, 'Asia/Makassar'));

        // Koordinat berada ~5 km di luar area kantor
        $response = $this->actingAs($this->enrolledKaryawan)->postJson('/karyawan/presensi/check-in', [
            'latitude'  => -0.2000000,
            'longitude' => 117.4833000,
            'photo'     => $this->fakeBase64Photo,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertDatabaseMissing('attendances', [
            'user_id' => $this->enrolledKaryawan->id,
        ]);
    }

    public function test_check_in_rejected_if_already_checked_in_today(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 25, 8, 0, 0, 'Asia/Makassar'));

        // Check-in pertama
        $this->actingAs($this->enrolledKaryawan)->postJson('/karyawan/presensi/check-in', [
            'latitude'  => -0.1333000,
            'longitude' => 117.4833000,
            'photo'     => $this->fakeBase64Photo,
        ]);

        // Check-in kedua pada hari yang sama
        $responseSecond = $this->actingAs($this->enrolledKaryawan)->postJson('/karyawan/presensi/check-in', [
            'latitude'  => -0.1333000,
            'longitude' => 117.4833000,
            'photo'     => $this->fakeBase64Photo,
        ]);

        $responseSecond->assertStatus(422);
        $responseSecond->assertJson([
            'success' => false,
            'message' => 'Anda sudah melakukan presensi masuk untuk hari ini.',
        ]);
    }

    public function test_employee_can_check_out_successfully(): void
    {
        // Simulasi jam pulang 17:05 WITA
        Carbon::setTestNow(Carbon::create(2026, 8, 25, 17, 5, 0, 'Asia/Makassar'));

        $today = Carbon::today('Asia/Makassar')->format('Y-m-d');
        $location = Location::first();

        // Buat presensi masuk sebelumnya
        Attendance::create([
            'user_id'         => $this->enrolledKaryawan->id,
            'location_id'     => $location->id,
            'date'            => $today,
            'time_in'         => '08:00:00',
            'lat_in'          => -0.1333000,
            'long_in'         => 117.4833000,
            'photo_in'        => 'attendance/in_sample.jpg',
            'status'          => 'tepat_waktu',
            'distance_meters' => 10.5,
        ]);

        $response = $this->actingAs($this->enrolledKaryawan)->postJson('/karyawan/presensi/check-out', [
            'latitude'  => -0.1333000,
            'longitude' => 117.4833000,
            'photo'     => $this->fakeBase64Photo,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $attendance = Attendance::where('user_id', $this->enrolledKaryawan->id)->where('date', $today)->first();
        $this->assertNotNull($attendance->time_out);
        $this->assertNotNull($attendance->photo_out);
        Storage::disk('public')->assertExists($attendance->photo_out);

        // Verifikasi notifikasi pulang
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->enrolledKaryawan->id,
            'title'   => 'Presensi Pulang Berhasil',
        ]);
    }

    public function test_check_out_rejected_if_not_checked_in_yet(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 25, 17, 0, 0, 'Asia/Makassar'));

        $response = $this->actingAs($this->enrolledKaryawan)->postJson('/karyawan/presensi/check-out', [
            'latitude'  => -0.1333000,
            'longitude' => 117.4833000,
            'photo'     => $this->fakeBase64Photo,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Anda belum melakukan presensi masuk untuk hari ini.',
        ]);
    }

    public function test_completed_attendance_displays_completed_view(): void
    {
        $today = Carbon::today();
        $location = Location::first();

        // Presensi lengkap masuk & pulang
        Attendance::create([
            'user_id'         => $this->enrolledKaryawan->id,
            'location_id'     => $location->id,
            'date'            => $today,
            'time_in'         => '07:55:00',
            'time_out'        => '17:05:00',
            'lat_in'          => -0.1333000,
            'long_in'         => 117.4833000,
            'lat_out'         => -0.1333000,
            'long_out'        => 117.4833000,
            'photo_in'        => 'attendance/in_sample.jpg',
            'photo_out'       => 'attendance/out_sample.jpg',
            'status'          => 'tepat_waktu',
            'distance_meters' => 5.0,
        ]);

        $response = $this->actingAs($this->enrolledKaryawan)->get('/karyawan/presensi');
        $response->assertStatus(200);
        $response->assertSee('Presensi Hari Ini Selesai');
        $response->assertSee('07:55:00');
        $response->assertSee('17:05:00');
    }
}
