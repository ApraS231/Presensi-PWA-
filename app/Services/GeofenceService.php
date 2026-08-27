<?php

namespace App\Services;

use App\Models\Location;

class GeofenceService
{
    /**
     * Radius rata-rata bumi dalam satuan meter.
     */
    protected const EARTH_RADIUS_METERS = 6371000;

    /**
     * Menghitung jarak geodesic antara dua titik koordinat (latitude, longitude)
     * menggunakan rumus Haversine Formula murni.
     *
     * @return float Jarak dalam satuan meter (dibulatkan 2 desimal)
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        $a = sin($deltaLat / 2) ** 2 +
             cos($lat1Rad) * cos($lat2Rad) * (sin($deltaLon / 2) ** 2);

        $c = 2 * asin(min(1.0, sqrt($a)));

        return round(self::EARTH_RADIUS_METERS * $c, 2);
    }

    /**
     * Mencari titik lokasi kantor / proyek aktif terdekat dari koordinat pengguna.
     *
     * @return array{location: Location, distance_meters: float, within_radius: bool}|null
     */
    public function findNearestLocation(float $latitude, float $longitude, ?int $targetLocationId = null): ?array
    {
        $query = Location::active();

        if ($targetLocationId) {
            $query->where('id', $targetLocationId);
        }

        $locations = $query->get();

        if ($locations->isEmpty()) {
            return null;
        }

        $nearest = null;
        $minDistance = INF;

        foreach ($locations as $loc) {
            $distance = $this->calculateDistance($latitude, $longitude, $loc->latitude, $loc->longitude);

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $loc;
            }
        }

        if (!$nearest) {
            return null;
        }

        return [
            'location'        => $nearest,
            'distance_meters' => $minDistance,
            'within_radius'   => ($minDistance <= $nearest->radius_meters),
        ];
    }

    /**
     * Memvalidasi koordinat presensi dan mengembalikan struktur evaluasi lengkap.
     *
     * @return array{is_valid: bool, distance_meters: ?float, location: ?Location, message: string}
     */
    public function validateCoordinates(float $latitude, float $longitude, ?int $targetLocationId = null): array
    {
        $result = $this->findNearestLocation($latitude, $longitude, $targetLocationId);

        if (!$result) {
            return [
                'is_valid'        => false,
                'distance_meters' => null,
                'location'        => null,
                'message'         => 'Tidak ada titik lokasi kantor atau proyek aktif yang terdaftar di sistem.',
            ];
        }

        $location = $result['location'];
        $distance = $result['distance_meters'];
        $withinRadius = $result['within_radius'];

        if ($withinRadius) {
            return [
                'is_valid'        => true,
                'distance_meters' => $distance,
                'location'        => $location,
                'message'         => "Anda berada di dalam area presensi {$location->name} (Jarak: {$distance}m, Radius: {$location->radius_meters}m).",
            ];
        }

        return [
            'is_valid'        => false,
            'distance_meters' => $distance,
            'location'        => $location,
            'message'         => "Posisi Anda berada di luar area presensi {$location->name} (Jarak Anda: {$distance}m, Maksimal radius: {$location->radius_meters}m).",
        ];
    }
}
