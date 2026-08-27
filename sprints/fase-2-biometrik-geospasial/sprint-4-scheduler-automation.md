# Sprint 2.4 - Otomasi Scheduler: Auto-Checkout & Auto-Alpha

- **Fase:** 2 (Biometrik Wajah & Geofencing Core)
- **Estimasi Durasi:** 2 Hari
- **Prasyarat:** Sprint 1.1 (Tabel attendances & settings) & Sprint 2.3 (Transaksi Presensi)

---

## 1. Kebutuhan Fungsional (FR)

- `FR-SCHED-01`: Artisan Command `presensi:daily-scheduler` yang dijalankan setiap hari pada pukul **23:00 WITA**.
- `FR-SCHED-02`: **Proses Auto-Checkout**:
  - Mencari seluruh karyawan aktif yang telah memiliki `time_in` namun field `time_out` masih bernilai `NULL` pada hari tersebut.
  - Memperbarui `time_out` dengan nilai `jam_pulang` dari tabel `settings` (default: 17:00).
  - Menandai flag boolean `auto_checkout = true` pada baris `attendances` tersebut untuk kebutuhan audit.
- `FR-SCHED-03`: **Proses Auto-Alpha**:
  - Mencari seluruh karyawan aktif (`users.is_active = true` dan `role = 'karyawan'`) yang:
    1. Tidak memiliki record pada tabel `attendances` pada tanggal hari tersebut.
    2. Tidak memiliki pengajuan perizinan dengan status `approved` pada tanggal tersebut di tabel `leaves`.
  - Membuat baris baru di tabel `attendances` dengan nilai `status = 'alpha'`.
- `FR-SCHED-04`: Penjadwalan otomatis terdaftar pada Laravel Task Scheduler.

---

## 2. Kebutuhan Non-Fungsional (NFR)

- `NFR-REL-01`: Eksekusi tugas scheduler idempotent dan aman dijalankan ulang tanpa merusak atau menduplikasi data yang sudah tercatat.
- `NFR-DATA-01`: Seluruh transaksi bulk insert/update dibungkus dalam Database Transaction untuk menjaga konsistensi data.

---

## 3. Langkah-Langkah Dekomposisi Teknis

### Langkah 1: Artisan Command Scheduler
Target: `app/Console/Commands/DailyAttendanceScheduler.php`

```php
namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DailyAttendanceScheduler extends Command
{
    protected $signature = 'presensi:daily-scheduler {date?}';
    protected $description = 'Otomasi auto-checkout dan generate record alpha harian';

    public function handle()
    {
        $targetDate = $this->argument('date') ? Carbon::parse($this->argument('date')) : Carbon::today();
        $this->info("Menjalankan scheduler presensi untuk tanggal: " . $targetDate->toDateString());

        DB::transaction(function () use ($targetDate) {
            $this->handleAutoCheckout($targetDate);
            $this->handleAutoAlpha($targetDate);
        });

        $this->info("Scheduler presensi selesai dieksekusi.");
    }

    protected function handleAutoCheckout(Carbon $date)
    {
        $jamPulang = Setting::getValue('jam_pulang', '17:00:00');

        $affected = Attendance::where('date', $date)
            ->whereNotNull('time_in')
            ->whereNull('time_out')
            ->update([
                'time_out'      => $jamPulang,
                'auto_checkout' => true,
            ]);

        $this->info("Auto-checkout selesai. {$affected} record diperbarui.");
    }

    protected function handleAutoAlpha(Carbon $date)
    {
        $activeEmployees = User::where('role', 'karyawan')
            ->where('is_active', true)
            ->get();

        $alphaCount = 0;

        foreach ($activeEmployees as $employee) {
            // Cek apakah sudah ada record presensi
            $hasAttendance = Attendance::where('user_id', $employee->id)
                ->where('date', $date)
                ->exists();

            if ($hasAttendance) {
                continue;
            }

            // Cek apakah ada perizinan approved
            $hasApprovedLeave = Leave::where('user_id', $employee->id)
                ->where('status', 'approved')
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->exists();

            if ($hasApprovedLeave) {
                continue;
            }

            // Buat record alpha
            Attendance::create([
                'user_id' => $employee->id,
                'date'    => $date,
                'status'  => 'alpha',
            ]);

            $alphaCount++;
        }

        $this->info("Auto-alpha selesai. {$alphaCount} karyawan tercatat alpha.");
    }
}
```

### Langkah 2: Registrasi Task Schedule di Laravel 11
Target: `routes/console.php`

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('presensi:daily-scheduler')
    ->dailyAt('23:00')
    ->timezone('Asia/Makassar')
    ->withoutOverlapping();
```

---

## 4. Kriteria Penerimaan (Definition of Done)

- [ ] Perintah `php artisan presensi:daily-scheduler` berhasil dijalankan via terminal.
- [ ] Record yang hanya memiliki `time_in` otomatis terisi `time_out = 17:00` dan `auto_checkout = true`.
- [ ] Karyawan yang tidak hadir dan tidak memiliki izin approved otomatis tercatat sebagai `alpha` di tabel `attendances`.
- [ ] Karyawan yang sedang cuti/izin/sakit yang sudah di-approve tidak dijadikan record alpha.
- [ ] Scheduler terdaftar dalam `php artisan schedule:list`.
