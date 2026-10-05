# 🗺️ Roadmap Implementasi — Sistem Presensi PWA PT. Cahaya Anugrah Kalimantan

> **Referensi Dokumen:**
> - `PRD_Presensi_PWA_PT_CAK.pdf` — Product Requirement Document v1.0
> - `Alur_Bisnis_dan_Data_Flow_Presensi.pdf` — Business Process & Data Flow Document

---

## 📋 Ringkasan Proyek

Membangun **Aplikasi Presensi Karyawan berbasis PWA** dengan fitur **Face Recognition (face-api.js)** dan **Geofencing (Haversine Formula)** menggunakan arsitektur **Laravel Monolith**. Sistem memiliki 3 role utama: **Karyawan**, **HRD/Admin Presensi**, dan **Super Admin/IT**.

### Tech Stack

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 11 (PHP 8.2+) |
| Frontend | Blade + Tailwind CSS / Bootstrap 5 + Alpine.js |
| PWA | Web App Manifest + Service Worker |
| Face Recognition | face-api.js (TensorFlow.js) — Client-Side |
| Geospasial & Peta | HTML5 Geolocation API + Leaflet.js (OpenStreetMap) |
| Database | MySQL 8.0 / MariaDB |
| Export Laporan | PhpSpreadsheet (.xlsx) + Laravel-DomPDF (.pdf) |

---

## 🏗️ Fase 1 — Fondasi Proyek & Infrastruktur (Minggu 1–2)

### 1.1 Setup Proyek Laravel 11

- [ ] Inisialisasi proyek Laravel 11 via Composer
- [ ] Konfigurasi `.env` (database MySQL, APP_URL, timezone `Asia/Makassar` WITA)
- [ ] Setup HTTPS/SSL (wajib untuk Service Worker, Camera API, Geolocation API)
- [ ] Konfigurasi CSRF protection

### 1.2 Database Migration & Seeder

Buat migration untuk **7 tabel utama** berdasarkan skema PRD + perbaikan analisis:

| Tabel | Field Utama | Keterangan |
|---|---|---|
| `users` | id, nik, name, email, password, role, jabatan, department, no_telp, avatar, enrollment_status, is_active | Akun, RBAC & status enrollment |
| `face_descriptors` | id, user_id, descriptor_data (JSON/LONGTEXT), sample_photo | Biometrik 128-float (relasi 1:1) |
| `locations` | id, name, latitude, longitude, radius_meters, is_active | Master lokasi & geofence |
| `attendances` | id, user_id, location_id, date, time_in, time_out, lat_in, long_in, lat_out, long_out, photo_in, photo_out, status, distance_meters, auto_checkout | Log presensi harian + flag auto-checkout |
| `leaves` | id, user_id, type, start_date, end_date, reason, attachment_file, status (pending/approved/rejected/cancelled), approved_by, review_note | Perizinan & cuti + status cancelled |
| `settings` | id, key, value, description | Konfigurasi global (jam kerja, toleransi, istirahat, hari kerja) |
| `notifications` | id, user_id, title, message, type, is_read | Notifikasi in-app untuk karyawan |

- [ ] Buat semua 7 migration files
- [ ] Buat seeder untuk data awal:
  - 1 superadmin (NIK: SA001)
  - 1 admin HRD (NIK: ADM001)
  - Settings default: `jam_masuk=08:00`, `jam_pulang=17:00`, `toleransi_terlambat=15`, `jam_istirahat=60`, `hari_kerja=senin,selasa,rabu,kamis,jumat`, `max_retroaktif_izin=3`
- [ ] Jalankan `php artisan migrate --seed`
- [ ] Jalankan `php artisan storage:link` (untuk akses foto presensi via URL)

### 1.3 Autentikasi & RBAC (FR-AUTH-01, FR-AUTH-02)

- [ ] Implementasi login berbasis **NIK / Email + Password** (Bcrypt hashing)
- [ ] **Cek `is_active` saat login** — jika nonaktif: "Akun Anda telah dinonaktifkan. Hubungi Admin."
- [ ] **Cek `is_active` di middleware** pada setiap request (bukan hanya login)
- [ ] Buat Middleware RBAC untuk 3 role:
  - `karyawan` → redirect ke PWA Mobile View
  - `admin` (HRD) → redirect ke Desktop Management View
  - `superadmin` → redirect ke System Control View
- [ ] Session management otomatis
- [ ] Fitur ganti password mandiri (FR-AUTH-03)

### 1.4 Layout & PWA Manifest (FR-BIO-01)

