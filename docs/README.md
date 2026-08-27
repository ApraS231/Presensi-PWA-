# Katalog Dokumentasi Sistem Presensi PWA PT. Cahaya Anugrah Kalimantan

Folder ini memuat seluruh arsip dokumentasi teknis, panduan operasional, laporan penyelesaian tugas, hasil pengujian, dan berkas format cetak (PDF) untuk Sistem Informasi Presensi Karyawan Berbasis PWA dengan Face Recognition dan Geofencing PT. CAK.

---

## 1. Struktur Direktori Dokumentasi

```
docs/
├── README.md                                          (Katalog Utama Dokumentasi)
├── pdf/                                               (Dokumen Resmi Siap Cetak Format PDF)
│   ├── PRD_Presensi_PWA_PT_CAK.pdf
│   ├── Alur_Bisnis_dan_Data_Flow_Presensi.pdf
│   ├── Panduan_Pengguna_Presensi_PWA_PT_CAK.pdf
│   ├── Panduan_Instalasi_Presensi_PWA_PT_CAK.pdf
│   ├── Dokumentasi_UAT_dan_Debugging_Presensi_PT_CAK.pdf
│   └── Laporan_Penyelesaian_Sprint_dan_Tugas_Presensi_PT_CAK.pdf
└── markdown/                                          (Dokumen Sumber Format Markdown)
    ├── ROADMAP.md
    ├── DESIGN.md
    ├── PANDUAN_PENGGUNA.md
    ├── PANDUAN_INSTALASI.md
    ├── DOKUMENTASI_UAT_DAN_DEBUGGING.md
    ├── LAPORAN_PENYELESAIAN_SPRINT_DAN_TUGAS.md
    └── DATA_DUMMY_DAN_MASTER.md
```

---

## 2. Daftar Dokumen Format PDF (Siap Cetak)

| Nama Berkas PDF | Deskripsi & Isi Dokumen | Lokasi Berkas |
| :--- | :--- | :--- |
| **PRD_Presensi_PWA_PT_CAK.pdf** | *Product Requirement Document* (PRD) yang merinci arsitektur, kebutuhan fungsional, matriks perizinan, dan batas toleransi operasional. | [PRD PDF](file:///d:/PROJECT/Presensi(PWA)/docs/pdf/PRD_Presensi_PWA_PT_CAK.pdf) |
| **Alur_Bisnis_dan_Data_Flow_Presensi.pdf** | Diagram alur proses bisnis (*flowchart*), arsitektur sistem, dan *Data Flow Diagram* (DFD Level 0-2) presensi masuk, pulang, dan perizinan. | [Alur Bisnis PDF](file:///d:/PROJECT/Presensi(PWA)/docs/pdf/Alur_Bisnis_dan_Data_Flow_Presensi.pdf) |
| **Panduan_Pengguna_Presensi_PWA_PT_CAK.pdf** | Buku panduan operasional (*User Guide*) lengkap untuk 3 peran pengguna: Karyawan, HRD/Admin, dan Super Admin. | [Panduan Pengguna PDF](file:///d:/PROJECT/Presensi(PWA)/docs/pdf/Panduan_Pengguna_Presensi_PWA_PT_CAK.pdf) |
| **Panduan_Instalasi_Presensi_PWA_PT_CAK.pdf** | Panduan teknis instalasi dan *deployment* aplikasi pada lingkungan lokal (Development) dan VPS/Server Produksi. | [Panduan Instalasi PDF](file:///d:/PROJECT/Presensi(PWA)/docs/pdf/Panduan_Instalasi_Presensi_PWA_PT_CAK.pdf) |
| **Dokumentasi_UAT_dan_Debugging_Presensi_PT_CAK.pdf** | Berita acara dan laporan hasil pengujian penerimaan pengguna (*User Acceptance Testing*) serta catatan perbaikan bug/debugging sintaks. | [UAT & Debugging PDF](file:///d:/PROJECT/Presensi(PWA)/docs/pdf/Dokumentasi_UAT_dan_Debugging_Presensi_PT_CAK.pdf) |
| **Laporan_Penyelesaian_Sprint_dan_Tugas_Presensi_PT_CAK.pdf** | Laporan pertanggungjawaban penyelesaian seluruh fase pengembangan Sprint 1 hingga Sprint 3 (Fase 1-4). | [Laporan Sprint PDF](file:///d:/PROJECT/Presensi(PWA)/docs/pdf/Laporan_Penyelesaian_Sprint_dan_Tugas_Presensi_PT_CAK.pdf) |

---

## 3. Daftar Dokumen Format Markdown (.md)

| Nama Dokumen | Deskripsi Isi Dokumen | Lokasi Berkas |
| :--- | :--- | :--- |
| **ROADMAP.md** | Peta jalan pengembangan sprint (*Sprint Backlog*), estimasi waktu, dan status implementasi per fase. | [ROADMAP.md](file:///d:/PROJECT/Presensi(PWA)/docs/markdown/ROADMAP.md) |
| **DESIGN.md** | Spesifikasi desain sistem, skema basis data, arsitektur teknis, dan standar visual *Material Design 3 (M3)*. | [DESIGN.md](file:///d:/PROJECT/Presensi(PWA)/docs/markdown/DESIGN.md) |
| **PANDUAN_PENGGUNA.md** | Panduan lengkap pengoperasian fitur presensi mobile, verifikasi wajah, pengajuan izin, dan dashboard HRD. | [PANDUAN_PENGGUNA.md](file:///d:/PROJECT/Presensi(PWA)/docs/markdown/PANDUAN_PENGGUNA.md) |
| **PANDUAN_INSTALASI.md** | Petunjuk konfigurasi environment, migrasi database, seeding, dan eksekusi server ASGI FastAPI (Uvicorn). | [PANDUAN_INSTALASI.md](file:///d:/PROJECT/Presensi(PWA)/docs/markdown/PANDUAN_INSTALASI.md) |
| **DOKUMENTASI_UAT_DAN_DEBUGGING.md** | Dokumentasi skenario uji UAT (UAT-01 s/d UAT-08), matriks hasil pengujian, dan log debugging teknis. | [DOKUMENTASI_UAT_DAN_DEBUGGING.md](file:///d:/PROJECT/Presensi(PWA)/docs/markdown/DOKUMENTASI_UAT_DAN_DEBUGGING.md) |
| **LAPORAN_PENYELESAIAN_SPRINT_DAN_TUGAS.md** | Rekapitulasi penyelesaian tugas pengembang per modul dan ringkasan metrik keberhasilan proyek. | [LAPORAN_PENYELESAIAN_SPRINT_DAN_TUGAS.md](file:///d:/PROJECT/Presensi(PWA)/docs/markdown/LAPORAN_PENYELESAIAN_SPRINT_DAN_TUGAS.md) |
| **DATA_DUMMY_DAN_MASTER.md** | Rincian data awal (*dummy dataset*), akun pengguna per departemen, titik lokasi kantor/proyek geofence, dan parameter kebijakan. | [DATA_DUMMY_DAN_MASTER.md](file:///d:/PROJECT/Presensi(PWA)/docs/markdown/DATA_DUMMY_DAN_MASTER.md) |

---

## 4. Dokumentasi Log Sprint

Dokumentasi berkas backlog dan progres teknis per fase sprint tersimpan pada folder `sprints/`:
- `sprints/fase-1-fondasi-infrastruktur/`
- `sprints/fase-2-biometrik-geospasial/`
- `sprints/fase-3-perizinan-dashboard/`
- `sprints/fase-4-laporan-master-data-uat/`
