---
name: presensi-pwa-implementation
description: >-
  Skill untuk mengimplementasikan Sistem Presensi PWA PT. Cahaya Anugrah Kalimantan.
  Gunakan skill ini ketika user meminta untuk membangun, mengembangkan, atau melanjutkan
  implementasi fitur pada proyek Presensi PWA (Laravel + face-api.js + Geofencing).
  Skill ini mencakup panduan arsitektur, database schema, business flow per role,
  konvensi kode, dan checklist implementasi per fase.
---

# Presensi PWA Implementation Skill

Panduan lengkap untuk agent mengimplementasikan **Sistem Presensi Karyawan PWA** dengan **Face Recognition** dan **Geofencing** untuk PT. Cahaya Anugrah Kalimantan.

> **WAJIB DIBACA SEBELUM CODING:**
> - Roadmap lengkap: [ROADMAP.md](../../ROADMAP.md)
> - PRD dokumen: [PRD_Presensi_PWA_PT_CAK.pdf](../../PRD_Presensi_PWA_PT_CAK.pdf)
> - Business Flow: [Alur_Bisnis_dan_Data_Flow_Presensi.pdf](../../Alur_Bisnis_dan_Data_Flow_Presensi.pdf)
> - Referensi detail arsitektur & schema: [references/architecture.md](./references/architecture.md)
> - Referensi alur bisnis per role: [references/business_flows.md](./references/business_flows.md)
> - Referensi Material Design 3 (M3): [references/design_system_m3.md](./references/design_system_m3.md) / [DESIGN.md](../../DESIGN.md)
> - Rencana Sprint & Dekomposisi: [sprints/README.md](../../sprints/README.md)

---

## 1. Konteks Proyek

- **Klien:** PT. Cahaya Anugrah Kalimantan
- **Tipe Aplikasi:** Laravel Monolith + PWA (Progressive Web App)
- **Tujuan:** Sistem absensi anti-titip absen (buddy punching) & anti-fake GPS, tanpa perlu install APK
- **3 Role Utama:**
  - `karyawan` — Mobile PWA (presensi + izin)
  - `admin` — Desktop Dashboard HRD (monitoring + enrollment + approval + laporan)
  - `superadmin` — System Control (master data + geofence + settings)

---

## 2. Arsitektur & Tech Stack

| Layer | Teknologi | Catatan |
|---|---|---|
| Backend | **Laravel 11** (PHP 8.2+) | Monolith MVC, Eloquent ORM |
| Frontend | **Blade** + **Bootstrap 5** / **Tailwind CSS** + **Alpine.js** | Server-side rendering |
| PWA | `manifest.json` + `service-worker.js` | Caching aset, Add to Home Screen |
| Face Recognition | **face-api.js** (TensorFlow.js) | 100% client-side, 128-float descriptor |
| Maps & Geofencing | **Leaflet.js** + **OpenStreetMap** | Gratis, tanpa API key |
| Geolocation | HTML5 `navigator.geolocation` | Validasi GANDA: client (UX) + server (security) |
| Database | **MySQL 8.0** / MariaDB | 7 tabel utama |
| Export | **PhpSpreadsheet** (.xlsx) + **Laravel-DomPDF** (.pdf) | Untuk rekapitulasi payroll |
| Notifikasi | In-app polling (30 detik) | Tabel `notifications` |

---

## 3. Konvensi Kode Laravel

Ikuti konvensi ini secara konsisten:

### Naming Conventions
- **Model:** Singular PascalCase → `User`, `Attendance`, `FaceDescriptor`, `Location`, `Leave`, `Setting`, `Notification`
- **Migration:** `create_<table>_table` → `create_attendances_table`
- **Controller:** `<Model>Controller` → `AttendanceController`
- **Service:** `<Domain>Service` → `NotificationService`, `HaversineService`, `AttendanceService`
- **Middleware:** `Role<Name>` → `RoleKaryawan`, `RoleAdmin`, `RoleSuperadmin`, `CheckActive`
- **Route prefix:**
  - Karyawan: `/karyawan/*`
  - Admin/HRD: `/admin/*`
  - Super Admin: `/superadmin/*`
  - API (notifikasi): `/api/*`

