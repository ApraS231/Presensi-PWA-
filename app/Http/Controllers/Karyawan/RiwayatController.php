<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RiwayatController extends Controller
{
    /**
     * Menampilkan riwayat kehadiran mandiri karyawan.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $selectedMonth = (int) $request->query('month', Carbon::now('Asia/Makassar')->month);
        $selectedYear  = (int) $request->query('year', Carbon::now('Asia/Makassar')->year);

        // 1. Data Presensi Bulan Terpilih
        $attendances = Attendance::with('location')
            ->where('user_id', $user->id)
            ->whereMonth('date', $selectedMonth)
            ->whereYear('date', $selectedYear)
            ->orderByDesc('date')
            ->paginate(15);

        // 2. Rekapitulasi Statistik Bulanan
        $allMonthAttendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $selectedMonth)
            ->whereYear('date', $selectedYear)
            ->get();

        $totalTepatWaktu = $allMonthAttendances->where('status', 'tepat_waktu')->count();
        $totalTerlambat  = $allMonthAttendances->where('status', 'terlambat')->count();
        $totalIzinCuti   = $allMonthAttendances->whereIn('status', ['izin', 'sakit', 'cuti'])->count();
        $totalAlpha      = $allMonthAttendances->where('status', 'alpha')->count();

        // 3. Opsi Bulan dan Tahun
        $months = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $currentYear = Carbon::now('Asia/Makassar')->year;
        $years = range($currentYear - 2, $currentYear);

        return view('karyawan.riwayat', compact(
            'attendances',
            'selectedMonth',
            'selectedYear',
            'totalTepatWaktu',
            'totalTerlambat',
            'totalIzinCuti',
            'totalAlpha',
            'months',
            'years'
        ));
    }
}
