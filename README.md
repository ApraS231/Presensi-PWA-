# Presensi PT. CAK - Sistem Presensi Karyawan Web PWA (Face Recognition & Geospasial)

Sistem pencatatan kehadiran karyawan berbasis Progressive Web Application (PWA) dengan verifikasi biometrik wajah (*Face Recognition*) dan validasi radius lokasi geospasial (*Geofencing*) untuk PT. Cahaya Anugrah Kalimantan.

---

## Ringkasan Proyek

Aplikasi ini dikembangkan untuk memitigasi praktik kecurangan absensi (*buddy punching* dan manipulasi fake GPS) serta meningkatkan efisiensi pemantauan kehadiran karyawan lapangan dan kantor secara real-time tanpa memerlukan instalasi aplikasi native melalui PlayStore/AppStore.

Sistem mengadopsi arsitektur hibrida:
- **Client-Side Biometrics:** Komputasi ekstraksi dan matching 128-float facial vector dieksekusi langsung di peramban mobile (face-api.js) guna meminimalkan beban komputasi server.
- **Server-Side Security:** Validasi ganda titik koordinat geospasial (Haversine Formula) dan otorisasi data diproses secara ketat pada backend Laravel.

---

## Fitur Utama

### 1. Karyawan (Mobile PWA)
- **Instalasi PWA:** Dukungan "Add to Home Screen" dan caching Service Worker untuk akses instan dan dukungan offline UI.
- **Presensi Masuk & Pulang:** Verifikasi biometrik wajah real-time (Euclidean Distance threshold <= 0.50) dan validasi radius kantor/proyek.
- **Pencegahan Presensi Ganda:** Validasi status harian otomatis untuk mencegah check-in berulang.
- **Live Location Tracking (SPG Keliling):** Pelacakan rute GPS otomatis selama jam kerja, filter akurasi <100m, stationary throttle, dan peta rute mandiri ("Perjalanan Hari Ini" dengan reset harian).
- **Pengaturan Profil Mandiri:** Pembaruan nama, kontak telepon, upload/delete foto profil avatar, dan ubah password mandiri.
- **Pengajuan Izin/Sakit/Cuti:** Form pengajuan mandiri dengan upload bukti berkas (maksimal 2MB) dan validasi batas retroaktif.
- **Riwayat Kehadiran:** Akses mandiri riwayat absensi (1-bar unified summary), status kedisiplinan, dan tanda auto-checkout.
- **Pusat Notifikasi In-App:** Pemberitahuan berkala pembaruan status perizinan.

### 2. HRD & Admin Presensi (Desktop Dashboard)
- **Enrollment Biometrik:** Pendaftaran dan pembaruan (re-enrollment) template wajah karyawan via webcam desktop.
- **Monitoring Real-Time:** Kartu statistik kehadiran harian, live map sebaran presensi, dan filter per departemen.
- **Pemantauan SPG Keliling:** Visualisasi polyline perjalanan SPG lapangan pada siklus bulanan 25-25 dengan checkpoint dan estimasi jarak tempuh kumulatif (view-only).
- **Persetujuan Perizinan:** Workflow review, persetujuan (Approve/Reject), dan sinkronisasi otomatis ke log kehadiran.
- **Rekapitulasi & Export:** Perhitungan jam kerja bersih, akumulasi keterlambatan, dan ekspor dokumen Excel (.xlsx) serta PDF (.pdf).

### 3. Super Admin & IT (System Control)
- **Manajemen Multi-Lokasi:** Konfigurasi titik koordinat latitude, longitude, dan radius geofence kantor/titik proyek.
- **Manajemen Akun & Karyawan:** Tata kelola data pegawai, hak akses (RBAC), dan toggle aktivasi akun.
- **Konfigurasi Kebijakan Global:** Pengaturan jam masuk, jam pulang, toleransi keterlambatan, jam istirahat, hari kerja aktif, interval tracking SPG, dan batas toleransi akurasi GPS.
- **Otomasi Scheduler:** Eksekusi harian untuk penanganan auto-alpha (18:00 WITA), auto-checkout (23:59 WITA), dan pembersihan data jejak lama 30 hari (02:00 WITA).

---

## Teknologi & Arsitektur

| Komponen | Teknologi yang Digunakan |
|---|---|
| **Backend Framework** | Laravel 11 (PHP 8.2+) |
| **Frontend Templating** | Blade + Alpine.js |
| **Design System** | Material Design 3 (M3) via CSS Custom Properties + Claymorphism (Light & Dark Theme) |
| **Face Recognition** | face-api.js (TensorFlow.js) di browser klien |
| **Peta & Geospasial** | Leaflet.js, OpenStreetMap Tiles, dan Haversine Formula |
| **PWA Engine** | Web App Manifest + Service Worker API |
| **Database** | MySQL 8.0 / MariaDB |
| **Document Export** | PhpSpreadsheet (.xlsx) dan Laravel-DomPDF (.pdf) |
| **Quality Assurance** | PHPUnit (18 Test Suites, 124 Test Cases, 485 Assertions - 100% Pass) |

---

## Skema Database

