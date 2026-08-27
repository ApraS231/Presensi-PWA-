# Indeks Rencana Sprint - Sistem Presensi PWA PT. CAK

Dokumen ini memetakan dekomposisi teknis sistem presensi ke dalam sprint-sprint terstruktur per fase.
Setiap sprint mencakup Kebutuhan Fungsional (FR), Kebutuhan Non-Fungsional (NFR), langkah implementasi langkah demi langkah, target file, dan kriteria penerimaan (Definition of Done).

---

## Struktur Folder Sprint

```
sprints/
├── README.md
├── fase-1-fondasi-infrastruktur/
│   ├── sprint-1-setup-database.md
│   ├── sprint-2-auth-rbac.md
│   ├── sprint-3-layout-m3-pwa.md
│   └── sprint-4-notification-system.md
├── fase-2-biometrik-geospasial/
│   ├── sprint-1-face-api-enrollment.md
│   ├── sprint-2-geofencing-haversine.md
│   ├── sprint-3-transaksi-presensi.md
│   └── sprint-4-scheduler-automation.md
├── fase-3-perizinan-dashboard/
│   ├── sprint-1-manajemen-perizinan.md
│   ├── sprint-2-dashboard-monitoring.md
│   ├── sprint-3-live-map-leaflet.md
│   └── sprint-4-riwayat-notifikasi-ui.md
└── fase-4-laporan-master-data-uat/
    ├── sprint-1-export-laporan.md
    ├── sprint-2-master-data-management.md
    └── sprint-3-uat-optimasi-nfr.md
```

---

## Matriks Fase & Sprint

| Fase | Sprint ID | Nama Sprint | Fokus Utama |
|---|---|---|---|
| **Fase 1: Fondasi** | `SP-1.1` | Setup Proyek, Environment & Database Schema | Inisialisasi Laravel 11, 7 Migration, Seeder, Storage Link |
| | `SP-1.2` | Autentikasi, Status Akun & Otorisasi RBAC | Login NIK/Email, Bcrypt, Middleware CheckActive & 3 Role |
| | `SP-1.3` | Design System M3, Responsive Layout & PWA Manifest | Token M3, Layout Mobile PWA & Admin Sidebar, Service Worker |
| | `SP-1.4` | Service Notifikasi & In-App Notification API | Model Notification, NotificationService, Polling API |
| **Fase 2: Biometrik & Geospasial** | `SP-2.1` | Integrasi face-api.js & Enrollment Biometrik | Model face-api.js, WebCam Enrollment, Mean Vector 128-float |
| | `SP-2.2` | Geolokasi & Geofencing Haversine Ganda | GPS Client UX, HaversineService Server Validation |
| | `SP-2.3` | Transaksi Presensi Masuk & Pulang | Face Matching (Threshold <= 0.50), Foto Capture, Validation Flow |
| | `SP-2.4` | Otomasi Scheduler: Auto-Checkout & Auto-Alpha | Cron Job 23:00 WITA, Generate Record Alpha & Flag Auto-Checkout |
| **Fase 3: Perizinan & Dashboard** | `SP-3.1` | Modul Pengajuan, Edit, Batal & Approval Izin | Form Izin, Upload Bukti, Approval Workflow, Auto-Attendance |
| | `SP-3.2` | Dashboard Monitoring Real-Time HRD | Kartu Statistik, Filter Departemen, Reminder Enrollment Badge |
| | `SP-3.3` | Visualisasi Live Map Leaflet.js | Layer OpenStreetMap, Marker Presensi, Circle Geofence |
| | `SP-3.4` | Riwayat Presensi & UI Notifikasi PWA | List Riwayat Presensi, Tanda Auto-Checkout, Badge Notifikasi |
| **Fase 4: Laporan & UAT** | `SP-4.1` | Rekapitulasi & Export Laporan Excel/PDF | Formula Jam Kerja Bersih, PhpSpreadsheet, Laravel-DomPDF |
| | `SP-4.2` | Manajemen Master Lokasi, Karyawan & Settings | CRUD Multi-Lokasi, User Management, Global Policy Config |
| | `SP-4.3` | Pengujian UAT, Optimasi Kecepatan & Audit NFR | Verifikasi Akurasi Wajah, Geofence Reject Test, Stress Test |