- [ ] Buat **Admin Layout** (Sidebar dashboard desktop)
- [ ] Buat **PWA Mobile Layout** (Responsive 360px–430px)
- [ ] Buat `manifest.json` (name, icons, start_url, display: standalone)
- [ ] Implementasi **Service Worker** untuk:
  - Caching aset statis (CSS, JS, model face-api.js) → loading < 1.5 detik
  - Offline fallback UI dengan pesan instruktif
- [ ] Pastikan prompt **"Add to Home Screen"** muncul di browser mobile

### 1.5 Sistem Notifikasi In-App (IK-03 Fix)

- [ ] Buat model `Notification` dengan relasi ke `User`
- [ ] Buat helper/service `NotificationService::send($userId, $title, $message, $type)`
- [ ] Endpoint API untuk polling notifikasi (GET `/api/notifications`)
- [ ] Endpoint API untuk mark-as-read (POST `/api/notifications/{id}/read`)
- [ ] Badge counter notifikasi belum dibaca di navbar PWA & admin
- [ ] Polling setiap 30 detik dari frontend

---

## 🧬 Fase 2 — Face Recognition & Geofencing Core (Minggu 3–4)

### 2.1 Integrasi face-api.js (FR-BIO-02, FR-BIO-03)

- [ ] Download & include model face-api.js:
  - `tinyFaceDetector` / `ssdMobilenetv1`
  - `faceLandmark68Net`
  - `faceRecognitionNet`
- [ ] Implementasi **Face Detection** real-time dari kamera depan
- [ ] Tampilkan **bounding box hijau** saat wajah terdeteksi (liveness indicator)
- [ ] Ekstraksi **128-dimensional facial descriptor** di browser (client-side)
- [ ] **Graceful error handling** saat kamera ditolak user (EC-03):
  - Tampilkan instruksi step-by-step mengaktifkan kamera
  - Tombol "Coba Lagi"
  - Jika tetap ditolak setelah 3x → "Hubungi HRD untuk bantuan"

### 2.2 Enrollment Biometrik Wajah (Role HRD)

- [ ] Halaman enrollment: pilih karyawan → buka webcam desktop
- [ ] Ambil **3–5 foto wajah** dari sudut berbeda
- [ ] Komputasi **mean vector** dari multiple descriptor
- [ ] Simpan descriptor (JSON) ke tabel `face_descriptors` (relasi 1:1)
- [ ] Update `users.enrollment_status` → `enrolled` (PH-02 Fix)
- [ ] Dashboard HRD: **daftar karyawan belum enrollment** + badge counter (PH-02 Fix)
- [ ] Tombol **"Re-Enrollment Wajah"** di halaman detail karyawan (PH-07 Fix):
  - Replace descriptor lama (bukan append)
  - Simpan `updated_at` sebagai riwayat

### 2.3 Geolocation & Geofencing (FR-ATT-01, FR-ATT-02)

- [ ] Implementasi pembacaan GPS real-time via `navigator.geolocation`
- [ ] Baca **latitude, longitude, accuracy** dari perangkat
- [ ] **Graceful error handling** saat GPS ditolak user (EC-03):
  - Tampilkan instruksi step-by-step mengaktifkan lokasi
  - Tombol "Coba Lagi"
  - Jika tetap ditolak → "Hubungi HRD untuk bantuan"
- [ ] Ambil semua lokasi aktif dari tabel `locations`
- [ ] Implementasi **Haversine Formula**:
  - **Client-side (JS):** untuk UX feedback — tampilkan jarak & enable/disable tombol (PH-06 Fix)
  - **Server-side (Laravel):** untuk validasi keamanan — penentu final (PH-06 Fix)
- [ ] Validasi: jika `d > radius_meters` → tombol presensi **disabled** + notifikasi jarak aktual
- [ ] Validasi: jika `d <= radius_meters` → buka kamera untuk face verification

### 2.4 Modul Presensi Masuk & Pulang (FR-ATT-03, FR-ATT-04)

**Alur Presensi Masuk:**
```
Karyawan → Klik Absen Masuk → Cek enrollment_status
→ [Belum enrolled? Tampilkan "Hubungi HRD untuk pendaftaran wajah" → STOP]
→ [Sudah enrolled? GPS Check] → [Luar Radius? STOP]
→ [Dalam Radius? Buka Kamera] → Face Detection → Face Matching (Threshold ≤ 0.50)
→ [Tidak Cocok? Ulangi] → [Cocok? Kirim Payload] → Server Validasi Ulang
→ Simpan ke attendances → Feedback "Presensi Berhasil: Tepat Waktu / Terlambat"
```

