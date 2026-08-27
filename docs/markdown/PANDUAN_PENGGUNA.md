# BUKU PANDUAN PENGGUNA (USER GUIDE)
## Sistem Informasi Presensi Karyawan PWA PT. Cahaya Anugrah Kalimantan

- **Nomor Dokumen:** UG-CAK-2026
- **Edisi / Versi:** v1.0 (Agustus 2026)
- **Format Cetak PDF:** [Panduan_Pengguna_Presensi_PWA_PT_CAK.pdf](file:///d:/PROJECT/Presensi(PWA)/Panduan_Pengguna_Presensi_PWA_PT_CAK.pdf)

---

## 1. Pendahuluan & Persyaratan Akses Sistem

Sistem Presensi Progressive Web Application (PWA) PT. Cahaya Anugrah Kalimantan melayani tiga tingkatan hak akses pengguna:

| Peran (Role) | Antarmuka Utama | Tanggung Jawab & Fitur Utama |
|---|---|---|
| **Karyawan** | Mobile PWA (Smartphone) | Enrollment biometrik wajah, presensi check-in/check-out harian, pengajuan izin/cuti/sakit, dan riwayat presensi mandiri. |
| **HRD / Admin Presensi** | Desktop Dashboard | Monitoring kehadiran real-time, live geospatial map, approval izin/cuti, rekapitulasi laporan, dan ekspor Excel/PDF. |
| **Super Admin** | Desktop Master Control | Manajemen data pengguna, reset biometrik wajah, master titik koordinat geofence, dan konfigurasi kebijakan jam kerja global. |

### Panduan Instalasi PWA pada Smartphone Karyawan
1. Buka peramban (Google Chrome / Safari) pada smartphone Anda, lalu akses alamat URL resmi presensi PT. CAK.
2. Tekan tombol menu browser (ikon titik tiga di kanan atas Chrome, atau ikon Share di Safari).
3. Pilih opsi **"Tambahkan ke Layar Utama" (Add to Home Screen)** atau **"Instal Aplikasi" (Install App)**.
4. Ikon aplikasi **Presensi PT. CAK** akan muncul di layar utama smartphone dan dapat diakses selayaknya aplikasi native.

> **PENTING:** Pastikan izin akses **Kamera** dan **Lokasi Perangkat (GPS Akurasi Tinggi)** selalu diberikan izin aktif (Allow) pada peramban web Anda.

---

## 2. Panduan Operasional Karyawan (Mobile PWA)

### Langkah 1: Login Akun & Keamanan Awal
- Masukkan **Nomor Induk Karyawan (NIK)** atau **Alamat Email** terdaftar beserta **Password** (default awal: `ptcak123`).
- Setelah berhasil login, karyawan disarankan segera memperbarui password melalui menu profil demi keamanan akun.

### Langkah 2: Pendaftaran Biometrik Wajah (Enrollment)
- Saat pertama kali login, akun berstatus `pending` dan otomatis diarahkan ke halaman pendaftaran wajah.
- Posisikan wajah tepat di tengah bingkai panduan kamera (lingkaran pemindai).
- Pastikan pencahayaan ruangan cukup terang dan tidak membelakangi sumber cahaya (backlight).
- Lepaskan masker, kacamata hitam, atau penutup wajah lainnya.
- Sistem akan mendeteksi kontur wajah dan mengekstraksi 128 vektor biometrik neural.
- Tekan tombol **"Simpan Biometrik Wajah"**. Status akun akan berubah menjadi `enrolled` dan modul presensi harian langsung aktif.

### Langkah 3: Pelaksanaan Presensi Masuk (Check-In) Harian
- Pastikan Anda telah berada di area kantor atau lokasi proyek PT. CAK (jarak GPS berada di dalam radius geofence yang ditentukan).
- Buka menu **"Presensi"** pada navigasi bawah PWA.
- Arahkan kamera ke wajah Anda. Indikator pemindai akan memverifikasi kecocokan wajah biometrik (Euclidean distance $\le 0.50$).
- Setelah status geofence dan wajah terverifikasi valid, tekan tombol **"Kirim Presensi Masuk"**.
- Sistem mencatat waktu kehadiran berdasarkan zona waktu WITA (Asia/Makassar):
  - **Tepat Waktu:** Check-in sebelum jam cut-off (Pukul 08:00 + toleransi 15 menit = Maksimal 08:15 WITA).
  - **Terlambat:** Check-in melewati pukul 08:15 WITA (menit keterlambatan dihitung otomatis).

### Langkah 4: Pelaksanaan Presensi Pulang (Check-Out) Harian
- Pada akhir jam kerja operasional (mulai pukul 17:00 WITA), buka kembali menu **"Presensi"**.
- Sistem otomatis beralih ke mode Check-Out. Verifikasi wajah dan lokasi di dalam radius kantor.
- Tekan tombol **"Kirim Presensi Pulang"**. Jam pulang berhasil tercatat dan durasi jam kerja bersih harian dihitung.

> **Peringatan Auto-Checkout:** Jika karyawan lupa melakukan check-out hingga pukul 23:59 WITA, sistem scheduler otomatis menutup sesi presensi dengan tanda khusus `Auto-Checkout`.

### Langkah 5: Pengajuan Izin, Sakit & Cuti Mandiri
- Buka menu **"Izin"** lalu tekan tombol **"Ajukan Izin Baru"**.
- Pilih Jenis Permohonan: **Cuti Tahunan**, **Sakit** (wajib melampirkan surat dokter), atau **Izin Keperluan Mendesak**.
- Tentukan rentang Tanggal Mulai dan Tanggal Selesai serta isi alasan permohonan.
- Unggah berkas foto/dokumen pendukung (format JPG, PNG, PDF maks. 2 MB).
- Tekan **"Kirim Pengajuan"**. Permohonan akan masuk ke antrean persetujuan HRD.

### Langkah 6: Pemantauan Riwayat Kehadiran Mandiri
- Karyawan dapat membuka menu **"Riwayat"** untuk melihat rangkuman statistik bulanan: total hari hadir, keterlambatan, cuti/izin yang disetujui, dan alpha, lengkap dengan pratinjau snapshot foto presensi masuk dan pulang.

---

## 3. Panduan Operasional HRD / Admin Presensi (Desktop)

### 1. Dashboard Monitoring Real-Time & Live Polling
- Dashboard desktop memuat metrik ringkasan harian: Total Karyawan, Hadir Tepat Waktu, Terlambat, Izin/Cuti, dan Belum Hadir.
- Tabel presensi harian otomatis diperbarui setiap **30 detik** tanpa perlu me-refresh browser.
- HRD dapat memfilter data berdasarkan **Tanggal Kehadiran** dan **Departemen**.
- Jika terdapat karyawan baru yang belum melakukan enrollment wajah, sistem menampilkan banner peringatan daftar nama yang perlu ditindaklanjuti.

### 2. Visualisasi Live Geospatial Map (Peta Lokasi)
- Buka menu **"Live Map Monitoring"** pada sidebar navigasi.
- Peta berbasis OpenStreetMap menampilkan lingkaran geofence seluruh kantor dan site proyek tambang PT. CAK.
- Titik pin koordinat kehadiran karyawan ditampilkan dengan kode warna status:
  - **Biru / Hijau:** Presensi Tepat Waktu di dalam geofence.
  - **Kuning / Oranye:** Presensi Terlambat.
- Klik pada pin karyawan untuk melihat popup detail: Nama, NIK, Waktu Presensi Masuk/Pulang, Jarak dari titik pusat kantor, dan thumbnail foto snapshot.

### 3. Pemrosesan & Approval Pengajuan Izin / Cuti
- Buka menu **"Persetujuan Izin"** untuk melihat daftar antrean permohonan karyawan.
- Klik permohonan untuk meninjau detail alasan, rentang tanggal, dan lampiran dokumen bukti.
- Pilih tindakan **"Setujui" (Approve)** atau **"Tolak" (Reject)** disertai catatan evaluasi.
- Permohonan yang disetujui otomatis menghasilkan catatan kehadiran sah pada hari kerja aktif, sehingga karyawan tidak terhitung Alpha.

### 4. Rekapitulasi Laporan & Ekspor Dokumen Resmi
- Buka menu **"Laporan & Rekapitulasi"**.
- Pilih rentang tanggal kustom atau gunakan preset cepat: *Hari Ini*, *7 Hari Terakhir*, *Bulan Ini*, atau *Bulan Lalu*.
- Sistem menyajikan tabel rekapitulasi lengkap dengan formula jam kerja bersih:
  `Jam Kerja Bersih = (Jam Pulang - Jam Masuk) - Durasi Istirahat`
- Tombol Ekspor Dokumen:
  - **Ekspor Excel (.xlsx):** Berkas spreadsheet terformat siap integrasi dengan sistem payroll keuangan.
  - **Cetak PDF (.pdf):** Dokumen resmi format A4 Landscape ber-kop perusahaan PT. Cahaya Anugrah Kalimantan.

---

## 4. Panduan Master Control Super Admin (System Administrator)

### 1. Manajemen Master Pengguna & Karyawan
- **Tambah Pengguna Baru:** Buka menu *Master Pengguna > Tambah Pengguna*. Isi NIK, Nama, Email, Role (Karyawan / Admin / Superadmin), Departemen, Jabatan, dan No. Telepon. Password awal otomatis diset ke `ptcak123`.
- **Reset Biometrik Wajah:** Jika karyawan mengganti foto biometrik atau terjadi kendala pengenalan wajah, Super Admin dapat menekan tombol **Reset Wajah** pada tabel pengguna.
- **Toggle Status Aktif (Aktivasi / Deaktivasi):** Super Admin dapat menonaktifkan akun karyawan seketika. Akun nonaktif akan langsung diputus sesinya saat melakukan request berikutnya.

### 2. Manajemen Master Titik Lokasi & Radius Geofence
- Buka menu *Master Titik Lokasi* untuk mengelola daftar kantor pusat dan site proyek operasional.
- **Pemilih Koordinat Interaktif (Interactive Map Picker):** Klik pada peta atau geser pin koordinat untuk otomatis mengisi latitude dan longitude lokasi presensi.
- Tentukan **Radius Geofence (Meter)** untuk menetapkan batas toleransi jarak presensi karyawan (contoh: 100 meter).
- Titik lokasi dapat diaktifkan atau dinonaktifkan sewaktu-waktu sesuai status proyek.

### 3. Konfigurasi Kebijakan Sistem Global
Super Admin dapat mengatur parameter operasional sistem melalui menu *Kebijakan Sistem*:

| Parameter Kebijakan | Nilai Standar | Keterangan & Fungsi |
|---|---|---|
| **Jam Masuk Kerja** | 08:00 WITA | Batas waktu awal dimulainya jam kerja operasional. |
| **Jam Pulang Kerja** | 17:00 WITA | Waktu dimulainya izin presensi pulang (check-out). |
| **Toleransi Keterlambatan** | 15 Menit | Check-in hingga pukul 08:15 WITA tetap terhitung Tepat Waktu. |
| **Durasi Istirahat Harian** | 60 Menit | Potongan waktu istirahat dalam formula perhitungan jam kerja bersih payroll. |
| **Kalender Hari Kerja Aktif** | senin s/d jumat | Hari kerja aktif untuk otomatisasi scheduler generate status Alpha. |
| **Batas Retroaktif Izin** | 3 Hari | Batas maksimum hari ke belakang pengajuan izin/sakit yang diperbolehkan. |

---

## 5. Panduan Penanganan Kendala (Troubleshooting FAQ)

| Kendala yang Ditemui | Langkah Solusi Penanganan |
|---|---|
| **Lokasi GPS Terdeteksi di Luar Radius Kantor** | Pastikan fitur GPS / Layanan Lokasi diaktifkan dengan mode *Akurasi Tinggi*. Hindari berada di dalam ruangan tertutup beton tebal saat pertama kali membuka aplikasi agar sinyal GPS satelit terkunci optimal. |
| **Kamera Tidak Terbuka di Peramban Web** | Buka pengaturan peramban (Chrome / Safari) > Setelan Situs > Kamera > Ubah izin akses untuk domain presensi menjadi **Izinkan (Allow)**. Muat ulang halaman presensi. |
| **Wajah Tidak Terdeteksi / Gagal Verifikasi** | Pastikan pencahayaan cukup terang, hindari bayangan gelap pada wajah, posisikan wajah sejajar lensa kamera, dan lepas kacamata hitam / masker. Jika kontur wajah berubah signifikan, hubungi HRD untuk meminta *Reset Biometrik Wajah*. |
| **Lupa Password Akun** | Hubungi Super Admin atau HRD untuk mereset password akun Anda kembali ke password standar perusahaan (`ptcak123`). |
