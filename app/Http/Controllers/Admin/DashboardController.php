<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $totalEmployees = User::where('role', 'karyawan')->where('is_active', true)->count();
        $attendances = Attendance::where('date', $today)->get();

        $hadirTepatWaktu = $attendances->where('status', 'tepat_waktu')->count();
        $hadirTerlambat  = $attendances->where('status', 'terlambat')->count();
        $totalIzin       = $attendances->whereIn('status', ['izin', 'sakit', 'cuti'])->count();
        $totalAlpha      = $attendances->where('status', 'alpha')->count();

        $pendingEnrollmentCount = User::where('role', 'karyawan')
            ->where('is_active', true)
            ->where('enrollment_status', 'pending')
            ->count();

        return view('admin.dashboard', compact(
            'totalEmployees',
            'hadirTepatWaktu',
            'hadirTerlambat',
            'totalIzin',
            'totalAlpha',
            'pendingEnrollmentCount'
        ));
    }
}
