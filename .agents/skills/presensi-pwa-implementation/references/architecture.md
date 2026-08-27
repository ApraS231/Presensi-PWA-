# Arsitektur Sistem & Spesifikasi Teknologi

Referensi detail arsitektur berdasarkan PRD & BPDF PT. Cahaya Anugrah Kalimantan.
Diperbaiki berdasarkan analisis plot hole & inkonsistensi.

---

## Diagram Arsitektur Global (Data Flow Context)

```
+------------------+         (1) Input GPS + Face Match Payload
|  KARYAWAN (PWA)  | ----------------------------------------------------> +------------------------+
| - Presensi Masuk | <---------------------------------------------------- |                        |
| - Presensi Pulang|         (2) Respons Validasi (Sukses/Gagal/Diluar)     |                        |
| - Pengajuan Izin | ----------------------------------------------------> |                        |
| - Edit/Cancel Izin         (3) Data Form Izin & Bukti Dokumen            |                        |
| - Notifikasi     | <---- (8) Polling Notifikasi (30 detik) ------------- |                        |
+------------------+                                                        |     LARAVEL BACKEND    |
                                                                            |         ENGINE         |
+------------------+         (4) Konfigurasi Radius, Master Data, Settings  |   - Auth & RBAC        |
|   SUPER ADMIN    | ----------------------------------------------------> |   - CheckActive MW     |
| - Lokasi Geofence| <---------------------------------------------------- |   - Haversine (Server) |
| - Data Karyawan  |         (5) Ringkasan Log Sistem & Audit Trail        |   - Data Validation    |
| - Settings Global|                                                        |   - Session & Token    |
+------------------+                                                        |   - NotificationSvc    |
                                                                            |   - AttendanceSvc      |
+------------------+         (6) Request Data Real-Time & Filter Periode    |   - CRON Scheduler     |
|    HRD / ADMIN   | ----------------------------------------------------> |     (23:00 WITA)       |
| - Live Monitoring| <---------------------------------------------------- +------------------------+
| - Enrollment     |         (7) Data Streaming Marker Peta & Rekap                   |        ^
| - Re-Enrollment  |                                                                   |        |
| - Approval Izin  |                                                                   v        |
+------------------+                                                        +-----------------------+
                                                                            |     DATABASE MYSQL    |
                                                                            | users, face_desc,     |
                                                                            | attendances, leaves,  |
                                                                            | locations, settings,  |
                                                                            | notifications         |
                                                                            +-----------------------+
```

---

## Sequence Diagram — Transaksi Presensi Masuk Lengkap

```
KARYAWAN (PWA)         BROWSER (CLIENT JS)       LARAVEL BACKEND        DATABASE MYSQL
     |                          |                       |                      |
     |-- 1. Klik Menu Absen --->|                       |                      |
     |                          |                       |-- 1a. Cek Enroll --->|
     |                          |                       |<-- enrollment_status-|
     |<-- [Belum Enrolled] -----|<-- "Hubungi HRD" ----|                      |
     |                          |                       |-- 1b. Cek Record --->|
     |                          |                       |<-- Existing? --------|
     |<-- [Sudah Absen] --------|<-- Redirect Pulang --|                      |
     |                          |                       |                      |
     |                          |-- 2. Ambil GPS ------>|                      |
     |                          |   (Geolocation API)   |                      |
     |<-- [GPS Ditolak] --------|   (Instruksi + Retry) |                      |
     |                          |                       |                      |
     |                          |-- 3a. Hitung Jarak -->| (CLIENT: untuk UX)   |
     |                          |   (Haversine JS)      |                      |
     |<-- [Diluar Radius] ------|                       |                      |
     |    (Tombol Disabled)     |                       |                      |
     |                          |                       |                      |
     |-- [Dalam Radius] ------->|                       |                      |
     |    (Buka Kamera Depan)   |-- 4. Pindai Wajah -->|                      |
     |<-- [Kamera Ditolak] -----|   (Instruksi + Retry) |                      |
     |                          |   (face-api.js)       |                      |
     |                          |-- 5. Komparasi Vektor |                      |
     |                          |   (Threshold <= 0.50) |                      |
     |<-- [Wajah Tidak Cocok] --|   "Ulangi"           |                      |
     |                          |                       |                      |
     |-- 6. Klik Tombol Kirim ->|                       |                      |
     |                          |-- 7. Kirim Payload -->|                      |
     |                          |   (Foto, Lat, Long)   |                      |
     |                          |                       |-- 8. Re-Validasi --->|
     |                          |                       |   Haversine SERVER    |
     |                          |                       |   Cek jam kerja      |
     |                          |                       |   Hitung status      |
     |                          |                       |-- 9. Simpan Foto --->|
     |                          |                       |   (JPEG 70% compress) |
     |                          |                       |-- 10. Insert Data -->|
     |                          |                       |<-- Sukses Simpan ----|
     |<-- 11. Tampil Notifikasi |<-- Respon Berhasil ---|                      |
     |    "Presensi Berhasil:   |                       |                      |
     |     Tepat Waktu/Terlambat"|                      |                      |
```

