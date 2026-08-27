<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Location;
use App\Models\Setting;
use App\Services\GeofenceService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Menampilkan antarmuka pemindai presensi real-time.
     */
    public function index(): View
    {
        $user = Auth::user();
        $today = Carbon::today('Asia/Makassar')->toDateString();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // Tentukan mode presensi
        $mode = 'check_in';
        if ($todayAttendance) {
            if ($todayAttendance->time_in && !$todayAttendance->time_out) {
                $mode = 'check_out';
            } elseif ($todayAttendance->time_in && $todayAttendance->time_out) {
                $mode = 'completed';
            }
        }

        $descriptor = $user->faceDescriptor?->descriptor_data ?? [];
        $activeLocations = Location::active()->get();

        $jamMasuk = Setting::getValue('jam_masuk', '08:00');
        $jamPulang = Setting::getValue('jam_pulang', '17:00');
        $toleransi = (int) Setting::getValue('toleransi_terlambat', '15');

        return view('karyawan.presensi', compact(
            'user',
            'todayAttendance',
            'mode',
            'descriptor',
            'activeLocations',
            'jamMasuk',
            'jamPulang',
            'toleransi'
        ));
    }

    /**
     * Memproses transaksi Presensi Masuk (Check-In).
     */
    public function checkIn(
        Request $request,
        GeofenceService $geofenceService,
        NotificationService $notificationService
    ): JsonResponse {
        $validated = $request->validate([
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'photo'       => 'required|string',
            'location_id' => 'nullable|integer|exists:locations,id',
        ]);

        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();

        // 1. Verifikasi Anti-Duplikasi Harian
        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing && $existing->time_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan presensi masuk untuk hari ini.',
            ], 422);
        }

        // 2. Validasi Geospasial Server-Side (Double Haversine)
        $geoResult = $geofenceService->validateCoordinates(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $validated['location_id'] ?? null
        );

        if (!$geoResult['is_valid'] || !$geoResult['location']) {
            return response()->json([
                'success' => false,
                'message' => $geoResult['message'],
            ], 422);
        }

        // 3. Evaluasi Status Kehadiran (Tepat Waktu vs Terlambat)
        $jamMasukStr = Setting::getValue('jam_masuk', '08:00');
        $toleransiMenit = (int) Setting::getValue('toleransi_terlambat', '15');

        $cutoffTime = Carbon::createFromTimeString($jamMasukStr)->addMinutes($toleransiMenit);
        $currentTime = Carbon::createFromTimeString($now->format('H:i:s'));

        $status = $currentTime->greaterThan($cutoffTime) ? 'terlambat' : 'tepat_waktu';

        // 4. Simpan Berkas Foto Snapshot Masuk
        $photoData = $validated['photo'];
        if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $type)) {
            $photoData = substr($photoData, strpos($photoData, ',') + 1);
            $extension = strtolower($type[1]);
        } else {
            $extension = 'jpg';
        }

        $decodedImage = base64_decode($photoData);
        if ($decodedImage === false) {
            return response()->json([
                'success' => false,
                'message' => 'Format foto snapshot tidak valid.',
            ], 422);
        }

        $fileName = "attendance/{$user->id}_in_" . time() . ".{$extension}";
        Storage::disk('public')->put($fileName, $decodedImage);

        // 5. Eksekusi Atomic Database Transaction
        DB::transaction(function () use ($user, $today, $now, $geoResult, $validated, $fileName, $status) {
            Attendance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date'    => $today,
                ],
                [
                    'location_id'     => $geoResult['location']->id,
                    'time_in'         => $now->format('H:i:s'),
                    'lat_in'          => $validated['latitude'],
                    'long_in'         => $validated['longitude'],
                    'photo_in'        => $fileName,
                    'status'          => $status,
                    'distance_meters' => $geoResult['distance_meters'],
                ]
            );
        });

        // 6. Kirim Notifikasi Konfirmasi
        $statusLabel = $status === 'tepat_waktu' ? 'Tepat Waktu' : 'Terlambat';
        $notificationService->send(
            $user,
            'Presensi Masuk Berhasil',
            "Presensi masuk Anda telah tercatat pada pukul {$now->format('H:i')} WITA di {$geoResult['location']->name} dengan status: {$statusLabel}.",
            'reminder_presensi'
        );

        return response()->json([
            'success'  => true,
            'message'  => "Presensi masuk berhasil dicatat ({$statusLabel})!",
            'status'   => $status,
            'redirect' => route('karyawan.dashboard'),
        ]);
    }

    /**
     * Memproses transaksi Presensi Pulang (Check-Out).
     */
    public function checkOut(
        Request $request,
        GeofenceService $geofenceService,
        NotificationService $notificationService
    ): JsonResponse {
        $validated = $request->validate([
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'photo'       => 'required|string',
            'location_id' => 'nullable|integer|exists:locations,id',
        ]);

        $user = Auth::user();
        $today = Carbon::today('Asia/Makassar')->toDateString();
        $now = Carbon::now('Asia/Makassar');

        // 1. Verifikasi Status Check-In Hari Ini
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || !$attendance->time_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan presensi masuk untuk hari ini.',
            ], 422);
        }

        if ($attendance->time_out) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah menyelesaikan presensi pulang hari ini.',
            ], 422);
        }

        // 2. Validasi Geospasial Server-Side (Double Haversine)
        $geoResult = $geofenceService->validateCoordinates(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $validated['location_id'] ?? null
        );

        if (!$geoResult['is_valid'] || !$geoResult['location']) {
            return response()->json([
                'success' => false,
                'message' => $geoResult['message'],
            ], 422);
        }

        // 3. Simpan Berkas Foto Snapshot Pulang
        $photoData = $validated['photo'];
        if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $type)) {
            $photoData = substr($photoData, strpos($photoData, ',') + 1);
            $extension = strtolower($type[1]);
        } else {
            $extension = 'jpg';
        }

        $decodedImage = base64_decode($photoData);
        if ($decodedImage === false) {
            return response()->json([
                'success' => false,
                'message' => 'Format foto snapshot tidak valid.',
            ], 422);
        }

        $fileName = "attendance/{$user->id}_out_" . time() . ".{$extension}";
        Storage::disk('public')->put($fileName, $decodedImage);

        // 4. Update Transaksi Pulang
        $attendance->update([
            'time_out'  => $now->format('H:i:s'),
            'lat_out'   => $validated['latitude'],
            'long_out'  => $validated['longitude'],
            'photo_out' => $fileName,
        ]);

        // 5. Kirim Notifikasi Konfirmasi
        $notificationService->send(
            $user,
            'Presensi Pulang Berhasil',
            "Presensi pulang Anda telah tercatat pada pukul {$now->format('H:i')} WITA. Selamat beristirahat!",
            'reminder_presensi'
        );

        return response()->json([
            'success'  => true,
            'message'  => 'Presensi pulang berhasil dicatat. Terima kasih atas dedikasi Anda hari ini!',
            'redirect' => route('karyawan.dashboard'),
        ]);
    }
}
