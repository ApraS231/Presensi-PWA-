<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();
        $totalKaryawan = User::where('role', 'karyawan')->count();
        $totalLocations = Location::count();
        $totalSettings = Setting::count();

        return view('superadmin.dashboard', compact(
            'totalUsers',
            'totalKaryawan',
            'totalLocations',
            'totalSettings'
        ));
    }
}
