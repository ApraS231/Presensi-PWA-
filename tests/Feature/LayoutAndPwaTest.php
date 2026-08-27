<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LayoutAndPwaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_manifest_json_is_accessible_and_valid(): void
    {
        $manifestPath = public_path('manifest.json');
        $this->assertFileExists($manifestPath);

        $jsonContent = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($jsonContent);
        $this->assertEquals('Presensi PT. Cahaya Anugrah Kalimantan', $jsonContent['name']);
        $this->assertEquals('standalone', $jsonContent['display']);
        $this->assertEquals('#1565C0', $jsonContent['theme_color']);
    }

    public function test_service_worker_file_exists(): void
    {
        $swPath = public_path('service-worker.js');
        $this->assertFileExists($swPath);

        $content = file_get_contents($swPath);
        $this->assertStringContainsString('presensi-cak-v1', $content);
        $this->assertStringContainsString('/offline.html', $content);
    }

    public function test_offline_fallback_page_exists_and_contains_information(): void
    {
        $offlinePath = public_path('offline.html');
        $this->assertFileExists($offlinePath);

        $content = file_get_contents($offlinePath);
        $this->assertStringContainsString('Koneksi Terputus', $content);
        $this->assertStringContainsString('Coba Muat Ulang', $content);
    }

    public function test_pwa_icons_exist(): void
    {
        $this->assertFileExists(public_path('icons/icon-192x192.png'));
        $this->assertFileExists(public_path('icons/icon-512x512.png'));
    }

    public function test_karyawan_dashboard_renders_pwa_layout(): void
    {
        $karyawan = User::create([
            'nik'               => 'KAR001',
            'name'              => 'Budi Santoso',
            'email'             => 'budi@ptcak.com',
            'password'          => Hash::make('password'),
            'role'              => 'karyawan',
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        $response = $this->actingAs($karyawan)->get('/karyawan/dashboard');
        $response->assertStatus(200);
        $response->assertSee('manifest.json');
        $response->assertSee('md-bottom-nav');
        $response->assertSee('Beranda');
        $response->assertSee('Absen');
        $response->assertSee('Izin');
        $response->assertSee('Riwayat');
        $response->assertSee('serviceWorker.register');
    }

    public function test_admin_dashboard_renders_admin_layout(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('admin-sidebar');
        $response->assertSee('themeToggleBtn');
        $response->assertSee('Monitoring Real-Time');
        $response->assertSee('Enrollment Biometrik');
        $response->assertSee('Persetujuan Izin');
    }

    public function test_superadmin_dashboard_renders_superadmin_layout(): void
    {
        $superadmin = User::where('role', 'superadmin')->first();

        $response = $this->actingAs($superadmin)->get('/superadmin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('admin-sidebar');
        $response->assertSee('Master Lokasi', false);
        $response->assertSee('Manajemen Karyawan');
        $response->assertSee('Kebijakan Sistem');
    }
}
