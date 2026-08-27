<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\User;
use App\Services\GeofenceService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GeofencingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_haversine_formula_distance_accuracy(): void
    {
        $service = new GeofenceService();

        // Titik 1: Kantor Pusat (-0.1333000, 117.4833000)
        // Titik 2: Koordinat yang sama persis
        $distSame = $service->calculateDistance(-0.1333000, 117.4833000, -0.1333000, 117.4833000);
        $this->assertEquals(0.0, $distSame);

        // Titik A: Monas (-6.175392, 106.827153)
        // Titik B: Masjid Istiqlal (-6.170200, 106.831700)
        // Ground truth jarak geodesic ~765 meter
        $distance = $service->calculateDistance(-6.175392, 106.827153, -6.170200, 106.831700);
        $this->assertGreaterThan(750, $distance);
        $this->assertLessThan(780, $distance);
    }

    public function test_geofence_service_validates_coordinate_inside_radius(): void
    {
        $service = new GeofenceService();

        // Kantor Pusat: Lat -0.1333000, Lon 117.4833000, Radius 100m
        // Koordinat user berjarak ~15m dari titik tengah
        $userLat = -0.1334000;
        $userLon = 117.4833500;

        $validation = $service->validateCoordinates($userLat, $userLon);

        $this->assertTrue($validation['is_valid']);
        $this->assertNotNull($validation['location']);
        $this->assertEquals('Kantor Pusat PT. CAK', $validation['location']->name);
        $this->assertLessThan(100, $validation['distance_meters']);
    }

    public function test_geofence_service_rejects_coordinate_outside_radius(): void
    {
        $service = new GeofenceService();

        // Koordinat berjarak ~2 km dari kantor
        $userLat = -0.1500000;
        $userLon = 117.4833000;

        $validation = $service->validateCoordinates($userLat, $userLon);

        $this->assertFalse($validation['is_valid']);
        $this->assertGreaterThan(1000, $validation['distance_meters']);
        $this->assertStringContainsString('di luar area presensi', $validation['message']);
    }

    public function test_geofence_verification_api_endpoint(): void
    {
        $karyawan = User::create([
            'nik'               => 'KAR001',
            'name'              => 'Doni Pratama',
            'email'             => 'doni@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        $response = $this->actingAs($karyawan)->postJson('/karyawan/geofence/verify', [
            'latitude'  => -0.1333000,
            'longitude' => 117.4833000,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'  => true,
            'is_valid' => true,
            'location' => [
                'name' => 'Kantor Pusat PT. CAK',
            ],
        ]);
    }

    public function test_multi_location_matching_finds_closest_active_location(): void
    {
        // Tambahkan lokasi proyek ke-2
        $proyekTambang = Location::create([
            'name'          => 'Proyek Tambang Site A',
            'latitude'      => -0.3000000,
            'longitude'     => 117.4000000,
            'radius_meters' => 200,
            'is_active'     => true,
        ]);

        $service = new GeofenceService();

        // User berada di dekat Proyek Tambang Site A
        $userLat = -0.3000500;
        $userLon = 117.4000500;

        $validation = $service->validateCoordinates($userLat, $userLon);

        $this->assertTrue($validation['is_valid']);
        $this->assertEquals($proyekTambang->id, $validation['location']->id);
        $this->assertEquals('Proyek Tambang Site A', $validation['location']->name);
    }

    public function test_admin_can_create_update_and_toggle_locations(): void
    {
        $admin = User::where('role', 'admin')->first();

        // 1. Create Location
        $responseStore = $this->actingAs($admin)->post('/admin/locations', [
            'name'          => 'Workshop Samarinda',
            'latitude'      => -0.5021000,
            'longitude'     => 117.1536000,
            'radius_meters' => 150,
            'is_active'     => 1,
        ]);
        $responseStore->assertRedirect(route('admin.locations.index'));
        $this->assertDatabaseHas('locations', ['name' => 'Workshop Samarinda']);

        $location = Location::where('name', 'Workshop Samarinda')->first();

        // 2. Update Location
        $responseUpdate = $this->actingAs($admin)->put("/admin/locations/{$location->id}", [
            'name'          => 'Workshop Utama Samarinda',
            'latitude'      => -0.5021000,
            'longitude'     => 117.1536000,
            'radius_meters' => 200,
            'is_active'     => 1,
        ]);
        $responseUpdate->assertRedirect(route('admin.locations.index'));
        $this->assertEquals('Workshop Utama Samarinda', $location->fresh()->name);
        $this->assertEquals(200, $location->fresh()->radius_meters);

        // 3. Toggle Status
        $responseToggle = $this->actingAs($admin)->patch("/admin/locations/{$location->id}/toggle");
        $responseToggle->assertRedirect();
        $this->assertFalse($location->fresh()->is_active);
    }
}
