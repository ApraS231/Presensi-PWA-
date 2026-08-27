<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeeHistoryAndNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $karyawanA;
    protected User $karyawanB;
    protected Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->location = Location::first();

        $this->karyawanA = User::create([
            'nik'               => 'KAR001',
            'name'              => 'Budi Karyawan A',
            'email'             => 'budi@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        $this->karyawanB = User::create([
            'nik'               => 'KAR002',
            'name'              => 'Siti Karyawan B',
            'email'             => 'siti@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);
    }

    public function test_employee_can_view_own_attendance_history(): void
    {
        $response = $this->actingAs($this->karyawanA)->get('/karyawan/riwayat');
        $response->assertStatus(200);
        $response->assertSee('Riwayat Presensi');
        $response->assertSee('Tepat Waktu');
    }

    public function test_history_filters_by_month_and_year(): void
    {
        // 1. Presensi Bulan Agustus 2026
        Attendance::create([
            'user_id'         => $this->karyawanA->id,
            'location_id'     => $this->location->id,
            'date'            => '2026-08-25',
            'time_in'         => '08:00:00',
            'status'          => 'tepat_waktu',
            'distance_meters' => 10.0,
        ]);

        // 2. Presensi Bulan Juli 2026
        Attendance::create([
            'user_id'         => $this->karyawanA->id,
            'location_id'     => $this->location->id,
            'date'            => '2026-07-15',
            'time_in'         => '07:45:00',
            'status'          => 'tepat_waktu',
            'distance_meters' => 12.0,
        ]);

        // Filter Bulan Juli (month=7, year=2026)
        $responseJuly = $this->actingAs($this->karyawanA)->get('/karyawan/riwayat?month=7&year=2026');
        $responseJuly->assertStatus(200);
        $responseJuly->assertSee('15 Juli 2026');
        $responseJuly->assertDontSee('25 Agustus 2026');

        // Filter Bulan Agustus (month=8, year=2026)
        $responseAugust = $this->actingAs($this->karyawanA)->get('/karyawan/riwayat?month=8&year=2026');
        $responseAugust->assertStatus(200);
        $responseAugust->assertSee('25 Agustus 2026');
        $responseAugust->assertDontSee('15 Juli 2026');
    }

    public function test_monthly_summary_statistics_calculation(): void
    {
        // 2 Tepat Waktu
        Attendance::create([
            'user_id'     => $this->karyawanA->id,
            'location_id' => $this->location->id,
            'date'        => '2026-08-24',
            'time_in'     => '07:50:00',
            'status'      => 'tepat_waktu',
        ]);
        Attendance::create([
            'user_id'     => $this->karyawanA->id,
            'location_id' => $this->location->id,
            'date'        => '2026-08-25',
            'time_in'     => '07:55:00',
            'status'      => 'tepat_waktu',
        ]);

        // 1 Terlambat
        Attendance::create([
            'user_id'     => $this->karyawanA->id,
            'location_id' => $this->location->id,
            'date'        => '2026-08-26',
            'time_in'     => '08:30:00',
            'status'      => 'terlambat',
        ]);

        // 1 Izin
        Attendance::create([
            'user_id' => $this->karyawanA->id,
            'date'    => '2026-08-27',
            'status'  => 'izin',
        ]);

        // 1 Alpha
        Attendance::create([
            'user_id' => $this->karyawanA->id,
            'date'    => '2026-08-28',
            'status'  => 'alpha',
        ]);

        $response = $this->actingAs($this->karyawanA)->get('/karyawan/riwayat?month=8&year=2026');
        $response->assertStatus(200);
        $response->assertSee('2 Hari'); // Tepat Waktu
        $response->assertSee('1 Hari'); // Terlambat / Izin / Alpha
    }

    public function test_auto_checkout_indicator_is_displayed(): void
    {
        Attendance::create([
            'user_id'         => $this->karyawanA->id,
            'location_id'     => $this->location->id,
            'date'            => '2026-08-25',
            'time_in'         => '08:00:00',
            'time_out'        => '17:00:00',
            'auto_checkout'   => true,
            'status'          => 'tepat_waktu',
        ]);

        $response = $this->actingAs($this->karyawanA)->get('/karyawan/riwayat?month=8&year=2026');
        $response->assertStatus(200);
        $response->assertSee('Auto');
    }

    public function test_employee_cannot_see_other_employees_attendance(): void
    {
        // Presensi Karyawan B
        Attendance::create([
            'user_id'         => $this->karyawanB->id,
            'location_id'     => $this->location->id,
            'date'            => '2026-08-25',
            'time_in'         => '08:00:00',
            'status'          => 'tepat_waktu',
            'distance_meters' => 99.9,
        ]);

        // Karyawan A mengunjungi riwayat
        $response = $this->actingAs($this->karyawanA)->get('/karyawan/riwayat?month=8&year=2026');
        $response->assertStatus(200);
        $response->assertDontSee('99.9m');
        $response->assertSee('Tidak Ada Catatan Presensi');
    }
}
