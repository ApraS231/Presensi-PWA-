<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Super Admin Default
        User::updateOrCreate(
            ['nik' => 'SA001'],
            [
                'name'              => 'Super Administrator',
                'email'             => 'superadmin@ptcak.com',
                'password'          => Hash::make('password'),
                'role'              => 'superadmin',
                'jabatan'           => 'IT Administrator',
                'department'        => 'IT & Infrastructure',
                'no_telp'           => '081234567890',
                'enrollment_status' => 'enrolled',
                'is_active'         => true,
            ]
        );

        // 2. Akun Admin HRD Default
        User::updateOrCreate(
            ['nik' => 'ADM001'],
            [
                'name'              => 'Admin HRD PT. CAK',
                'email'             => 'hrd@ptcak.com',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'jabatan'           => 'HR Specialist',
                'department'        => 'Human Resources',
                'no_telp'           => '081234567891',
                'enrollment_status' => 'enrolled',
                'is_active'         => true,
            ]
        );

        // 3. Lokasi Default: Kantor Pusat PT. CAK
        Location::updateOrCreate(
            ['name' => 'Kantor Pusat PT. CAK'],
            [
                'latitude'      => -0.1333000,
                'longitude'     => 117.4833000,
                'radius_meters' => 100,
                'is_active'     => true,
            ]
        );

        // 4. Pengaturan Kebijakan Kerja Global
        $defaultSettings = [
            ['key' => 'jam_masuk', 'value' => '08:00', 'description' => 'Jam masuk kerja standar (WITA)'],
            ['key' => 'jam_pulang', 'value' => '17:00', 'description' => 'Jam pulang kerja standar (WITA)'],
            ['key' => 'toleransi_terlambat', 'value' => '15', 'description' => 'Batas waktu toleransi keterlambatan (menit)'],
            ['key' => 'jam_istirahat', 'value' => '60', 'description' => 'Durasi istirahat harian dalam menit (pengurang jam kerja)'],
            ['key' => 'hari_kerja', 'value' => 'senin,selasa,rabu,kamis,jumat', 'description' => 'Daftar hari kerja operasional aktif (dipisahkan koma)'],
            ['key' => 'max_retroaktif_izin', 'value' => '3', 'description' => 'Batas maksimal hari mundur untuk pengajuan izin lampau'],
        ];

        foreach ($defaultSettings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
