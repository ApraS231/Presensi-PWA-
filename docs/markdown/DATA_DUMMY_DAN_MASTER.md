# DOKUMENTASI DATA DUMMY AKUN & MASTER DATA SISTEM
## Sistem Informasi Presensi PWA PT. Cahaya Anugrah Kalimantan

Dokumen ini berisi daftar lengkap akun dummy pengujian, master titik lokasi presensi geofence, kebijakan jam kerja sistem, serta data transaksi kehadiran yang telah terpasang pada basis data.

---

## 1. Daftar Akun Pengguna (User Credentials)

> **Catatan:** Password standar untuk semua akun bawaan adalah **`password`**, sedangkan untuk akun baru yang belum mengganti password adalah **`ptcak123`**.

### A. Super Admin (Master Control)
| NIK | Nama Pengguna | Alamat Email | Password | Jabatan & Departemen | Status Akun |
|---|---|---|---|---|---|
| `SA001` | Super Administrator | `superadmin@ptcak.com` | `password` | IT Administrator (IT & Infrastructure) | Aktif & Enrolled |

### B. HRD / Admin Presensi (Desktop Dashboard)
| NIK | Nama Pengguna | Alamat Email | Password | Jabatan & Departemen | Status Akun |
|---|---|---|---|---|---|
| `ADM001` | Siti Rahmawati, S.Psi | `hrd@ptcak.com` | `password` | HR Specialist & Attendance Lead (HR) | Aktif & Enrolled |
| `ADM002` | Bambang Pratama | `admin.operasional@ptcak.com` | `password` | Operations Administrator (Operasional) | Aktif & Enrolled |

### C. Karyawan Lapangan & Kantor (Mobile PWA - Terdaftar Biometrik)
| NIK | Nama Karyawan | Alamat Email | Password | Jabatan & Departemen | Status Biometrik | Status Presensi Hari Ini |
|---|---|---|---|---|---|---|
| `KAR001` | Budi Santoso | `budi.santoso@ptcak.com` | `password` | Heavy Equipment Operator (Operasional Tambang) | Enrolled | Hadir Tepat Waktu (Muara Badak) |
| `KAR002` | Agus Setiawan | `agus.setiawan@ptcak.com` | `password` | Dump Truck Driver (Operasional Tambang) | Enrolled | Hadir Terlambat (Muara Badak) |
| `KAR003` | Dewi Lestari | `dewi.lestari@ptcak.com` | `password` | Senior Accountant (Keuangan & Akuntansi) | Enrolled | Hadir & Selesai Pulang (Bontang) |
| `KAR004` | Eko Prasetyo | `eko.prasetyo@ptcak.com` | `password` | Warehouse Supervisor (Logistik & Gudang) | Enrolled | Hadir Tepat Waktu (Sangatta) |
| `KAR005` | Fajar Hidayat | `fajar.hidayat@ptcak.com` | `password` | Mechanic Lead (Engineering & Maintenance) | Enrolled | Hadir Tepat Waktu (Sangatta) |
| `KAR006` | Hendra Wijaya | `hendra.wijaya@ptcak.com` | `password` | Safety Officer (HSE & Safety) | Enrolled | Hadir Tepat Waktu (Muara Berau) |
| `KAR007` | Indah Permata | `indah.permata@ptcak.com` | `password` | HR Staff (Human Resources) | Enrolled | Hadir Tepat Waktu (Bontang) |
| `KAR008` | Joko Susilo | `joko.susilo@ptcak.com` | `password` | Mining Surveyor (Operasional Tambang) | Enrolled | Cuti Tahunan (Disetujui HRD) |
| `KAR009` | Rizky Kurniawan | `kurniawan@ptcak.com` | `password` | Junior Systems Developer (IT) | Enrolled | Pengajuan Sakit (Menunggu Approval) |
| `KAR010` | Nur Hidayah | `nur.hidayah@ptcak.com` | `password` | Billing & Cashier (Keuangan & Akuntansi) | Enrolled | Pengajuan Izin Ditolak |

### D. Karyawan Baru (Uji Coba Alur Pendaftaran Wajah)
| NIK | Nama Karyawan | Alamat Email | Password | Jabatan & Departemen | Status Biometrik |
|---|---|---|---|---|---|
| `KAR011` | Aditya Nugraha | `aditya.nugraha@ptcak.com` | `ptcak123` | Field Technician (Operasional Tambang) | **Pending Enrollment** (Otomatis diarahkan ke pemindai wajah saat login) |
| `KAR012` | Bayu Samudra | `bayu.samudra@ptcak.com` | `ptcak123` | Staff Logistik (Logistik & Gudang) | **Pending Enrollment** |

---

## 2. Master Data Titik Lokasi & Radius Geofence

| No | Nama Lokasi / Site Proyek | Titik Latitude | Titik Longitude | Radius Toleransi | Status | Keterangan Operasional |
|---|---|---|---|---|---|---|
| 1 | **Kantor Pusat PT. CAK (Bontang)** | `-0.1333000` | `117.4833000` | 100 Meter | **Aktif** | Kantor Administrasi & Manajemen Utama |
| 2 | **Site Tambang Muara Badak (Pit A)** | `-0.3125000` | `117.3850000` | 250 Meter | **Aktif** | Area Penambangan & Operasional Alat Berat |
| 3 | **Site Workshop & Logistik Sangatta** | `0.4900000` | `117.5400000` | 150 Meter | **Aktif** | Bengkel Pemeliharaan & Gudang Logistik |
| 4 | **Pelabuhan Jetty Muara Berau** | `-0.5200000` | `117.6100000` | 200 Meter | **Aktif** | Fasilitas Transshipment & Pelabuhan Batubara |
| 5 | **Site Samarinda Seberang (Standby)** | `-0.5400000` | `117.1400000` | 100 Meter | **Nonaktif** | Lokasi Cadangan / Standby Project |

---

## 3. Master Data Kebijakan Sistem Global (Settings)

| Kunci Pengaturan (`key`) | Nilai Baku (`value`) | Satuan / Format | Keterangan Aturan Bisnis |
|---|---|---|---|
| `jam_masuk` | `08:00` | Jam (WITA) | Jam dimulainya operasional kerja harian. |
| `jam_pulang` | `17:00` | Jam (WITA) | Jam dimulainya presensi pulang (check-out). |
| `toleransi_terlambat` | `15` | Menit | Check-in hingga pukul 08:15 WITA tetap Tepat Waktu. |
| `jam_istirahat` | `60` | Menit | Potongan waktu istirahat dalam jam kerja bersih. |
| `hari_kerja` | `senin,selasa,rabu,kamis,jumat` | Teks Komparasi | Hari kerja aktif untuk otomatisasi auto-alpha. |
| `max_retroaktif_izin` | `3` | Hari Kalender | Batas maksimum hari mundur pengajuan izin yang diizinkan. |

---

## 4. Cara Menjalankan Seeder Kembali
Jika basis data perlu di-reset atau diisi ulang sewaktu-waktu:
```bash
php artisan db:seed --class=DummyDataSeeder
```
Atau reset total seluruh tabel dan seeder:
```bash
php artisan migrate:fresh --seed
php artisan db:seed --class=DummyDataSeeder
```
