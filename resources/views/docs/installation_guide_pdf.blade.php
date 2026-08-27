<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panduan Instalasi & Deployment - PT. CAK</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.4cm 1.4cm 1.4cm 1.4cm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1E293B;
            line-height: 1.4;
            font-size: 9.5px;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #0D47A1;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .company-title {
            font-size: 14px;
            font-weight: bold;
            color: #0D47A1;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .company-sub {
            font-size: 8px;
            color: #475569;
            margin-top: 1px;
        }

        .doc-title {
            font-size: 13px;
            font-weight: bold;
            color: #0D47A1;
            text-align: center;
            text-transform: uppercase;
            margin: 8px 0 2px 0;
        }

        .doc-subtitle {
            font-size: 9px;
            color: #475569;
            text-align: center;
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 10.5px;
            font-weight: bold;
            color: #0D47A1;
            border-bottom: 1px solid #CBD5E1;
            padding-bottom: 3px;
            margin-top: 12px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .sub-section-title {
            font-size: 9.5px;
            font-weight: bold;
            color: #1E3A8A;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        .guide-box {
            border: 1px solid #E2E8F0;
            border-left: 3.5px solid #0D47A1;
            background-color: #F8FAFC;
            padding: 7px 9px;
            margin-bottom: 8px;
            border-radius: 4px;
            page-break-inside: avoid;
        }

        .guide-box-title {
            font-size: 9.5px;
            font-weight: bold;
            color: #0D47A1;
            margin-bottom: 3px;
        }

        .note-box {
            border: 1px solid #BAE6FD;
            border-left: 3.5px solid #0284C7;
            background-color: #F0F9FF;
            padding: 6px 8px;
            margin: 6px 0;
            border-radius: 4px;
            font-size: 8.5px;
            color: #0369A1;
            page-break-inside: avoid;
        }

        .warn-box {
            border: 1px solid #FED7AA;
            border-left: 3.5px solid #EA580C;
            background-color: #FFF7ED;
            padding: 6px 8px;
            margin: 6px 0;
            border-radius: 4px;
            font-size: 8.5px;
            color: #9A3412;
            page-break-inside: avoid;
        }

        .step-num {
            display: inline-block;
            width: 15px;
            height: 15px;
            background-color: #0D47A1;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 8px;
            text-align: center;
            line-height: 15px;
            border-radius: 50%;
            margin-right: 4px;
        }

        table.grid-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
            font-size: 9px;
        }

        table.grid-table th {
            background-color: #0D47A1;
            color: #FFFFFF;
            font-weight: bold;
            padding: 4px 6px;
            text-align: left;
            border: 1px solid #0D47A1;
        }

        table.grid-table td {
            padding: 4px 6px;
            border: 1px solid #CBD5E1;
            vertical-align: top;
        }

        table.grid-table tr:nth-child(even) td {
            background-color: #F8FAFC;
        }

        pre.code-block {
            background-color: #1E293B;
            color: #F8FAFC;
            padding: 6px 8px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 8px;
            line-height: 1.35;
            border-radius: 4px;
            margin: 4px 0;
            white-space: pre-wrap;
            word-break: break-all;
        }

        ul, ol {
            margin: 3px 0 6px 16px;
            padding: 0;
        }

        li {
            margin-bottom: 2px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="company-title">PT. CAHAYA ANUGRAH KALIMANTAN</div>
                <div class="company-sub">Engineering, Heavy Equipment & Mining Support Services</div>
                <div class="company-sub">Jl. Mulawarman No. 45, Bontang, Kalimantan Timur 75311</div>
            </td>
            <td style="width: 30%; text-align: right; vertical-align: top;">
                <div style="font-size: 8px; color: #475569;">Dokumen Teknis: <b>IG-CAK-2026</b></div>
                <div style="font-size: 8px; color: #475569;">Edisi / Versi: <b>v1.0 (Agustus 2026)</b></div>
                <div style="font-size: 8px; color: #475569;">Klasifikasi: <b>Dokumentasi Teknis TI</b></div>
            </td>
        </tr>
    </table>

    <!-- JUDUL PANDUAN -->
    <div class="doc-title">Panduan Instalasi & Deployment Sistem</div>
    <div class="doc-subtitle">Sistem Informasi Presensi PWA PT. Cahaya Anugrah Kalimantan (Laravel 11 + face-api.js)</div>

    <!-- 1. PRASYARAT SISTEM -->
    <div class="section-title">1. Prasyarat Perangkat Keras & Perangkat Lunak</div>
    <p>
        Sebelum memulai proses instalasi, pastikan lingkungan server lokal (development) maupun server produksi (production) telah memenuhi spesifikasi minimum berikut:
    </p>

    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 25%;">Komponen</th>
                <th style="width: 35%;">Spesifikasi Minimum</th>
                <th style="width: 40%;">Catatan & Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><b>Sistem Operasi</b></td>
                <td>Linux Ubuntu 22.04 LTS / Debian 12 / Windows 10/11</td>
                <td>Direkomendasikan Linux untuk lingkungan server produksi.</td>
            </tr>
            <tr>
                <td><b>PHP Engine</b></td>
                <td>PHP >= 8.2 (CLI & FPM)</td>
                <td>Ekstensi wajib: <code>openssl, pdo, pdo_mysql, mbstring, tokenizer, xml, ctype, json, bcmath, fileinfo, gd/imagick, zip</code>.</td>
            </tr>
            <tr>
                <td><b>Basis Data</b></td>
                <td>MySQL >= 8.0 atau MariaDB >= 10.4</td>
                <td>Mendukung tipe data JSON dan pengindeksan desimal geospasial.</td>
            </tr>
            <tr>
                <td><b>Package Manager</b></td>
                <td>Composer v2.x+</td>
                <td>Untuk mengelola dependensi PHP framework Laravel 11.</td>
            </tr>
            <tr>
                <td><b>Python (Opsional)</b></td>
                <td>Python >= 3.10 (Uvicorn, FastAPI, HTTPX)</td>
                <td>Digunakan untuk menjalankan local ASGI reverse proxy (opsional).</td>
            </tr>
            <tr>
                <td><b>Protokol Keamanan</b></td>
                <td>HTTPS / SSL (TLS 1.2+)</td>
                <td><b>Wajib</b> untuk akses Geolocation API dan Camera WebCam di peramban.</td>
            </tr>
        </tbody>
    </table>

    <!-- 2. INSTALASI LOKAL -->
    <div class="section-title">2. Panduan Instalasi Lingkungan Lokal (Local Development)</div>

    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">1</span> Kloning Repositori & Masuk ke Direktori Proyek</div>
        <pre class="code-block">git clone https://github.com/username/presensi-pwa-ptcak.git
cd presensi-pwa-ptcak</pre>
    </div>

    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">2</span> Instal Dependensi Composer</div>
        <pre class="code-block">composer install</pre>
    </div>

    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">3</span> Konfigurasi Berkas Lingkungan (.env)</div>
        <div>Salin berkas template lingkungan dan generate application key:</div>
        <pre class="code-block">cp .env.example .env
php artisan key:generate</pre>
        <div style="margin-top: 4px;">Sesuaikan konfigurasi database dan zona waktu Kalimantan Timur (WITA):</div>
        <pre class="code-block">APP_NAME="Presensi PT. CAK"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Makassar

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=presensi_cak
DB_USERNAME=root
DB_PASSWORD=</pre>
    </div>

    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">4</span> Migrasi Basis Data & Pembuatan Symlink Storage</div>
        <div>Jalankan migrasi tabel basis data dan data awalan (seeder akun & lokasi):</div>
        <pre class="code-block">php artisan migrate:fresh --seed
php artisan storage:link</pre>
    </div>

    <div class="page-break"></div>

    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">5</span> Menjalankan Server Lokal</div>
        <div>Anda dapat menjalankan aplikasi menggunakan salah satu opsi berikut:</div>
        <ul>
            <li><b>Opsi A: Menggunakan Uvicorn ASGI Runner</b>
                <pre class="code-block">python serve.py
# atau
uvicorn server:app --host 127.0.0.1 --port 8000
# atau klik ganda berkas start.bat pada Windows</pre>
            </li>
            <li><b>Opsi B: Menggunakan PHP Built-in Server</b>
                <pre class="code-block">php artisan serve --port=8000</pre>
            </li>
        </ul>
        <div>Akses aplikasi melalui peramban pada alamat: <code>http://localhost:8000</code> atau <code>http://127.0.0.1:8000</code>.</div>
    </div>

    <!-- 3. AKUN DEFAULT -->
    <div class="section-title">3. Akun Bawaan Sistem (Default Credentials)</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 20%;">Peran (Role)</th>
                <th style="width: 20%;">NIK</th>
                <th style="width: 30%;">Email Login</th>
                <th style="width: 15%;">Password</th>
                <th style="width: 15%;">Status Biometrik</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><b>Super Admin</b></td>
                <td><code>SA001</code></td>
                <td><code>superadmin@ptcak.com</code></td>
                <td><code>password</code></td>
                <td>Enrolled</td>
            </tr>
            <tr>
                <td><b>HRD / Admin</b></td>
                <td><code>ADM001</code></td>
                <td><code>admin@ptcak.com</code></td>
                <td><code>password</code></td>
                <td>Enrolled</td>
            </tr>
            <tr>
                <td><b>Karyawan</b></td>
                <td><code>KAR001</code></td>
                <td><code>karyawan@ptcak.com</code></td>
                <td><code>password</code></td>
                <td>Enrolled</td>
            </tr>
            <tr>
                <td><b>Karyawan Baru (Pending)</b></td>
                <td><code>KAR002</code></td>
                <td><code>budi@ptcak.com</code></td>
                <td><code>ptcak123</code></td>
                <td>Pending Enrollment</td>
            </tr>
        </tbody>
    </table>

    <!-- 4. PANDUAN DEPLOYMENT PRODUKSI -->
    <div class="section-title">4. Panduan Deployment Server Produksi (Production Server)</div>

    <div class="sub-section-title">A. Konfigurasi Izin Hak Akses Direktori (Linux Permissions)</div>
    <pre class="code-block">sudo chown -R www-data:www-data /var/www/presensi-pwa
sudo chmod -R 775 /var/www/presensi-pwa/storage
sudo chmod -R 775 /var/www/presensi-pwa/bootstrap/cache</pre>

    <div class="sub-section-title">B. Optimasi Caching & Sanitasi Produksi</div>
    <pre class="code-block">composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache</pre>

    <div class="sub-section-title">C. Konfigurasi Virtual Host Nginx (Server Block)</div>
    <pre class="code-block">server {
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
}</pre>

    <div class="sub-section-title">D. Konfigurasi Background Task Schedulers (Linux Crontab)</div>
    <div>Tambahkan baris scheduler berikut ke dalam crontab server (<code>crontab -e</code>):</div>
    <pre class="code-block">* * * * * cd /var/www/presensi-pwa && php artisan schedule:run >> /dev/null 2>&1</pre>
    <div style="font-size: 8.5px; color: #475569; margin-top: 2px;">
        Scheduler ini akan otomatis mengeksekusi penutupan <em>Auto-Checkout</em> (23:59 WITA) dan pencatatan <em>Auto-Alpha</em> (20:00 WITA) setiap hari.
    </div>

    <!-- 5. TROUBLESHOOTING INSTALASI -->
    <div class="section-title">5. Penanganan Masalah Instalasi (Troubleshooting)</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 35%;">Gejala Masalah</th>
                <th style="width: 65%;">Solusi Penanganan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><b>HTTP 500 Internal Server Error / Permission Denied</b></td>
                <td>Jalankan <code>chmod -R 775 storage bootstrap/cache</code> dan pastikan kepemilikan user web server sesuai (<code>chown -R www-data:www-data storage</code>).</td>
            </tr>
            <tr>
                <td><b>Aset Model Face Recognition 404 Not Found</b></td>
                <td>Pastikan berkas bobot model AI tersimpan lengkap di direktori <code>public/models/</code> (model ssd_mobilenetv1, face_landmark_68, face_recognition).</td>
            </tr>
            <tr>
                <td><b>Kamera atau GPS Diblokir Peramban</b></td>
                <td>Pastikan website diakses menggunakan protokol <b>HTTPS (SSL aktif)</b>. Peramban mobile memblokir WebCam dan Geolocation API pada protokol HTTP non-aman.</td>
            </tr>
            <tr>
                <td><b>Foto Presensi Snapshot Tidak Muncul</b></td>
                <td>Pastikan perintah symlink storage publik telah dieksekusi melalui <code>php artisan storage:link</code>.</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
