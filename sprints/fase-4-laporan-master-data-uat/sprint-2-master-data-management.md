# Sprint 4.2 - Manajemen Master Lokasi, Karyawan & Settings

- **Fase:** 4 (Laporan, Master Data & UAT)
- **Estimasi Durasi:** 3 Hari
- **Prasyarat:** Fase 1, 2, 3 selesai

---

## 1. Kebutuhan Fungsional (FR)

- `FR-MSTR-01`: **Manajemen Master Lokasi & Geofence (Super Admin)**:
  - CRUD Titik Lokasi Kantor / Proyek (`locations`).
  - Input field: Nama Lokasi, Latitude, Longitude, Radius (meter), Status Aktif (`is_active`).
  - Pratinjau interaktif Leaflet.js pada form: Admin dapat menggeser pin lokasi atau mengklik peta untuk mendapatkan koordinat desimal secara instan.
- `FR-MSTR-02`: **Manajemen Data Karyawan & Akun (Super Admin)**:
  - CRUD Karyawan (`users`).
  - Input: NIK (unik), Nama, Email (unik), Password default (Bcrypt), Role (`karyawan`, `admin`, `superadmin`), Jabatan, Departemen, No. Telepon.
  - Akun baru otomatis memiliki status `enrollment_status = 'pending'`.
  - Toggle switch status aktif (`is_active`): Menonaktifkan akun karyawan seketika jika karyawan berhenti/cuti panjang.
- `FR-MSTR-03`: **Pengaturan Kebijakan Kerja Global (Super Admin)**:
  - Manajemen konfigurasi sistem pada tabel `settings`:
    - `jam_masuk`: Jam kerja mulai (contoh: "08:00").
    - `jam_pulang`: Jam kerja selesai (contoh: "17:00").
    - `toleransi_terlambat`: Batas menit toleransi (contoh: 15 menit).
    - `jam_istirahat`: Durasi istirahat harian dalam menit (contoh: 60 menit).
    - `hari_kerja`: Daftar hari kerja aktif (contoh: "senin,selasa,rabu,kamis,jumat").
    - `max_retroaktif_izin`: Batas hari mundur pengajuan izin (contoh: 3 hari).

---

## 2. Kebutuhan Non-Fungsional (NFR)

- `NFR-SEC-01`: Akses seluruh controller master data dibatasi secara ketat hanya untuk role `superadmin` via middleware `RoleSuperadmin`.
- `NFR-DATA-01`: Validasi keunikan NIK dan Email yang ketat saat aksi pembuatan maupun pembaruan data pengguna.

---

## 3. Langkah-Langkah Dekomposisi Teknis

### Langkah 1: Controller Master Lokasi
Target: `app/Http/Controllers/Superadmin/LocationController.php`

- Method `index()`, `create()`, `store()`, `edit()`, `update()`, `destroy()`.
- Validasi format koordinat desimal regex: `latitude` (-90 s/d 90), `longitude` (-180 s/d 180), `radius_meters` (min 10 meter).

### Langkah 2: Controller Master Karyawan
Target: `app/Http/Controllers/Superadmin/UserController.php`

- Method `index()`, `create()`, `store()`, `edit()`, `update()`, `toggleActive()`.
- Password default otomatis di-hash Bcrypt jika tidak diisi custom.

### Langkah 3: Controller Pengaturan Kebijakan Sistem
Target: `app/Http/Controllers/Superadmin/SettingController.php`

- Method `index()` & `updateBatch(Request $request)`:
  ```php
  public function updateBatch(Request $request)
  {
      $validated = $request->validate([
          'settings.jam_masuk'          => 'required|date_format:H:i',
          'settings.jam_pulang'         => 'required|date_format:H:i|after:settings.jam_masuk',
          'settings.toleransi_terlambat'=> 'required|integer|min:0|max:120',
          'settings.jam_istirahat'      => 'required|integer|min:0|max:180',
          'settings.hari_kerja'         => 'required|string',
          'settings.max_retroaktif_izin'=> 'required|integer|min:0|max:30',
      ]);

      foreach ($validated['settings'] as $key => $value) {
          Setting::updateOrCreate(['key' => $key], ['value' => $value]);
      }

      return back()->with('success', 'Konfigurasi kebijakan kerja berhasil diperbarui.');
  }
  ```

---

## 4. Kriteria Penerimaan (Definition of Done)

- [ ] Super Admin dapat menambah titik lokasi baru dan koordinatnya teruji pada kalkulasi presensi geofence.
- [ ] CRUD Karyawan berhasil memvalidasi keunikan NIK/Email dan mengeset status awal `enrollment_status = 'pending'`.
- [ ] Menonaktifkan karyawan (`is_active = false`) langsung mencabut akses login karyawan tersebut.
- [ ] Perubahan jam kerja atau toleransi di settings langsung berpengaruh pada evaluasi presensi hari berikutnya.
