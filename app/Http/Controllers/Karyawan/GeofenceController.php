<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Services\GeofenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeofenceController extends Controller
{
    /**
     * Memverifikasi koordinat GPS pengguna terhadap titik geofence aktif secara real-time.
     */
    public function verify(Request $request, GeofenceService $geofenceService): JsonResponse
    {
        $validated = $request->validate([
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'location_id' => 'nullable|integer|exists:locations,id',
        ], [
            'latitude.required'  => 'Koordinat latitude wajib dikirimkan.',
            'longitude.required' => 'Koordinat longitude wajib dikirimkan.',
        ]);

        $result = $geofenceService->validateCoordinates(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $validated['location_id'] ?? null
        );

        return response()->json([
            'success'         => true,
            'is_valid'        => $result['is_valid'],
            'distance_meters' => $result['distance_meters'],
            'location'        => $result['location'] ? [
                'id'            => $result['location']->id,
                'name'          => $result['location']->name,
                'latitude'      => $result['location']->latitude,
                'longitude'     => $result['location']->longitude,
                'radius_meters' => $result['location']->radius_meters,
            ] : null,
            'message'         => $result['message'],
        ]);
    }
}
