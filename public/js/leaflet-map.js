/**
 * Leaflet Live Map Integration Module
 * PT. Cahaya Anugrah Kalimantan - Sistem Presensi PWA
 */

function initAttendanceLiveMap(mapElementId, locationsData, attendancesData, options = {}) {
    const mapEl = document.getElementById(mapElementId);
    if (!mapEl) return null;

    // 1. Tentukan Titik Tengah Default
    const defaultCenter = locationsData && locationsData.length > 0
        ? [locationsData[0].latitude, locationsData[0].longitude]
        : [-0.1333, 117.4833]; // Bontang / Kalimantan Timur

    const map = L.map(mapElementId).setView(defaultCenter, options.zoom || 13);

    // 2. OpenStreetMap Tile Layer (100% Bebas API Key)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    const bounds = [];

    // 3. Render Area Geofence Lingkaran & Pin Kantor / Proyek
    if (Array.isArray(locationsData)) {
        locationsData.forEach(loc => {
            const center = [parseFloat(loc.latitude), parseFloat(loc.longitude)];
            bounds.push(center);

            // Geofence Circle
            L.circle(center, {
                color: '#1565C0',
                weight: 2,
                fillColor: '#6750A4',
                fillOpacity: 0.18,
                radius: parseInt(loc.radius_meters, 10)
            }).addTo(map).bindPopup(`
                <div style="font-family: Inter, sans-serif; font-size: 13px;">
                    <div style="font-weight: 700; color: #1565C0;">🏢 ${loc.name}</div>
                    <div style="font-size: 11px; color: #546E7A; margin-top: 2px;">Radius Geofence: <b>${loc.radius_meters} Meter</b></div>
                </div>
            `);

            // Office Marker
            const officeIcon = L.divIcon({
                className: 'custom-office-pin',
                html: `
                    <div style="background-color: #1565C0; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3); border: 2px solid white;">
                        <span style="font-size: 16px;">🏢</span>
                    </div>
                `,
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });

            L.marker(center, { icon: officeIcon }).addTo(map).bindPopup(`
                <div style="font-family: Inter, sans-serif; font-size: 13px;">
                    <div style="font-weight: 700; color: #1565C0;">${loc.name}</div>
                    <div style="font-size: 11px; color: #546E7A;">Titik Tengah Presensi</div>
                </div>
            `);
        });
    }

    // 4. Render Pin Presensi Karyawan
    if (Array.isArray(attendancesData)) {
        attendancesData.forEach(att => {
            if (!att.lat_in || !att.long_in) return;

            const pos = [parseFloat(att.lat_in), parseFloat(att.long_in)];
            bounds.push(pos);

            const statusColor = att.status === 'tepat_waktu' ? '#2E7D32' : '#E65100';
            const statusLabel = att.status === 'tepat_waktu' ? 'Tepat Waktu' : 'Terlambat';

            const employeeIcon = L.divIcon({
                className: 'custom-employee-pin',
                html: `
                    <div style="background-color: ${statusColor}; width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.35);"></div>
                `,
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });

            const photoHtml = att.photo_in
                ? `<img src="/storage/${att.photo_in}" style="width: 100%; height: 110px; object-fit: cover; border-radius: 6px; margin-bottom: 8px; border: 1px solid #E2E2E6;">`
                : '';

            const popupHtml = `
                <div style="font-family: Inter, sans-serif; font-size: 13px; min-width: 190px; line-height: 1.4;">
                    ${photoHtml}
                    <div style="font-weight: 700; color: #1C1B1F; font-size: 14px;">${att.user ? att.user.name : 'Karyawan'}</div>
                    <div style="font-size: 11px; color: #546E7A;">${att.user ? (att.user.department || 'Staff') : ''} (NIK: ${att.user ? att.user.nik : '-'})</div>
                    <hr style="margin: 6px 0; border: 0; border-top: 1px solid #E2E2E6;">
                    <div>Jam Masuk: <b>${att.time_in || '--:--'} WITA</b></div>
                    <div>Status: <span style="color: ${statusColor}; font-weight: 700;">${statusLabel}</span></div>
                    ${att.distance_meters !== null && att.distance_meters !== undefined ? `<div>Jarak ke Kantor: <b>${att.distance_meters}m</b></div>` : ''}
                </div>
            `;

            L.marker(pos, { icon: employeeIcon }).addTo(map).bindPopup(popupHtml);
        });
    }

    // 5. Sesuaikan Zoom dan Batas Wilayah Peta Otomatis (Auto-Fit Bounds)
    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 16 });
    }

    return map;
}

window.initAttendanceLiveMap = initAttendanceLiveMap;
