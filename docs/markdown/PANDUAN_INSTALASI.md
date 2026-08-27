# PANDUAN INSTALASI & DEPLOYMENT SISTEM
## Sistem Informasi Presensi PWA PT. Cahaya Anugrah Kalimantan

- **Dokumen Teknis:** IG-CAK-2026
- **Edisi / Versi:** v1.0 (Agustus 2026)
- **Format Cetak PDF:** [Panduan_Instalasi_Presensi_PWA_PT_CAK.pdf](file:///d:/PROJECT/Presensi(PWA)/Panduan_Instalasi_Presensi_PWA_PT_CAK.pdf)

---

## 1. Prasyarat Perangkat Keras & Perangkat Lunak

| Komponen | Spesifikasi Minimum | Catatan & Keterangan |
|---|---|---|
| **Sistem Operasi** | Linux Ubuntu 22.04 LTS / Debian 12 / Windows 10/11 | Direkomendasikan Linux untuk lingkungan server produksi. |
| **PHP Engine** | PHP >= 8.2 (CLI & FPM) | Ekstensi wajib: `openssl, pdo, pdo_mysql, mbstring, tokenizer, xml, ctype, json, bcmath, fileinfo, gd/imagick, zip`. |
| **Basis Data** | MySQL >= 8.0 atau MariaDB >= 10.4 | Mendukung tipe data JSON dan pengindeksan desimal geospasial. |
| **Package Manager** | Composer v2.x+ | Untuk mengelola dependensi PHP framework Laravel 11. |
| **Python (Opsional)** | Python >= 3.10 (Uvicorn, FastAPI, HTTPX) | Digunakan untuk menjalankan local ASGI reverse proxy (opsional). |
| **Protokol Keamanan** | HTTPS / SSL (TLS 1.2+) | **Wajib** untuk akses Geolocation API dan Camera WebCam di peramban. |

---

## 2. Panduan Instalasi Lingkungan Lokal (Local Development)

### Langkah 1: Kloning Repositori & Masuk ke Direktori Proyek
```bash
git clone https://github.com/username/presensi-pwa-ptcak.git
cd presensi-pwa-ptcak
```

### Langkah 2: Instal Dependensi Composer
```bash
composer install
```

### Langkah 3: Konfigurasi Berkas Lingkungan (.env)
Salin berkas template lingkungan dan generate application key:
```bash
cp .env.example .env
php artisan key:generate
```
Sesuaikan konfigurasi database dan zona waktu Kalimantan Timur (WITA):
```env
APP_NAME="Presensi PT. CAK"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Makassar

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=presensi_cak
DB_USERNAME=root
DB_PASSWORD=
```

### Langkah 4: Migrasi Basis Data & Pembuatan Symlink Storage
Jalankan migrasi tabel basis data dan data awalan (seeder akun & lokasi):
```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

### Langkah 5: Menjalankan Server Lokal
Anda dapat menjalankan aplikasi menggunakan salah satu opsi berikut:

- **Opsi A: Menggunakan Uvicorn ASGI Runner (Rekomendasi)**
  ```bash
  python serve.py
  # atau
  uvicorn server:app --host 127.0.0.1 --port 8000
  # atau klik ganda berkas start.bat pada Windows
  ```
- **Opsi B: Menggunakan PHP Built-in Server**
  ```bash
  php artisan serve --port=8000
  ```

Akses aplikasi melalui peramban pada alamat: `http://localhost:8000` atau `http://127.0.0.1:8000`.

---

## 3. Akun Bawaan Sistem (Default Credentials)

| Peran (Role) | NIK | Email Login | Password | Status Biometrik |
|---|---|---|---|---|
| **Super Admin** | `SA001` | `superadmin@ptcak.com` | `password` | Enrolled |
| **HRD / Admin** | `ADM001` | `admin@ptcak.com` | `password` | Enrolled |
| **Karyawan** | `KAR001` | `karyawan@ptcak.com` | `password` | Enrolled |
| **Karyawan Baru (Pending)** | `KAR002` | `budi@ptcak.com` | `ptcak123` | Pending Enrollment |

---

## 4. Panduan Deployment Server Produksi (Production Server)

### A. Konfigurasi Izin Hak Akses Direktori (Linux Permissions)
```bash
sudo chown -R www-data:www-data /var/www/presensi-pwa
sudo chmod -R 775 /var/www/presensi-pwa/storage
sudo chmod -R 775 /var/www/presensi-pwa/bootstrap/cache
```

### B. Optimasi Caching & Sanitasi Produksi
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### C. Konfigurasi Virtual Host Nginx (Server Block)
```nginx
server {
    listen 80;
    server_name presensi.ptcak.co.id;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name presensi.ptcak.co.id;
    root /var/www/presensi-pwa/public;

    ssl_certificate /etc/letsencrypt/live/presensi.ptcak.co.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/presensi.ptcak.co.id/privkey.pem;

    index index.php index.html;
    charset utf-8;
    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### D. Konfigurasi Background Task Schedulers (Linux Crontab)
Tambahkan baris scheduler berikut ke dalam crontab server (`crontab -e`):
```bash
* * * * * cd /var/www/presensi-pwa && php artisan schedule:run >> /dev/null 2>&1
```
Scheduler ini akan otomatis mengeksekusi penutupan *Auto-Checkout* (23:59 WITA) dan pencatatan *Auto-Alpha* (20:00 WITA) setiap hari.

---

## 5. Penanganan Masalah Instalasi (Troubleshooting)

| Gejala Masalah | Solusi Penanganan |
|---|---|
| **HTTP 500 Internal Server Error / Permission Denied** | Jalankan `chmod -R 775 storage bootstrap/cache` dan pastikan kepemilikan user web server sesuai (`chown -R www-data:www-data storage`). |
| **Aset Model Face Recognition 404 Not Found** | Pastikan berkas bobot model AI tersimpan lengkap di direktori `public/models/` (model ssd_mobilenetv1, face_landmark_68, face_recognition). |
| **Kamera atau GPS Diblokir Peramban** | Pastikan website diakses menggunakan protokol **HTTPS (SSL aktif)**. Peramban mobile memblokir WebCam dan Geolocation API pada protokol HTTP non-aman. |
| **Foto Presensi Snapshot Tidak Muncul** | Pastikan perintah symlink storage publik telah dieksekusi melalui `php artisan storage:link`. |
