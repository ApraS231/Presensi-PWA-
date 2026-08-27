# Alur Bisnis & Data Flow Per Role

Referensi detail alur bisnis berdasarkan BPDF PT. Cahaya Anugrah Kalimantan.
Diperbaiki berdasarkan analisis plot hole & inkonsistensi.

---

## Role 1: Karyawan (Mobile PWA Interface)

Karyawan mengakses aplikasi melalui smartphone via peramban modern (Service Worker/PWA).

### Proses 1 — Autentikasi Karyawan

| Aspek | Detail |
|---|---|
| **Input** | NIK / Email, Kata Sandi (Password) |
| **Proses** | Verifikasi hash Bcrypt pada tabel `users`, pengecekan role `karyawan`, pengecekan `is_active = true` |
| **Output (Sukses)** | Auth Token / Session, redirect ke Dashboard Beranda PWA |
| **Output (Nonaktif)** | Pesan: "Akun Anda telah dinonaktifkan. Hubungi Admin." → Login ditolak |

### Proses 2 — Validasi Geospasial (Lokasi)

| Aspek | Detail |
|---|---|
| **Input** | Koordinat GPS perangkat (Latitude, Longitude, Accuracy) via HTML5 Geolocation |
| **Proses (Client)** | Hitung Haversine di JavaScript → untuk UX feedback (tampilkan jarak, enable/disable tombol) |
| **Proses (Server)** | Re-kalkulasi Haversine di Laravel → penentu FINAL (anti manipulasi client) |
| **Output Valid** | Kamera aktif & tombol presensi terbuka |
| **Output Invalid** | Tombol disable, notifikasi jarak aktual (misal: "120m dari kantor") |
| **GPS Ditolak** | Instruksi step-by-step mengaktifkan GPS + tombol "Coba Lagi" + "Hubungi HRD" setelah 3x gagal |

### Proses 3 — Verifikasi Biometrik Wajah

| Aspek | Detail |
|---|---|
| **Input** | Citra video stream dari kamera depan perangkat |
| **Pre-check** | Cek `enrollment_status` — jika `pending`: "Akun belum terdaftar biometrik. Hubungi HRD untuk pendaftaran wajah." → STOP |
| **Proses** | face-api.js mendeteksi landmark wajah (client-side), ekstrak 128-float facial vector, hitung jarak Euclidean terhadap template descriptor terdaftar (Threshold ≤ 0.50) |
| **Output (Cocok)** | Bounding box hijau pada frame wajah, payload (foto snapshot base64 + koordinat GPS + timestamp) |
| **Output (Tidak Cocok)** | Notifikasi "Wajah tidak dikenali, ulangi" |
| **Kamera Ditolak** | Instruksi step-by-step mengaktifkan kamera + tombol "Coba Lagi" + "Hubungi HRD" setelah 3x gagal |

### Proses 4 — Presensi Masuk

| Aspek | Detail |
|---|---|
| **Pre-check** | Cek apakah sudah ada record `attendances` hari ini untuk user |
| **Jika sudah masuk, belum pulang** | Redirect ke flow Presensi Pulang |
| **Jika sudah masuk DAN pulang** | Pesan: "Anda sudah presensi hari ini" → STOP |
| **Input** | Klik tombol "Kirim Presensi Masuk" |
| **Proses** | Laravel memvalidasi token CSRF, re-kalkulasi Haversine (server-side), pengecekan jam kerja pada tabel `settings` (`jam_masuk` + `toleransi_terlambat`) untuk menentukan status |
| **Status** | `tepat_waktu` jika masuk ≤ jam_masuk + toleransi, `terlambat` jika lebih |
| **Foto** | Simpan ke `storage/app/public/attendances/{user_id}/{date}/photo_in.jpg`, compress JPEG 70% |
| **Output** | Record tersimpan di tabel `attendances` (unique constraint `[user_id, date]`), tampilan feedback sukses beserta jam & foto kehadiran |

### Proses 5 — Presensi Pulang

| Aspek | Detail |
|---|---|
| **Pre-check** | Cek record `attendances` hari ini: harus sudah ada `time_in` dan `time_out` masih NULL |
| **Jika belum masuk** | Pesan: "Anda belum presensi masuk hari ini" → STOP |
| **Jika sudah pulang** | Pesan: "Anda sudah presensi hari ini" → STOP |
| **Input** | GPS + Face Recognition (sama seperti masuk — Opsi A, konsisten anti-buddy-punching) |
| **Proses** | Validasi CSRF, re-kalkulasi Haversine di server, face matching |
| **Foto** | Simpan ke `storage/app/public/attendances/{user_id}/{date}/photo_out.jpg` |
| **Output** | Update record existing: `time_out`, `lat_out`, `long_out`, `photo_out` |