---

## Sequence Diagram — Presensi Pulang

```
KARYAWAN (PWA)         BROWSER (CLIENT JS)       LARAVEL BACKEND        DATABASE MYSQL
     |                          |                       |                      |
     |-- 1. Klik Absen Pulang ->|                       |                      |
     |                          |                       |-- 2. Cek Record ---->|
     |                          |                       |<-- Record hari ini --|
     |<-- [Belum masuk] --------|<-- "Absen masuk dulu"-|                      |
     |<-- [Sudah pulang] -------|<-- "Sudah presensi"  -|                      |
     |                          |                       |                      |
     |                          |-- 3. GPS + Face ----->| (sama seperti masuk) |
     |                          |                       |                      |
     |                          |-- 4. Kirim Payload -->|                      |
     |                          |                       |-- 5. Re-Validasi --->|
     |                          |                       |-- 6. UPDATE Record ->|
     |                          |                       |   time_out, lat_out, |
     |                          |                       |   long_out, photo_out|
     |                          |                       |<-- Sukses Update ----|
     |<-- 7. "Pulang Berhasil" -|<-- Respon Berhasil ---|                      |
```

---

## Scheduler Flow — Auto-Checkout & Alpha (Harian 23:00 WITA)

```
LARAVEL SCHEDULER (23:00)                          DATABASE MYSQL
     |                                                   |
     |-- 1. Query: Karyawan aktif tanpa time_out ------->|
     |<-- Records tanpa time_out ------------------------|
     |                                                   |
     |-- 2. UPDATE: set time_out = jam_pulang ---------->|
     |              set auto_checkout = true             |
     |                                                   |
     |-- 3. Query: Karyawan aktif tanpa record hari ini->|
     |            DAN tanpa leave approved               |
     |<-- Karyawan tanpa record -------------------------|
     |                                                   |
     |-- 4. INSERT: attendance status = 'alpha' -------->|
     |                                                   |
```

---

## Komponen Teknologi Detail

### 1. Backend: Laravel 11 (PHP 8.2+)
- Arsitektur MVC monolith
- Eloquent ORM untuk query database
- Keamanan bawaan: CSRF, Bcrypt password hashing
- Session-based authentication (bukan JWT/API token)
- Blade templating engine
- **Service Layer:** `HaversineService`, `AttendanceService`, `NotificationService`
- **Scheduler:** `AutoCheckoutAndAlpha` command (harian jam 23:00)
- **Middleware:** `CheckActive` — cek `is_active` setiap request

### 2. Frontend: Blade + Bootstrap 5 / Tailwind CSS + Alpine.js
- Server-side rendering via Blade templates
- Alpine.js untuk interaktivitas ringan (toggle, modal, dsb)
- Responsif: 360px–430px (smartphone), tablet, desktop
- Dua layout terpisah:
  - `layouts/pwa.blade.php` — Mobile karyawan (bottom navigation bar)
  - `layouts/admin.blade.php` — Desktop admin/HRD (sidebar navigation)
- **Notification polling** (30 detik) + badge counter

### 3. PWA (Progressive Web App)
- `manifest.json` dengan properti:
  ```json
  {
    "name": "Presensi PT. CAK",
    "short_name": "Presensi CAK",
    "start_url": "/",
    "display": "standalone",
    "background_color": "#ffffff",
    "theme_color": "#1a73e8",
    "icons": [
      { "src": "/icons/icon-192x192.png", "sizes": "192x192", "type": "image/png" },
      { "src": "/icons/icon-512x512.png", "sizes": "512x512", "type": "image/png" }
    ]
  }
  ```
- Service Worker:
  - Cache-first strategy untuk aset statis (CSS, JS, font, model face-api.js)
  - Network-first strategy untuk API/data
  - Offline fallback page

### 4. Face Recognition: face-api.js
- Library: face-api.js (berbasis TensorFlow.js)
- Model yang digunakan:
  - `tinyFaceDetector` — deteksi wajah ringan
  - `faceLandmark68Net` — 68 titik landmark wajah
  - `faceRecognitionNet` — ekstraksi 128-float descriptor