**Alur Presensi Pulang (PH-01 Fix):**
```
Karyawan → Klik Absen Pulang → Cek sudah ada record masuk hari ini?
→ [Belum ada? "Anda belum presensi masuk hari ini" → STOP]
→ [Sudah ada & sudah pulang? "Anda sudah presensi hari ini" → STOP]
→ [Sudah masuk, belum pulang? GPS Check + Face Recognition (sama seperti masuk)]
→ Server validasi → Update time_out, lat_out, long_out, photo_out
→ Feedback "Presensi Pulang Berhasil"
```

- [ ] **Cek enrollment status** sebelum mulai presensi (PH-02 Fix)
- [ ] **Cek presensi ganda** — jika sudah masuk, arahkan ke pulang (EC-01 Fix):
  - `Attendance::where('user_id', $user->id)->where('date', today())->first()`
  - Sudah ada `time_in` tapi belum `time_out` → redirect ke presensi pulang
  - Sudah ada `time_in` DAN `time_out` → "Anda sudah presensi hari ini"
- [ ] Face Matching: hitung **Euclidean Distance** terhadap descriptor terdaftar
- [ ] Threshold: cocok jika `distance ≤ 0.50`
- [ ] Payload ke server: foto snapshot (base64) + koordinat GPS + timestamp
- [ ] Server: validasi CSRF, **re-kalkulasi Haversine** (server-side), cek jam kerja dari `settings`
- [ ] Tentukan status: **Tepat Waktu** / **Terlambat** (berdasarkan `jam_masuk` + `toleransi_terlambat`)
- [ ] Simpan foto ke `storage/app/public/attendances/{user_id}/{date}/` (IK-04 Fix)
- [ ] Compress JPEG ke quality 70% sebelum simpan (hemat storage)
- [ ] Simpan record ke tabel `attendances` (semua field termasuk foto & koordinat)
- [ ] Tampilkan feedback sukses: jam, foto kehadiran, status

### 2.5 Auto-Checkout & Alpha Scheduler (PH-01 + PH-05 Fix)

- [ ] Buat Laravel **Scheduler** (Cron Job) yang berjalan setiap hari jam 23:00 WITA:
  1. **Auto-checkout:** Untuk karyawan yang punya `time_in` tapi tidak punya `time_out`:
     - Set `time_out` = `jam_pulang` dari settings
     - Set `auto_checkout = true`
  2. **Generate Alpha:** Untuk karyawan aktif yang tidak punya record `attendances` hari itu DAN tidak ada `leaves` approved pada tanggal tersebut:
     - Buat record `attendances` dengan `status = 'alpha'`
- [ ] Register scheduler di `app/Console/Kernel.php` atau `routes/console.php`

---

## 📊 Fase 3 — Perizinan, Dashboard HRD & Live Map (Minggu 5–6)

### 3.1 Modul Pengajuan Izin / Sakit / Cuti (FR-LV-01, FR-LV-02)

**Karyawan (PWA):**

- [ ] Form pengajuan: jenis perizinan (izin/sakit/cuti)
- [ ] Input rentang tanggal (start_date, end_date)
- [ ] **Validasi retroaktif:** pengajuan maksimal `max_retroaktif_izin` hari ke belakang (PH-04 Fix)
- [ ] Textarea keterangan alasan
- [ ] Upload lampiran (surat dokter/PDF) — validasi ekstensi & ukuran ≤ 2MB
- [ ] Simpan ke tabel `leaves` dengan status default `pending`
- [ ] Tampilkan notifikasi "Menunggu persetujuan HRD"
- [ ] **Edit pengajuan** selama status masih `pending` (PH-04 Fix)
- [ ] **Batalkan pengajuan** selama status masih `pending` → set status `cancelled` (PH-04 Fix)
- [ ] Setelah `approved`/`rejected`, tidak bisa diubah lagi

**HRD (Dashboard):**

- [ ] Halaman daftar pengajuan perizinan (filter status: pending/approved/rejected/cancelled)
- [ ] Preview lampiran dokumen
- [ ] Tombol **Approve** / **Reject** + textarea catatan evaluasi
- [ ] Jika Approved — **generate attendance records** untuk hari kerja dalam rentang izin (EC-02 Fix):
  - Loop setiap tanggal dari `start_date` s/d `end_date`
  - Skip hari yang bukan `hari_kerja` (dari settings)
  - Buat record `attendances` dengan `status = izin/sakit/cuti`
- [ ] Kirim **notifikasi** ke karyawan via `NotificationService` (IK-03 Fix)
- [ ] Notifikasi perubahan status terlihat di PWA Karyawan

### 3.2 Dashboard Monitoring Real-Time (FR-DASH-01)