### Proses 6 — Pengajuan Izin / Cuti / Sakit

| Aspek | Detail |
|---|---|
| **Input** | Jenis perizinan, rentang tanggal mulai s/d selesai, keterangan alasan, file lampiran (foto surat dokter/PDF) |
| **Validasi** | Ekstensi file & ukuran upload (≤ 2MB), pengajuan retroaktif maksimal `max_retroaktif_izin` hari ke belakang |
| **Proses** | Penyimpanan file bukti ke storage server, set default status `pending` |
| **Output** | Record tersimpan di tabel `leaves`, notifikasi status perizinan menunggu persetujuan HRD |

### Proses 7 — Edit & Batalkan Izin

| Aspek | Detail |
|---|---|
| **Syarat** | Hanya bisa dilakukan jika status izin masih `pending` |
| **Edit** | Karyawan bisa mengubah tanggal, alasan, lampiran |
| **Cancel** | Update status izin ke `cancelled` |
| **Setelah Approved/Rejected** | TIDAK bisa diubah atau dibatalkan — status final |

### Proses 8 — Menerima Notifikasi

| Aspek | Detail |
|---|---|
| **Mekanisme** | Polling setiap 30 detik ke `GET /api/notifications` |
| **Tampilan** | Badge counter notifikasi belum dibaca di navbar PWA |
| **Tipe Notifikasi** | `leave_approved`, `leave_rejected`, dan notifikasi lainnya |
| **Aksi** | Klik notifikasi → mark as read + navigasi ke halaman terkait |

---

## Role 2: HRD / Admin Presensi (Desktop Management View)

HRD bertanggung jawab terhadap validitas data pegawai, proses pendaftaran wajah, pengawasan kehadiran real-time, dan rekapitulasi penggajian.

### Proses 1 — Enrollment Biometrik Karyawan

| Aspek | Detail |
|---|---|
| **Input** | Pemilihan ID Karyawan, pengambilan 3–5 foto wajah karyawan dari webcam desktop |
| **Proses** | Ekstraksi landmark wajah multi-sudut via face-api.js, komputasi rata-rata (mean vector) 128 descriptor, enkoding data descriptor ke format JSON |
| **Output** | Data tersimpan pada tabel `face_descriptors` (relasi 1:1), `users.enrollment_status` berubah ke `enrolled` |

### Proses 2 — Re-Enrollment Wajah

| Aspek | Detail |
|---|---|
| **Kapan** | Perubahan penampilan karyawan (kacamata, jenggot, dll) yang menyebabkan face matching gagal |
| **Proses** | Sama seperti enrollment awal — ambil 3–5 foto baru, hitung mean vector baru |
| **Output** | Descriptor lama di-**replace** (bukan append), `updated_at` ter-update sebagai riwayat |

### Proses 3 — Monitoring Presensi & Live Map

| Aspek | Detail |
|---|---|
| **Input** | Pemilihan tanggal pemantauan, filter departemen / cabang kantor |
| **Proses** | Query agregasi real-time tabel `attendances` hari berjalan, mapping titik koordinat (`lat_in`, `long_in`) ke layer Leaflet.js, kalkulasi persentase kehadiran: Masuk, Terlambat, Izin, Alpha |
| **Dashboard Extras** | Badge: "X karyawan belum enrollment" — reminder dari tabel `users` WHERE `enrollment_status = 'pending'` |
| **Output** | Visualisasi peta interaktif dengan marker lokasi presensi, tabel monitoring langsung (auto-refresh/polling) |

### Proses 4 — Verifikasi & Approval Izin / Cuti

| Aspek | Detail |
|---|---|
| **Input** | Pilihan persetujuan: Approve / Reject, catatan evaluasi HRD |
| **Proses Approve** | Update status pada tabel `leaves`, **generate attendance records** untuk setiap hari kerja dalam rentang izin (skip hari di luar `hari_kerja` dari settings), set status attendance = `izin`/`sakit`/`cuti` |
| **Proses Reject** | Update status `leaves` ke `rejected` |
| **Notifikasi** | Kirim notifikasi ke karyawan via `NotificationService` — "Izin Anda telah disetujui/ditolak" |
| **Output** | Update status perizinan, notifikasi perubahan status terlihat di PWA Karyawan |

