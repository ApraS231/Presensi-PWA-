<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EndToEndUatScenarioTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $admin;
    protected Location $office;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->superadmin = User::where('role', 'superadmin')->first();
        $this->admin      = User::where('role', 'admin')->first();
        $this->office     = Location::first();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * UAT-SC-01: Skenario Onboarding Karyawan Baru & Enrollment Wajah.
     */
    public function test_uat_01_onboarding_and_enrollment_workflow(): void
    {
        // 1. Super Admin membuat user baru
        $responseCreate = $this->actingAs($this->superadmin)->post('/superadmin/users', [
            'nik'        => 'KAR777',
            'name'       => 'Andi Karyawan Baru',
            'email'      => 'andi@ptcak.com',
            'role'       => 'karyawan',
            'department' => 'Operasional Tambang',
            'jabatan'    => 'Field Technician',
            'no_telp'    => '0812998877',
        ]);
        $responseCreate->assertRedirect('/superadmin/users');

        $user = User::where('nik', 'KAR777')->first();
        $this->assertNotNull($user);
        $this->assertEquals('pending', $user->enrollment_status);

        // 2. Karyawan login dan mencoba akses presensi -> dialihkan ke enrollment
        $responsePresensiBlocked = $this->actingAs($user)->get('/karyawan/presensi');
        $responsePresensiBlocked->assertRedirect('/karyawan/enrollment');

        // 3. Karyawan melakukan enrollment wajah (128-float descriptor)
        $descriptor = array_fill(0, 128, 0.25);
        $responseEnroll = $this->actingAs($user)->post('/karyawan/enrollment', [
            'descriptor_data' => $descriptor,
            'sample_photo'    => 'data:image/jpeg;base64,' . base64_encode('dummy_face_image'),
        ]);
        $responseEnroll->assertRedirect('/karyawan/dashboard');

        $user->refresh();
        $this->assertEquals('enrolled', $user->enrollment_status);
        $this->assertNotNull($user->faceDescriptor);

        // 4. Karyawan sekarang dapat membuka halaman presensi
        $responsePresensiSuccess = $this->actingAs($user)->get('/karyawan/presensi');
        $responsePresensiSuccess->assertStatus(200);
        $responsePresensiSuccess->assertSee('Presensi Harian');
    }

    /**
     * UAT-SC-02: Skenario Presensi Masuk Valid di Dalam Geofence.
     */
    public function test_uat_02_presensi_masuk_valid_within_geofence(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 25, 7, 55, 0, 'Asia/Makassar'));

        $user = User::create([
            'nik'               => 'KAR888',
            'name'              => 'Bima Karyawan',
            'email'             => 'bima@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        // Koordinat tepat di kantor pada pukul 07:55 WITA
        $responseCheckIn = $this->actingAs($user)->postJson('/karyawan/presensi/check-in', [
            'latitude'    => $this->office->latitude,
            'longitude'   => $this->office->longitude,
            'location_id' => $this->office->id,
            'photo'       => 'data:image/jpeg;base64,' . base64_encode('photo_in'),
        ]);

        $responseCheckIn->assertStatus(200);
        $responseCheckIn->assertJson([
            'success' => true,
            'status'  => 'tepat_waktu',
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id'     => $user->id,
            'location_id' => $this->office->id,
            'status'      => 'tepat_waktu',
        ]);
    }

    /**
     * UAT-SC-03: Skenario Penolakan Presensi di Luar Radius Geofence.
     */
    public function test_uat_03_presensi_masuk_rejected_outside_geofence(): void
    {
        $user = User::create([
            'nik'               => 'KAR889',
            'name'              => 'Candra Karyawan',
            'email'             => 'candra@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        // Koordinat 10 km di luar radius kantor
        $responseCheckIn = $this->actingAs($user)->postJson('/karyawan/presensi/check-in', [
            'latitude'    => $this->office->latitude + 0.1,
            'longitude'   => $this->office->longitude + 0.1,
            'location_id' => $this->office->id,
            'photo'       => 'data:image/jpeg;base64,' . base64_encode('photo_in'),
        ]);

        $responseCheckIn->assertStatus(422);
        $responseCheckIn->assertJson([
            'success' => false,
        ]);

        $today = Carbon::today('Asia/Makassar')->format('Y-m-d');
        $this->assertDatabaseMissing('attendances', [
            'user_id' => $user->id,
            'date'    => $today,
        ]);
    }

    /**
     * UAT-SC-04: Skenario Presensi Pulang Normal & Auto-Checkout via Scheduler.
     */
    public function test_uat_04_presensi_checkout_and_daily_scheduler_auto_checkout(): void
    {
        $today = Carbon::today('Asia/Makassar')->format('Y-m-d');

        // 1. User Normal Checkout
        $userNormal = User::create([
            'nik'               => 'KAR890',
            'name'              => 'Dedi Normal',
            'email'             => 'dedi@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);
        $attNormal = Attendance::create([
            'user_id'     => $userNormal->id,
            'location_id' => $this->office->id,
            'date'        => $today,
            'time_in'     => '07:55:00',
            'status'      => 'tepat_waktu',
        ]);

        $responseCheckOut = $this->actingAs($userNormal)->postJson('/karyawan/presensi/check-out', [
            'latitude'    => $this->office->latitude,
            'longitude'   => $this->office->longitude,
            'location_id' => $this->office->id,
            'photo'       => 'data:image/jpeg;base64,' . base64_encode('photo_out'),
        ]);

        $responseCheckOut->assertStatus(200);
        $responseCheckOut->assertJson(['success' => true]);

        $attNormal->refresh();
        $this->assertNotNull($attNormal->time_out);
        $this->assertFalse($attNormal->auto_checkout);

        // 2. User Lupa Checkout -> Auto-Checkout Scheduler
        $userLupa = User::create([
            'nik'               => 'KAR891',
            'name'              => 'Eka Lupa Pulang',
            'email'             => 'eka@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);
        $attLupa = Attendance::create([
            'user_id'     => $userLupa->id,
            'location_id' => $this->office->id,
            'date'        => $today,
            'time_in'     => '08:00:00',
            'time_out'    => null,
            'status'      => 'tepat_waktu',
        ]);

        Artisan::call('attendance:auto-checkout');

        $attLupa->refresh();
        $this->assertNotNull($attLupa->time_out);
        $this->assertTrue($attLupa->auto_checkout);
    }

    /**
     * UAT-SC-05: Skenario Perizinan Multi-Hari & Penghindaran Status Alpha.
     */
    public function test_uat_05_leave_approval_generates_attendances_and_prevents_alpha(): void
    {
        $user = User::create([
            'nik'               => 'KAR892',
            'name'              => 'Fajar Izin',
            'email'             => 'fajar@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        // Tanggal Senin s/d Rabu minggu depan
        $startMonday = Carbon::now('Asia/Makassar')->next(Carbon::MONDAY);
        $endWednesday = (clone $startMonday)->addDays(2);

        $leave = Leave::create([
            'user_id'         => $user->id,
            'type'            => 'cuti',
            'start_date'      => $startMonday->toDateString(),
            'end_date'        => $endWednesday->toDateString(),
            'total_days'      => 3,
            'reason'          => 'Cuti tahunan keluarga',
            'status'          => 'pending',
        ]);

        // HRD Menyetujui Pengajuan Cuti
        $responseApprove = $this->from('/admin/leaves')->actingAs($this->admin)->post("/admin/leaves/{$leave->id}/process", [
            'action'      => 'approve',
            'review_note' => 'Disetujui oleh HRD',
        ]);
        $responseApprove->assertRedirect('/admin/leaves');

        $leave->refresh();
        $this->assertEquals('approved', $leave->status);

        // Memastikan 3 record attendance tercipta dengan status 'cuti'
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date'    => $startMonday->toDateString(),
            'status'  => 'cuti',
        ]);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date'    => (clone $startMonday)->addDay()->toDateString(),
            'status'  => 'cuti',
        ]);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date'    => $endWednesday->toDateString(),
            'status'  => 'cuti',
        ]);
    }

    /**
     * UAT-SC-06: Skenario Rekapitulasi Presensi & Ekspor Laporan Excel/PDF.
     */
    public function test_uat_06_report_recap_and_export_calculations(): void
    {
        // 1. Akses Halaman Laporan
        $responseReport = $this->actingAs($this->admin)->get('/admin/reports');
        $responseReport->assertStatus(200);
        $responseReport->assertSee('Rekapitulasi Laporan Presensi Karyawan');

        // 2. Ekspor Excel
        $responseExcel = $this->actingAs($this->admin)->get('/admin/reports/export/excel');
        $responseExcel->assertStatus(200);
        $responseExcel->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // 3. Ekspor PDF
        $responsePdf = $this->actingAs($this->admin)->get('/admin/reports/export/pdf');
        $responsePdf->assertStatus(200);
        $responsePdf->assertHeader('content-type', 'application/pdf');
    }

    /**
     * UAT-SC-07: Skenario Keamanan Deaktivasi Akun & Pemutusan Sesi Seketika.
     */
    public function test_uat_07_security_deactivated_user_immediate_session_revocation(): void
    {
        $user = User::create([
            'nik'               => 'KAR893',
            'name'              => 'Gani Deactivated',
            'email'             => 'gani@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        // User login aktif
        $this->actingAs($user)->get('/karyawan/dashboard')->assertStatus(200);

        // Superadmin menonaktifkan akun Gani
        $this->actingAs($this->superadmin)->patch("/superadmin/users/{$user->id}/toggle")
            ->assertRedirect();

        $user->refresh();
        $this->assertFalse($user->is_active);

        // Request berikutnya oleh user langsung di-logout dan diarahkan ke login
        $responseBlocked = $this->actingAs($user)->get('/karyawan/dashboard');
        $responseBlocked->assertRedirect('/login');
        $responseBlocked->assertSessionHasErrors('login');
    }
}
