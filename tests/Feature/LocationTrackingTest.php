<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\TrackingViewController;
use App\Models\Attendance;
use App\Models\Location;
use App\Models\LocationTrack;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LocationTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $karyawan;
    protected User $karyawanB;
    protected User $admin;
    protected User $superadmin;
    protected Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::updateOrCreate(['key' => 'tracking_interval_minutes'], ['value' => '5']);
        Setting::updateOrCreate(['key' => 'tracking_max_accuracy'], ['value' => '100']);

        $this->location = Location::create([
            'name'          => 'Site Tambang Muara Badak',
            'latitude'      => -0.3125000,
            'longitude'     => 117.3850000,
            'radius_meters' => 200,
            'is_active'     => true,
        ]);

        $this->karyawan = User::factory()->create([
            'nik'               => 'KAR001',
            'name'              => 'Budi Santoso (SPG)',
            'email'             => 'budi.spg@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'department'        => 'Marketing & Sales',
            'jabatan'           => 'SPG Keliling',
            'enrollment_status' => 'enrolled',
            'is_active'         => true,
        ]);

        $this->karyawanB = User::factory()->create([
            'nik'               => 'KAR002',
            'name'              => 'Siti SPG',
            'email'             => 'siti.spg@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'department'        => 'Marketing & Sales',
            'jabatan'           => 'SPG Keliling',
            'enrollment_status' => 'enrolled',
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

        $this->superadmin = User::factory()->create([
            'nik'               => 'SA001',
            'name'              => 'Super Administrator',
            'email'             => 'superadmin@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'superadmin',
            'is_active'         => true,
        ]);
    }

    public function test_employee_who_checked_in_can_send_location_ping_and_it_is_saved(): void
    {
        $today = Carbon::today('Asia/Makassar')->toDateString();

        $attendance = Attendance::create([
            'user_id'     => $this->karyawan->id,
            'location_id' => $this->location->id,
            'date'        => $today,
            'time_in'     => '07:45:00',
            'status'      => 'tepat_waktu',
        ]);

        $response = $this->actingAs($this->karyawan)->postJson('/karyawan/tracking/ping', [
            'latitude'    => -0.3126000,
            'longitude'   => 117.3852000,
            'accuracy'    => 15.5,
            'recorded_at' => now()->toIso8601String(),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'         => true,
            'tracking_active' => true,
            'saved'           => true,
        ]);

        $this->assertDatabaseHas('location_tracks', [
            'user_id'       => $this->karyawan->id,
            'attendance_id' => $attendance->id,
            'date'          => $today,
            'latitude'      => -0.3126000,
            'longitude'     => 117.3852000,
        ]);
    }

    public function test_employee_who_has_not_checked_in_receives_tracking_inactive(): void
    {
        $response = $this->actingAs($this->karyawan)->postJson('/karyawan/tracking/ping', [
            'latitude'    => -0.3126000,
            'longitude'   => 117.3852000,
            'accuracy'    => 15.5,
            'recorded_at' => now()->toIso8601String(),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'         => false,
            'tracking_active' => false,
            'saved'           => false,
        ]);

        $this->assertDatabaseCount('location_tracks', 0);
    }

    public function test_employee_who_has_checked_out_receives_tracking_inactive(): void
    {
        $today = Carbon::today('Asia/Makassar')->toDateString();

        Attendance::create([
            'user_id'     => $this->karyawan->id,
            'location_id' => $this->location->id,
            'date'        => $today,
            'time_in'     => '07:45:00',
            'time_out'    => '17:05:00',
            'status'      => 'tepat_waktu',
        ]);

        $response = $this->actingAs($this->karyawan)->postJson('/karyawan/tracking/ping', [
            'latitude'    => -0.3126000,
            'longitude'   => 117.3852000,
            'accuracy'    => 15.5,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'         => false,
            'tracking_active' => false,
            'saved'           => false,
        ]);

        $this->assertDatabaseCount('location_tracks', 0);
    }

    public function test_location_ping_with_excessive_accuracy_is_ignored(): void
    {
        $today = Carbon::today('Asia/Makassar')->toDateString();

        Attendance::create([
            'user_id'     => $this->karyawan->id,
            'location_id' => $this->location->id,
            'date'        => $today,
            'time_in'     => '07:45:00',
            'status'      => 'tepat_waktu',
        ]);

        $response = $this->actingAs($this->karyawan)->postJson('/karyawan/tracking/ping', [
            'latitude'    => -0.3126000,
            'longitude'   => 117.3852000,
            'accuracy'    => 150.0, // > 100m max accuracy
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'         => true,
            'tracking_active' => true,
            'saved'           => false,
        ]);

        $this->assertDatabaseCount('location_tracks', 0);
    }

    public function test_stationary_location_ping_under_five_meters_is_throttled(): void
    {
        $today = Carbon::today('Asia/Makassar')->toDateString();

        $att = Attendance::create([
            'user_id'     => $this->karyawan->id,
            'location_id' => $this->location->id,
            'date'        => $today,
            'time_in'     => '07:45:00',
            'status'      => 'tepat_waktu',
        ]);

        // First point
        LocationTrack::create([
            'user_id'       => $this->karyawan->id,
            'attendance_id' => $att->id,
            'date'          => $today,
            'latitude'      => -0.3125000,
            'longitude'     => 117.3850000,
            'accuracy'      => 10,
            'recorded_at'   => now()->subMinutes(5),
        ]);

        // Second point with negligible movement (< 1 meter)
        $response = $this->actingAs($this->karyawan)->postJson('/karyawan/tracking/ping', [
            'latitude'    => -0.3125010,
            'longitude'   => 117.3850010,
            'accuracy'    => 10,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'         => true,
            'tracking_active' => true,
            'saved'           => false,
        ]);

        $this->assertDatabaseCount('location_tracks', 1);
    }

    public function test_employee_can_view_own_today_tracking_data(): void
    {
        $today = Carbon::today('Asia/Makassar')->toDateString();

        $att = Attendance::create([
            'user_id'     => $this->karyawan->id,
            'location_id' => $this->location->id,
            'date'        => $today,
            'time_in'     => '07:45:00',
            'status'      => 'tepat_waktu',
        ]);

        LocationTrack::create([
            'user_id'       => $this->karyawan->id,
            'attendance_id' => $att->id,
            'date'          => $today,
            'latitude'      => -0.3125000,
            'longitude'     => 117.3850000,
            'accuracy'      => 10,
            'recorded_at'   => now(),
        ]);

        $response = $this->actingAs($this->karyawan)->getJson('/karyawan/tracking/today');
        $response->assertStatus(200);
        $response->assertJson([
            'success'         => true,
            'tracking_active' => true,
            'total_points'    => 1,
        ]);

        // Test HTML view
        $htmlResponse = $this->actingAs($this->karyawan)->get('/karyawan/tracking');
        $htmlResponse->assertStatus(200);
        $htmlResponse->assertSee('Perjalanan Hari Ini');
    }

    public function test_employee_tracking_today_does_not_contain_yesterdays_tracks(): void
    {
        $today = Carbon::today('Asia/Makassar')->toDateString();
        $yesterday = Carbon::yesterday('Asia/Makassar')->toDateString();

        $attYesterday = Attendance::create([
            'user_id'     => $this->karyawan->id,
            'location_id' => $this->location->id,
            'date'        => $yesterday,
            'time_in'     => '07:45:00',
            'time_out'    => '17:00:00',
            'status'      => 'tepat_waktu',
        ]);

        LocationTrack::create([
            'user_id'       => $this->karyawan->id,
            'attendance_id' => $attYesterday->id,
            'date'          => $yesterday,
            'latitude'      => -0.3125000,
            'longitude'     => 117.3850000,
            'recorded_at'   => Carbon::parse($yesterday . ' 08:00:00'),
        ]);

        $response = $this->actingAs($this->karyawan)->getJson('/karyawan/tracking/today');
        $response->assertStatus(200);
        $response->assertJson([
            'total_points' => 0,
        ]);
    }

    public function test_admin_can_access_tracking_page_and_view_summaries(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/tracking');
        $response->assertStatus(200);
        $response->assertSee('Pemantauan Jejak Lapangan SPG');
        $response->assertSee('Budi Santoso (SPG)');
    }

    public function test_admin_can_retrieve_spg_trail_json_with_distance_and_duration(): void
    {
        $today = Carbon::today('Asia/Makassar')->toDateString();

        $att = Attendance::create([
            'user_id'     => $this->karyawan->id,
            'location_id' => $this->location->id,
            'date'        => $today,
            'time_in'     => '07:45:00',
            'status'      => 'tepat_waktu',
        ]);

        LocationTrack::create([
            'user_id'       => $this->karyawan->id,
            'attendance_id' => $att->id,
            'date'          => $today,
            'latitude'      => -0.3125000,
            'longitude'     => 117.3850000,
            'accuracy'      => 10,
            'recorded_at'   => Carbon::parse($today . ' 08:00:00'),
        ]);

        LocationTrack::create([
            'user_id'       => $this->karyawan->id,
            'attendance_id' => $att->id,
            'date'          => $today,
            'latitude'      => -0.3175000,
            'longitude'     => 117.3900000,
            'accuracy'      => 12,
            'recorded_at'   => Carbon::parse($today . ' 09:00:00'),
        ]);

        $response = $this->actingAs($this->admin)->getJson("/admin/tracking/{$this->karyawan->id}/trail?date={$today}");
        $response->assertStatus(200);
        $response->assertJson([
            'success'          => true,
            'total_points'     => 2,
            'duration_minutes' => 60,
        ]);
        $this->assertGreaterThan(0, $response->json('total_distance_km'));
    }

    public function test_superadmin_can_access_tracking_page(): void
    {
        $response = $this->actingAs($this->superadmin)->get('/superadmin/tracking');
        $response->assertStatus(200);
        $response->assertSee('Pemantauan Jejak Lapangan SPG');
    }

    public function test_karyawan_cannot_access_admin_or_superadmin_tracking_pages(): void
    {
        $adminRes = $this->actingAs($this->karyawan)->get('/admin/tracking');
        $adminRes->assertStatus(403);

        $superRes = $this->actingAs($this->karyawan)->get('/superadmin/tracking');
        $superRes->assertStatus(403);
    }

    public function test_guest_cannot_access_tracking_endpoints(): void
    {
        $this->get('/karyawan/tracking')->assertRedirect('/login');
        $this->get('/admin/tracking')->assertRedirect('/login');
        $this->get('/superadmin/tracking')->assertRedirect('/login');
        $this->postJson('/karyawan/tracking/ping', [])->assertStatus(401);
    }

    public function test_cleanup_command_removes_records_older_than_30_days(): void
    {
        $oldDate = Carbon::today('Asia/Makassar')->subDays(35)->toDateString();
        $recentDate = Carbon::today('Asia/Makassar')->subDays(10)->toDateString();

        $attOld = Attendance::create([
            'user_id' => $this->karyawan->id,
            'date'    => $oldDate,
            'status'  => 'tepat_waktu',
        ]);

        $attRecent = Attendance::create([
            'user_id' => $this->karyawan->id,
            'date'    => $recentDate,
            'status'  => 'tepat_waktu',
        ]);

        LocationTrack::create([
            'user_id'       => $this->karyawan->id,
            'attendance_id' => $attOld->id,
            'date'          => $oldDate,
            'latitude'      => -0.3125000,
            'longitude'     => 117.3850000,
            'recorded_at'   => Carbon::parse($oldDate . ' 08:00:00'),
        ]);

        LocationTrack::create([
            'user_id'       => $this->karyawan->id,
            'attendance_id' => $attRecent->id,
            'date'          => $recentDate,
            'latitude'      => -0.3125000,
            'longitude'     => 117.3850000,
            'recorded_at'   => Carbon::parse($recentDate . ' 08:00:00'),
        ]);

        $this->assertDatabaseCount('location_tracks', 2);

        $this->artisan('tracking:cleanup --days=30')->assertSuccessful();

        $this->assertDatabaseCount('location_tracks', 1);
        $this->assertDatabaseHas('location_tracks', ['date' => $recentDate]);
        $this->assertDatabaseMissing('location_tracks', ['date' => $oldDate]);
    }

    public function test_monthly_cycle_25_25_date_calculation_accuracy(): void
    {
        $controller = new TrackingViewController();

        // Testing for key "2026-08" -> 2026-07-25 s.d. 2026-08-25
        [$start, $end, $label] = $controller->getPeriodDates('2026-08');

        $this->assertEquals('2026-07-25', $start);
        $this->assertEquals('2026-08-25', $end);
        $this->assertStringContainsString('25 Juli - 25 Agustus', $label);

        // Testing for key "2026-09" -> 2026-08-25 s.d. 2026-09-25
        [$startSep, $endSep, $labelSep] = $controller->getPeriodDates('2026-09');

        $this->assertEquals('2026-08-25', $startSep);
        $this->assertEquals('2026-09-25', $endSep);
        $this->assertStringContainsString('25 Agustus - 25 September', $labelSep);
    }
}