### Proses 5 — Rekapitulasi & Export Laporan

| Aspek | Detail |
|---|---|
| **Input** | Rentang tanggal (misal: 01 s/d 30 tiap bulan), pilihan format output (Excel .xlsx atau PDF) |
| **Kalkulasi** | Total jam kerja: `(time_out - time_in) - jam_istirahat`. Jika `time_out` NULL → fallback ke `jam_pulang`. Izin/sakit/cuti approved → jam kerja penuh. Alpha → 0 jam. Record `auto_checkout = true` ditandai khusus. |
| **Proses** | Rendering dokumen via PhpSpreadsheet / Laravel-DomPDF |
| **Output** | File rekapitulasi `Laporan_Presensi_CAK.xlsx` / `.pdf` siap unduh untuk payroll |

---

## Role 3: Super Admin / IT Administrator (System Control View)

Super Admin memiliki kewenangan penuh atas master data infrastruktur, konfigurasi titik geofence, dan tata kelola akun sistem.

### Proses 1 — Manajemen Master Lokasi & Geofence

| Aspek | Detail |
|---|---|
| **Input** | Nama titik proyek/kantor, titik Latitude & Longitude, radius geofence (dalam meter) |
| **Proses** | Validasi format koordinat desimal, visualisasi circle polygon pada peta preview admin, simpan/update data ke tabel `locations` |
| **Output** | Titik koordinat aktif baru yang dapat digunakan oleh seluruh karyawan di lokasi terkait |
| **Catatan** | Karyawan bisa presensi di SEMUA lokasi aktif (sistem mencari lokasi terdekat yang dalam radius) |

### Proses 2 — Manajemen Karyawan & Jabatan

| Aspek | Detail |
|---|---|
| **Input** | Form data karyawan (NIK, Nama, Email, Jabatan, Department, Role, Status Aktif) |
| **Proses** | Generasi default password (Bcrypt), pengecekan keunikan NIK/Email di tabel `users` |
| **Output** | Akun karyawan baru terbuat dengan `enrollment_status = pending` — siap untuk enrollment wajah oleh HRD |
| **Nonaktifkan** | Toggle `is_active = false` → karyawan tidak bisa login lagi (di-block oleh middleware `CheckActive`) |

### Proses 3 — Pengaturan Kebijakan Kerja (Global Config)

| Aspek | Detail |
|---|---|
| **Input** | Jam Masuk Kerja (default: 08:00 WITA), Jam Pulang Kerja (default: 17:00 WITA), Toleransi Terlambat (default: 15 menit), Jam Istirahat (default: 60 menit), Hari Kerja (default: Senin–Jumat), Maks Retroaktif Izin (default: 3 hari) |
| **Proses** | Update parameter global pada tabel `settings` |
| **Output** | Kebijakan waktu baru otomatis berlaku pada perhitungan absensi hari berikutnya |

---

## Proses Otomatis — Scheduler Harian (23:00 WITA)

### Auto-Checkout

| Aspek | Detail |
|---|---|
| **Trigger** | Laravel Scheduler, setiap hari jam 23:00 |
| **Kondisi** | Karyawan aktif yang punya `time_in` tapi `time_out` = NULL hari ini |
| **Aksi** | Set `time_out` = `jam_pulang` dari settings, set `auto_checkout = true` |
| **Tujuan** | Menangani karyawan yang lupa presensi pulang agar laporan tetap akurat |

### Generate Alpha

| Aspek | Detail |
|---|---|
| **Trigger** | Laravel Scheduler, setiap hari jam 23:00 (setelah auto-checkout) |
| **Kondisi** | Karyawan aktif yang tidak punya record `attendances` hari ini DAN tidak ada `leaves` dengan status `approved` yang mencakup tanggal hari ini |
| **Aksi** | Insert record `attendances` dengan `status = 'alpha'` |
| **Tujuan** | Dashboard dan laporan bisa query langsung dari DB tanpa kalkulasi tambahan |

---

## Alur Presensi Harian Karyawan (User Flow Ringkasan)

