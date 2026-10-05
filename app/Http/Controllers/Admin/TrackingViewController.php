<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LocationTrack;
use App\Models\User;
use App\Services\GeofenceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackingViewController extends Controller
{
    /**
     * Menampilkan dashboard pemantauan operasional SPG / Karyawan Keliling.
     */
    public function index(Request $request): View
    {
        // 1. Tentukan Periode Bulanan Siklus 25-25
        $availablePeriods = $this->getAvailablePeriods();
        $selectedPeriod = $request->query('period', $this->getDefaultPeriodKey());

        [$startDate, $endDate, $periodLabel] = $this->getPeriodDates($selectedPeriod);

        $department = $request->query('department');
        $search = $request->query('search');
        $selectedDate = $request->query('date', Carbon::today('Asia/Makassar')->toDateString());

        // 2. Query Daftar Karyawan / SPG
        $employeesQuery = User::where('role', 'karyawan')->where('is_active', true);

        if ($department) {
            $employeesQuery->where('department', $department);
        }

        if ($search) {
            $employeesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $employees = $employeesQuery->orderBy('name')->get();

        // 3. Agregasi Statistik Pelacakan Per Karyawan Dalam Periode 25-25
        $tracksInPeriod = LocationTrack::whereBetween('date', [$startDate, $endDate])
            ->whereIn('user_id', $employees->pluck('id'))
            ->get();

        $geofenceService = app(GeofenceService::class);

        $trackingSummaries = [];
        foreach ($employees as $emp) {
            $empTracks = $tracksInPeriod->where('user_id', $emp->id);
            $activeDaysCount = $empTracks->pluck('date')->unique()->count();
            $totalPoints = $empTracks->count();

            // Hitung estimasi jarak tempuh kumulatif periode
            $totalDistanceMeters = 0;
            $tracksByDate = $empTracks->groupBy('date');

            foreach ($tracksByDate as $dayTracks) {
                $sorted = $dayTracks->sortBy('recorded_at')->values();
                for ($i = 0; $i < count($sorted) - 1; $i++) {
                    $totalDistanceMeters += $geofenceService->calculateDistance(
                        $sorted[$i]->latitude,
                        $sorted[$i]->longitude,
                        $sorted[$i + 1]->latitude,
                        $sorted[$i + 1]->longitude
                    );
                }
            }

            $lastTrack = $empTracks->sortByDesc('recorded_at')->first();

            $trackingSummaries[$emp->id] = [
                'user'                => $emp,
                'active_days'         => $activeDaysCount,
                'total_points'        => $totalPoints,
                'total_distance_km'   => round($totalDistanceMeters / 1000, 2),
                'last_recorded_at'    => $lastTrack?->recorded_at,
                'last_latitude'       => $lastTrack?->latitude,
                'last_longitude'      => $lastTrack?->longitude,
            ];
        }

        // 4. Daftar Departemen untuk Filter
        $departments = User::where('role', 'karyawan')
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        $isSuperAdmin = $request->routeIs('superadmin.*');

        return view('admin.tracking.index', compact(
            'availablePeriods',
            'selectedPeriod',
            'periodLabel',
            'startDate',
            'endDate',
            'department',
            'search',
            'selectedDate',
            'employees',
            'trackingSummaries',
            'departments',
            'isSuperAdmin'
        ));
    }

    /**
     * API JSON: Mengembalikan detail rute jejak perjalanan (trail) karyawan pada tanggal tertentu.
     */
    public function trail(Request $request, User $user, GeofenceService $geofenceService): JsonResponse
    {
        $date = $request->query('date', Carbon::today('Asia/Makassar')->toDateString());

        $attendance = Attendance::with('location')
            ->where('user_id', $user->id)
            ->whereDate('date', $date)
            ->first();

        $tracks = LocationTrack::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->orderBy('recorded_at', 'asc')
            ->get();

        $totalDistanceMeters = 0;
        for ($i = 0; $i < count($tracks) - 1; $i++) {
            $totalDistanceMeters += $geofenceService->calculateDistance(
                $tracks[$i]->latitude,
                $tracks[$i]->longitude,
                $tracks[$i + 1]->latitude,
                $tracks[$i + 1]->longitude
            );
        }

        $durationMinutes = 0;
        if ($tracks->count() >= 2) {
            $first = $tracks->first()->recorded_at;
            $last = $tracks->last()->recorded_at;
            $durationMinutes = $first->diffInMinutes($last);
        }

        return response()->json([
            'success'           => true,
            'user'              => [
                'id'         => $user->id,
                'name'       => $user->name,
                'nik'        => $user->nik,
                'department' => $user->department,
                'jabatan'    => $user->jabatan,
            ],
            'date'              => $date,
            'attendance'        => $attendance ? [
                'time_in'         => $attendance->time_in,
                'time_out'        => $attendance->time_out,
                'status'          => $attendance->status,
                'location_name'   => $attendance->location?->name,
                'distance_meters' => $attendance->distance_meters,
            ] : null,
            'total_points'      => $tracks->count(),
            'total_distance_km' => round($totalDistanceMeters / 1000, 2),
            'duration_minutes'  => $durationMinutes,
            'points'            => $tracks->map(function ($t) {
                return [
                    'id'          => $t->id,
                    'lat'         => (float) $t->latitude,
                    'lng'         => (float) $t->longitude,
                    'accuracy'    => (float) $t->accuracy,
                    'time'        => $t->recorded_at->format('H:i:s'),
                    'recorded_at' => $t->recorded_at->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * Menghitung rentang tanggal siklus 25-25 untuk kunci periode "YYYY-MM".
     *
     * Contoh: Periode "2026-08" -> 25 Juli 2026 s.d. 25 Agustus 2026.
     *
     * @return array{0: string, 1: string, 2: string} [startDate, endDate, label]
     */
    public function getPeriodDates(string $periodKey): array
    {
        $parts = explode('-', $periodKey);
        $year = isset($parts[0]) ? (int) $parts[0] : (int) date('Y');
        $month = isset($parts[1]) ? (int) $parts[1] : (int) date('m');

        $endDate = Carbon::create($year, $month, 25, 23, 59, 59, 'Asia/Makassar');
        $startDate = (clone $endDate)->subMonth()->day(25)->startOfDay();

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $startMonthName = $monthNames[$startDate->month];
        $endMonthName = $monthNames[$endDate->month];

        $label = "Periode {$endMonthName} {$endDate->year} (25 {$startMonthName} - 25 {$endMonthName})";

        return [
            $startDate->toDateString(),
            $endDate->toDateString(),
            $label,
        ];
    }

    /**
     * Menentukan kunci periode default (25-25) berdasarkan tanggal hari ini.
     */
    public function getDefaultPeriodKey(): string
    {
        $today = Carbon::today('Asia/Makassar');

        // Jika hari ini tanggal >= 26, periode aktif adalah bulan depan (misal: 28 Agustus -> Periode September)
        if ($today->day >= 26) {
            $next = (clone $today)->addMonth();
            return $next->format('Y-m');
        }

        // Jika hari ini tanggal 1-25, periode aktif adalah bulan ini
        return $today->format('Y-m');
    }

    /**
     * Menghasilkan daftar pilihan periode bulanan (6 bulan terakhir hingga 1 bulan ke depan).
     */
    public function getAvailablePeriods(): array
    {
        $periods = [];
        $current = Carbon::today('Asia/Makassar')->addMonth();

        for ($i = 0; $i < 8; $i++) {
            $key = $current->format('Y-m');
            [$start, $end, $label] = $this->getPeriodDates($key);
            $periods[$key] = $label;
            $current->subMonth();
        }

        return $periods;
    }
}
