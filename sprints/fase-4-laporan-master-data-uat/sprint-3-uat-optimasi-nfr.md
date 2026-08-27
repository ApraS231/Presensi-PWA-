# Sprint 4.3 - Pengujian UAT, Optimasi Kecepatan & Audit NFR

- **Fase:** 4 (Laporan, Master Data & UAT)
- **Estimasi Durasi:** 4 Hari
- **Prasyarat:** Seluruh Sprint 1.1 hingga 4.2 selesai

---

## 1. Kebutuhan Fungsional & Skenario UAT

- `UAT-SC-01`: **Skenario Onboarding & Enrollment Wajah**:
  - Super Admin membuat user baru -> HRD membuka webcam dan mengambil 3 snapshot wajah -> Status berubah menjadi `enrolled`.
- `UAT-SC-02`: **Skenario Presensi Masuk (Valid)**:
  - Karyawan berada di area kantor (< radius) -> Buka PWA -> Wajah dikenali -> Submit berhasil tercatat status `tepat_waktu` atau `terlambat`.
- `UAT-SC-03`: **Skenario Penolakan Geofencing (Luar Radius)**:
  - Karyawan mencoba presensi di luar radius kantor -> Tombol submit nonaktif -> Manipulasi request via Postman/curl tetap ditolak oleh server dengan status 422.
- `UAT-SC-04`: **Skenario Penolakan Wajah (Bukan Pemilik Akun / Buddy Punching)**:
  - Karyawan A login dengan akun Karyawan B -> Deteksi wajah mendeteksi deviasi Euclidean > 0.50 -> Submit ditolak.
- `UAT-SC-05`: **Skenario Presensi Pulang & Auto-Checkout**:
  - Karyawan melakukan presensi pulang normal.
  - Simulasi karyawan lupa presensi pulang -> Menjalankan command `php artisan presensi:daily-scheduler` -> Record terisi otomatis dengan flag `auto_checkout = true`.
- `UAT-SC-06`: **Skenario Perizinan Multi-Hari**:
  - Pengajuan izin 3 hari kerja -> HRD approve -> Tabel `attendances` terisi otomatis untuk 3 hari tersebut dan tidak terhitung alpha saat scheduler berjalan.
- `UAT-SC-07`: **Skenario Rekapitulasi & Ekspor Laporan**:
  - HRD memfilter periode bulanan -> Ekspor Excel & PDF -> Memverifikasi kebenaran formula jam kerja bersih dan total keterlambatan.

---

## 2. Target Kebutuhan Non-Fungsional (NFR Verification)

| Kategori NFR | Target Metrik | Metode Pengujian |
|---|---|---|
| **Akurasi Face Recognition** | >= 95% kecocokan biometrik pada pencahayaan wajar | Uji coba 20 karyawan dengan berbagai pose wajah |
| **Efektivitas Geofencing** | 100% presensi luar radius tertolak | Uji coba simulasi titik GPS 100m, 500m, 1km dari kantor |
| **PWA Installability** | Lighthouse PWA Score >= 90% | Audit Google Chrome Lighthouse (Manifest + SW) |
| **Kecepatan Deteksi Wajah** | <= 2.0 detik di smartphone kelas menengah | Pengukuran waktu inferensi `faceapi.detectSingleFace()` |
| **Loading Awal PWA** | < 1.5 detik (dengan aset tercache) | Pengukuran Network Performance Chrome DevTools |
| **Keamanan Autentikasi** | 100% request rute terproteksi memeriksa status `is_active` | Pengujian akses sesi saat akun dinonaktifkan di DB |

---

## 3. Langkah-Langkah Dekomposisi Teknis

### Langkah 1: Automated Unit & Feature Testing (PHPUnit / Pest)
Target folder: `tests/Feature/`

1. `AuthenticationTest.php`: Login sukses NIK/Email, gagal password, pemblokiran akun `is_active = false`.
2. `HaversineGeofenceTest.php`: Unit test formula Haversine dengan berbagai set koordinat referensi.
3. `AttendanceTransactionTest.php`: Pengujian penolakan luar radius, pencegahan double check-in, presensi pulang.
4. `LeaveWorkflowTest.php`: Pengujian flow pengajuan izin, penolakan retroaktif, auto-generate record attendance saat approve.
5. `SchedulerDailyTest.php`: Pengujian command auto-checkout dan auto-alpha.

Eksekusi:
```bash
php artisan test
```

### Langkah 2: Audit Performa Frontend & PWA
1. Buka Chrome DevTools -> Tab **Lighthouse**.
2. Jalankan audit untuk **Progressive Web App**, **Performance**, dan **Accessibility**.
3. Pastikan tidak ada console error atau render-blocking resource yang berlebihan.

### Langkah 3: Bug Fixing, Optimasi Query & Deployment Staging
1. Optimasi query Eloquent (`select()`, eager loading `with()`, index kolom `user_id`, `date`, `status`).
2. Konfigurasi caching produksi:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## 4. Kriteria Penerimaan (Definition of Done)

- [ ] Seluruh automated feature test (PHPUnit) lulus 100% tanpa kegagalan (0 failures, 0 errors).
- [ ] Seluruh skenario pengujian UAT (UAT-SC-01 hingga UAT-SC-07) teruji dan divalidasi berhasil.
- [ ] Audit Lighthouse PWA menghasilkan badge PWA Installable.
- [ ] Sistem presensi siap dideploy ke lingkungan produksi/staging PT. Cahaya Anugrah Kalimantan.
