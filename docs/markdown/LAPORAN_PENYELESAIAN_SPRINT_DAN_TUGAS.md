# BERITA ACARA & LAPORAN AKHIR PENYELESAIAN TUGAS PROYEK
## Sistem Informasi Presensi Karyawan PWA PT. Cahaya Anugrah Kalimantan

- **Nomor Dokumen:** DOC-SPRINT-FINAL-2026
- **Tanggal Rilis:** 26 Agustus 2026
- **Status Proyek:** 100% Selesai Penuh (Final Delivery & Verified)
- **Format Cetak PDF:** [Laporan_Penyelesaian_Sprint_dan_Tugas_Presensi_PT_CAK.pdf](file:///d:/PROJECT/Presensi(PWA)/Laporan_Penyelesaian_Sprint_dan_Tugas_Presensi_PT_CAK.pdf)

---

## 1. Identifikasi Proyek & Lingkup Pekerjaan

| Parameter | Keterangan |
|---|---|
| **Nama Proyek** | Sistem Presensi Karyawan Progressive Web Application (PWA) PT. Cahaya Anugrah Kalimantan |
| **Arsitektur & Teknologi** | Laravel 11, PHP 8.2+, MySQL, face-api.js (SSD MobileNetV1), Leaflet.js, DomPDF, PhpSpreadsheet, Material Design 3 |
| **Target Pengguna** | 3 Role Terintegrasi: Karyawan Lapangan (Mobile PWA), HRD / Admin (Desktop), Super Admin (Master Control) |
| **Status Penyelesaian** | **4 Fase / 15 Sprints Selesai Penuh (100% Complete, 96 Automated Tests Passed)** |

---

## 2. Matriks Penyelesaian 15 Sprint Pengembangan

### Fase 1: Inisialisasi Arsitektur, Autentikasi, Database & Layout PWA
- **Sprint 1.1 (Fondasi Laravel 11 & Skema Database)**: Setup skema tabel `users`, `locations`, `attendances`, `leaves`, `settings`, `notifications`; Seeder akun 3 role (Karyawan, HRD, Super Admin); Material Design 3 Design System tokens.
- **Sprint 1.2 (Autentikasi Multi-Role & Keamanan Sesi)**: Login NIK & Email dengan Password Bcrypt, proteksi middleware `CheckActive`, isolasi otorisasi RBAC 3 role, manajemen ubah password.
- **Sprint 1.3 (PWA Manifest & Service Worker)**: Konfigurasi `manifest.json`, pendaftaran `sw.js`, halaman offline fallback cerdas, mobile standalone layout experience.
- **Sprint 1.4 (Sistem Notifikasi In-App)**: `NotificationService`, siaran broadcast pengumuman HRD, counter unread badge, penandaan telah dibaca via AJAX.

### Fase 2: Biometrik Face Recognition, Double Geofencing & Presensi Harian
- **Sprint 2.1 (Enrollment Biometrik Wajah 128-Float)**: Integrasi model `face-api.js` (SSD MobileNetV1 & Face Recognition), ekstraksi embedding 128-float, perlindungan anti-buddy punching.
- **Sprint 2.2 (Double Geofencing Server-Side)**: Implementasi formula Haversine desimal ganda (`GeofenceService`), validasi radius toleransi meter, deteksi titik kantor terdekat.
- **Sprint 2.3 (Presensi Check-In / Check-Out Karyawan)**: Pemindaian kamera real-time, pencocokan Euclidean biometrik ($\le 0.50$), evaluasi keterlambatan WITA, penyimpanan snapshot foto terenkripsi.
- **Sprint 2.4 (Otomatisasi Background Schedulers)**: Command scheduler `attendance:generate-alpha` (20:00 WITA) dan `attendance:auto-checkout` (23:59 WITA).

### Fase 3: Manajemen Izin, Monitoring Real-Time & Geospatial Live Map
- **Sprint 3.1 (Modul Pengajuan & Approval Izin HRD)**: Formulir pengajuan cuti/sakit/izin dengan unggah berkas bukti, validasi batas retroaktif mundur, auto-generate record presensi pada hari kerja saat disetujui.
- **Sprint 3.2 (Dashboard Monitoring Real-Time HRD)**: Kartu statistik elevated harian, live polling asynchronous 30 detik, reminder peringatan karyawan belum mendaftar wajah.
- **Sprint 3.3 (Visualisasi Geospatial Live Map)**: Peta interaktif Leaflet.js + OpenStreetMap, radius circle geofence kantor, status-coded presence pins, modal popup pratinjau snapshot foto.
- **Sprint 3.4 (Riwayat Presensi Mandiri Karyawan)**: Filter riwayat bulanan/tahunan, kartu ringkasan kehadiran mandiri, modal pratinjau snapshot foto masuk dan pulang.

### Fase 4: Rekapitulasi Laporan, Master Data Management, UAT & Production Polish
- **Sprint 4.1 (Rekapitulasi Laporan Excel & PDF)**: Perhitungan jam kerja bersih payroll `(time_out - time_in) - durasi_istirahat`, ekspor Excel `.xlsx` via PhpSpreadsheet dan cetak dokumen PDF `.pdf` A4 Landscape ber-kop resmi PT. CAK.
- **Sprint 4.2 (Master Control Super Admin)**: CRUD pengguna & karyawan lengkap, toggle aktifasi instan, reset biometrik wajah, interactive map coordinate picker master lokasi presensi, batch setting kebijakan jam kerja global.
- **Sprint 4.3 (UAT End-to-End, Optimasi Kecepatan & Audit NFR)**: Pengujian 7 skenario UAT operasional penuh, pengerasan keamanan rate limiting & session hijacking, sanitasi caching Laravel.

---

## 3. Statistik Pengujian Mutu Sistem (Quality Assurance)

```
Total Test Suites : 14 Test Suites
Total Test Cases  : 96 Feature & Unit Tests
Total Assertions  : 382 Assertions
Status Kelulusan  : 100% PASS (0 Failures, 0 Errors)
```

| Kategori Pengujian | Test Suite | Hasil |
|---|---|---|
| Autentikasi & RBAC | `AuthenticationTest.php` | **100% PASS** |
| Biometrik Wajah | `EnrollmentBiometricTest.php` | **100% PASS** |
| Double Geofencing | `GeofencingTest.php` | **100% PASS** |
| Transaksi Presensi | `AttendanceTransactionTest.php` | **100% PASS** |
| Manajemen Perizinan | `LeaveManagementTest.php` | **100% PASS** |
| Monitoring & Live Map | `MonitoringDashboardTest.php`, `LiveMapMonitoringTest.php` | **100% PASS** |
| Riwayat & Notifikasi | `EmployeeHistoryAndNotificationTest.php`, `NotificationSystemTest.php` | **100% PASS** |
| Rekapitulasi Laporan | `ReportExportTest.php` | **100% PASS** |
| Master Control Super Admin | `MasterDataManagementTest.php` | **100% PASS** |
| Background Schedulers | `SchedulerAutomationTest.php` | **100% PASS** |
| Skenario UAT End-to-End | `EndToEndUatScenarioTest.php` | **100% PASS** |
| PWA & Layouts | `LayoutAndPwaTest.php` | **100% PASS** |
