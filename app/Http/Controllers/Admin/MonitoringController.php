<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    /**
     * Menampilkan dashboard monitoring kehadiran harian HRD.
     */
    public function index(Request $request): View
    {
        $targetDate = $request->query('date') 
            ? Carbon::parse($request->query('date')) 
            : Carbon::today('Asia/Makassar');
        $formattedDate = $targetDate->format('Y-m-d');

        $department = $request->query('department');
        $status = $request->query('status');

        // 1. Total Karyawan Aktif
        $employeesQuery = User::where('role', 'karyawan')->where('is_active', true);
        if ($department) {
            $employeesQuery->where('department', $department);
        }
        $totalEmployees = $employeesQuery->count();

        // 2. Query Data Presensi
        $attendancesQuery = Attendance::with(['user', 'location'])
            ->where('date', $formattedDate)
            ->whereHas('user', function ($q) use ($department) {
                $q->where('role', 'karyawan')->where('is_active', true);
                if ($department) {
                    $q->where('department', $department);
                }
            });

        // 3. Agregasi Statistik Real-Time
        $allDayAttendances = (clone $attendancesQuery)->get();
        $tepatWaktuCount = $allDayAttendances->where('status', 'tepat_waktu')->count();
        $terlambatCount  = $allDayAttendances->where('status', 'terlambat')->count();
        $izinCount       = $allDayAttendances->whereIn('status', ['izin', 'sakit', 'cuti'])->count();
        $alphaCount      = $allDayAttendances->where('status', 'alpha')->count();
        $totalHadir      = $tepatWaktuCount + $terlambatCount;
        $belumHadirCount = max(0, $totalEmployees - ($totalHadir + $izinCount + $alphaCount));

        $persentaseKehadiran = $totalEmployees > 0 
            ? round(($totalHadir / $totalEmployees) * 100, 1) 
            : 0;

        // 4. Filter Status jika ada
        if ($status) {
            if ($status === 'izin_cuti') {
                $attendancesQuery->whereIn('status', ['izin', 'sakit', 'cuti']);
            } else {
                $attendancesQuery->where('status', $status);
            }
        }

        $attendances = $attendancesQuery->orderByDesc('time_in')->paginate(20);

        // 5. Karyawan Belum Enrollment Biometrik
        $pendingEnrollmentCount = User::where('role', 'karyawan')
            ->where('is_active', true)
            ->where('enrollment_status', 'pending')
            ->count();

        // 6. Daftar Departemen
        $departments = User::where('role', 'karyawan')
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        // 7. Data Geospasial untuk Widget Peta Dashboard
        $activeLocations = Location::active()->get();
        $mappedAttendances = $allDayAttendances->whereNotNull('lat_in')->whereNotNull('long_in')->values();

        return view('admin.dashboard', compact(
            'targetDate',
            'formattedDate',
            'department',
            'status',
            'departments',
            'totalEmployees',
            'tepatWaktuCount',
            'terlambatCount',
            'izinCount',
            'alphaCount',
            'belumHadirCount',
            'persentaseKehadiran',
            'pendingEnrollmentCount',
            'attendances',
            'activeLocations',
            'mappedAttendances'
        ));
    }

    /**
     * Menampilkan halaman visualisasi peta interaktif Live Map Leaflet.js.
     */
    public function liveMap(Request $request): View
    {
        $targetDate = $request->query('date') 
            ? Carbon::parse($request->query('date')) 
            : Carbon::today('Asia/Makassar');
        $formattedDate = $targetDate->format('Y-m-d');

        $department = $request->query('department');
        $locationId = $request->query('location_id');
        $status = $request->query('status');

        $activeLocations = Location::active()->get();

        $query = Attendance::with(['user', 'location'])
            ->where('date', $formattedDate)
            ->whereNotNull('lat_in')
            ->whereNotNull('long_in')
            ->whereHas('user', function ($q) use ($department) {
                $q->where('role', 'karyawan')->where('is_active', true);
                if ($department) {
                    $q->where('department', $department);
                }
            });

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $mappedAttendances = $query->orderByDesc('time_in')->get();

        $departments = User::where('role', 'karyawan')
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('admin.map.index', compact(
            'targetDate',
            'formattedDate',
            'department',
            'locationId',
            'status',
            'departments',
            'activeLocations',
            'mappedAttendances'
        ));
    }

    /**
     * Endpoint API JSON untuk polling pembaruan real-time (interval 30 detik).
     */
    public function data(Request $request): JsonResponse
    {
        $targetDate = $request->query('date') 
            ? Carbon::parse($request->query('date')) 
            : Carbon::today('Asia/Makassar');
        $formattedDate = $targetDate->format('Y-m-d');

        $department = $request->query('department');

        $employeesQuery = User::where('role', 'karyawan')->where('is_active', true);
        if ($department) {
            $employeesQuery->where('department', $department);
        }
        $totalEmployees = $employeesQuery->count();

        $attendances = Attendance::where('date', $formattedDate)
            ->whereHas('user', function ($q) use ($department) {
                $q->where('role', 'karyawan')->where('is_active', true);
                if ($department) {
                    $q->where('department', $department);
                }
            })
            ->get();

        $tepatWaktuCount = $attendances->where('status', 'tepat_waktu')->count();
        $terlambatCount  = $attendances->where('status', 'terlambat')->count();
        $izinCount       = $attendances->whereIn('status', ['izin', 'sakit', 'cuti'])->count();
        $alphaCount      = $attendances->where('status', 'alpha')->count();
        $totalHadir      = $tepatWaktuCount + $terlambatCount;
        $belumHadirCount = max(0, $totalEmployees - ($totalHadir + $izinCount + $alphaCount));

        $persentaseKehadiran = $totalEmployees > 0 
            ? round(($totalHadir / $totalEmployees) * 100, 1) 
            : 0;

        $pendingEnrollmentCount = User::where('role', 'karyawan')
            ->where('is_active', true)
            ->where('enrollment_status', 'pending')
            ->count();

        return response()->json([
            'success'                => true,
            'date'                   => $formattedDate,
            'total_employees'        => $totalEmployees,
            'tepat_waktu'            => $tepatWaktuCount,
            'terlambat'              => $terlambatCount,
            'izin'                   => $izinCount,
            'alpha'                  => $alphaCount,
            'belum_hadir'            => $belumHadirCount,
            'persentase_kehadiran'   => $persentaseKehadiran,
            'pending_enrollment'     => $pendingEnrollmentCount,
        ]);
    }
}
