# Sprint 1.1 - Setup Proyek, Environment & Database Schema

- **Fase:** 1 (Fondasi Proyek & Infrastruktur)
- **Estimasi Durasi:** 3 Hari
- **Prasyarat:** PHP >= 8.2, Composer, MySQL 8.0 / MariaDB

---

## 1. Kebutuhan Fungsional (FR)

- `FR-SETUP-01`: Inisialisasi proyek Laravel 11 dengan struktur MVC standar dan dependency pendukung.
- `FR-SETUP-02`: Konfigurasi environment time-zone ke `Asia/Makassar` (WITA) dan koneksi database MySQL.
- `FR-SETUP-03`: Pembuatan 7 tabel relasional utama sesuai spesifikasi data model:
  1. `users` (Akun, jabatan, departemen, role, status aktif, status enrollment).
  2. `face_descriptors` (Vector 128-float facial descriptor relasi 1:1 dengan user).
  3. `locations` (Master lokasi kantor & proyek, koordinat latitude-longitude, radius geofence).
  4. `attendances` (Log presensi masuk/pulang, koordinat aktual, status, foto snapshot, flag auto-checkout).
  5. `leaves` (Pengajuan izin/sakit/cuti, lampiran, status persetujuan, reviewer).
  6. `settings` (Konfigurasi global jam masuk, jam pulang, toleransi, istirahat, hari kerja, retroaktif izin).
  7. `notifications` (Log notifikasi in-app untuk karyawan).
- `FR-SETUP-04`: Seeding data awal sistem (akun Super Admin default, akun HRD default, setting kebijakan jam kerja default).
- `FR-SETUP-05`: Pembuatan symbolic link storage publik (`storage:link`) untuk aksesibilitas berkas foto presensi dan lampiran surat.

---

## 2. Kebutuhan Non-Fungsional (NFR)

- `NFR-SEC-01`: Wajib menerapkan HTTPS/SSL lokal atau staging agar Web API kritis (Camera API, Geolocation API, Service Worker) diizinkan oleh peramban modern.
- `NFR-SEC-02`: Proteksi CSRF aktif pada seluruh endpoint non-API dan sanitasi input form.
- `NFR-DATA-01`: Penegakan integritas data melalui Foreign Key constraints dengan opsi `onDelete('cascade')` atau `onDelete('set null')` yang tepat.
- `NFR-DATA-02`: Unique index pada kombinasi `['user_id', 'date']` pada tabel `attendances` untuk mencegah anomali multi-record pada tanggal yang sama.

---

## 3. Langkah-Langkah Dekomposisi Teknis

### Langkah 1: Inisialisasi Proyek & Konfigurasi Dasar
1. Jalankan inisialisasi Laravel:
   ```bash
   composer create-project laravel/laravel . "11.*"
   ```
2. Konfigurasi file `.env`:
   ```env
   APP_NAME="Presensi PT CAK"
   APP_ENV=local
   APP_KEY=
   APP_DEBUG=true
   APP_TIMEZONE=Asia/Makassar
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=presensi_cak
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Generate application key: `php artisan key:generate`.
4. Atur time zone default di `config/app.php` memastikan fallback ke `Asia/Makassar`.

### Langkah 2: Pembuatan Migration 7 Tabel Utama
Target folder: `database/migrations/`

1. **Migration `users`** (`create_users_table.php`):
   ```php
   Schema::create('users', function (Blueprint $table) {
       $table->id();
       $table->string('nik')->unique();
       $table->string('name');
       $table->string('email')->unique();
       $table->string('password');
       $table->enum('role', ['karyawan', 'admin', 'superadmin'])->default('karyawan');
       $table->string('jabatan')->nullable();
       $table->string('department')->nullable();
       $table->string('no_telp')->nullable();
       $table->string('avatar')->nullable();
       $table->enum('enrollment_status', ['pending', 'enrolled'])->default('pending');
       $table->boolean('is_active')->default(true);
       $table->rememberToken();
       $table->timestamps();
   });
   ```
2. **Migration `face_descriptors`** (`create_face_descriptors_table.php`):
   ```php
   Schema::create('face_descriptors', function (Blueprint $table) {
       $table->id();
       $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
       $table->longText('descriptor_data');
       $table->string('sample_photo')->nullable();
       $table->timestamps();
   });
   ```
3. **Migration `locations`** (`create_locations_table.php`):
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
4. **Migration `attendances`** (`create_attendances_table.php`):
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
       $table->boolean('auto_checkout')->default(false);
       $table->timestamps();

       $table->unique(['user_id', 'date']);
   });
   ```
