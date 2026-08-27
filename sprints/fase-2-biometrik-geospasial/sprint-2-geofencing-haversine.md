# Sprint 2.2 - Geolokasi & Geofencing Haversine Ganda

- **Fase:** 2 (Biometrik Wajah & Geofencing Core)
- **Estimasi Durasi:** 3 Hari
- **Prasyarat:** Sprint 1.1 (Tabel locations) & Sprint 2.1 (Aset JS)

---

## 1. Kebutuhan Fungsional (FR)

- `FR-GEO-01`: Pembacaan titik koordinat perangkat mobile secara real-time via HTML5 Geolocation API (Latitude, Longitude, Accuracy).
- `FR-GEO-02`: Penanganan kesalahan izin GPS (Graceful GPS Error Handling):
  - Deteksi izin ditolak, sinyal GPS timeout, atau lokasi tidak akurat.
  - Tampilan modal instruksi pengaktifan GPS presisi tinggi + tombol "Coba Lagi" (maks 3 kali).
- `FR-GEO-03`: **Kalkulasi Geofencing Sisi Klien (Client-Side UX)**:
  - Mengambil daftar titik lokasi kantor/proyek aktif dari backend.
  - Menghitung jarak terdekat ke salah satu lokasi menggunakan formula Haversine di JavaScript.
  - Memberikan feedback visual instan:
    - Dalam radius: Tombol "Buka Kamera / Lanjut Presensi" aktif.
    - Luar radius: Tombol dinonaktifkan + pesan *"Anda berada di luar area presensi (jarak: X meter dari kantor)"*.
- `FR-GEO-04`: **Validasi Ulang Geofencing Sisi Server (Server-Side Security Validation)**:
  - Service backend `HaversineService` memverifikasi ulang payload koordinat GPS dari klien terhadap database `locations`.
  - Keputusan validasi presensi ditentukan secara final oleh backend (Anti-Spoofing & Anti-Client-Manipulation).

---

## 2. Kebutuhan Non-Fungsional (NFR)

- `NFR-SEC-01`: Integritas data geospasial wajib diverifikasi ganda (Double Validation). Data koordinat yang dikirim oleh request klien tidak boleh dipercaya secara mentah tanpa kalkulasi ulang di server.
- `NFR-ACC-01`: Efektivitas Geofencing 100% — presensi dengan koordinat di luar radius toleransi lokasi kantor/proyek harus ditolak secara otomatis oleh backend.
- `NFR-PERF-01`: Komputasi formula Haversine server-side di bawah 5 milidetik untuk menjaga responsivitas API.

---

## 3. Langkah-Langkah Dekomposisi Teknis

### Langkah 1: Backend Service Formula Haversine
Target: `app/Services/HaversineService.php`

```php
namespace App\Services;

class HaversineService
{
    private const EARTH_RADIUS_METERS = 6371000;

    /**
     * Menghitung jarak dalam meter antara dua titik koordinat.
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * asin(sqrt($a));

        return round(self::EARTH_RADIUS_METERS * $c, 2);
    }

    /**
     * Memeriksa apakah koordinat karyawan berada dalam radius salah satu lokasi aktif.
     * Mengembalikan array status validitas, objek lokasi terdekat, dan jaraknya.
     */
    public static function validateLocation(float $userLat, float $userLon, $locations): array
    {
        $minDistance = INF;
        $matchedLocation = null;

        foreach ($locations as $location) {
            $dist = self::calculateDistance($userLat, $userLon, $location->latitude, $location->longitude);
            if ($dist < $minDistance) {
                $minDistance = $dist;
                $matchedLocation = $location;
            }
        }

        $isValid = ($matchedLocation !== null) && ($minDistance <= $matchedLocation->radius_meters);

        return [
            'is_valid'         => $isValid,
            'location'         => $matchedLocation,
            'distance_meters'  => $minDistance,
        ];
    }
}
```

### Langkah 2: Formula Haversine & Geolocation Sisi Klien
Target: `public/js/geofencing.js`

1. Fungsi akuisisi koordinat HTML5 Geolocation:
   ```javascript
   function getCurrentGPSPosition() {
       return new Promise((resolve, reject) => {
           if (!navigator.geolocation) {
               reject(new Error('Geolocation tidak didukung oleh browser Anda.'));
           }
           navigator.geolocation.getCurrentPosition(
               position => resolve({
                   latitude: position.coords.latitude,
                   longitude: position.coords.longitude,
                   accuracy: position.coords.accuracy
               }),
               error => reject(error),
               { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
           );
       });
   }
   ```
2. Formula Haversine JS:
   ```javascript
   function haversineJS(lat1, lon1, lat2, lon2) {
       const R = 6371000;
       const dLat = (lat2 - lat1) * Math.PI / 180;
       const dLon = (lon2 - lon1) * Math.PI / 180;
       const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                 Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                 Math.sin(dLon/2) * Math.sin(dLon/2);
       const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
       return Math.round(R * c);
   }
   ```
3. Fetch data lokasi aktif dari `/api/locations/active` dan bandingkan dengan posisi GPS pengguna.

---

## 4. Kriteria Penerimaan (Definition of Done)

- [ ] HTML5 Geolocation berhasil membaca latitude dan longitude perangkat mobile.
- [ ] Penolakan izin lokasi memunculkan instruksi panduan dan tombol retry.
- [ ] Klien menampilkan status jarak aktual secara dinamis (contoh: "Jarak ke kantor: 25 meter - Dalam radius").
- [ ] Tombol presensi otomatis nonaktif jika jarak melebihi `radius_meters`.
- [ ] `HaversineService` di server berhasil melakukan unit test kalkulasi jarak dengan deviasi < 1 meter terhadap standar geospasial.
- [ ] Request presensi dengan payload lokasi manipulasi di luar radius ditolak oleh server dengan status HTTP 422 Unprocessable Entity.
