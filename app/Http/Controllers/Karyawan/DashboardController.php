<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $today = Carbon::today();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        return view('karyawan.dashboard', compact('user', 'todayAttendance'));
    }
}