5. **Migration `leaves`** (`create_leaves_table.php`):
   ```php
   Schema::create('leaves', function (Blueprint $table) {
       $table->id();
       $table->foreignId('user_id')->constrained()->onDelete('cascade');
       $table->enum('type', ['izin', 'sakit', 'cuti']);
       $table->date('start_date');
       $table->date('end_date');
       $table->text('reason');
       $table->string('attachment_file')->nullable();
       $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
       $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
       $table->text('review_note')->nullable();
       $table->timestamps();
   });
   ```
6. **Migration `settings`** (`create_settings_table.php`):
   ```php
   Schema::create('settings', function (Blueprint $table) {
       $table->id();
       $table->string('key')->unique();
       $table->text('value');
       $table->string('description')->nullable();
       $table->timestamps();
   });
   ```
7. **Migration `notifications`** (`create_notifications_table.php`):
   ```php
   Schema::create('notifications', function (Blueprint $table) {
       $table->id();
       $table->foreignId('user_id')->constrained()->onDelete('cascade');
       $table->string('title');
       $table->text('message');
       $table->string('type')->nullable();
       $table->boolean('is_read')->default(false);
       $table->timestamps();
   });
   ```

### Langkah 3: Pembuatan Eloquent Model & Relasi
Target folder: `app/Models/`

1. `User.php`: Relasi `hasOne(FaceDescriptor::class)`, `hasMany(Attendance::class)`, `hasMany(Leave::class)`, `hasMany(Notification::class)`.
2. `FaceDescriptor.php`: Relasi `belongsTo(User::class)`. Casting `descriptor_data` => `array`.
3. `Location.php`: Scope `active()`.
4. `Attendance.php`: Relasi `belongsTo(User::class)`, `belongsTo(Location::class)`.
5. `Leave.php`: Relasi `belongsTo(User::class, 'user_id')`, `belongsTo(User::class, 'approved_by')`.
6. `Setting.php`: Helper method statis `getValue(string $key, $default = null)`.
7. `Notification.php`: Relasi `belongsTo(User::class)`.

### Langkah 4: Database Seeding & Storage Setup
1. Buat seeder di `database/seeders/DatabaseSeeder.php`:
   - Akun Super Admin: NIK `SA001`, Email `superadmin@ptcak.com`, Password `password`, Role `superadmin`.
   - Akun Admin HRD: NIK `ADM001`, Email `hrd@ptcak.com`, Password `password`, Role `admin`.
   - Master Lokasi Awal: "Kantor Pusat PT. CAK", Lat: `-0.1333000`, Long: `117.4833000`, Radius: `100` meter.
   - Settings Default:
     - `jam_masuk` => `08:00`
     - `jam_pulang` => `17:00`
     - `toleransi_terlambat` => `15`
     - `jam_istirahat` => `60`
     - `hari_kerja` => `senin,selasa,rabu,kamis,jumat`
     - `max_retroaktif_izin` => `3`
2. Jalankan perintah eksekusi:
   ```bash
   php artisan migrate:fresh --seed
   php artisan storage:link
   ```

---

## 4. Kriteria Penerimaan (Definition of Done)

- [ ] Seluruh migration (7 file) dieksekusi tanpa error pada MySQL database.
- [ ] Database seeder berhasil menginjeksi 2 akun awal, 1 lokasi kantor, dan 6 konfigurasi settings.
- [ ] Foreign keys dan unique index `[user_id, date]` terverifikasi di schema database.
- [ ] Folder link `public/storage` berhasil mengarah ke `storage/app/public`.
- [ ] Perintah `php artisan tinker` dapat mengambil konfigurasi: `Setting::getValue('jam_masuk')` menghasilkan `'08:00'`.