- [ ] Statistik ringkas harian:
  - Total Karyawan Masuk
  - Total Terlambat
  - Total Izin/Cuti/Sakit
  - Total Belum Hadir (Alpha) — query langsung dari `attendances` karena sudah auto-generate
- [ ] Kalkulasi persentase kehadiran
- [ ] Auto-refresh data (polling / real-time)
- [ ] Filter berdasarkan tanggal, **departemen** (IK-01 Fix), cabang
- [ ] **Badge "X karyawan belum enrollment"** sebagai reminder HRD (PH-02 Fix)

### 3.3 Live Interactive Map — Leaflet.js (FR-DASH-02)

- [ ] Integrasi **Leaflet.js** dengan tile **OpenStreetMap**
- [ ] Tampilkan **marker lokasi presensi** karyawan pada peta
- [ ] Mapping koordinat (`lat_in`, `long_in`) ke layer peta
- [ ] Popup info marker: nama, jam masuk, status, jarak
- [ ] Visualisasi **circle polygon** radius geofence setiap lokasi

### 3.4 Riwayat Presensi Karyawan (PWA)

- [ ] Halaman riwayat kehadiran mandiri (per karyawan)
- [ ] Filter berdasarkan bulan/tahun
- [ ] Tampilkan detail: tanggal, jam masuk/pulang, status, foto
- [ ] Tandai record dengan `auto_checkout = true` (misal: ikon peringatan)

---

## 📄 Fase 4 — Export Laporan, Master Data & UAT (Minggu 7–8)

### 4.1 Rekapitulasi & Export Laporan (FR-DASH-04)

- [ ] Filter periode laporan (harian, mingguan, bulanan — misal 01 s/d 30)
- [ ] Perhitungan otomatis (IK-05 Fix):
  - Total jam kerja per karyawan: `(time_out - time_in) - jam_istirahat`
  - Jika `time_out` NULL → gunakan `jam_pulang` dari settings sebagai fallback
  - Hari izin/sakit/cuti approved → dihitung sebagai jam kerja penuh
  - Akumulasi keterlambatan
  - Total cuti terpakai
- [ ] Export **Excel (.xlsx)** via **PhpSpreadsheet** → `Laporan_Presensi_CAK.xlsx`
- [ ] Export **PDF** via **Laravel-DomPDF** → `Laporan_Presensi_CAK.pdf`
- [ ] File siap unduh untuk payroll HRD

### 4.2 Manajemen Master Lokasi & Geofence (Super Admin — FR-DASH-03)

- [ ] CRUD lokasi: nama titik proyek/kantor, latitude, longitude, radius (meter), status aktif
- [ ] Validasi format koordinat desimal
- [ ] Preview **circle polygon** pada peta admin sebelum simpan
- [ ] Simpan/update ke tabel `locations`

### 4.3 Manajemen Karyawan & Jabatan (Super Admin)

- [ ] CRUD data karyawan: NIK, Nama, Email, Jabatan, **Department**, Role, Status Aktif
- [ ] Generasi default password (Bcrypt)
- [ ] Validasi keunikan NIK & Email
- [ ] Akun baru → `enrollment_status = pending` (siap untuk enrollment wajah)
- [ ] Toggle `is_active` untuk nonaktifkan karyawan

### 4.4 Pengaturan Kebijakan Kerja / Global Config (Super Admin)

- [ ] Form settings:
  - Jam Masuk Kerja (default: 08:00 WITA)
  - Jam Pulang Kerja (default: 17:00 WITA)
  - Toleransi Terlambat (default: 15 menit)
  - **Jam Istirahat** (default: 60 menit) — IK-05 Fix
  - **Hari Kerja** (default: Senin–Jumat) — EC-02 Fix
  - **Maks Retroaktif Izin** (default: 3 hari) — PH-04 Fix
- [ ] Update parameter pada tabel `settings`
- [ ] Kebijakan berlaku otomatis pada hari berikutnya

### 4.5 User Acceptance Testing (UAT)

- [ ] Testing seluruh flow presensi masuk **dan pulang** di lingkungan PT. CAK
- [ ] Testing karyawan baru (belum enrollment) → pesan error yang benar
- [ ] Testing presensi ganda → redirect yang benar
- [ ] Testing auto-checkout & auto-alpha (scheduler)
- [ ] Testing pengajuan izin: submit, edit, cancel, approve, reject
- [ ] Testing izin multi-hari → attendance records ter-generate dengan benar
- [ ] Testing karyawan nonaktif → tidak bisa login
- [ ] Testing kamera/GPS ditolak → graceful error
- [ ] Verifikasi akurasi face recognition (target ≥ 95%)
- [ ] Verifikasi geofencing (100% presensi luar radius tertolak)
- [ ] Testing multi-device (Chrome Mobile, Firefox Mobile, Safari iOS 14.5+)
- [ ] Testing responsivitas (360px–430px smartphone, tablet)
- [x] Load testing performa deteksi wajah ≤ 2 detik (Snapdragon 600 series)
- [x] Bug fixing & optimization

