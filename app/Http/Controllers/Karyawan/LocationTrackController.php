<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LocationTrack;
use App\Models\Setting;
use App\Services\GeofenceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LocationTrackController extends Controller
{
    /**
     * Menampilkan halaman visualisasi "Perjalanan Hari Ini" untuk SPG/karyawan.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $today = Carbon::today('Asia/Makassar')->toDateString();

        $todayAttendance = Attendance::with('location')
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $tracks = LocationTrack::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->orderBy('recorded_at', 'asc')
            ->get();

        $trackingActive = $todayAttendance && $todayAttendance->time_in && !$todayAttendance->time_out;

        return view('karyawan.tracking', compact('user', 'todayAttendance', 'tracks', 'trackingActive', 'today'));
    }

    /**
     * Mengecek status pelacakan lokasi real-time karyawan.
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();
        $today = Carbon::today('Asia/Makassar')->toDateString();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $isActive = (bool) ($todayAttendance && $todayAttendance->time_in && !$todayAttendance->time_out);
        $intervalMinutes = (int) Setting::getValue('tracking_interval_minutes', '5');
        $maxAccuracy = (float) Setting::getValue('tracking_max_accuracy', '100');

        return response()->json([
            'success'          => true,
            'tracking_active'  => $isActive,
            'interval_minutes' => max(1, $intervalMinutes),
            'max_accuracy'     => $maxAccuracy,
            'attendance_id'    => $todayAttendance?->id,
            'server_time'      => Carbon::now('Asia/Makassar')->toIso8601String(),
        ]);
    }

    /**
     * Menerima koordinat lokasi periodik (ping) dari PWA karyawan.
     */
    public function ping(Request $request, GeofenceService $geofenceService): JsonResponse
    {
        $validated = $request->validate([
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'accuracy'    => 'nullable|numeric|min:0',
            'recorded_at' => 'nullable|string',
        ]);

        $user = Auth::user();
        $today = Carbon::today('Asia/Makassar')->toDateString();

        // 1. Verifikasi Status Sesi Kerja (Hanya aktif jika sudah check-in dan belum check-out)
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$todayAttendance || !$todayAttendance->time_in || $todayAttendance->time_out) {
            return response()->json([
                'success'         => false,
                'tracking_active' => false,
                'saved'           => false,
                'message'         => 'Pelacakan lokasi tidak aktif (belum presensi masuk atau sudah presensi pulang).',
            ], 200);
        }

        $maxAccuracy = (float) Setting::getValue('tracking_max_accuracy', '100');

        // 2. Filter Akurasi GPS
        if (isset($validated['accuracy']) && $validated['accuracy'] > $maxAccuracy) {
            return response()->json([
                'success'         => true,
                'tracking_active' => true,
                'saved'           => false,
                'message'         => "Akurasi GPS ({$validated['accuracy']}m) melampaui batas toleransi ({$maxAccuracy}m), titik dilewati.",
            ]);
        }

        // 3. Throttle Posisi Stasioner (< 5 meter dari titik terakhir yang terekam hari ini)
        $lastTrack = LocationTrack::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->orderByDesc('recorded_at')
            ->first();

        if ($lastTrack) {
            $distFromLast = $geofenceService->calculateDistance(
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                (float) $lastTrack->latitude,
                (float) $lastTrack->longitude
            );

            if ($distFromLast < 5.0) {
                return response()->json([
                    'success'         => true,
                    'tracking_active' => true,
                    'saved'           => false,
                    'distance_moved'  => $distFromLast,
                    'message'         => 'Perangkat berada dalam radius stasioner (< 5m), titik dilewati untuk efisiensi.',
                ]);
            }
        }

        // 4. Simpan Titik Jejak Lokasi
        $recordedAt = !empty($validated['recorded_at'])
            ? Carbon::parse($validated['recorded_at'])
            : Carbon::now('Asia/Makassar');

        $track = LocationTrack::create([
            'user_id'       => $user->id,
            'attendance_id' => $todayAttendance->id,
            'date'          => $today,
            'latitude'      => (float) $validated['latitude'],
            'longitude'     => (float) $validated['longitude'],
            'accuracy'      => isset($validated['accuracy']) ? (float) $validated['accuracy'] : null,
            'recorded_at'   => $recordedAt,
        ]);

        return response()->json([
            'success'         => true,
            'tracking_active' => true,
            'saved'           => true,
            'track_id'        => $track->id,
            'recorded_at'     => $track->recorded_at->toIso8601String(),
            'message'         => 'Koordinat lokasi operasional berhasil dicatat.',
        ]);
    }

    /**
     * Mengembalikan data riwayat titik lokasi hari berjalan untuk karyawan yang sedang login (JSON).
     */
    public function today(): JsonResponse
    {
        $user = Auth::user();
        $today = Carbon::today('Asia/Makassar')->toDateString();

        $todayAttendance = Attendance::with('location')
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $tracks = LocationTrack::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->orderBy('recorded_at', 'asc')
            ->get(['id', 'latitude', 'longitude', 'accuracy', 'recorded_at']);

        return response()->json([
            'success'         => true,
            'date'            => $today,
            'tracking_active' => (bool) ($todayAttendance && $todayAttendance->time_in && !$todayAttendance->time_out),
            'attendance'      => $todayAttendance,
            'total_points'    => $tracks->count(),
            'tracks'          => $tracks,
        ]);
    }
}
