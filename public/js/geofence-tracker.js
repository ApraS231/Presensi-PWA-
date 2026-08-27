/**
 * Client-Side Geofence Tracker
 * PT. Cahaya Anugrah Kalimantan - Sistem Presensi PWA
 */

class GeofenceTracker {
    static EARTH_RADIUS_METERS = 6371000;

    /**
     * Mengambil koordinat GPS presisi tinggi dari perangkat pengguna.
     * @returns {Promise<{latitude: number, longitude: number, accuracy: number}>}
     */
    static async getCurrentCoordinates() {
        return new Promise((resolve, reject) => {
            if (!('geolocation' in navigator)) {
                reject(new Error('Perangkat Anda tidak mendukung fitur Geolocation GPS.'));
                return;
            }

            const options = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            };

            navigator.geolocation.getCurrentPosition(
                position => {
                    resolve({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    });
                },
                error => {
                    let msg = 'Gagal mendapatkan lokasi GPS.';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            msg = 'Izin akses lokasi GPS ditolak oleh pengguna.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            msg = 'Sinyal satelit GPS tidak tersedia saat ini.';
                            break;
                        case error.TIMEOUT:
                            msg = 'Waktu permintaan lokasi GPS habis (Timeout).';
                            break;
                    }
                    reject(new Error(msg));
                },
                options
            );
        });
    }

    /**
     * Menghitung jarak geodesic client-side (Haversine) untuk preview visual UI.
     */
    static calculateDistanceClient(lat1, lon1, lat2, lon2) {
        const toRad = deg => (deg * Math.PI) / 180;
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);

        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);

        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return Math.round(GeofenceTracker.EARTH_RADIUS_METERS * c * 100) / 100;
    }

    /**
     * Melakukan verifikasi koordinat ke server (Double Haversine Validation).
     */
    static async verifyWithServer(latitude, longitude, targetLocationId = null, csrfToken = '') {
        const payload = { latitude, longitude };
        if (targetLocationId) {
            payload.location_id = targetLocationId;
        }

        const response = await fetch('/karyawan/geofence/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        });

        return await response.json();
    }
}

window.GeofenceTracker = GeofenceTracker;