Sistem didukung oleh 8 tabel utama:
1. `users` - Akun pengguna, hak akses peran (karyawan, admin, superadmin), departemen, dan status pendaftaran biometrik.
2. `face_descriptors` - Vektor numerik 128-float representasi biometrik wajah (relasi 1:1).
3. `locations` - Master data titik lokasi kantor dan radius toleransi geofence (meter).
4. `attendances` - Log transaksi kehadiran harian, waktu, koordinat aktual, status, dan foto snapshot.
5. `leaves` - Data pengajuan cuti/izin/sakit, berkas bukti, dan catatan review persetujuan.
6. `settings` - Pengaturan parameter jam kerja dan kebijakan operasional.
7. `notifications` - Log notifikasi in-app untuk pengguna.
8. `location_tracks` - Log titik jejak koordinat GPS operasional SPG keliling (retensi 30 hari).

---

## Prasyarat Sistem

- PHP >= 8.2
- Composer
- Node.js & NPM (opsional untuk bundling aset)
- MySQL >= 8.0 atau MariaDB >= 10.4
- Web Server dengan dukungan HTTPS / SSL (wajib untuk akses Geolocation dan Camera API)

---

## Panduan Instalasi Lokal

1. **Clone Repositori:**
   ```bash
   git clone https://github.com/username/presensi-pwa-ptcak.git
   cd presensi-pwa-ptcak
   ```

2. **Install Dependencies:**
   ```bash
   composer install
   ```

3. **Konfigurasi Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Sesuaikan parameter database dan zona waktu pada `.env`:
   ```env
   APP_TIMEZONE=Asia/Makassar
   DB_DATABASE=presensi_cak
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Migrasi Database & Seeding Awal:**
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```

5. **Jalankan Aplikasi Lokal:**
   - **Opsi A (Menggunakan Uvicorn ASGI Runner):**
     ```bash
     python serve.py
     # atau
     uvicorn server:app --host 127.0.0.1 --port 8000
     # atau klik 2x start.bat pada Windows
     ```
   - **Opsi B (Menggunakan PHP Artisan CLI):**
     ```bash
     php artisan serve
     ```
   Akses aplikasi melalui peramban: `http://localhost:8000` (atau `http://127.0.0.1:8000`).

---

## Dokumentasi Proyek

Seluruh dokumentasi lengkap sistem, diagram alur, panduan pengguna, dan laporan pengujian tersimpan terstruktur dalam folder [docs/](file:///d:/PROJECT/Presensi(PWA)/docs/README.md):

- **Dokumen Format PDF:** [docs/pdf/](file:///d:/PROJECT/Presensi(PWA)/docs/pdf)
  - [PRD_Presensi_PWA_PT_CAK.pdf](file:///d:/PROJECT/Presensi(PWA)/docs/pdf/PRD_Presensi_PWA_PT_CAK.pdf)
  - [Alur_Bisnis_dan_Data_Flow_Presensi.pdf](file:///d:/PROJECT/Presensi(PWA)/docs/pdf/Alur_Bisnis_dan_Data_Flow_Presensi.pdf)
  - [Panduan_Pengguna_Presensi_PWA_PT_CAK.pdf](file:///d:/PROJECT/Presensi(PWA)/docs/pdf/Panduan_Pengguna_Presensi_PWA_PT_CAK.pdf)
  - [Panduan_Instalasi_Presensi_PWA_PT_CAK.pdf](file:///d:/PROJECT/Presensi(PWA)/docs/pdf/Panduan_Instalasi_Presensi_PWA_PT_CAK.pdf)
  - [Dokumentasi_UAT_dan_Debugging_Presensi_PT_CAK.pdf](file:///d:/PROJECT/Presensi(PWA)/docs/pdf/Dokumentasi_UAT_dan_Debugging_Presensi_PT_CAK.pdf)
  - [Laporan_Penyelesaian_Sprint_dan_Tugas_Presensi_PT_CAK.pdf](file:///d:/PROJECT/Presensi(PWA)/docs/pdf/Laporan_Penyelesaian_Sprint_dan_Tugas_Presensi_PT_CAK.pdf)

- **Dokumen Format Markdown:** [docs/markdown/](file:///d:/PROJECT/Presensi(PWA)/docs/markdown)
  - [ROADMAP.md](file:///d:/PROJECT/Presensi(PWA)/docs/markdown/ROADMAP.md)
  - [DESIGN.md](file:///d:/PROJECT/Presensi(PWA)/docs/markdown/DESIGN.md)
  - [PANDUAN_PENGGUNA.md](file:///d:/PROJECT/Presensi(PWA)/docs/markdown/PANDUAN_PENGGUNA.md)
  - [PANDUAN_INSTALASI.md](file:///d:/PROJECT/Presensi(PWA)/docs/markdown/PANDUAN_INSTALASI.md)
  - [DOKUMENTASI_UAT_DAN_DEBUGGING.md](file:///d:/PROJECT/Presensi(PWA)/docs/markdown/DOKUMENTASI_UAT_DAN_DEBUGGING.md)
  - [LAPORAN_PENYELESAIAN_SPRINT_DAN_TUGAS.md](file:///d:/PROJECT/Presensi(PWA)/docs/markdown/LAPORAN_PENYELESAIAN_SPRINT_DAN_TUGAS.md)
  - [DATA_DUMMY_DAN_MASTER.md](file:///d:/PROJECT/Presensi(PWA)/docs/markdown/DATA_DUMMY_DAN_MASTER.md)

---

## Lisensi & Objek Studi

Sistem dikembangkan sebagai blueprint implementasi sistem presensi digital pada **PT. Cahaya Anugrah Kalimantan**.
Hak cipta dilindungi undang-undang.
