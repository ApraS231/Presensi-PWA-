<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

class ReportCalculationService
{
    /**
     * Menghitung ringkasan rekapitulasi kehadiran dan jam kerja karyawan.
     */
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

        $fullWorkdayMinutes = max(0, Carbon::createFromTimeString($jamMasukSetting)
            ->diffInMinutes(Carbon::createFromTimeString($jamPulangSetting)) - $jamIstirahatMenit);

        $reportData = [];

        foreach ($users as $user) {
            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();

            $totalHadirTepatWaktu = 0;
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
                        $totalHadirTepatWaktu++;
                        $totalMenitKerja += self::calculateDailyWorkMinutes($att, $jamPulangSetting, $jamIstirahatMenit);
                        break;

                    case 'terlambat':
                        $totalTerlambat++;
                        $totalMenitKerja += self::calculateDailyWorkMinutes($att, $jamPulangSetting, $jamIstirahatMenit);
                        if ($att->time_in) {
                            $diff = Carbon::createFromTimeString($jamMasukSetting)->diffInMinutes(Carbon::createFromTimeString($att->time_in), false);
                            if ($diff > 0) {
                                $totalMenitTerlambat += $diff;
                            }
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

            $totalHadir = $totalHadirTepatWaktu + $totalTerlambat;
            $totalJamKerjaBersih = round($totalMenitKerja / 60, 1);

            $reportData[] = [
                'user_id'                 => $user->id,
                'nik'                     => $user->nik,
                'name'                    => $user->name,
                'department'              => $user->department ?? '-',
                'total_hadir_tepat_waktu' => $totalHadirTepatWaktu,
                'total_terlambat'         => $totalTerlambat,
                'total_hadir'             => $totalHadir,
                'total_menit_terlambat'   => (int) round($totalMenitTerlambat),
                'total_izin'              => $totalIzin,
                'total_sakit'             => $totalSakit,
                'total_cuti'              => $totalCuti,
                'total_alpha'             => $totalAlpha,
                'total_jam_kerja'         => $totalJamKerjaBersih,
            ];
        }

        return $reportData;
    }

    /**
     * Menghitung menit kerja bersih per hari.
     */
    private static function calculateDailyWorkMinutes(Attendance $att, string $jamPulangSetting, int $jamIstirahatMenit): int
    {
        if (!$att->time_in) {
            return 0;
        }

        $in = Carbon::createFromTimeString($att->time_in);
        $out = $att->time_out 
            ? Carbon::createFromTimeString($att->time_out) 
            : Carbon::createFromTimeString($jamPulangSetting);

        $grossMinutes = $in->diffInMinutes($out);
        return max(0, $grossMinutes - $jamIstirahatMenit);
    }
}
