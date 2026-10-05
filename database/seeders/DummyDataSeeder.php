<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\FaceDescriptor;
use App\Models\Leave;
use App\Models\Location;
use App\Models\LocationTrack;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::today('Asia/Makassar')->format('Y-m-d');
        $yesterday = Carbon::yesterday('Asia/Makassar')->format('Y-m-d');

        // ==========================================
        // 1. MASTER PENGATURAN KEBIJAKAN SISTEM
        // ==========================================
        $settings = [
            ['key' => 'jam_masuk', 'value' => '08:00', 'description' => 'Jam masuk kerja standar (WITA)'],
            ['key' => 'jam_pulang', 'value' => '17:00', 'description' => 'Jam pulang kerja standar (WITA)'],
            ['key' => 'toleransi_terlambat', 'value' => '15', 'description' => 'Batas toleransi keterlambatan (menit)'],
            ['key' => 'jam_istirahat', 'value' => '60', 'description' => 'Durasi istirahat harian dalam menit'],
            ['key' => 'hari_kerja', 'value' => 'senin,selasa,rabu,kamis,jumat', 'description' => 'Hari kerja operasional aktif'],
            ['key' => 'max_retroaktif_izin', 'value' => '3', 'description' => 'Batas maksimal hari mundur pengajuan izin'],
            ['key' => 'tracking_interval_minutes', 'value' => '5', 'description' => 'Interval pengiriman lokasi SPG/karyawan (menit)'],
            ['key' => 'tracking_max_accuracy', 'value' => '100', 'description' => 'Batas maksimal toleransi akurasi GPS (meter)'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        // ==========================================
        // 2. MASTER TITIK LOKASI & GEOFENCE
        // ==========================================
        $locations = [
            [
                'name'          => 'Kantor Pusat PT. CAK (Bontang)',
                'latitude'      => -0.1333000,
                'longitude'     => 117.4833000,
                'radius_meters' => 100,
                'is_active'     => true,
            ],
            [
                'name'          => 'Site Tambang Muara Badak (Pit A)',
                'latitude'      => -0.3125000,
                'longitude'     => 117.3850000,
                'radius_meters' => 250,
                'is_active'     => true,
            ],
            [
                'name'          => 'Site Workshop & Logistik Sangatta',
                'latitude'      => 0.4900000,
                'longitude'     => 117.5400000,
                'radius_meters' => 150,
                'is_active'     => true,
            ],
            [
                'name'          => 'Pelabuhan Jetty Muara Berau',
                'latitude'      => -0.5200000,
                'longitude'     => 117.6100000,
                'radius_meters' => 200,
                'is_active'     => true,
            ],
            [
                'name'          => 'Site Samarinda Seberang (Standby)',
                'latitude'      => -0.5400000,
                'longitude'     => 117.1400000,
                'radius_meters' => 100,
                'is_active'     => false,
            ],
        ];

        $locModels = [];
        foreach ($locations as $loc) {
            $locModels[$loc['name']] = Location::updateOrCreate(['name' => $loc['name']], $loc);
        }

        $locBontang = $locModels['Kantor Pusat PT. CAK (Bontang)'];
        $locMuaraBadak = $locModels['Site Tambang Muara Badak (Pit A)'];
        $locSangatta = $locModels['Site Workshop & Logistik Sangatta'];
        $locJetty = $locModels['Pelabuhan Jetty Muara Berau'];

        // ==========================================
        // 3. MASTER DATA AKUN PENGGUNA (USERS)
        // ==========================================
        $dummyDescriptor = array_fill(0, 128, 0.123);

        $users = [
            // A. Super Admin
            [
                'nik'               => 'SA001',
                'name'              => 'Super Administrator',
                'email'             => 'superadmin@ptcak.com',
                'password'          => Hash::make('password'),
                'role'              => 'superadmin',
                'jabatan'           => 'IT Administrator',
                'department'        => 'IT & Infrastructure',
                'no_telp'           => '081234567890',
                'enrollment_status' => 'enrolled',
                'is_active'         => true,
            ],
            // B. HRD / Admin Presensi
            [
                'nik'               => 'ADM001',
                'name'              => 'Siti Rahmawati, S.Psi',
                'email'             => 'hrd@ptcak.com',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'jabatan'           => 'HR Specialist & Attendance Lead',
                'department'        => 'Human Resources',
                'no_telp'           => '081234567891',
                'enrollment_status' => 'enrolled',
                'is_active'         => true,
            ],
            [
                'nik'               => 'ADM002',
                'name'              => 'Bambang Pratama',
                'email'             => 'admin.operasional@ptcak.com',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'jabatan'           => 'Operations Administrator',
                'department'        => 'Operasional Tambang',
                'no_telp'           => '081234567892',
                'enrollment_status' => 'enrolled',
                'is_active'         => true,
            ],

            // C. Karyawan Enrolled (Berbagai Departemen)
            [
                'nik'               => 'KAR001',
                'name'              => 'Budi Santoso',
                'email'             => 'budi.santoso@ptcak.com',
                'password'          => Hash::make('password'),
                'role'              => 'karyawan',
                'jabatan'           => 'Heavy Equipment Operator',
                'department'        => 'Operasional Tambang',
                'no_telp'           => '081345678901',
                'enrollment_status' => 'enrolled',
                'is_active'         => true,
            ],
            [
                'nik'               => 'KAR002',
                'name'              => 'Agus Setiawan',
                'email'             => 'agus.setiawan@ptcak.com',
                'password'          => Hash::make('password'),
                'role'              => 'karyawan',
                'jabatan'           => 'Dump Truck Driver',
                'department'        => 'Operasional Tambang',
                'no_telp'           => '081345678902',
                'enrollment_status' => 'enrolled',
                'is_active'         => true,
            ],
            [
                'nik'               => 'KAR003',
                'name'              => 'Dewi Lestari',
                'email'             => 'dewi.lestari@ptcak.com',
                'password'          => Hash::make('password'),
                'role'              => 'karyawan',
                'jabatan'           => 'Senior Accountant',
                'department'        => 'Keuangan & Akuntansi',
                'no_telp'           => '081345678903',
                'enrollment_status' => 'enrolled',
                'is_active'         => true,
            ],
            [
                'nik'               => 'KAR004',
                'name'              => 'Eko Prasetyo',
                'email'             => 'eko.prasetyo@ptcak.com',
                'password'          => Hash::make('password'),
                'role'              => 'karyawan',
                'jabatan'           => 'Warehouse Supervisor',
                'department'        => 'Logistik & Gudang',
                'no_telp'           => '081345678904',
                'enrollment_status' => 'enrolled',
                'is_active'         => true,
            ],
            [
                'nik'               => 'KAR005',
                'name'              => 'Fajar Hidayat',
                'email'             => 'fajar.hidayat@ptcak.com',
                'password'          => Hash::make('password'),
                'role'              => 'karyawan',
                'jabatan'           => 'Mechanic Lead',
                'department'        => 'Engineering & Maintenance',
                'no_telp'           => '081345678905',
                'enrollment_status' => 'enrolled',
                'is_active'         => true,
            ],
            [
                'nik'               => 'KAR006',
                'name'              => 'Hendra Wijaya',
                'email'             => 'hendra.wijaya@ptcak.com',
                'password'          => Hash::make('password'),
                'role'              => 'karyawan',
                'jabatan'           => 'Safety Officer (HSE)',
                'department'        => 'HSE & Safety',
                'no_telp'           => '081345678906',
                'enrollment_status' => 'enrolled',
                'is_active'         => true,
            ],
            [
                'nik'               => 'KAR007',
                'name'              => 'Indah Permata',
                'email'             => 'indah.permata@ptcak.com',
                'password'          => Hash::make('password'),
                'role'              => 'karyawan',
                'jabatan'           => 'HR Staff',
                'department'        => 'Human Resources',
                'no_telp'           => '081345678907',
                'enrollment_status' => 'enrolled',
                'is_active'         => true,
            ],
            [
                'nik'               => 'KAR008',
                'name'              => 'Joko Susilo',
                'email'             => 'joko.susilo@ptcak.com',
                'password'          => Hash::make('password'),
                'role'              => 'karyawan',
                'jabatan'           => 'Mining Surveyor',
                'department'        => 'Operasional Tambang',
                'no_telp'           => '081345678908',
                'enrollment_status' => 'enrolled',
                'is_active'         => true,
            ],
            [
                'nik'               => 'KAR009',
                'name'              => 'Rizky Kurniawan',
                'email'             => 'kurniawan@ptcak.com',
                'password'          => Hash::make('password'),
                'role'              => 'karyawan',
                'jabatan'           => 'Junior Systems Developer',
                'department'        => 'IT & Infrastructure',
                'no_telp'           => '081345678909',
                'enrollment_status' => 'enrolled',
                'is_active'         => true,
            ],
            [
                'nik'               => 'KAR010',
                'name'              => 'Nur Hidayah',
                'email'             => 'nur.hidayah@ptcak.com',
                'password'          => Hash::make('password'),
                'role'              => 'karyawan',
                'jabatan'           => 'Billing & Cashier',
                'department'        => 'Keuangan & Akuntansi',
                'no_telp'           => '081345678910',
                'enrollment_status' => 'enrolled',
                'is_active'         => true,
            ],

            // D. Karyawan Baru (Pending Enrollment)
            [
                'nik'               => 'KAR011',
                'name'              => 'Aditya Nugraha',
                'email'             => 'aditya.nugraha@ptcak.com',
                'password'          => Hash::make('ptcak123'),
                'role'              => 'karyawan',
                'jabatan'           => 'Field Technician',
                'department'        => 'Operasional Tambang',
                'no_telp'           => '081345678911',
                'enrollment_status' => 'pending',
                'is_active'         => true,
            ],
            [
                'nik'               => 'KAR012',
                'name'              => 'Bayu Samudra',
                'email'             => 'bayu.samudra@ptcak.com',
                'password'          => Hash::make('ptcak123'),
                'role'              => 'karyawan',
                'jabatan'           => 'Staff Logistik',
                'department'        => 'Logistik & Gudang',
                'no_telp'           => '081345678912',
                'enrollment_status' => 'pending',
                'is_active'         => true,
            ],
        ];

        $userModels = [];
        foreach ($users as $userData) {
            $user = User::updateOrCreate(['nik' => $userData['nik']], $userData);
            $userModels[$userData['nik']] = $user;

            // Generate face descriptor untuk user yang enrolled
            if ($user->enrollment_status === 'enrolled') {
                FaceDescriptor::updateOrCreate(
                    ['user_id' => $user->id],
                    ['descriptor_data' => $dummyDescriptor, 'sample_photo' => null]
                );
            }
        }

        // ==========================================
        // 4. DATA DUMMY PRESENSI HARI INI
        // ==========================================
        // Budi Santoso (Hadir Tepat Waktu di Muara Badak)
        Attendance::updateOrCreate(
            ['user_id' => $userModels['KAR001']->id, 'date' => $today],
            [
                'location_id'     => $locMuaraBadak->id,
                'time_in'         => '07:45:12',
                'time_out'        => null,
                'lat_in'          => -0.3125100,
                'long_in'         => 117.3850200,
                'distance_meters' => 12.5,
                'status'          => 'tepat_waktu',
                'auto_checkout'   => false,
            ]
        );

        // Agus Setiawan (Hadir Terlambat di Muara Badak)
        Attendance::updateOrCreate(
            ['user_id' => $userModels['KAR002']->id, 'date' => $today],
            [
                'location_id'     => $locMuaraBadak->id,
                'time_in'         => '08:24:05',
                'time_out'        => null,
                'lat_in'          => -0.3125200,
                'long_in'         => 117.3850500,
                'distance_meters' => 18.0,
                'status'          => 'terlambat',
                'auto_checkout'   => false,
            ]
        );

        // Dewi Lestari (Hadir Tepat Waktu + Sudah Check-Out di Kantor Pusat)
        Attendance::updateOrCreate(
            ['user_id' => $userModels['KAR003']->id, 'date' => $today],
            [
                'location_id'     => $locBontang->id,
                'time_in'         => '07:50:00',
                'time_out'        => '17:05:30',
                'lat_in'          => -0.1333100,
                'long_in'         => 117.4833200,
                'lat_out'         => -0.1333100,
                'long_out'        => 117.4833200,
                'distance_meters' => 15.0,
                'status'          => 'tepat_waktu',
                'auto_checkout'   => false,
            ]
        );

        // Eko Prasetyo (Hadir di Sangatta)
        Attendance::updateOrCreate(
            ['user_id' => $userModels['KAR004']->id, 'date' => $today],
            [
                'location_id'     => $locSangatta->id,
                'time_in'         => '07:58:20',
                'time_out'        => null,
                'lat_in'          => 0.4900100,
                'long_in'         => 117.5400200,
                'distance_meters' => 8.2,
                'status'          => 'tepat_waktu',
                'auto_checkout'   => false,
            ]
        );

        // Fajar Hidayat (Hadir di Sangatta)
        Attendance::updateOrCreate(
            ['user_id' => $userModels['KAR005']->id, 'date' => $today],
            [
                'location_id'     => $locSangatta->id,
                'time_in'         => '08:02:10',
                'time_out'        => null,
                'lat_in'          => 0.4900200,
                'long_in'         => 117.5400300,
                'distance_meters' => 14.1,
                'status'          => 'tepat_waktu',
                'auto_checkout'   => false,
            ]
        );

        // Hendra Wijaya (Hadir di Jetty Muara Berau)
        Attendance::updateOrCreate(
            ['user_id' => $userModels['KAR006']->id, 'date' => $today],
            [
                'location_id'     => $locJetty->id,
                'time_in'         => '07:35:40',
                'time_out'        => null,
                'lat_in'          => -0.5200100,
                'long_in'         => 117.6100200,
                'distance_meters' => 22.0,
                'status'          => 'tepat_waktu',
                'auto_checkout'   => false,
            ]
        );

        // Indah Permata (Hadir di Kantor Pusat)
        Attendance::updateOrCreate(
            ['user_id' => $userModels['KAR007']->id, 'date' => $today],
            [
                'location_id'     => $locBontang->id,
                'time_in'         => '07:55:00',
                'time_out'        => null,
                'lat_in'          => -0.1333050,
                'long_in'         => 117.4833100,
                'distance_meters' => 6.5,
                'status'          => 'tepat_waktu',
                'auto_checkout'   => false,
            ]
        );

        // ==========================================
        // 5. DATA DUMMY PENGAJUAN IZIN & CUTI
        // ==========================================
        // Joko Susilo (Sedang Cuti Tahunan Hari Ini)
        Leave::updateOrCreate(
            ['user_id' => $userModels['KAR008']->id, 'start_date' => $today],
            [
                'type'        => 'cuti',
                'end_date'    => Carbon::today('Asia/Makassar')->addDays(2)->format('Y-m-d'),
                'reason'      => 'Cuti tahunan keluarga di luar kota.',
                'status'      => 'approved',
                'approved_by' => $userModels['ADM001']->id,
                'review_note' => 'Disetujui, selamat berlibur.',
            ]
        );

        // Record attendance cuti untuk Joko Susilo
        Attendance::updateOrCreate(
            ['user_id' => $userModels['KAR008']->id, 'date' => $today],
            ['status' => 'cuti']
        );

        // Rizky Kurniawan (Pengajuan Sakit - Menunggu Persetujuan / Pending)
        Leave::updateOrCreate(
            ['user_id' => $userModels['KAR009']->id, 'start_date' => $today],
            [
                'type'        => 'sakit',
                'end_date'    => $today,
                'reason'      => 'Demam tinggi dan flu berat sesuai anjuran dokter.',
                'status'      => 'pending',
            ]
        );

        // Nur Hidayah (Pengajuan Izin Keperluan Mendesak - Ditolak)
        Leave::updateOrCreate(
            ['user_id' => $userModels['KAR010']->id, 'start_date' => $yesterday],
            [
                'type'        => 'izin',
                'end_date'    => $yesterday,
                'reason'      => 'Urusan perpanjangan administrasi kendaraan bermotor.',
                'status'      => 'rejected',
                'approved_by' => $userModels['ADM001']->id,
                'review_note' => 'Mohon dijadwalkan pada hari libur atau akhir pekan.',
            ]
        );

        // ==========================================
        // 6. DATA DUMMY NOTIFIKASI IN-APP
        // ==========================================
        foreach ($userModels as $u) {
            Notification::create([
                'user_id' => $u->id,
                'title'   => 'Selamat Datang di Sistem Presensi PWA PT. CAK',
                'message' => "Halo {$u->name}, akun Anda telah terdaftar di sistem presensi digital PT. Cahaya Anugrah Kalimantan.",
                'type'    => 'reminder_presensi',
                'is_read' => true,
            ]);
        }

        Notification::create([
            'user_id' => $userModels['KAR008']->id,
            'title'   => 'Pengajuan Cuti Disetujui',
            'message' => 'Permohonan Cuti Tahunan Anda untuk periode ' . $today . ' telah disetujui oleh HRD.',
            'type'    => 'status_izin',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $userModels['KAR009']->id,
            'title'   => 'Pengajuan Izin Sakit Diterima',
            'message' => 'Permohonan izin sakit Anda sedang dalam antrean review oleh Tim HRD.',
            'type'    => 'status_izin',
            'is_read' => false,
        ]);

        // ==========================================
        // 7. DATA DUMMY REKAMAN JEJAK LOKASI SPG (LIVE TRACKS)
        // ==========================================
        // Ambil data presensi hari ini dan kemarin untuk KAR001 & KAR002
        $attBudiToday = Attendance::where('user_id', $userModels['KAR001']->id)->whereDate('date', $today)->first();
        $attBudiYesterday = Attendance::where('user_id', $userModels['KAR001']->id)->whereDate('date', $yesterday)->first();

        // Rute SPG Budi Santoso Hari Ini (Sekitar Samarinda / Muara Badak)
        if ($attBudiToday) {
            $baseLat = -0.3125000;
            $baseLng = 117.3850000;

            $routePoints = [
                ['offsetLat' => 0.0000, 'offsetLng' => 0.0000, 'minute' => 0],
                ['offsetLat' => 0.0025, 'offsetLng' => 0.0018, 'minute' => 15],
                ['offsetLat' => 0.0058, 'offsetLng' => 0.0042, 'minute' => 30],
                ['offsetLat' => 0.0092, 'offsetLng' => 0.0075, 'minute' => 45],
                ['offsetLat' => 0.0125, 'offsetLng' => 0.0110, 'minute' => 60],
                ['offsetLat' => 0.0160, 'offsetLng' => 0.0145, 'minute' => 75],
                ['offsetLat' => 0.0185, 'offsetLng' => 0.0180, 'minute' => 90],
                ['offsetLat' => 0.0150, 'offsetLng' => 0.0210, 'minute' => 120],
                ['offsetLat' => 0.0110, 'offsetLng' => 0.0245, 'minute' => 150],
                ['offsetLat' => 0.0070, 'offsetLng' => 0.0270, 'minute' => 180],
                ['offsetLat' => 0.0035, 'offsetLng' => 0.0295, 'minute' => 210],
                ['offsetLat' => 0.0010, 'offsetLng' => 0.0315, 'minute' => 240],
            ];

            $startTime = Carbon::parse($today . ' 07:45:00', 'Asia/Makassar');
            foreach ($routePoints as $pt) {
                LocationTrack::create([
                    'user_id'       => $userModels['KAR001']->id,
                    'attendance_id' => $attBudiToday->id,
                    'date'          => $today,
                    'latitude'      => round($baseLat + $pt['offsetLat'], 7),
                    'longitude'     => round($baseLng + $pt['offsetLng'], 7),
                    'accuracy'      => rand(8, 25),
                    'recorded_at'   => (clone $startTime)->addMinutes($pt['minute']),
                ]);
            }
        }

        // Rute SPG Budi Santoso Kemarin
        if ($attBudiYesterday) {
            $baseLat = -0.3125000;
            $baseLng = 117.3850000;

            $routePointsYesterday = [
                ['offsetLat' => 0.0000, 'offsetLng' => 0.0000, 'minute' => 0],
                ['offsetLat' => -0.0030, 'offsetLng' => 0.0025, 'minute' => 20],
                ['offsetLat' => -0.0065, 'offsetLng' => 0.0055, 'minute' => 40],
                ['offsetLat' => -0.0090, 'offsetLng' => 0.0090, 'minute' => 60],
                ['offsetLat' => -0.0120, 'offsetLng' => 0.0120, 'minute' => 90],
                ['offsetLat' => -0.0150, 'offsetLng' => 0.0160, 'minute' => 120],
                ['offsetLat' => -0.0110, 'offsetLng' => 0.0190, 'minute' => 180],
                ['offsetLat' => -0.0050, 'offsetLng' => 0.0220, 'minute' => 240],
            ];

            $startYesterday = Carbon::parse($yesterday . ' 07:50:00', 'Asia/Makassar');
            foreach ($routePointsYesterday as $pt) {
                LocationTrack::create([
                    'user_id'       => $userModels['KAR001']->id,
                    'attendance_id' => $attBudiYesterday->id,
                    'date'          => $yesterday,
                    'latitude'      => round($baseLat + $pt['offsetLat'], 7),
                    'longitude'     => round($baseLng + $pt['offsetLng'], 7),
                    'accuracy'      => rand(8, 20),
                    'recorded_at'   => (clone $startYesterday)->addMinutes($pt['minute']),
                ]);
            }
        }
    }
}