```
1. Buka Aplikasi PWA di HP → Login dengan NIK & Password.
   ├─ JIKA akun nonaktif: "Akun dinonaktifkan" → STOP.
   └─ JIKA aktif: masuk ke Dashboard.

2. Klik tombol "Presensi Masuk" di Beranda.
   ├─ JIKA belum enrollment: "Hubungi HRD untuk pendaftaran wajah" → STOP.
   ├─ JIKA sudah masuk hari ini (belum pulang): redirect ke Presensi Pulang.
   └─ JIKA sudah masuk DAN pulang: "Anda sudah presensi hari ini" → STOP.

3. Sistem mengaktifkan Geolocation API → Menghitung jarak GPS karyawan ke Kantor terdekat.
   ├─ JIKA GPS ditolak: Instruksi + "Coba Lagi" (max 3x).
   ├─ JIKA Jarak > Radius Kantor: "Di luar jangkauan (Jarak: X meter)" → STOP.
   └─ JIKA Jarak <= Radius Kantor: Lanjut ke tahap kamera.

4. Kamera PWA aktif → face-api.js mendeteksi wajah karyawan secara real-time.
   └─ JIKA Kamera ditolak: Instruksi + "Coba Lagi" (max 3x).

5. Tombol "Ambil Foto Presensi" ditekan → Descriptor dicocokkan dengan data terdaftar.
   ├─ JIKA Wajah Tidak Cocok (> Threshold): "Wajah tidak dikenali, ulangi".
   └─ JIKA Wajah Cocok (<= Threshold): Kirim payload ke Backend Laravel.

6. Server memverifikasi kembali koordinat (Haversine SERVER) & menyimpan record ke `attendances`.
   - Foto disimpan di storage (JPEG 70%)
   - Status dihitung berdasarkan settings (jam_masuk + toleransi)

7. Layar menampilkan status "Presensi Berhasil: Tepat Waktu / Terlambat".
```

---

## Alur Pengajuan Izin (User Flow Ringkasan)

```
1. Karyawan → Klik "Pengajuan Izin" di menu PWA.
2. Isi form: jenis (izin/sakit/cuti), tanggal mulai-selesai, alasan, upload lampiran.
   ├─ JIKA tanggal < hari ini - max_retroaktif_izin: "Pengajuan retroaktif melebihi batas" → STOP.
   └─ JIKA valid: Submit → status = pending.

3. [Opsional] Karyawan bisa EDIT atau CANCEL selama status masih pending.
   └─ JIKA status sudah approved/rejected: Tidak bisa diubah.

4. HRD → Lihat daftar pengajuan pending → Klik Approve/Reject + catatan.
   ├─ JIKA Approve:
   │   ├─ Update leaves.status = 'approved'
   │   ├─ Generate attendance records untuk setiap hari kerja dalam rentang
   │   │   (skip weekend/hari non-kerja sesuai settings)
   │   └─ Kirim notifikasi ke karyawan: "Izin Anda disetujui"
   └─ JIKA Reject:
       ├─ Update leaves.status = 'rejected'
       └─ Kirim notifikasi ke karyawan: "Izin Anda ditolak"
```

---

## Manajemen Risiko

| Risiko | Level | Strategi Mitigasi |
|---|---|---|
| **Fake GPS / Location Mocking** | Sedang–Tinggi | Library pengecekan mock location pada API browser + validasi network-based IP geocoding + re-validasi Haversine server-side (penentu FINAL) |
| **Koneksi Internet Lapangan Tidak Stabil** | Sedang | PWA dilengkapi Service Worker caching agar UI tetap terbuka, pesan instruksi jelas untuk menekan tombol kirim saat sinyal kembali pulih |
| **Pencahayaan Redup saat Face Recognition** | Rendah–Sedang | Feedback indikator bounding box hijau pada deteksi wajah + instruksi pindah ke area berpenerangan cukup sebelum snapshot |
| **Kamera/GPS Ditolak User** | Sedang | Instruksi step-by-step + tombol "Coba Lagi" (max 3x) + "Hubungi HRD untuk bantuan" setelah gagal |
| **Karyawan Lupa Presensi Pulang** | Sedang | Auto-checkout scheduler jam 23:00 WITA + flag `auto_checkout = true` di laporan |
| **Presensi Ganda** | Rendah | Unique constraint `[user_id, date]` + logic check sebelum insert + redirect otomatis ke flow yang benar |
| **Karyawan Baru Belum Enrollment** | Rendah | Pesan "Hubungi HRD" + dashboard badge reminder untuk HRD |
