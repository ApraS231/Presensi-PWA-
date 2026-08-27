<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Setting;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDailyAlpha extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-alpha {--date= : Tanggal presensi dalam format Y-m-d}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengevaluasi ketidakhadiran karyawan harian dan menetapkan status Alpha / Sinkronisasi Cuti pada pukul 18:00 WITA';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $dateStr = $this->option('date');
        $targetDate = $dateStr ? Carbon::parse($dateStr) : Carbon::today('Asia/Makassar');
        $formattedDate = $targetDate->format('Y-m-d');

        $this->info("Menjalankan evaluasi kehadiran untuk tanggal: {$formattedDate}");

        // 1. Verifikasi Hari Kerja Aktif
        $dayNames = [
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
            7 => 'minggu',
        ];

        $currentDayName = $dayNames[$targetDate->dayOfWeekIso] ?? 'senin';
        $activeDaysSetting = Setting::getValue('hari_kerja', 'senin,selasa,rabu,kamis,jumat');
        $activeDays = array_map('trim', explode(',', strtolower($activeDaysSetting)));

        if (!in_array($currentDayName, $activeDays)) {
            $this->warn("Tanggal {$formattedDate} ({$currentDayName}) bukan merupakan hari kerja aktif ({$activeDaysSetting}). Evaluasi Alpha dilewati.");
            return Command::SUCCESS;
        }

        // 2. Ambil Seluruh Karyawan Aktif
        $employees = User::where('role', 'karyawan')
            ->where('is_active', true)
            ->get();

        $alphaCount = 0;
        $leaveSyncCount = 0;
        $skippedCount = 0;

        foreach ($employees as $employee) {
            $existingAttendance = Attendance::where('user_id', $employee->id)
                ->where('date', $formattedDate)
                ->first();

            // Jika karyawan sudah presensi masuk (tepat_waktu / terlambat)
            if ($existingAttendance && $existingAttendance->time_in) {
                $skippedCount++;
                continue;
            }

            // Jika record sudah disinkronkan sebelumnya (idempotency check)
            if ($existingAttendance && in_array($existingAttendance->status, ['izin', 'sakit', 'cuti', 'alpha'])) {
                $skippedCount++;
                continue;
            }

            // 3. Cek Pengajuan Izin / Cuti / Sakit yang Approved
            $approvedLeave = Leave::where('user_id', $employee->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $formattedDate)
                ->whereDate('end_date', '>=', $formattedDate)
                ->first();

            if ($approvedLeave) {
                // Sinkronisasi status kehadiran sesuai jenis izin
                Attendance::updateOrCreate(
                    [
                        'user_id' => $employee->id,
                        'date'    => $formattedDate,
                    ],
                    [
                        'status' => $approvedLeave->type, // izin, sakit, cuti
                    ]
                );
                $leaveSyncCount++;
            } else {
                // Catat sebagai Alpha
                Attendance::updateOrCreate(
                    [
                        'user_id' => $employee->id,
                        'date'    => $formattedDate,
                    ],
                    [
                        'status' => 'alpha',
                    ]
                );
                $alphaCount++;

                // Kirim notifikasi peringatan ke karyawan
                $notificationService->send(
                    $employee,
                    'Ketidakhadiran Tercatat (Alpha)',
                    "Anda tercatat tidak hadir tanpa keterangan (Alpha) pada tanggal {$targetDate->isoFormat('D MMMM Y')}.",
                    'reminder_presensi'
                );
            }
        }

        $this->info("Selesai: {$alphaCount} karyawan Alpha, {$leaveSyncCount} cuti/izin disinkronkan, {$skippedCount} hadir/dilewati.");

        return Command::SUCCESS;
    }
}
