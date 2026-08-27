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

class LiveMapMonitoringTest extends TestCase
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

    public function test_admin_can_access_live_map_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/map');
        $response->assertStatus(200);
        $response->assertSee('Visualisasi Live Map Sebaran Presensi');
        $response->assertSee('leaflet-map.js');
        $response->assertSee('live-attendance-map');
    }

    public function test_live_map_contains_active_locations_and_geofences(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/map');
        $response->assertStatus(200);
        $response->assertSee($this->location->name);
        $response->assertSee((string) $this->location->radius_meters);
    }

    public function test_live_map_contains_employee_presence_coordinates(): void
    {
        $today = Carbon::today('Asia/Makassar')->format('Y-m-d');

        $user = User::create([
            'nik'               => 'KAR001',
            'name'              => 'Eko Map Test',
            'email'             => 'eko@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'department'        => 'Operasional',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        Attendance::create([
            'user_id'         => $user->id,
            'location_id'     => $this->location->id,
            'date'            => $today,
            'time_in'         => '07:50:00',
            'lat_in'          => -0.1333500,
            'long_in'         => 117.4833200,
            'status'          => 'tepat_waktu',
            'distance_meters' => 8.5,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/map');
        $response->assertStatus(200);
        $response->assertSee('Eko Map Test');
        $response->assertSee('-0.13335');
        $response->assertSee('117.48332');
    }

    public function test_live_map_filters_by_date_and_location(): void
    {
        $pastDate = '2026-08-20';

        $loc2 = Location::create([
            'name'          => 'Proyek Muara Badak Site B',
            'latitude'      => -0.3500000,
            'longitude'     => 117.4200000,
            'radius_meters' => 150,
            'is_active'     => true,
        ]);

        $user1 = User::create([
            'nik'               => 'KAR002',
            'name'              => 'User Site A',
            'email'             => 'user1@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);
        Attendance::create([
            'user_id'     => $user1->id,
            'location_id' => $this->location->id,
            'date'        => $pastDate,
            'time_in'     => '08:00:00',
            'lat_in'      => -0.1333000,
            'long_in'     => 117.4833000,
            'status'      => 'tepat_waktu',
        ]);

        $user2 = User::create([
            'nik'               => 'KAR003',
            'name'              => 'User Site B',
            'email'             => 'user2@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);
        Attendance::create([
            'user_id'     => $user2->id,
            'location_id' => $loc2->id,
            'date'        => $pastDate,
            'time_in'     => '08:05:00',
            'lat_in'      => -0.3500000,
            'long_in'     => 117.4200000,
            'status'      => 'tepat_waktu',
        ]);

        // Filter lokasi Site B pada tanggal lampau
        $response = $this->actingAs($this->admin)->get("/admin/map?date={$pastDate}&location_id={$loc2->id}");
        $response->assertStatus(200);
        $response->assertSee('User Site B');
        $response->assertDontSee('User Site A');
    }
}
