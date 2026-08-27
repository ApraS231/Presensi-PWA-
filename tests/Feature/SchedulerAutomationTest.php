<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SchedulerAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected User $karyawan1;
    protected User $karyawan2;
    protected User $karyawan3;
    protected Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->location = Location::first();

        $this->karyawan1 = User::create([
            'nik'               => 'KAR001',
            'name'              => 'Karyawan Tanpa Presensi',
            'email'             => 'kar1@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        $this->karyawan2 = User::create([
            'nik'               => 'KAR002',
            'name'              => 'Karyawan Dengan Izin Cuti',
            'email'             => 'kar2@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        $this->karyawan3 = User::create([
            'nik'               => 'KAR003',
            'name'              => 'Karyawan Hadir',
            'email'             => 'kar3@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);
    }

    public function test_generate_alpha_marks_absent_employees_as_alpha(): void
    {
        // Tanggal 2026-08-25 adalah hari Selasa (hari kerja)
        $this->artisan('attendance:generate-alpha --date=2026-08-25')
            ->assertSuccessful();

        // Karyawan 1 tercatat Alpha
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->karyawan1->id,
            'date'    => '2026-08-25',
            'status'  => 'alpha',
        ]);

        // Verifikasi notifikasi alpha
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->karyawan1->id,
            'title'   => 'Ketidakhadiran Tercatat (Alpha)',
        ]);
    }

    public function test_generate_alpha_synchronizes_approved_leave_status(): void
    {
        // Buat pengajuan cuti yang disetujui untuk Karyawan 2
        Leave::create([
            'user_id'         => $this->karyawan2->id,
            'type'            => 'cuti',
            'start_date'      => '2026-08-24',
            'end_date'        => '2026-08-26',
            'reason'          => 'Cuti tahunan keluarga',
            'status'          => 'approved',
            'approved_by'     => User::where('role', 'admin')->first()->id,
        ]);

        $this->artisan('attendance:generate-alpha --date=2026-08-25')
            ->assertSuccessful();

        // Karyawan 2 tercatat Cuti, bukan Alpha
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->karyawan2->id,
            'date'    => '2026-08-25',
            'status'  => 'cuti',
        ]);
    }

    public function test_generate_alpha_skips_employees_who_checked_in(): void
    {
        // Karyawan 3 sudah presensi masuk
        Attendance::create([
            'user_id'         => $this->karyawan3->id,
            'location_id'     => $this->location->id,
            'date'            => '2026-08-25',
            'time_in'         => '07:58:00',
            'lat_in'          => -0.1333000,
            'long_in'         => 117.4833000,
            'status'          => 'tepat_waktu',
            'distance_meters' => 12.0,
        ]);

        $this->artisan('attendance:generate-alpha --date=2026-08-25')
            ->assertSuccessful();

        // Status Karyawan 3 tetap tepat_waktu
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->karyawan3->id,
            'date'    => '2026-08-25',
            'status'  => 'tepat_waktu',
        ]);
    }

    public function test_generate_alpha_is_idempotent(): void
    {
        // Eksekusi pertama
        $this->artisan('attendance:generate-alpha --date=2026-08-25')->assertSuccessful();
        $countAfterFirst = Attendance::where('date', '2026-08-25')->count();

        // Eksekusi kedua
        $this->artisan('attendance:generate-alpha --date=2026-08-25')->assertSuccessful();
        $countAfterSecond = Attendance::where('date', '2026-08-25')->count();

        $this->assertEquals($countAfterFirst, $countAfterSecond);
    }

    public function test_generate_alpha_skips_non_working_days(): void
    {
        // Tanggal 2026-08-23 adalah hari Minggu (bukan hari kerja aktif)
        $this->artisan('attendance:generate-alpha --date=2026-08-23')
            ->assertSuccessful();

        $this->assertDatabaseMissing('attendances', [
            'date' => '2026-08-23',
        ]);
    }

    public function test_auto_checkout_closes_hanging_attendance_session(): void
    {
        // Presensi masuk tanpa checkout
        $attendance = Attendance::create([
            'user_id'         => $this->karyawan3->id,
            'location_id'     => $this->location->id,
            'date'            => '2026-08-25',
            'time_in'         => '08:00:00',
            'time_out'        => null,
            'lat_in'          => -0.1333000,
            'long_in'         => 117.4833000,
            'status'          => 'tepat_waktu',
            'auto_checkout'   => false,
        ]);

        $this->artisan('attendance:auto-checkout --date=2026-08-25')
            ->assertSuccessful();

        $attendance->refresh();
        $this->assertEquals('17:00:00', $attendance->time_out);
        $this->assertTrue($attendance->auto_checkout);

        // Verifikasi notifikasi auto-checkout
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->karyawan3->id,
            'title'   => 'Sesi Presensi Ditutup Otomatis',
        ]);
    }

    public function test_auto_checkout_skips_already_checked_out_employees(): void
    {
        // Presensi masuk dan checkout manual 16:45:00
        $attendance = Attendance::create([
            'user_id'         => $this->karyawan3->id,
            'location_id'     => $this->location->id,
            'date'            => '2026-08-25',
            'time_in'         => '08:00:00',
            'time_out'        => '16:45:00',
            'lat_in'          => -0.1333000,
            'long_in'         => 117.4833000,
            'status'          => 'tepat_waktu',
            'auto_checkout'   => false,
        ]);

        $this->artisan('attendance:auto-checkout --date=2026-08-25')
            ->assertSuccessful();

        $attendance->refresh();
        $this->assertEquals('16:45:00', $attendance->time_out);
        $this->assertFalse($attendance->auto_checkout);
    }

    public function test_auto_checkout_is_idempotent(): void
    {
        Attendance::create([
            'user_id'         => $this->karyawan3->id,
            'location_id'     => $this->location->id,
            'date'            => '2026-08-25',
            'time_in'         => '08:00:00',
            'time_out'        => null,
            'status'          => 'tepat_waktu',
        ]);

        $this->artisan('attendance:auto-checkout --date=2026-08-25')->assertSuccessful();
        $this->artisan('attendance:auto-checkout --date=2026-08-25')->assertSuccessful();

        $attendance = Attendance::where('user_id', $this->karyawan3->id)->where('date', '2026-08-25')->first();
        $this->assertEquals('17:00:00', $attendance->time_out);
        $this->assertTrue($attendance->auto_checkout);
    }
}
