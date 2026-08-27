<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
use App\Services\ReportCalculationService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $karyawan;
    protected Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('role', 'admin')->first();
        $this->location = Location::first();

        $this->karyawan = User::create([
            'nik'               => 'KAR999',
            'name'              => 'Report Test Employee',
            'email'             => 'report@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'department'        => 'Operasional',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);
    }

    public function test_admin_can_access_report_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reports');
        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi Laporan Presensi');
        $response->assertSee('Excel');
        $response->assertSee('PDF');
    }

    public function test_report_calculation_service_accurate_metrics(): void
    {
        $startDate = Carbon::parse('2026-08-01');
        $endDate   = Carbon::parse('2026-08-31');

        // 1. Hadir Tepat Waktu (08:00 - 17:00, Net: 8 Jam)
        Attendance::create([
            'user_id'     => $this->karyawan->id,
            'location_id' => $this->location->id,
            'date'        => '2026-08-03',
            'time_in'     => '08:00:00',
            'time_out'    => '17:00:00',
            'status'      => 'tepat_waktu',
        ]);

        // 2. Hadir Terlambat (08:30 - 17:00, Net: 7.5 Jam, Telat: 30 menit)
        Attendance::create([
            'user_id'     => $this->karyawan->id,
            'location_id' => $this->location->id,
            'date'        => '2026-08-04',
            'time_in'     => '08:30:00',
            'time_out'    => '17:00:00',
            'status'      => 'terlambat',
        ]);

        // 3. Izin (Disetujui, Net: 8 Jam)
        Attendance::create([
            'user_id' => $this->karyawan->id,
            'date'    => '2026-08-05',
            'status'  => 'izin',
        ]);

        // 4. Alpha (Net: 0 Jam)
        Attendance::create([
            'user_id' => $this->karyawan->id,
            'date'    => '2026-08-06',
            'status'  => 'alpha',
        ]);

        $summary = ReportCalculationService::generateSummary($startDate, $endDate, 'Operasional');

        $this->assertNotEmpty($summary);
        $row = collect($summary)->firstWhere('user_id', $this->karyawan->id);

        $this->assertNotNull($row);
        $this->assertEquals(1, $row['total_hadir_tepat_waktu']);
        $this->assertEquals(1, $row['total_terlambat']);
        $this->assertEquals(2, $row['total_hadir']);
        $this->assertEquals(30, $row['total_menit_terlambat']);
        $this->assertEquals(1, $row['total_izin']);
        $this->assertEquals(1, $row['total_alpha']);
        // Total jam kerja: 8 + 7.5 + 8 = 23.5
        $this->assertEquals(23.5, $row['total_jam_kerja']);
    }

    public function test_admin_can_export_excel_report(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reports/export/excel?start_date=2026-08-01&end_date=2026-08-31');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_admin_can_export_pdf_report(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reports/export/pdf?start_date=2026-08-01&end_date=2026-08-31');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('.pdf', $response->headers->get('content-disposition'));
    }

    public function test_karyawan_cannot_access_report_endpoints(): void
    {
        $responseIndex = $this->actingAs($this->karyawan)->get('/admin/reports');
        $responseIndex->assertStatus(403);

        $responseExcel = $this->actingAs($this->karyawan)->get('/admin/reports/export/excel');
        $responseExcel->assertStatus(403);

        $responsePdf = $this->actingAs($this->karyawan)->get('/admin/reports/export/pdf');
        $responsePdf->assertStatus(403);
    }
}