### Folder Structure (Laravel Standard)
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/LoginController.php
│   │   ├── Api/
│   │   │   └── NotificationController.php      # Polling notifikasi
│   │   ├── Karyawan/
│   │   │   ├── DashboardController.php
│   │   │   ├── PresensiController.php           # Masuk + Pulang
│   │   │   ├── LeaveController.php              # Submit + Edit + Cancel
│   │   │   ├── RiwayatController.php
│   │   │   └── ProfileController.php
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── MonitoringController.php
│   │   │   ├── EnrollmentController.php         # Enrollment + Re-enrollment
│   │   │   ├── LeaveApprovalController.php
│   │   │   └── ReportController.php
│   │   └── Superadmin/
│   │       ├── DashboardController.php
│   │       ├── LocationController.php
│   │       ├── UserController.php
│   │       └── SettingController.php
│   └── Middleware/
│       ├── RoleKaryawan.php
│       ├── RoleAdmin.php
│       ├── RoleSuperadmin.php
│       └── CheckActive.php                      # Cek is_active setiap request
├── Models/
│   ├── User.php
│   ├── Attendance.php
│   ├── FaceDescriptor.php
│   ├── Location.php
│   ├── Leave.php
│   ├── Setting.php
│   └── Notification.php
├── Services/
│   ├── HaversineService.php                     # Kalkulasi jarak GPS
│   ├── AttendanceService.php                     # Logic presensi masuk/pulang/status
│   └── NotificationService.php                  # Kirim notifikasi in-app
├── Console/
│   └── Commands/
│       └── AutoCheckoutAndAlpha.php             # Scheduler harian jam 23:00
resources/
├── views/
│   ├── layouts/
│   │   ├── admin.blade.php
│   │   └── pwa.blade.php
│   ├── auth/
│   │   └── login.blade.php
│   ├── karyawan/
│   │   ├── dashboard.blade.php
│   │   ├── presensi.blade.php
│   │   ├── riwayat.blade.php
│   │   ├── izin/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── edit.blade.php                   # Edit izin pending
│   │   └── profile.blade.php
│   ├── admin/
│   │   ├── dashboard.blade.php
│   │   ├── monitoring.blade.php
│   │   ├── enrollment.blade.php
│   │   ├── leaves/
│   │   │   └── index.blade.php
│   │   └── reports/
│   │       └── index.blade.php
│   └── superadmin/
│       ├── dashboard.blade.php
│       ├── locations/
│       │   ├── index.blade.php
│       │   └── create.blade.php
│       ├── users/
│       │   ├── index.blade.php
│       │   └── create.blade.php
│       └── settings.blade.php
public/
├── manifest.json
├── service-worker.js
├── js/
│   ├── face-api.min.js
│   ├── presensi.js                              # GPS + Face matching logic
│   ├── enrollment.js                            # Face enrollment logic
│   ├── leaflet-map.js                           # Peta interaktif
│   └── notifications.js                         # Polling notifikasi
├── models/                                      # face-api.js model weights
│   ├── tiny_face_detector_model-weights_manifest.json
│   ├── face_landmark_68_model-weights_manifest.json
│   └── face_recognition_model-weights_manifest.json
└── icons/
    ├── icon-192x192.png
    └── icon-512x512.png
storage/
└── app/public/
    └── attendances/                             # Foto presensi
        └── {user_id}/{date}/
            ├── photo_in.jpg
            └── photo_out.jpg
