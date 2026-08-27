<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panduan Pengguna (User Guide) - Presensi PWA PT. CAK</title>
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
                <div style="font-size: 8px; color: #475569;">Dokumen Panduan: <b>UG-CAK-2026</b></div>
                <div style="font-size: 8px; color: #475569;">Edisi / Versi: <b>v1.0 (Agustus 2026)</b></div>
                <div style="font-size: 8px; color: #475569;">Klasifikasi: <b>Internal Perusahaan</b></div>
            </td>
        </tr>
    </table>

    <!-- JUDUL PANDUAN -->
    <div class="doc-title">Buku Panduan Pengguna (User Guide)</div>
    <div class="doc-subtitle">Sistem Informasi Presensi PWA Berbasis Face Recognition & Double Geofencing</div>

    <!-- 1. PENDAHULUAN -->
    <div class="section-title">1. Pendahuluan & Persyaratan Akses Sistem</div>
    <p>
        Buku panduan ini disusun sebagai petunjuk teknis operasional bagi seluruh jajaran karyawan dan manajemen <b>PT. Cahaya Anugrah Kalimantan</b> dalam menggunakan aplikasi presensi berbasis Progressive Web Application (PWA).
    </p>

    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 25%;">Peran (Role)</th>
                <th style="width: 30%;">Antarmuka Utama</th>
                <th style="width: 45%;">Tanggung Jawab & Fitur Utama</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><b>Karyawan</b></td>
                <td>Mobile PWA (Smartphone)</td>
                <td>Enrollment biometrik wajah, presensi check-in/check-out harian, pengajuan izin/cuti/sakit, dan riwayat presensi mandiri.</td>
            </tr>
            <tr>
                <td><b>HRD / Admin Presensi</b></td>
                <td>Desktop Dashboard</td>
                <td>Monitoring kehadiran real-time, live geospatial map, approval izin/cuti, rekapitulasi laporan, dan ekspor Excel/PDF.</td>
            </tr>
            <tr>
                <td><b>Super Admin</b></td>
                <td>Desktop Master Control</td>
                <td>Manajemen data pengguna, reset biometrik wajah, master titik koordinat geofence, dan konfigurasi kebijakan jam kerja global.</td>
            </tr>
        </tbody>
    </table>

    <div class="sub-section-title">Instalasi Aplikasi PWA pada Smartphone Karyawan</div>
    <ol>
        <li>Buka peramban (Google Chrome / Safari) pada smartphone Anda, lalu akses alamat URL resmi presensi PT. CAK.</li>
        <li>Tekan tombol menu browser (ikon titik tiga di kanan atas Chrome, atau ikon Share di Safari).</li>
        <li>Pilih opsi <b>"Tambahkan ke Layar Utama" (Add to Home Screen)</b> atau <b>"Instal Aplikasi" (Install App)</b>.</li>
        <li>Ikon aplikasi <b>Presensi PT. CAK</b> akan muncul di layar utama smartphone dan dapat diakses selayaknya aplikasi native tanpa perlu mengunduh dari app store.</li>
    </ol>

    <div class="note-box">
        <b>PENTING:</b> Pastikan izin akses <b>Kamera</b> dan <b>Lokasi Perangkat (GPS Akurasi Tinggi)</b> selalu diberikan izin aktif (Allow) pada peramban web Anda.
    </div>

    <!-- 2. PANDUAN PENGGUNA KARYAWAN -->
    <div class="section-title">2. Panduan Operasional Karyawan (Mobile PWA)</div>

    <!-- Login -->
    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">1</span> Login Akun & Keamanan Awal</div>
        <div>Masukkan <b>Nomor Induk Karyawan (NIK)</b> atau <b>Alamat Email</b> terdaftar beserta <b>Password</b> (default awal: <code>ptcak123</code>).</div>
        <div>Setelah berhasil login, karyawan disarankan segera memperbarui password melalui menu profil demi keamanan akun.</div>
    </div>

    <!-- Enrollment Wajah -->
    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">2</span> Pendaftaran Biometrik Wajah (Enrollment)</div>
        <div>Saat pertama kali login, akun berstatus <code>pending</code> dan otomatis diarahkan ke halaman pendaftaran wajah.</div>
        <ul>
            <li>Posisikan wajah tepat di tengah bingkai panduan kamera (lingkaran pemindai).</li>
            <li>Pastikan pencahayaan ruangan cukup terang dan tidak membelakangi sumber cahaya (backlight).</li>
            <li>Lepaskan masker, kacamata hitam, atau penutup wajah lainnya.</li>
            <li>Sistem akan mendeteksi kontur wajah dan mengekstraksi 128 vektor biometrik neural.</li>
            <li>Tekan tombol <b>"Simpan Biometrik Wajah"</b>. Status akun akan berubah menjadi <code>enrolled</code> dan modul presensi harian langsung aktif.</li>
        </ul>
    </div>

    <!-- Presensi Masuk -->
    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">3</span> Pelaksanaan Presensi Masuk (Check-In) Harian</div>
        <ul>
            <li>Pastikan Anda telah berada di area kantor atau lokasi proyek PT. CAK (jarak GPS berada di dalam radius geofence yang ditentukan).</li>
            <li>Buka menu <b>"Presensi"</b> pada navigasi bawah PWA.</li>
            <li>Arahkan kamera ke wajah Anda. Indikator pemindai akan memverifikasi kecocokan wajah biometrik (Euclidean distance &le; 0.50).</li>
            <li>Setelah status geofence dan wajah terverifikasi valid, tekan tombol <b>"Kirim Presensi Masuk"</b>.</li>
            <li>Sistem mencatat waktu kehadiran berdasarkan zona waktu WITA (Asia/Makassar):
                <ul>
                    <li><b>Tepat Waktu:</b> Check-in sebelum jam cut-off (Pukul 08:00 + toleransi 15 menit = Maksimal 08:15 WITA).</li>
                    <li><b>Terlambat:</b> Check-in melewati pukul 08:15 WITA (menit keterlambatan dihitung otomatis).</li>
                </ul>
            </li>
        </ul>
    </div>

    <div class="page-break"></div>

    <!-- Presensi Pulang -->
    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">4</span> Pelaksanaan Presensi Pulang (Check-Out) Harian</div>
        <ul>
            <li>Pada akhir jam kerja operasional (mulai pukul 17:00 WITA), buka kembali menu <b>"Presensi"</b>.</li>
            <li>Sistem otomatis beralih ke mode Check-Out. Verifikasi wajah dan lokasi di dalam radius kantor.</li>
            <li>Tekan tombol <b>"Kirim Presensi Pulang"</b>. Jam pulang berhasil tercatat dan durasi jam kerja bersih harian dihitung.</li>
        </ul>
        <div class="note-box">
            <b>Peringatan Auto-Checkout:</b> Jika karyawan lupa melakukan check-out hingga pukul 23:59 WITA, sistem scheduler otomatis menutup sesi presensi dengan tanda khusus <code>Auto-Checkout</code>.
        </div>
    </div>

    <!-- Pengajuan Izin -->
    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">5</span> Pengajuan Izin, Sakit & Cuti Mandiri</div>
        <ul>
            <li>Buka menu <b>"Izin"</b> lalu tekan tombol <b>"Ajukan Izin Baru"</b>.</li>
            <li>Pilih Jenis Permohonan: <b>Cuti Tahunan</b>, <b>Sakit</b> (wajib melampirkan surat dokter), atau <b>Izin Keperluan Mendesak</b>.</li>
            <li>Tentukan rentang Tanggal Mulai dan Tanggal Selesai serta isi alasan permohonan.</li>
            <li>Unggah berkas foto/dokumen pendukung (format JPG, PNG, PDF maks. 2 MB).</li>
            <li>Tekan <b>"Kirim Pengajuan"</b>. Permohonan akan masuk ke antrean persetujuan HRD. Status permohonan (Pending / Approved / Rejected) dapat dipantau langsung pada daftar permohonan.</li>
        </ul>
    </div>

    <!-- Riwayat Presensi -->
    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">6</span> Pemantauan Riwayat Kehadiran Mandiri</div>
        <div>Karyawan dapat membuka menu <b>"Riwayat"</b> untuk melihat rangkuman statistik bulanan: total hari hadir, keterlambatan, cuti/izin yang disetujui, dan alpha, lengkap dengan pratinjau snapshot foto presensi masuk dan pulang.</div>
    </div>

    <!-- 3. PANDUAN PENGGUNA HRD / ADMIN -->
    <div class="section-title">3. Panduan Operasional HRD / Admin Presensi (Desktop)</div>

    <!-- Dashboard Monitoring -->
    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">1</span> Dashboard Monitoring Real-Time & Live Polling</div>
        <ul>
            <li>Dashboard desktop memuat metrik ringkasan harian: Total Karyawan, Hadir Tepat Waktu, Terlambat, Izin/Cuti, dan Belum Hadir.</li>
            <li>Tabel presensi harian otomatis diperbarui setiap <b>30 detik</b> tanpa perlu me-refresh browser.</li>
            <li>HRD dapat memfilter data berdasarkan <b>Tanggal Kehadiran</b> dan <b>Departemen</b>.</li>
            <li>Jika terdapat karyawan baru yang belum melakukan enrollment wajah, sistem menampilkan banner peringatan daftar nama yang perlu ditindaklanjuti.</li>
        </ul>
    </div>

    <!-- Live Map -->
    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">2</span> Visualisasi Live Geospatial Map (Peta Lokasi)</div>
        <ul>
            <li>Buka menu <b>"Live Map Monitoring"</b> pada sidebar navigasi.</li>
            <li>Peta berbasis OpenStreetMap menampilkan lingkaran geofence seluruh kantor dan site proyek tambang PT. CAK.</li>
            <li>Titik pin koordinat kehadiran karyawan ditampilkan dengan kode warna status:
                <ul>
                    <li><b>Biru / Hijau:</b> Presensi Tepat Waktu di dalam geofence.</li>
                    <li><b>Kuning / Oranye:</b> Presensi Terlambat.</li>
                </ul>
            </li>
            <li>Klik pada pin karyawan untuk melihat popup detail: Nama, NIK, Waktu Presensi Masuk/Pulang, Jarak dari titik pusat kantor, dan thumbnail foto snapshot.</li>
        </ul>
    </div>

    <!-- Approval Izin -->
    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">3</span> Pemrosesan & Approval Pengajuan Izin / Cuti</div>
        <ul>
            <li>Buka menu <b>"Persetujuan Izin"</b> untuk melihat daftar antrean permohonan karyawan.</li>
            <li>Klik permohonan untuk meninjau detail alasan, rentang tanggal, dan lampiran dokumen bukti.</li>
            <li>Pilih tindakan <b>"Setujui" (Approve)</b> atau <b>"Tolak" (Reject)</b> disertai catatan evaluasi.</li>
            <li>Permohonan yang disetujui otomatis menghasilkan catatan kehadiran sah pada hari kerja aktif, sehingga karyawan tidak terhitung Alpha.</li>
        </ul>
    </div>

    <div class="page-break"></div>

    <!-- Ekspor Laporan -->
    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">4</span> Rekapitulasi Laporan & Ekspor Dokumen Resmi</div>
        <ul>
            <li>Buka menu <b>"Laporan & Rekapitulasi"</b>.</li>
            <li>Pilih rentang tanggal kustom atau gunakan preset cepat: <em>Hari Ini</em>, <em>7 Hari Terakhir</em>, <em>Bulan Ini</em>, atau <em>Bulan Lalu</em>.</li>
            <li>Sistem menyajikan tabel rekapitulasi lengkap dengan formula jam kerja bersih: <code>Jam Kerja Bersih = (Jam Pulang - Jam Masuk) - Durasi Istirahat</code>.</li>
            <li>Tombol Ekspor Dokumen:
                <ul>
                    <li><b>Ekspor Excel (.xlsx):</b> Berkas spreadsheet terformat siap integrasi dengan sistem payroll keuangan.</li>
                    <li><b>Cetak PDF (.pdf):</b> Dokumen resmi format A4 Landscape ber-kop perusahaan PT. Cahaya Anugrah Kalimantan.</li>
                </ul>
            </li>
        </ul>
    </div>

    <!-- 4. PANDUAN PENGGUNA SUPER ADMIN -->
    <div class="section-title">4. Panduan Master Control Super Admin (System Administrator)</div>

    <!-- Manajemen User -->
    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">1</span> Manajemen Master Pengguna & Karyawan</div>
        <ul>
            <li><b>Tambah Pengguna Baru:</b> Buka menu <em>Master Pengguna &gt; Tambah Pengguna</em>. Isi NIK, Nama, Email, Role (Karyawan / Admin / Superadmin), Departemen, Jabatan, dan No. Telepon. Password awal otomatis diset ke <code>ptcak123</code>.</li>
            <li><b>Reset Biometrik Wajah:</b> Jika karyawan mengganti foto biometrik atau terjadi kendala pengenalan wajah, Super Admin dapat menekan tombol <b>Reset Wajah</b> pada tabel pengguna.</li>
            <li><b>Toggle Status Aktif (Aktivasi / Deaktivasi):</b> Super Admin dapat menonaktifkan akun karyawan seketika. Akun nonaktif akan langsung diputus sesinya saat melakukan request berikutnya.</li>
        </ul>
    </div>

    <!-- Master Lokasi & Geofence -->
    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">2</span> Manajemen Master Titik Lokasi & Radius Geofence</div>
        <ul>
            <li>Buka menu <em>Master Titik Lokasi</em> untuk mengelola daftar kantor pusat dan site proyek operasional.</li>
            <li><b>Pemilih Koordinat Interaktif (Interactive Map Picker):</b> Klik pada peta atau geser pin koordinat untuk otomatis mengisi latitude dan longitude lokasi presensi.</li>
            <li>Tentukan <b>Radius Geofence (Meter)</b> untuk menetapkan batas toleransi jarak presensi karyawan (contoh: 100 meter).</li>
            <li>Titik lokasi dapat diaktifkan atau dinonaktifkan sewaktu-waktu sesuai status proyek.</li>
        </ul>
    </div>

    <!-- Pengaturan Kebijakan Sistem -->
    <div class="guide-box">
        <div class="guide-box-title"><span class="step-num">3</span> Konfigurasi Kebijakan Sistem Global</div>
        <div>Super Admin dapat mengatur parameter operasional sistem melalui menu <em>Kebijakan Sistem</em>:</div>
        <table class="grid-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Parameter Kebijakan</th>
                    <th style="width: 25%;">Nilai Standar</th>
                    <th style="width: 45%;">Keterangan & Fungsi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><b>Jam Masuk Kerja</b></td>
                    <td>08:00 WITA</td>
                    <td>Batas waktu awal dimulainya jam kerja operasional.</td>
                </tr>
                <tr>
                    <td><b>Jam Pulang Kerja</b></td>
                    <td>17:00 WITA</td>
                    <td>Waktu dimulainya izin presensi pulang (check-out).</td>
                </tr>
                <tr>
                    <td><b>Toleransi Keterlambatan</b></td>
                    <td>15 Menit</td>
                    <td>Check-in hingga pukul 08:15 WITA tetap terhitung Tepat Waktu.</td>
                </tr>
                <tr>
                    <td><b>Durasi Istirahat Harian</b></td>
                    <td>60 Menit</td>
                    <td>Potongan waktu istirahat dalam formula perhitungan jam kerja bersih payroll.</td>
                </tr>
                <tr>
                    <td><b>Kalender Hari Kerja Aktif</b></td>
                    <td>senin s/d jumat</td>
                    <td>Hari kerja aktif untuk otomatisasi scheduler generate status Alpha.</td>
                </tr>
                <tr>
                    <td><b>Batas Retroaktif Izin</b></td>
                    <td>3 Hari</td>
                    <td>Batas maksimum hari ke belakang pengajuan izin/sakit yang diperbolehkan.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- 5. TROUBLESHOOTING & PANDUAN KENDALA -->
    <div class="section-title">5. Panduan Penanganan Kendala (Troubleshooting FAQ)</div>

    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 35%;">Kendala yang Ditemui</th>
                <th style="width: 65%;">Langkah Solusi Penanganan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><b>Lokasi GPS Terdeteksi di Luar Radius Kantor</b></td>
                <td>Pastikan fitur GPS / Layanan Lokasi diaktifkan dengan mode <em>Akurasi Tinggi</em>. Hindari berada di dalam ruangan tertutup beton tebal saat pertama kali membuka aplikasi agar sinyal GPS satelit terkunci optimal.</td>
            </tr>
            <tr>
                <td><b>Kamera Tidak Terbuka di Peramban Web</b></td>
                <td>Buka pengaturan peramban (Chrome / Safari) &gt; Setelan Situs &gt; Kamera &gt; Ubah izin akses untuk domain presensi menjadi <b>Izinkan (Allow)</b>. Muat ulang halaman presensi.</td>
            </tr>
            <tr>
                <td><b>Wajah Tidak Terdeteksi / Gagal Verifikasi</b></td>
                <td>Pastikan pencahayaan cukup terang, hindari bayangan gelap pada wajah, posisikan wajah sejajar lensa kamera, dan lepas kacamata hitam / masker. Jika kontur wajah berubah signifikan, hubungi HRD untuk meminta <em>Reset Biometrik Wajah</em>.</td>
            </tr>
            <tr>
                <td><b>Lupa Password Akun</b></td>
                <td>Hubungi Super Admin atau HRD untuk mereset password akun Anda kembali ke password standar perusahaan (<code>ptcak123</code>).</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
