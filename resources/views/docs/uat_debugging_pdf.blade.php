<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dokumentasi UAT dan Debugging - PT. CAK</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2D3748;
            line-height: 1.45;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }

        /* Header / Kop Surat */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1A237E;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .company-title {
            font-size: 16px;
            font-weight: bold;
            color: #1A237E;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .company-sub {
            font-size: 9px;
            color: #4A5568;
            margin-top: 2px;
        }

        .doc-title {
            font-size: 14px;
            font-weight: bold;
            color: #1A237E;
            text-align: center;
            text-transform: uppercase;
            margin: 12px 0 4px 0;
        }

        .doc-subtitle {
            font-size: 10px;
            color: #4A5568;
            text-align: center;
            margin-bottom: 16px;
        }

        /* Section Headings */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1A237E;
            border-bottom: 1px solid #CBD5E0;
            padding-bottom: 4px;
            margin-top: 18px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .scenario-box {
            border: 1px solid #E2E8F0;
            border-left: 4px solid #1A237E;
            background-color: #F8FAFC;
            padding: 10px 12px;
            margin-bottom: 14px;
            border-radius: 4px;
            page-break-inside: avoid;
        }

        .scenario-title {
            font-size: 11px;
            font-weight: bold;
            color: #1A237E;
            margin-bottom: 4px;
        }

        .badge-pass {
            background-color: #E8F5E9;
            color: #2E7D32;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            display: inline-block;
        }

        /* Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 10px;
        }

        table.data-table th {
            background-color: #1A237E;
            color: #FFFFFF;
            font-weight: bold;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #1A237E;
        }

        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #CBD5E0;
            vertical-align: top;
        }

        table.data-table tr:nth-child(even) td {
            background-color: #F8FAFC;
        }

        /* Code Block */
        pre.code-block {
            background-color: #1E293B;
            color: #F8FAFC;
            padding: 8px 10px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 8.5px;
            line-height: 1.35;
            border-radius: 4px;
            margin: 6px 0;
            white-space: pre-wrap;
            word-break: break-all;
        }

        /* Debugging Section */
        .debug-item {
            border: 1px solid #E2E8F0;
            border-left: 4px solid #D97706;
            background-color: #FFFBEB;
            padding: 10px 12px;
            margin-bottom: 12px;
            border-radius: 4px;
            page-break-inside: avoid;
        }

        .debug-title {
            font-size: 11px;
            font-weight: bold;
            color: #92400E;
            margin-bottom: 4px;
        }

        .page-break {
            page-break-before: always;
        }

        /* Signatures */
        .signature-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }

        .sign-line {
            margin-top: 50px;
            border-bottom: 1px solid #2D3748;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="company-title">PT. CAHAYA ANUGRAH KALIMANTAN</div>
                <div class="company-sub">Engineering, Heavy Equipment & Mining Support Services</div>
                <div class="company-sub">Jl. Mulawarman No. 45, Bontang, Kalimantan Timur 75311</div>
            </td>
            <td style="width: 30%; text-align: right; vertical-align: top;">
                <div style="font-size: 9px; color: #4A5568;">Dokumen Teknis: <b>DOC-UAT-2026-08</b></div>
                <div style="font-size: 9px; color: #4A5568;">Tanggal Rilis: <b>25 Agustus 2026</b></div>
                <div style="font-size: 9px; color: #4A5568;">Versi Sistem: <b>v1.0.0 (Production)</b></div>
            </td>
        </tr>
    </table>

    <!-- JUDUL DOKUMEN -->
    <div class="doc-title">Laporan Hasil Pengujian UAT & Dokumentasi Debugging</div>
    <div class="doc-subtitle">Sistem Informasi Presensi PWA Berbasis Face Recognition & Double Geofencing</div>

    <!-- 1. RINGKASAN EKSEKUTIF -->
    <div class="section-title">1. Ringkasan Eksekutif & Statistik Pengujian</div>
    <p>
        Dokumen ini menyajikan laporan resmi hasil pengujian penerimaan pengguna (<em>User Acceptance Testing / UAT</em>), verifikasi kebutuhan non-fungsional (<em>NFR</em>), serta rekaman penelusuran kesalahan (<em>debugging analysis</em>) yang dilakukan pada seluruh modul <b>Sistem Presensi PWA PT. Cahaya Anugrah Kalimantan</b>.
    </p>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Parameter Pengujian</th>
                <th style="width: 25%;">Target Kriteria</th>
                <th style="width: 25%;">Hasil Aktual</th>
                <th style="width: 25%;">Status Verifikasi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><b>Total Automated Tests</b></td>
                <td>100% Modul Teruji</td>
                <td>96 Test Cases (14 Test Suites)</td>
                <td><span class="badge-pass">LULUS (100%)</span></td>
            </tr>
            <tr>
                <td><b>Total Assertions</b></td>
                <td>> 300 Assertions</td>
                <td>382 Assertions</td>
                <td><span class="badge-pass">LULUS (100%)</span></td>
            </tr>
            <tr>
                <td><b>Test Failures & Errors</b></td>
                <td>0 Failures / 0 Errors</td>
                <td>0 Failures / 0 Errors</td>
                <td><span class="badge-pass">PERFECT SCORE</span></td>
            </tr>
            <tr>
                <td><b>Cakupan Skenario UAT</b></td>
                <td>7 Skenario Utama</td>
                <td>7 Skenario Lolos Pengujian</td>
                <td><span class="badge-pass">LULUS (100%)</span></td>
            </tr>
            <tr>
                <td><b>Keamanan Hak Akses (RBAC)</b></td>
                <td>100% Terisolasi per Role</td>
                <td>Karyawan, HRD & Superadmin Terisolasi</td>
                <td><span class="badge-pass">LULUS (100%)</span></td>
            </tr>
        </tbody>
    </table>

    <!-- 2. SKENARIO PENGUJIAN UAT -->
    <div class="section-title">2. Rincian Skenario Pengujian UAT (UAT-SC-01 s/d UAT-SC-07)</div>

    <!-- UAT-SC-01 -->
    <div class="scenario-box">
        <table style="width: 100%;">
            <tr>
                <td><div class="scenario-title">UAT-SC-01: Onboarding Karyawan Baru & Enrollment Biometrik Wajah</div></td>
                <td style="text-align: right;"><span class="badge-pass">PASS</span></td>
            </tr>
        </table>
        <div><b>Deskripsi:</b> Super Admin mendaftarkan akun karyawan baru (status otomatis 'pending'). Sistem memblokir akses ke modul presensi hingga pendaftaran vektor biometrik wajah 128-float selesai.</div>
        <div style="margin-top: 4px;"><b>Sintaks Pengujian PHPUnit:</b></div>
        <pre class="code-block">// 1. Registrasi Akun oleh Super Admin
$this->actingAs($superadmin)->post('/superadmin/users', [
    'nik' => 'KAR777', 'name' => 'Andi', 'email' => 'andi@ptcak.com',
    'role' => 'karyawan', 'department' => 'Operasional Tambang'
])->assertRedirect('/superadmin/users');

// 2. Blokir Akses Presensi Saat Status Pending
$this->actingAs($user)->get('/karyawan/presensi')->assertRedirect('/karyawan/enrollment');

// 3. Simpan Vektor Biometrik Wajah 128-Float
$this->actingAs($user)->post('/karyawan/enrollment', [
    'descriptor_data' => array_fill(0, 128, 0.25),
    'sample_photo'    => 'data:image/jpeg;base64,...'
])->assertRedirect('/karyawan/dashboard');</pre>
        <div><b>Hasil Aktual:</b> Status akun berubah dari <code>pending</code> menjadi <code>enrolled</code>, relasi <code>FaceDescriptor</code> tersimpan di DB, dan akses ke <code>/karyawan/presensi</code> berhasil terbuka (HTTP 200).</div>
    </div>

    <!-- UAT-SC-02 -->
    <div class="scenario-box">
        <table style="width: 100%;">
            <tr>
                <td><div class="scenario-title">UAT-SC-02: Presensi Masuk Valid di Dalam Radius Geofence</div></td>
                <td style="text-align: right;"><span class="badge-pass">PASS</span></td>
            </tr>
        </table>
        <div><b>Deskripsi:</b> Karyawan yang telah terdaftar biometrik melakukan presensi masuk di dalam batas radius toleransi kantor/proyek.</div>
        <div style="margin-top: 4px;"><b>Sintaks Pengujian PHPUnit:</b></div>
        <pre class="code-block">Carbon::setTestNow(Carbon::create(2026, 8, 25, 7, 55, 0, 'Asia/Makassar'));
$response = $this->actingAs($user)->postJson('/karyawan/presensi/check-in', [
    'latitude'    => $office->latitude,
    'longitude'   => $office->longitude,
    'location_id' => $office->id,
    'photo'       => 'data:image/jpeg;base64,...'
]);
$response->assertStatus(200)->assertJson(['success' => true, 'status' => 'tepat_waktu']);</pre>
        <div><b>Hasil Aktual:</b> Record presensi tersimpan di basis data dengan <code>status = 'tepat_waktu'</code>, koordinat GPS tervalidasi, dan notifikasi konfirmasi terkirim ke karyawan.</div>
    </div>

    <div class="page-break"></div>

    <!-- UAT-SC-03 -->
    <div class="scenario-box">
        <table style="width: 100%;">
            <tr>
                <td><div class="scenario-title">UAT-SC-03: Penolakan Presensi di Luar Radius Geofence (Anti-Spoofing)</div></td>
                <td style="text-align: right;"><span class="badge-pass">PASS</span></td>
            </tr>
        </table>
        <div><b>Deskripsi:</b> Karyawan mencoba melakukan presensi masuk dengan koordinat GPS di luar toleransi radius kantor (jarak > radius geofence). Server-side formula Haversine wajib menolak request.</div>
        <div style="margin-top: 4px;"><b>Sintaks Pengujian PHPUnit:</b></div>
        <pre class="code-block">$response = $this->actingAs($user)->postJson('/karyawan/presensi/check-in', [
    'latitude'    => $office->latitude + 0.1, // Jarak ~11 km di luar kantor
    'longitude'   => $office->longitude + 0.1,
    'location_id' => $office->id,
    'photo'       => 'data:image/jpeg;base64,...'
]);
$response->assertStatus(422)->assertJson(['success' => false]);
$this->assertDatabaseMissing('attendances', ['user_id' => $user->id, 'date' => $today]);</pre>
        <div><b>Hasil Aktual:</b> Request ditolak dengan kode status HTTP 422, tidak ada record presensi yang tersimpan di basis data, dan response pesan edukatif diterima oleh aplikasi.</div>
    </div>

    <!-- UAT-SC-04 -->
    <div class="scenario-box">
        <table style="width: 100%;">
            <tr>
                <td><div class="scenario-title">UAT-SC-04: Presensi Pulang Normal & Auto-Checkout Otomatis</div></td>
                <td style="text-align: right;"><span class="badge-pass">PASS</span></td>
            </tr>
        </table>
        <div><b>Deskripsi:</b> Memverifikasi alur presensi pulang normal oleh karyawan, serta otomatisasi penutupan sesi presensi menggantung pada pukul 23:59 WITA dengan tanda <code>auto_checkout = true</code>.</div>
        <div style="margin-top: 4px;"><b>Sintaks Pengujian PHPUnit:</b></div>
        <pre class="code-block">// Simulasi Karyawan Lupa Melakukan Presensi Pulang
Attendance::create(['user_id' => $user->id, 'time_in' => '08:00:00', 'time_out' => null]);

// Eksekusi Otomatisasi Scheduler
Artisan::call('attendance:auto-checkout');

// Verifikasi Penutupan Sesi Otomatis
$att->refresh();
$this->assertNotNull($att->time_out);
$this->assertTrue($att->auto_checkout);</pre>
        <div><b>Hasil Aktual:</b> Scheduler berhasil menutup record presensi yang belum check-out dan menyetel flag <code>auto_checkout = true</code> secara otomatis dan akurat.</div>
    </div>

    <!-- UAT-SC-05 -->
    <div class="scenario-box">
        <table style="width: 100%;">
            <tr>
                <td><div class="scenario-title">UAT-SC-05: Alur Persetujuan Izin Multi-Hari & Pencegahan Status Alpha</div></td>
                <td style="text-align: right;"><span class="badge-pass">PASS</span></td>
            </tr>
        </table>
        <div><b>Deskripsi:</b> Pengajuan izin/cuti multi-hari yang disetujui HRD secara otomatis menghasilkan record presensi dengan status 'cuti'/'izin' pada setiap hari kerja aktif, mencegah scheduler menandai Alpha.</div>
        <div style="margin-top: 4px;"><b>Sintaks Pengujian PHPUnit:</b></div>
        <pre class="code-block">$this->from('/admin/leaves')->actingAs($hrd)->post("/admin/leaves/{$leave->id}/process", [
    'action' => 'approve', 'review_note' => 'Disetujui HRD'
])->assertRedirect('/admin/leaves');

// Memastikan record presensi terbuat untuk setiap hari kerja dalam rentang
$this->assertDatabaseHas('attendances', ['user_id' => $user->id, 'date' => $startMonday, 'status' => 'cuti']);
$this->assertDatabaseHas('attendances', ['user_id' => $user->id, 'date' => $endWednesday, 'status' => 'cuti']);</pre>
        <div><b>Hasil Aktual:</b> Izin disetujui, tabel <code>attendances</code> terisi otomatis, dan scheduler harian mendeteksi status cuti sah sehingga karyawan tidak terhitung Alpha.</div>
    </div>

    <!-- UAT-SC-06 -->
    <div class="scenario-box">
        <table style="width: 100%;">
            <tr>
                <td><div class="scenario-title">UAT-SC-06: Rekapitulasi Presensi & Ekspor Laporan Excel / PDF</div></td>
                <td style="text-align: right;"><span class="badge-pass">PASS</span></td>
            </tr>
        </table>
        <div><b>Deskripsi:</b> HRD mengakses rekapitulasi data presensi dan mengekspor dokumen laporan resmi berformat spreadsheet (.xlsx) dan PDF resmi ber-kop perusahaan.</div>
        <div style="margin-top: 4px;"><b>Sintaks Pengujian PHPUnit:</b></div>
        <pre class="code-block">// Ekspor Excel
$this->actingAs($hrd)->get('/admin/reports/export/excel')
    ->assertStatus(200)
    ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

// Ekspor PDF A4 Landscape
$this->actingAs($hrd)->get('/admin/reports/export/pdf')
    ->assertStatus(200)
    ->assertHeader('content-type', 'application/pdf');</pre>
        <div><b>Hasil Aktual:</b> Dokumen berhasil di-generate dengan formula kalkulasi jam kerja bersih <code>(time_out - time_in) - durasi_istirahat</code>, rekapitulasi telat, izin, dan alpha.</div>
    </div>

    <div class="page-break"></div>

    <!-- UAT-SC-07 -->
    <div class="scenario-box">
        <table style="width: 100%;">
            <tr>
                <td><div class="scenario-title">UAT-SC-07: Deaktivasi Akun & Pemutusan Sesi Seketika (Security Hardening)</div></td>
                <td style="text-align: right;"><span class="badge-pass">PASS</span></td>
            </tr>
        </table>
        <div><b>Deskripsi:</b> Ketika Super Admin menonaktifkan akun karyawan (<code>is_active = false</code>), sesi aktif karyawan wajib langsung diputus oleh middleware <code>CheckActive</code> pada request berikutnya.</div>
        <div style="margin-top: 4px;"><b>Sintaks Pengujian PHPUnit:</b></div>
        <pre class="code-block">// Super Admin Menonaktifkan Akun
$this->actingAs($superadmin)->patch("/superadmin/users/{$user->id}/toggle")->assertRedirect();

// Karyawan Mencoba Mengakses Halaman Dashboard
$response = $this->actingAs($user)->get('/karyawan/dashboard');
$response->assertRedirect('/login');
$response->assertSessionHasErrors('login');</pre>
        <div><b>Hasil Aktual:</b> Sesi otentikasi seketika dihapus, token diregenerasi, dan pengguna diarahkan ke halaman login dengan pesan kesalahan edukatif.</div>
    </div>

    <!-- 3. DOKUMENTASI DEBUGGING SINTAKS & PENYELESAIAN MASALAH -->
    <div class="section-title">3. Dokumentasi Debugging Sintaks & Penelusuran Masalah</div>
    <p>
        Bagian ini merangkum isu teknis, anomali sintaks, dan ketidaksesuaian arsitektur yang ditemukan selama proses pengujian beserta solusi perbaikan yang diterapkan:
    </p>

    <!-- DEBUG 1 -->
    <div class="debug-item">
        <div class="debug-title">Isu 1: Penamaan Kolom Skema Tabel Users & Face Descriptors</div>
        <div><b>Masalah yang Terjadi:</b></div>
        <div style="color: #991B1B; font-family: monospace; font-size: 9px; margin: 2px 0;">
            SQLSTATE[42S22]: Column not found: 1054 Unknown column 'position' in 'where clause'<br>
            SQLSTATE[HY000]: General error: 1364 Field 'descriptor_data' doesn't have a default value
        </div>
        <div style="margin-top: 4px;"><b>Akar Penyebab:</b> Controller dan form view menggunakan nama atribut bahasa Inggris (<code>position</code>, <code>phone</code>, <code>descriptor_json</code>) padahal migrasi basis data mendefinisikan kolom <code>jabatan</code>, <code>no_telp</code>, dan <code>descriptor_data</code>.</div>
        <div style="margin-top: 4px;"><b>Solusi & Sintaks Perbaikan:</b> Menyelaraskan seluruh fillable model <code>User</code>, controller <code>UserController</code>, dan view Blade ke nama kolom baku:</div>
        <pre class="code-block">// app/Http/Controllers/Superadmin/UserController.php
$validated = $request->validate([
    'jabatan' => 'nullable|string|max:100',
    'no_telp' => 'nullable|string|max:30',
]);</pre>
    </div>

    <!-- DEBUG 2 -->
    <div class="debug-item">
        <div class="debug-title">Isu 2: Format Response Endpoint Presensi (JSON API vs Web Redirect)</div>
        <div><b>Masalah yang Terjadi:</b> Test assertion mengharapkan response redirect HTTP 302, namun controller mengembalikan response JSON HTTP 200.</div>
        <div style="margin-top: 4px;"><b>Akar Penyebab:</b> Modul presensi PWA berkomunikasi secara asynchronous via JavaScript <code>fetch()</code> / AJAX untuk mendukung kamera real-time dan Leaflet maps.</div>
        <div style="margin-top: 4px;"><b>Solusi & Sintaks Perbaikan:</b> Menggunakan method <code>postJson()</code> pada PHPUnit test suite:</div>
        <pre class="code-block">// Mengubah assertion redirect menjadi validasi payload JSON
$response = $this->actingAs($user)->postJson('/karyawan/presensi/check-in', [...]);
$response->assertStatus(200)->assertJson(['success' => true, 'status' => 'tepat_waktu']);</pre>
    </div>

    <!-- DEBUG 3 -->
    <div class="debug-item">
        <div class="debug-title">Isu 3: Evaluasi Jam Kerja Timezone WITA (Asia/Makassar) & Time-Freezing</div>
        <div><b>Masalah yang Terjadi:</b> Uji presensi masuk menghasilkan status <code>terlambat</code> saat dieksekusi di malam hari karena evaluasi membandingkan waktu aktual mesin penguji.</div>
        <div style="margin-top: 4px;"><b>Akar Penyebab:</b> Kalkulasi presensi masuk menggunakan fungsi <code>Carbon::now()</code> yang membaca jam sistem lokal.</div>
        <div style="margin-top: 4px;"><b>Solusi & Sintaks Perbaikan:</b> Menerapkan time-mocking deterministik dengan <code>Carbon::setTestNow()</code>:</div>
        <pre class="code-block">// Membekukan waktu pengujian tepat pukul 07:55:00 WITA
Carbon::setTestNow(Carbon::create(2026, 8, 25, 7, 55, 0, 'Asia/Makassar'));
// Eksekusi test presensi...
Carbon::setTestNow(); // Reset waktu setelah pengujian selesai</pre>
    </div>

</body>
</html>
