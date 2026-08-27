<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Setting;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCheckoutAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-checkout {--date= : Tanggal presensi dalam format Y-m-d}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menutup sesi presensi yang tidak melakukan check-out dengan jam pulang default pada pukul 23:59 WITA';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $dateStr = $this->option('date');
        $targetDate = $dateStr ? Carbon::parse($dateStr) : Carbon::today('Asia/Makassar');
        $formattedDate = $targetDate->format('Y-m-d');

        $this->info("Menjalankan penutupan otomatis sesi presensi menggantung untuk tanggal: {$formattedDate}");

        $jamPulangSetting = Setting::getValue('jam_pulang', '17:00');
        // Pastikan format H:i:s
        $defaultTimeOut = strlen($jamPulangSetting) === 5 ? "{$jamPulangSetting}:00" : $jamPulangSetting;

        // Ambil presensi yang check-in ada namun check-out masih null
        $hangingAttendances = Attendance::where('date', $formattedDate)
            ->whereNotNull('time_in')
            ->whereNull('time_out')
            ->with('user')
            ->get();

        $closedCount = 0;

        foreach ($hangingAttendances as $attendance) {
            $attendance->update([
                'time_out'      => $defaultTimeOut,
                'auto_checkout' => true,
            ]);

            $closedCount++;

            // Kirim notifikasi ke karyawan
            if ($attendance->user) {
                $notificationService->send(
                    $attendance->user,
                    'Sesi Presensi Ditutup Otomatis',
                    "Presensi Anda pada tanggal {$targetDate->isoFormat('D MMMM Y')} telah ditutup otomatis pada pukul {$defaultTimeOut} WITA karena tidak melakukan presensi pulang.",
                    'reminder_presensi'
                );
            }
        }

        $this->info("Selesai: {$closedCount} sesi presensi berhasil ditutup otomatis.");

        return Command::SUCCESS;
    }
}
