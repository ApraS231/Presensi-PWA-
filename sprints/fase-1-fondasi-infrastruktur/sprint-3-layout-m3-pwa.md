# Sprint 1.3 - Design System M3, Responsive Layout & PWA Manifest

- **Fase:** 1 (Fondasi Proyek & Infrastruktur)
- **Estimasi Durasi:** 4 Hari
- **Prasyarat:** Sprint 1.2 (Sistem Auth & Routing selesai)

---

## 1. Kebutuhan Fungsional (FR)

- `FR-UI-01`: Implementasi Design System Material Design 3 (M3) berbasis CSS Custom Properties tanpa dependency framework CSS berat.
- `FR-UI-02`: Pembuatan Layout Mobile PWA (`layouts/pwa.blade.php`) untuk role Karyawan:
  - Top Bar ringkas dengan profil dan indikator online/offline.
  - Container konten responsif (viewport mobile: 360px hingga 430px).
  - Bottom Navigation Bar M3 dengan 4 menu utama: Beranda, Absen, Izin, Riwayat.
- `FR-UI-03`: Pembuatan Layout Desktop Admin (`layouts/admin.blade.php`) untuk role HRD dan Super Admin:
  - Collapsible Sidebar M3 dengan menu hierarkis.
  - Header admin dengan pencarian cepat, theme toggle (Dark/Light), dan dropdown akun.
  - Breadcrumb navigasi dan container konten tabular.
- `FR-PWA-01`: Web App Manifest (`public/manifest.json`) valid untuk memicu prompt instalasi "Add to Home Screen" pada smartphone Android & iOS.
- `FR-PWA-02`: Registrasi Service Worker (`public/service-worker.js`) untuk caching aset statis (CSS, JS, font Inter, icons) dan penyediaan halaman fallback saat offline.

---

## 2. Kebutuhan Non-Fungsional (NFR)

- `NFR-PERF-01`: Waktu muat awal (Initial Load) PWA di bawah 1.5 detik pada jaringan 4G standar berkat pemanfaatan cache Service Worker.
- `NFR-RESP-01`: Dukungan lintas resolusi dari layar kecil smartphone (min width 360px), tablet (768px), hingga monitor desktop (1920px).
- `NFR-UX-01`: Transisi halaman halus menggunakan M3 Motion Easing (`cubic-bezier(0.2, 0.0, 0, 1.0)`) dan micro-interaction state layer (ripple effect).
- `NFR-THEME-01`: Dukungan tema ganda (Light Mode dan Dark Mode) yang mendeteksi preferensi OS perangkat secara otomatis serta dapat di-override manual.

---

## 3. Langkah-Langkah Dekomposisi Teknis

### Langkah 1: Modul CSS Tokens Material Design 3
Target folder: `public/css/` atau `resources/css/`

1. **`m3-tokens.css`**: Definisi seluruh custom properties dari dokumentasi `DESIGN.md` (Light & Dark Color Schemes, Type Scale, Shape Scale, Elevation 0-5, Spacing Scale).
2. **`m3-components.css`**: Styling tombol (`.md-btn-filled`, `.md-btn-tonal`, `.md-btn-outlined`), kartu (`.md-card-elevated`, `.md-card-outlined`), badges status presensi, dan form fields.
3. **`m3-utilities.css`**: Utility class typescale, spacing helpers, dan keyframe animasi micro-interaction.
4. **`m3-layout-pwa.css`**: Styling khusus viewport mobile, bottom navigation, notch safety area (`env(safe-area-inset-bottom)`).
5. **`m3-layout-admin.css`**: Styling sidebar admin desktop, grid responsif dashboard, dan data table.

### Langkah 2: Layout Blade PWA Karyawan
Target: `resources/views/layouts/pwa.blade.php`

1. Head section mengikutsertakan Google Fonts (Inter + Material Symbols Rounded), manifest, dan meta tags PWA:
   ```html
   <link rel="manifest" href="{{ asset('manifest.json') }}">
   <meta name="theme-color" content="#1565C0">
   <meta name="apple-mobile-web-app-capable" content="yes">
   <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
   ```
2. Struktur Body:
   - Header aplikasi PWA (Avatar, NIK, Salam waktu).
   - Content container `<main class="md-layout-pwa">@yield('content')</main>`.
   - Bottom Navigation Bar dengan active state indicator M3.
   - Script registrasi `service-worker.js`.

### Langkah 3: Layout Blade Admin Dashboard
Target: `resources/views/layouts/admin.blade.php`

1. Sidebar Desktop (280px) dengan link navigasi:
   - Dashboard Statistik
   - Monitoring & Live Map
   - Enrollment Biometrik Wajah
   - Approval Perizinan
   - Rekapitulasi & Laporan
   - Master Lokasi & Geofence (Superadmin)
   - Master Data Karyawan (Superadmin)
   - Pengaturan Kebijakan (Superadmin)
2. Main Content Area dengan Topbar Header dan Alert Container.

### Langkah 4: PWA Web App Manifest
Target: `public/manifest.json`

```json
{
  "name": "Presensi PT. Cahaya Anugrah Kalimantan",
  "short_name": "Presensi CAK",
  "description": "Sistem Presensi Karyawan PWA dengan Face Recognition dan Geofencing",
  "start_url": "/karyawan/dashboard",
  "display": "standalone",
  "background_color": "#FAFAFA",
  "theme_color": "#1565C0",
  "orientation": "portrait",
  "icons": [
    {
      "src": "/icons/icon-192x192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "any maskable"
    },
    {
      "src": "/icons/icon-512x512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any maskable"
    }
  ]
}
```

### Langkah 5: Service Worker & Caching Strategy
Target: `public/service-worker.js`

1. Cache aset statis saat `install` event (CSS, JS, model weights face-api.js).
2. Strategi **Cache-First** untuk static asset: CSS, Fonts, Icons, JS.
3. Strategi **Network-First** dengan Fallback Offline untuk endpoint transaksi presensi dan data dinamis.
4. Tangani offline fallback page jika koneksi data terputus total di lapangan.

---

## 4. Kriteria Penerimaan (Definition of Done)

- [ ] Desain antarmuka mematuhi standar Material Design 3 (M3) tanpa dependency framework pihak ketiga yang tidak perlu.
- [ ] Tampilan PWA di smartphone (resolusi 360px-430px) pas tanpa horizontal overflow/scroll.
- [ ] Bottom Navigation Bar berfungsi dengan transisi aktif yang presisi.
- [ ] Layout Admin Desktop menampilkan Sidebar responsif dan grid kartu statistik yang rapi.
- [ ] Peramban Chrome Mobile memvalidasi Web App Manifest dan menampilkan prompt install PWA.
- [ ] Service Worker terdaftar (`navigator.serviceWorker.ready`) dan melakukan caching aset statis.
- [ ] Toggle Dark Mode berfungsi mulus mengubah token CSS variable tanpa reload halaman.
