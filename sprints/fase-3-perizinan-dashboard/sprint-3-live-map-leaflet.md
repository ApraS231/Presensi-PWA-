# Sprint 3.3 - Visualisasi Live Map Leaflet.js

- **Fase:** 3 (Perizinan, Dashboard Monitoring & Live Map)
- **Estimasi Durasi:** 3 Hari
- **Prasyarat:** Sprint 2.3 (Presensi dengan koordinat) & Sprint 3.2 (Monitoring Controller)

---

## 1. Kebutuhan Fungsional (FR)

- `FR-MAP-01`: Integrasi library peta interaktif **Leaflet.js** dengan tile server **OpenStreetMap** (100% gratis tanpa API key berbayar).
- `FR-MAP-02`: **Visualisasi Marker Presensi Karyawan**:
  - Mapping koordinat aktual presensi masuk (`lat_in`, `long_in`) ke layer peta.
  - Marker berwarna sesuai status: Hijau (Tepat Waktu), Oranye (Terlambat).
  - Popup interaktif pada marker: Foto snapshot thumbnail, Nama Karyawan, Departemen, Jam Masuk, Jarak ke Kantor.
- `FR-MAP-03`: **Visualisasi Area Geofence (Circle Polygon)**:
  - Render lingkaran radius geofence (`radius_meters`) pada setiap titik kantor atau proyek aktif PT. CAK dengan transparansi warna M3 Primary.
  - Pusat lingkaran ditandai dengan ikon kantor/proyek.
- `FR-MAP-04`: Sinkronisasi posisi peta otomatis (Auto-fit Bounds) agar mencakup seluruh sebaran marker karyawan dan kantor dalam satu tampilan layar.

---

## 2. Kebutuhan Non-Fungsional (NFR)

- `NFR-PERF-01`: Rendering peta responsif dan lancar (smooth 60fps zooming and panning) hingga 500 marker presensi simultan (memanfaatkan Marker Clustering jika data padat).
- `NFR-UX-01`: Kontrol peta (Zoom in/out, Reset View, Filter Layer) mengikuti ergonomi antarmuka modern.

---

## 3. Langkah-Langkah Dekomposisi Teknis

### Langkah 1: Setup Aset Leaflet.js
Target: `public/js/` dan `public/css/`

1. Load CSS & JS Leaflet via CDN / aset lokal di `resources/views/layouts/admin.blade.php`:
   ```html
   <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
   <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
   ```

### Langkah 2: Modul Script Live Map Leaflet
Target: `public/js/leaflet-map.js`

```javascript
function initAttendanceLiveMap(mapElementId, locationsData, attendancesData) {
    // 1. Inisialisasi Peta
    const defaultCenter = locationsData.length > 0
        ? [locationsData[0].latitude, locationsData[0].longitude]
        : [-0.1333, 117.4833]; // Default Bontang / Kaltim

    const map = L.map(mapElementId).setView(defaultCenter, 13);

    // 2. Tile Layer OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    const bounds = [];

    // 3. Render Geofence Circle untuk setiap Lokasi Kantor/Proyek
    locationsData.forEach(loc => {
        const center = [loc.latitude, loc.longitude];
        bounds.push(center);

        // Geofence Circle
        L.circle(center, {
            color: '#1565C0',
            fillColor: '#D4E3FF',
            fillOpacity: 0.35,
            radius: loc.radius_meters
        }).addTo(map).bindPopup(`<b>${loc.name}</b><br>Radius: ${loc.radius_meters}m`);

        // Center Pin
        L.marker(center).addTo(map).bindPopup(`<b>${loc.name}</b>`);
    });

    // 4. Render Marker Presensi Karyawan
    attendancesData.forEach(att => {
        if (!att.lat_in || !att.long_in) return;

        const pos = [att.lat_in, att.long_in];
        bounds.push(pos);

        const statusColor = att.status === 'tepat_waktu' ? '#2E7D32' : '#E65100';

        const customIcon = L.divIcon({
            className: 'custom-attendance-marker',
            html: `<div style="background-color: ${statusColor}; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
            iconSize: [14, 14],
            iconAnchor: [7, 7]
        });

        const popupContent = `
            <div style="font-family: Inter, sans-serif; font-size: 13px; line-height: 1.4;">
                <div style="font-weight: 600; color: #1C1B1F;">${att.user.name}</div>
                <div style="color: #546E7A; font-size: 11px;">${att.user.department ?? 'Staff'}</div>
                <hr style="margin: 6px 0; border: 0; border-top: 1px solid #E2E2E6;">
                <div>Jam Masuk: <b>${att.time_in} WITA</b></div>
                <div>Status: <span style="color: ${statusColor}; font-weight: 600;">${att.status.toUpperCase()}</span></div>
                <div>Jarak: ${att.distance_meters}m</div>
            </div>
        `;

        L.marker(pos, { icon: customIcon }).addTo(map).bindPopup(popupContent);
    });

    // 5. Auto Fit Bounds
    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [40, 40] });
    }
}
```

### Langkah 3: Integrasi Data JSON ke View Blade
Target: `resources/views/admin/dashboard.blade.php` atau `monitoring.blade.php`

- Container `#attendance-map` dengan tinggi 450px ber-radius M3.
- Mengirimkan variabel PHP `$locationsJson` dan `$attendancesJson` ke JavaScript initialization.

---

## 4. Kriteria Penerimaan (Definition of Done)

- [ ] Peta OpenStreetMap berhasil dimuat tanpa error autentikasi / token API.
- [ ] Seluruh lingkaran radius geofence kantor/proyek tergambar presisi di atas peta.
- [ ] Posisi presensi karyawan muncul sebagai marker dengan pembedaan warna status (Hijau vs Oranye).
- [ ] Klik pada marker menampilkan popup detail identitas, jam masuk, dan foto.
- [ ] Peta otomatis menyesuaikan tingkat zoom (fit bounds) mencakup seluruh titik presensi.
