# Sprint 4.1 - Rekapitulasi & Export Laporan Excel/PDF

- **Fase:** 4 (Laporan, Master Data & UAT)
- **Estimasi Durasi:** 3 Hari
- **Prasyarat:** Fase 2 & Fase 3 selesai (Data kehadiran & perizinan lengkap)

---

## 1. Kebutuhan Fungsional (FR)

- `FR-REP-01`: **Kalkulasi Rekapitulasi Kehadiran**:
  - Filter periode fleksibel (Harian, Rentang Tanggal Tertentu, atau Bulanan misal 01 s/d 30).
  - Filter berdasarkan departemen atau seluruh karyawan.
  - Perhitungan **Total Jam Kerja Bersih**:
    - Formula: `Total Jam Kerja = (time_out - time_in) - durasi_istirahat`.
    - Durasi istirahat diambil dari parameter `settings.jam_istirahat` (default: 60 menit).
    - Jika `time_out` kosong (sebelum scheduler berjalan), fallback ke nilai `settings.jam_pulang`.
    - Status `izin`, `sakit`, `cuti` (approved) dihitung sebagai jam kerja penuh harian normal.
    - Status `alpha` dihitung sebagai 0 jam kerja.
  - Perhitungan **Akumulasi Menit Keterlambatan**.
  - Perhitungan **Total Cuti Terpakai**.
- `FR-REP-02`: **Export ke Microsoft Excel (.xlsx)**:
  - Menggunakan library **PhpSpreadsheet** (`phpoffice/phpspreadsheet`).
  - Format tabel siap cetak untuk payroll/penggajian dengan styling header, border rapi, dan auto-size kolom.
  - Penamaan file otomatis: `Laporan_Presensi_CAK_{Periode}.xlsx`.
- `FR-REP-03`: **Export ke Dokumen PDF (.pdf)**:
  - Menggunakan library **Laravel-DomPDF** (`barryvdh/laravel-dompdf`).
  - Layout kertas A4 Landscape resmi bertanda tangan HRD / Pimpinan PT. CAK.
  - Penamaan file otomatis: `Laporan_Presensi_CAK_{Periode}.pdf`.

---

## 2. Kebutuhan Non-Fungsional (NFR)

- `NFR-PERF-01`: Efisiensi HRD — Ekspor dokumen laporan hingga 500 karyawan diselesaikan dalam waktu < 10 detik (dibandingkan sistem manual 3 hari kerja).
- `NFR-MEM-01`: Pemanfaatan chunking query database untuk mencegah memori server overload (Memory Exhaustion) saat mengekspor data berskala besar.

---

## 3. Langkah-Langkah Dekomposisi Teknis

### Langkah 1: Install Dependency Composer
```bash
composer require phpoffice/phpspreadsheet barryvdh/laravel-dompdf
```

### Langkah 2: Service Perhitungan Rekapitulasi
Target: `app/Services/ReportCalculationService.php`

```php
namespace App\Services;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

class ReportCalculationService
{
    public static function generateSummary(Carbon $startDate, Carbon $endDate, ?string $department = null): array
    {
        $usersQuery = User::where('role', 'karyawan')->where('is_active', true);
        if ($department) {
            $usersQuery->where('department', $department);
        }
        $users = $usersQuery->orderBy('name')->get();

        $jamMasukSetting = Setting::getValue('jam_masuk', '08:00');
        $jamPulangSetting = Setting::getValue('jam_pulang', '17:00');
        $jamIstirahatMenit = (int) Setting::getValue('jam_istirahat', '60');

        $fullWorkdayMinutes = Carbon::createFromTimeString($jamMasukSetting)
            ->diffInMinutes(Carbon::createFromTimeString($jamPulangSetting)) - $jamIstirahatMenit;

        $reportData = [];

        foreach ($users as $user) {
            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();

            $totalHadir = 0;
            $totalTerlambat = 0;
            $totalIzin = 0;
            $totalSakit = 0;
            $totalCuti = 0;
            $totalAlpha = 0;
            $totalMenitKerja = 0;
            $totalMenitTerlambat = 0;

            foreach ($attendances as $att) {
                switch ($att->status) {
                    case 'tepat_waktu':
                        $totalHadir++;
                        $totalMenitKerja += self::calculateDailyWorkMinutes($att, $jamPulangSetting, $jamIstirahatMenit);
                        break;
                    case 'terlambat':
                        $totalHadir++;
                        $totalTerlambat++;
                        $totalMenitKerja += self::calculateDailyWorkMinutes($att, $jamPulangSetting, $jamIstirahatMenit);
                        if ($att->time_in) {
                            $diff = Carbon::createFromTimeString($jamMasukSetting)->diffInMinutes(Carbon::createFromTimeString($att->time_in), false);
                            if ($diff > 0) $totalMenitTerlambat += $diff;
                        }
                        break;
                    case 'izin':
                        $totalIzin++;
                        $totalMenitKerja += $fullWorkdayMinutes;
                        break;
                    case 'sakit':
                        $totalSakit++;
                        $totalMenitKerja += $fullWorkdayMinutes;
                        break;
                    case 'cuti':
                        $totalCuti++;
                        $totalMenitKerja += $fullWorkdayMinutes;
                        break;
                    case 'alpha':
                        $totalAlpha++;
                        break;
                }
            }

            $totalJamKerjaBersih = round($totalMenitKerja / 60, 1);

            $reportData[] = [
                'nik'                   => $user->nik,
                'name'                  => $user->name,
                'department'            => $user->department ?? '-',
                'total_hadir'           => $totalHadir,
                'total_terlambat'       => $totalTerlambat,
                'total_menit_terlambat' => $totalMenitTerlambat,
                'total_izin'            => $totalIzin,
                'total_sakit'           => $totalSakit,
                'total_cuti'            => $totalCuti,
                'total_alpha'           => $totalAlpha,
                'total_jam_kerja'       => $totalJamKerjaBersih,
            ];
        }

        return $reportData;
    }

    private static function calculateDailyWorkMinutes(Attendance $att, string $jamPulangSetting, int $jamIstirahatMenit): int
    {
        if (!$att->time_in) return 0;
        $in = Carbon::createFromTimeString($att->time_in);
        $out = $att->time_out ? Carbon::createFromTimeString($att->time_out) : Carbon::createFromTimeString($jamPulangSetting);

        $grossMinutes = $in->diffInMinutes($out);
        $netMinutes = max(0, $grossMinutes - $jamIstirahatMenit);
        return $netMinutes;
    }
}
```

### Langkah 3: Controller Laporan & Generator Excel/PDF
Target: `app/Http/Controllers/Admin/ReportController.php`

1. Method `exportExcel()`: Menggunakan PhpSpreadsheet membangun file `.xlsx`.
2. Method `exportPdf()`: Merender view Blade `admin.reports.pdf_template` via DomPDF.

---

## 4. Kriteria Penerimaan (Definition of Done)

- [ ] Laporan menghitung total jam kerja bersih, keterlambatan, dan cuti secara tepat.
- [ ] Export Excel menghasilkan file `.xlsx` valid yang dapat dibuka di MS Excel / Google Sheets tanpa error corrupt.
- [ ] Export PDF menghasilkan dokumen `.pdf` siap cetak dengan layout A4 Landscape dan kop resmi perusahaan.
- [ ] Filter tanggal dan departemen diterapkan pada output dokumen ekspor.