- **Semua komputasi di browser (client-side)** — server TIDAK memproses gambar
- Threshold Euclidean Distance: **≤ 0.50 = MATCH**
- Enrollment: ambil 3–5 foto → hitung mean vector descriptor → simpan ke DB (relasi 1:1)
- Re-enrollment: replace descriptor lama (bukan append)
- **Graceful error:** Jika kamera ditolak → instruksi + "Coba Lagi" + "Hubungi HRD" setelah 3x

### 5. Geospasial: Leaflet.js + Haversine Formula
- Leaflet.js + OpenStreetMap tiles (gratis, tanpa API key)
- Haversine Formula untuk kalkulasi jarak GPS:
  ```
  d = 2r × arcsin(√[ sin²(Δlat/2) + cos(lat1) × cos(lat2) × sin²(Δlon/2) ])
  ```
  Dimana `r = 6,371,000 meter` (radius bumi)
- **Validasi GANDA (WAJIB):**
  - **Client-side (JavaScript):** Haversine untuk UX feedback — tampilkan jarak & enable/disable tombol
  - **Server-side (Laravel HaversineService):** Haversine untuk validasi keamanan — ini yang jadi penentu FINAL
- Karyawan bisa presensi di SEMUA lokasi aktif (lokasi terdekat yang dalam radius)
- Visualisasi: marker presensi + circle polygon geofence
- **Graceful error:** Jika GPS ditolak → instruksi + "Coba Lagi"

### 6. Database: MySQL 8.0
- **7 tabel utama:** `users`, `face_descriptors`, `locations`, `attendances`, `leaves`, `settings`, `notifications`
- Relasi:
  - `face_descriptors.user_id` → `users.id` (**1:1** — satu user = satu mean descriptor)
  - `attendances.user_id` → `users.id`
  - `attendances.location_id` → `locations.id`
  - `attendances` memiliki unique constraint `['user_id', 'date']`
  - `leaves.user_id` → `users.id`
  - `leaves.approved_by` → `users.id`
  - `notifications.user_id` → `users.id`

### 7. Storage Foto Presensi
- Lokasi: `storage/app/public/attendances/{user_id}/{date}/photo_in.jpg`
- Jalankan `php artisan storage:link` untuk public access via URL
- Compress JPEG ke quality 70% sebelum simpan (hemat storage)
- Retention policy: 6 bulan (opsional, configurable)

### 8. Export Laporan
- **Excel (.xlsx):** PhpSpreadsheet (`phpoffice/phpspreadsheet`)
- **PDF:** Laravel-DomPDF (`barryvdh/laravel-dompdf`)
- Output: `Laporan_Presensi_CAK.xlsx` / `Laporan_Presensi_CAK.pdf`
- Kalkulasi:
  - Total jam kerja: `(time_out - time_in) - jam_istirahat`
  - Jika `time_out` NULL → fallback ke `jam_pulang` dari settings
  - Izin/sakit/cuti approved → jam kerja penuh
  - Alpha → 0 jam
  - Akumulasi keterlambatan, total cuti terpakai

---

## Kebutuhan Non-Fungsional

| Kategori | Standar |
|---|---|
| **Security** | HTTPS/SSL wajib, CSRF token tiap request, Bcrypt hashing, validasi server-side ganda untuk GPS, cek `is_active` setiap request |
| **Performance** | Deteksi wajah ≤ 2 detik (Snapdragon 600 series), PWA loading < 1.5 detik (cached) |
| **Cross-Platform** | Chrome Mobile, Firefox Mobile, Safari iOS 14.5+, responsif 360px–430px |
| **Reliability** | Uptime ≥ 99% jam kerja, graceful error untuk GPS/kamera ditolak, auto-checkout scheduler |

---

## Matriks Hak Akses CRUD

| Modul | Karyawan | HRD / Admin | Super Admin |
|---|---|---|---|
| **Attendances** | Create (Absen Masuk+Pulang), Read (Riwayat Sendiri) | Read (Semua), Export (Excel/PDF) | Read, Update, Delete (Audit Log) |
| **Face Descriptors** | Read (Saat Verifikasi Wajah) | Create (Enrollment), Read, Update (Re-Enroll) | Full CRUD |
| **Leaves** | Create (Pengajuan), Read (Sendiri), Update/Cancel (Pending saja) | Read, Update (Approve/Reject) | Read, Delete |
| **Locations** | Read (Jarak Radius Saja) | Read | Full CRUD |
| **Users** | Read (Profil Sendiri), Update (Password) | Read (Data Karyawan) | Full CRUD (Akun & Hak Akses) |
| **Settings** | Read (Ketentuan Jam) | Read | Full CRUD (Jam Kerja, Istirahat, Hari Kerja, Toleransi, Retroaktif Izin) |
| **Notifications** | Read (Sendiri), Update (Mark Read) | — | — |
