<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MonitoringDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('role', 'admin')->first();
        $this->location = Location::first();
    }

    public function test_admin_can_access_monitoring_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Monitoring Kehadiran Real-Time');
        $response->assertSee('Tingkat Kehadiran');
    }

    public function test_dashboard_statistics_calculation_accuracy(): void
    {
        $today = Carbon::today('Asia/Makassar')->format('Y-m-d');

        // 1. Karyawan Tepat Waktu (IT)
        $user1 = User::create([
            'nik'               => 'KAR001',
            'name'              => 'Doni IT',
            'email'             => 'doni@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'department'        => 'Teknologi Informasi',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);
        Attendance::create([
            'user_id'         => $user1->id,
            'location_id'     => $this->location->id,
            'date'            => $today,
            'time_in'         => '07:55:00',
            'status'          => 'tepat_waktu',
            'distance_meters' => 10.0,
        ]);

        // 2. Karyawan Terlambat (Operasional)
        $user2 = User::create([
            'nik'               => 'KAR002',
            'name'              => 'Bima Ops',
            'email'             => 'bima@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'department'        => 'Operasional Tambang',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);
        Attendance::create([
            'user_id'         => $user2->id,
            'location_id'     => $this->location->id,
            'date'            => $today,
            'time_in'         => '08:45:00',
            'status'          => 'terlambat',
            'distance_meters' => 15.0,
        ]);

        // 3. Karyawan Izin (HRD)
        $user3 = User::create([
            'nik'               => 'KAR003',
            'name'              => 'Citra HR',
            'email'             => 'citra@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'department'        => 'Human Resources',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);
        Attendance::create([
            'user_id' => $user3->id,
            'date'    => $today,
            'status'  => 'izin',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);

        // 2 hadir (1 tepat waktu + 1 terlambat) dari 3 karyawan aktif = 66.7%
        $response->assertSee('66.7%');
        $response->assertSee('Doni IT');
        $response->assertSee('Bima Ops');
        $response->assertSee('Citra HR');
    }

    public function test_dashboard_filters_by_date(): void
    {
        $pastDate = '2026-08-20';
        $user = User::create([
            'nik'               => 'KAR010',
            'name'              => 'Karyawan Past Date',
            'email'             => 'past@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        Attendance::create([
            'user_id'         => $user->id,
            'location_id'     => $this->location->id,
            'date'            => $pastDate,
            'time_in'         => '08:00:00',
            'status'          => 'tepat_waktu',
            'distance_meters' => 5.0,
        ]);

        // Akses dashboard pada tanggal 2026-08-20
        $response = $this->actingAs($this->admin)->get("/admin/dashboard?date={$pastDate}");
        $response->assertStatus(200);
        $response->assertSee('Karyawan Past Date');

        // Akses dashboard pada tanggal lain
        $otherResponse = $this->actingAs($this->admin)->get('/admin/dashboard?date=2026-08-21');
        $otherResponse->assertStatus(200);
        $otherResponse->assertDontSee('Karyawan Past Date');
    }

    public function test_dashboard_filters_by_department(): void
    {
        $today = Carbon::today('Asia/Makassar')->format('Y-m-d');

        $userIT = User::create([
            'nik'               => 'KAR020',
            'name'              => 'Developer Web',
            'email'             => 'dev@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'department'        => 'IT Support',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);
        Attendance::create([
            'user_id'     => $userIT->id,
            'location_id' => $this->location->id,
            'date'        => $today,
            'time_in'     => '08:00:00',
            'status'      => 'tepat_waktu',
        ]);

        $userFinance = User::create([
            'nik'               => 'KAR021',
            'name'              => 'Finance Staff',
            'email'             => 'finance@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'department'        => 'Finance & Accounting',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);
        Attendance::create([
            'user_id'     => $userFinance->id,
            'location_id' => $this->location->id,
            'date'        => $today,
            'time_in'     => '08:00:00',
            'status'      => 'tepat_waktu',
        ]);

        // Filter IT Support
        $responseIT = $this->actingAs($this->admin)->get('/admin/dashboard?department=IT+Support');
        $responseIT->assertStatus(200);
        $responseIT->assertSee('Developer Web');
        $responseIT->assertDontSee('Finance Staff');
    }

    public function test_pending_enrollment_banner_appears_when_pending_users_exist(): void
    {
        User::create([
            'nik'               => 'KAR099',
            'name'              => 'Karyawan Baru Belum Wajah',
            'email'             => 'baru@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('belum menyelesaikan pendaftaran biometrik');
    }

    public function test_monitoring_json_polling_endpoint(): void
    {
        $today = Carbon::today('Asia/Makassar')->format('Y-m-d');

        $user = User::create([
            'nik'               => 'KAR100',
            'name'              => 'Polling Test Employee',
            'email'             => 'polling@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);
        Attendance::create([
            'user_id'     => $user->id,
            'location_id' => $this->location->id,
            'date'        => $today,
            'time_in'     => '07:50:00',
            'status'      => 'tepat_waktu',
        ]);

        $response = $this->actingAs($this->admin)->getJson("/admin/monitoring/data?date={$today}");
        $response->assertStatus(200);
        $response->assertJson([
            'success'     => true,
            'date'        => $today,
            'tepat_waktu' => 1,
        ]);
    }
}