---

## 🛰️ Fase 5 — Live Location Tracking SPG & Self-Service Profiling

### 5.1 Pelacakan Lapangan SPG Keliling (Live GPS Tracking)
- [x] Tabel `location_tracks` (user_id, attendance_id, date, latitude, longitude, accuracy, recorded_at)
- [x] Model `LocationTrack` dengan relasi ke `User` dan `Attendance`
- [x] Background tracking engine pada PWA Karyawan saat presensi masuk aktif
- [x] Filter akurasi GPS (< 100m) dan stationary throttle (< 5m)
- [x] Halaman PWA "Perjalanan Hari Ini" dengan peta rute Leaflet.js (reset harian)

### 5.2 Siklus Laporan Bulanan (25-25) & Pemantauan Admin
- [x] Dashboard pemantauan operasional SPG pada Admin HRD dan Super Admin (view-only)
- [x] Filter periode bulanan siklus tanggal 25 s.d. 25
- [x] Trail inspector Leaflet.js dengan rute polyline, start/end pins, dan checkpoints
- [x] Kebijakan retensi data 30 hari + command `tracking:cleanup` (02:00 WITA)

### 5.3 Pengaturan Profil Mandiri & Polish Desain M3
- [x] Modul edit profil mandiri karyawan (nama, no_telp, avatar upload/delete)
- [x] Modul ganti password mandiri dengan verifikasi `current_password`
- [x] Kompatibilitas Material Design 3 Dark Mode menyeluruh
- [x] Animasi transisi halaman View Transitions API dan micro-interactions bottom nav

---

## 🔒 Matriks Hak Akses CRUD (Access Control)

| Modul | Karyawan | HRD / Admin | Super Admin |
|---|---|---|---|
| **Attendances** | Create (Absen), Read (Riwayat Sendiri) | Read (Semua), Export | Read, Update, Delete (Audit) |
| **Face Descriptors** | Read (Saat Verifikasi) | Create (Enrollment), Read, Update (Re-Enroll) | Full CRUD |
| **Leaves** | Create, Read (Sendiri), Update/Cancel (Pending saja) | Read, Update (Approve/Reject) | Read, Delete |
| **Locations** | Read (Radius Saja) | Read | Full CRUD |
| **Users** | Read (Profil), Update (Password) | Read (Data Karyawan) | Full CRUD |
| **Settings** | Read (Ketentuan Jam) | Read | Full CRUD |
| **Notifications** | Read (Sendiri), Update (Mark Read) | — | — |

---

## ⚠️ Manajemen Risiko & Mitigasi

| Risiko | Level | Strategi Mitigasi |
|---|---|---|
| **Fake GPS / Location Mocking** | Sedang–Tinggi | Library pengecekan mock location + validasi network-based IP geocoding + re-validasi server-side |
| **Koneksi Internet Tidak Stabil** | Sedang | Service Worker caching UI tetap terbuka + pesan instruksi kirim ulang saat sinyal pulih |
| **Pencahayaan Redup** | Rendah–Sedang | Feedback bounding box hijau + instruksi pindah ke area berpenerangan cukup sebelum snapshot |
| **Kamera/GPS Ditolak User** | Sedang | Instruksi step-by-step + tombol "Coba Lagi" + fallback "Hubungi HRD" setelah 3x gagal |
| **Karyawan Lupa Presensi Pulang** | Sedang | Auto-checkout scheduler jam 23:00 + flag `auto_checkout` di laporan |
| **Presensi Ganda** | Rendah | Validasi record existing sebelum insert + redirect otomatis ke flow yang benar |

---

## 🎯 Key Performance Indicators (KPIs)

| KPI | Target |
|---|---|
| Akurasi Face Recognition | ≥ 95% dalam kondisi pencahayaan wajar |
| Efektivitas Geofencing | 100% presensi luar radius tertolak otomatis |
| Adopsi Karyawan | > 90% instalasi PWA sukses dalam 2 minggu pertama |
| Efisiensi HRD | Rekapitulasi dari 3 hari kerja → 1 klik (< 1 menit) |
| Performa Deteksi Wajah | ≤ 2 detik di smartphone kelas menengah |
| Loading PWA | < 1.5 detik (cached assets) |
| Uptime Sistem | ≥ 99% pada jam operasional |