```

---

## 4. Database Schema Detail

Selalu gunakan schema ini sebagai acuan saat membuat migration:

### Tabel `users`
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('nik')->unique();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');                    // Bcrypt
    $table->enum('role', ['karyawan', 'admin', 'superadmin'])->default('karyawan');
    $table->string('jabatan')->nullable();
    $table->string('department')->nullable();      // Untuk filter dashboard
    $table->string('no_telp')->nullable();
    $table->string('avatar')->nullable();
    $table->enum('enrollment_status', ['pending', 'enrolled'])->default('pending');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### Tabel `face_descriptors`
```php
Schema::create('face_descriptors', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->longText('descriptor_data');           // JSON: mean vector 128-float
    $table->string('sample_photo')->nullable();
    $table->timestamps();
    // Relasi 1:1 — satu user = satu record mean descriptor
});
```

### Tabel `locations`
```php
Schema::create('locations', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->decimal('latitude', 10, 7);
    $table->decimal('longitude', 10, 7);
    $table->integer('radius_meters')->default(50);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### Tabel `attendances`
```php
Schema::create('attendances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('location_id')->nullable()->constrained()->onDelete('set null');
    $table->date('date');
    $table->time('time_in')->nullable();
    $table->time('time_out')->nullable();
    $table->decimal('lat_in', 10, 7)->nullable();
    $table->decimal('long_in', 10, 7)->nullable();
    $table->decimal('lat_out', 10, 7)->nullable();
    $table->decimal('long_out', 10, 7)->nullable();
    $table->string('photo_in')->nullable();
    $table->string('photo_out')->nullable();
    $table->enum('status', ['tepat_waktu', 'terlambat', 'izin', 'sakit', 'cuti', 'alpha'])->default('alpha');
    $table->decimal('distance_meters', 8, 2)->nullable();
    $table->boolean('auto_checkout')->default(false);  // True jika lupa pulang
    $table->timestamps();

    // Unique constraint: satu karyawan hanya 1 record per hari
    $table->unique(['user_id', 'date']);
});
```

### Tabel `leaves`
```php
Schema::create('leaves', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('type', ['izin', 'sakit', 'cuti']);
    $table->date('start_date');
    $table->date('end_date');
    $table->text('reason');
    $table->string('attachment_file')->nullable();   // Max 2MB
    $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
    $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
    $table->text('review_note')->nullable();
    $table->timestamps();
});
```

### Tabel `settings`
```php
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->string('value');
    $table->string('description')->nullable();
    $table->timestamps();
});
// Default seeds:
// jam_masuk          = 08:00
// jam_pulang         = 17:00
// toleransi_terlambat = 15  (menit)
// jam_istirahat      = 60  (menit)
// hari_kerja         = senin,selasa,rabu,kamis,jumat
// max_retroaktif_izin = 3  (hari ke belakang)
```

### Tabel `notifications`
```php
Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('title');
    $table->text('message');
    $table->string('type')->nullable();            // leave_approved, leave_rejected, etc.
    $table->boolean('is_read')->default(false);
    $table->timestamps();
});
```

---

## 5. Implementasi Per Fase — Panduan Step-by-Step

### Fase 1: Fondasi (Minggu 1-2)

**Step 1 — Inisialisasi Laravel:**
```bash
composer create-project laravel/laravel . "11.*"
```
Konfigurasi `.env`:
- `APP_TIMEZONE=Asia/Makassar`
- `DB_DATABASE=presensi_cak`

**Step 2 — Migration & Seeder:**
Buat semua 7 migration sesuai schema di atas, lalu jalankan:
```bash
php artisan migrate --seed
php artisan storage:link
```
Seeder harus membuat:
- 1 superadmin (NIK: SA001, password: `password`)
- 1 admin HRD (NIK: ADM001, password: `password`)
- 6 settings default

**Step 3 — Auth & RBAC:**
- Login via NIK + Password (Bcrypt)
- **PENTING:** Cek `is_active` saat login DAN di middleware `CheckActive` setiap request
- 3 middleware role: `RoleKaryawan`, `RoleAdmin`, `RoleSuperadmin`
- Setiap middleware cek `auth()->user()->role`

**Step 4 — Layout & PWA:**
- Admin layout: sidebar navigasi + content area
- PWA layout: mobile-first, bottom navigation
- `manifest.json` di `public/`
- `service-worker.js` untuk caching assets

**Step 5 — Notification System:**
- Model + Migration `notifications`
- `NotificationService::send($userId, $title, $message, $type)`
- API endpoint: `GET /api/notifications`, `POST /api/notifications/{id}/read`
- Frontend: polling 30 detik + badge counter

### Fase 2: Face Recognition & Geofencing (Minggu 3-4)

**Haversine Service:**
```php
// app/Services/HaversineService.php
class HaversineService
{
    public static function distance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * asin(sqrt($a));
        return $earthRadius * $c;
    }
}
```

**Face Recognition Flow (Client-Side JS):**
```javascript
// Langkah: Load models → Detect face → Extract descriptor → Compare
// Threshold: Euclidean distance ≤ 0.50 = MATCH
// Jika match → kirim payload {photo_base64, lat, long, timestamp} ke server
```

**Presensi Controller Logic (PENTING — semua validasi ini WAJIB):**
```php
// PresensiController@store
public function store(Request $request) {
    $user = auth()->user();

    // 1. Cek enrollment status
    if ($user->enrollment_status !== 'enrolled') {
        return back()->with('error', 'Akun belum terdaftar biometrik. Hubungi HRD.');
    }

    // 2. Cek presensi ganda
    $existing = Attendance::where('user_id', $user->id)->where('date', today())->first();
    if ($existing && $existing->time_in && $existing->time_out) {
        return back()->with('error', 'Anda sudah presensi hari ini.');
    }
    if ($existing && $existing->time_in && !$existing->time_out) {
        // Redirect ke flow presensi pulang
        return redirect()->route('karyawan.presensi.pulang');
    }

    // 3. Re-validasi Haversine di server (JANGAN percaya client)
    $locations = Location::where('is_active', true)->get();
    $nearestDistance = INF;
    $nearestLocation = null;
    foreach ($locations as $loc) {
        $dist = HaversineService::distance($request->lat, $request->long, $loc->latitude, $loc->longitude);
        if ($dist < $nearestDistance) {
            $nearestDistance = $dist;
            $nearestLocation = $loc;
        }
    }
    if ($nearestDistance > $nearestLocation->radius_meters) {
        return back()->with('error', "Anda di luar area presensi (jarak: {$nearestDistance}m)");
    }

    // 4. Simpan foto (compress JPEG 70%)
    $photoPath = "attendances/{$user->id}/" . today()->format('Y-m-d') . "/photo_in.jpg";
    // ... decode base64, compress, store ...

    // 5. Hitung status dari settings
    $jamMasuk = Setting::getValue('jam_masuk');         // "08:00"
    $toleransi = Setting::getValue('toleransi_terlambat'); // "15" menit
    $batasTerlambat = Carbon::parse($jamMasuk)->addMinutes($toleransi);
    $status = now()->gt($batasTerlambat) ? 'terlambat' : 'tepat_waktu';

    // 6. Insert record
    Attendance::create([...]);
}
```

**Auto-Checkout & Alpha Scheduler:**
```php
// app/Console/Commands/AutoCheckoutAndAlpha.php
// Jalankan setiap hari jam 23:00 WITA
// 1. Auto-checkout: update time_out + set auto_checkout=true
// 2. Generate alpha: buat record untuk karyawan tanpa attendance & tanpa leave approved
```

### Fase 3: Dashboard & Perizinan (Minggu 5-6)

**Approval Izin — Generate Attendance Records:**
```php
// Saat HRD approve izin multi-hari:
$leave = Leave::find($id);
$leave->update(['status' => 'approved', 'approved_by' => auth()->id()]);

$hariKerja = explode(',', Setting::getValue('hari_kerja'));
$current = Carbon::parse($leave->start_date);
$end = Carbon::parse($leave->end_date);

while ($current->lte($end)) {
    $dayName = strtolower($current->locale('id')->dayName); // senin, selasa, ...
    if (in_array($dayName, $hariKerja)) {
        Attendance::updateOrCreate(
            ['user_id' => $leave->user_id, 'date' => $current->toDateString()],
            ['status' => $leave->type] // izin/sakit/cuti
        );
    }
    $current->addDay();
}

// Kirim notifikasi
NotificationService::send($leave->user_id, 'Izin Disetujui', '...', 'leave_approved');
```

**Leave Edit & Cancel (Karyawan):**
```php
// Edit: hanya jika status === 'pending'
// Cancel: update status ke 'cancelled', hanya jika status === 'pending'
// Setelah approved/rejected → TIDAK bisa diubah
```

### Fase 4: Export & Master Data (Minggu 7-8)

**Kalkulasi Jam Kerja untuk Export:**
```php
// Formula: (time_out - time_in) - jam_istirahat
// Jika time_out NULL → gunakan jam_pulang dari settings
// Jika status izin/sakit/cuti (approved) → jam kerja penuh = (jam_pulang - jam_masuk) - jam_istirahat
// Jika status alpha → 0 jam
```

---

## 6. Critical Rules & Anti-Patterns

### WAJIB DIPATUHI:
1. **SELALU re-validasi koordinat di server** (Haversine ganda: client untuk UX, server untuk security)
2. **SELALU cek `enrollment_status`** sebelum membuka halaman presensi
3. **SELALU cek presensi ganda** (`unique(['user_id', 'date'])` constraint + logic check)
4. **SELALU cek `is_active`** di middleware setiap request (bukan hanya login)
5. **Face matching di client, tapi simpan foto di server** — untuk audit trail
6. **CSRF token wajib** di setiap form dan AJAX request presensi
7. **Password harus Bcrypt** — jangan pernah simpan plaintext
8. **Upload file lampiran max 2MB** — validasi di frontend DAN backend
9. **Timezone konsisten WITA** (`Asia/Makassar`) — di `.env`, database, dan tampilan
10. **Foto presensi** simpan di `storage/app/public/attendances/{user_id}/{date}/` — compress JPEG 70%
11. **Relasi face_descriptors 1:1** — satu user = satu record mean vector
12. **Izin hanya bisa edit/cancel saat status `pending`** — setelah approved/rejected = final
13. **Notifikasi via tabel `notifications`** — polling 30 detik, bukan push notification

### JANGAN LAKUKAN:
- ❌ Jangan proses face recognition di server — gunakan face-api.js di browser
- ❌ Jangan gunakan Google Maps API — gunakan Leaflet.js + OpenStreetMap (gratis)
- ❌ Jangan buat SPA/API-only — ini monolith Laravel Blade
- ❌ Jangan skip validasi geofence di server meskipun sudah validasi di client
- ❌ Jangan hardcode jam kerja — ambil dari tabel `settings`
- ❌ Jangan biarkan karyawan nonaktif bisa login atau akses sistem
- ❌ Jangan izinkan presensi ganda — cek record existing sebelum insert
- ❌ Jangan izinkan edit/cancel izin yang sudah approved/rejected

---

## 7. Verifikasi & Testing Checklist

Setelah selesai implementasi setiap fase, verifikasi:

### Fase 1 Checklist:
- [ ] `php artisan migrate` berjalan tanpa error (7 tabel tercipta)
- [ ] Login dengan NIK + password berhasil
- [ ] Login karyawan nonaktif **ditolak** dengan pesan jelas
- [ ] Role redirect benar (karyawan → PWA, admin → dashboard, superadmin → system)
- [ ] PWA manifest terdeteksi browser (prompt install muncul)
- [ ] Service worker registered
- [ ] Notification polling berjalan + badge counter tampil

### Fase 2 Checklist:
- [ ] face-api.js models ter-load di browser tanpa error console
- [ ] Kamera aktif dan bounding box hijau muncul saat wajah terdeteksi
- [ ] Kamera ditolak → graceful error + instruksi + tombol "Coba Lagi"
- [ ] GPS ditolak → graceful error + instruksi + tombol "Coba Lagi"
- [ ] Enrollment: descriptor tersimpan + `enrollment_status` berubah ke `enrolled`
- [ ] Re-enrollment: descriptor lama di-replace
- [ ] Karyawan belum enrolled → pesan "Hubungi HRD" saat buka presensi
- [ ] Geofencing: tombol presensi disabled saat di luar radius
- [ ] Presensi masuk: record masuk ke tabel `attendances` dengan semua field
- [ ] Presensi pulang: update `time_out`, `lat_out`, `long_out`, `photo_out`
- [ ] Presensi ganda: redirect ke flow yang benar, tidak buat record baru
- [ ] Foto tersimpan di `storage/app/public/attendances/...` dan accessible via URL
- [ ] Scheduler auto-checkout & alpha berjalan di jam 23:00

### Fase 3 Checklist:
- [ ] Dashboard menampilkan statistik akurat (termasuk alpha dari scheduler)
- [ ] Filter departemen berfungsi
- [ ] Badge "X karyawan belum enrollment" tampil di dashboard
- [ ] Peta Leaflet menampilkan marker + circle geofence
- [ ] Pengajuan izin tersimpan dengan status pending
- [ ] Edit izin pending berhasil
- [ ] Cancel izin pending → status berubah ke `cancelled`
- [ ] Edit/cancel izin approved/rejected → ditolak
- [ ] Approval izin multi-hari → attendance records ter-generate (skip weekend)
- [ ] Notifikasi terkirim ke karyawan saat izin di-approve/reject

### Fase 4 Checklist:
- [ ] Export Excel menghasilkan file .xlsx yang bisa dibuka
- [ ] Export PDF menghasilkan file .pdf yang bisa dibuka
- [ ] Kalkulasi jam kerja benar (termasuk pengurangan istirahat)
- [ ] Record auto_checkout ditandai di laporan
- [ ] CRUD lokasi bekerja dengan preview peta
- [ ] CRUD karyawan bekerja dengan validasi unik NIK/Email + field department
- [ ] Karyawan baru → `enrollment_status = pending`
- [ ] Settings jam kerja, istirahat, hari kerja, retroaktif izin bisa diubah
- [ ] Settings berpengaruh pada kalkulasi status presensi
