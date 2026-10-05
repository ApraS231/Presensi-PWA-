@extends('layouts.pwa')

@section('title', 'Perjalanan Hari Ini - Presensi PT. CAK')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #today-tracking-map {
        width: 100%;
        height: 380px;
        border-radius: 20px;
        z-index: 1;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--md-sys-color-outline-variant);
    }

    .tracking-pulse-dot {
        width: 12px;
        height: 12px;
        background-color: var(--md-custom-color-success);
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 rgba(46, 125, 50, 0.4);
        animation: pulseAnimation 1.6s infinite;
    }

    @keyframes pulseAnimation {
        0% {
            box-shadow: 0 0 0 0 rgba(46, 125, 50, 0.6);
        }
        70% {
            box-shadow: 0 0 0 8px rgba(46, 125, 50, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(46, 125, 50, 0);
        }
    }
</style>
@endpush

@section('content')
<div style="display: flex; flex-direction: column; gap: 14px;">

    <!-- Header Section -->
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
        <div>
            <div class="clay-pill-tag" style="margin-bottom: 4px;">
                <span class="material-symbols-rounded" style="font-size: 13px;">route</span>
                <span>Pemantauan SPG Keliling</span>
            </div>
            <h2 class="font-editorial-serif" style="font-size: 22px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0; line-height: 1.2;">
                Perjalanan Hari Ini
            </h2>
            <div style="font-size: 12px; color: var(--md-sys-color-outline); margin-top: 2px;">
                {{ \Carbon\Carbon::parse($today)->isoFormat('dddd, D MMMM Y') }}
            </div>
        </div>

        <div>
            @if($trackingActive)
                <span class="clay-pill-tag" style="background: rgba(46, 125, 50, 0.12); color: var(--md-custom-color-success); border-color: rgba(46, 125, 50, 0.3); font-weight: 700;">
                    <span class="tracking-pulse-dot" style="margin-right: 4px;"></span>
                    <span>Pelacakan Aktif</span>
                </span>
            @else
                <span class="clay-pill-tag" style="background: var(--md-sys-color-surface-container-high); color: var(--md-sys-color-outline);">
                    <span class="material-symbols-rounded" style="font-size: 14px;">location_off</span>
                    <span>Pelacakan Nonaktif</span>
                </span>
            @endif
        </div>
    </div>

    <!-- Status Alert Notice -->
    @if(!$todayAttendance || !$todayAttendance->time_in)
        <div class="clay-card-cloudy" style="padding: 14px 16px;">
            <div style="display: flex; align-items: flex-start; gap: 10px;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--color-ocean-blue); margin-top: 1px;">info</span>
                <div style="font-size: 12.5px; line-height: 1.4; color: var(--md-sys-color-on-surface);">
                    <strong>Anda belum melakukan presensi masuk hari ini.</strong><br>
                    Pelacakan lokasi operasional SPG akan otomatis dimulai setelah Anda berhasil melakukan presensi masuk.
                </div>
            </div>
        </div>
    @elseif($todayAttendance->time_out)
        <div class="clay-card" style="padding: 14px 16px; border-left: 4px solid var(--color-ocean-blue);">
            <div style="display: flex; align-items: flex-start; gap: 10px;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--color-ocean-blue); margin-top: 1px;">task_alt</span>
                <div style="font-size: 12.5px; line-height: 1.4; color: var(--md-sys-color-on-surface);">
                    <strong>Sesi kerja hari ini telah selesai.</strong><br>
                    Presensi pulang tercatat pada pukul {{ \Carbon\Carbon::parse($todayAttendance->time_out)->format('H:i') }} WITA. Pelacakan rute perjalanan telah dihentikan.
                </div>
            </div>
        </div>
    @endif

    <!-- Interactive Route Map Container -->
    <div class="clay-card" style="padding: 12px; position: relative;">
        <div id="today-tracking-map"></div>

        <div style="margin-top: 10px; display: flex; align-items: center; justify-content: space-between; font-size: 11.5px; color: var(--md-sys-color-outline);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="display: flex; align-items: center; gap: 4px;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #2E7D32; display: inline-block;"></span>
                    Titik Awal
                </span>
                <span style="display: flex; align-items: center; gap: 4px;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #2872A1; display: inline-block;"></span>
                    Posisi Terkini
                </span>
            </div>

            <button type="button" class="clay-btn clay-btn-cloudy" onclick="reloadTodayTracks()" style="height: 28px; padding: 0 10px; font-size: 11px; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">sync</span>
                <span>Perbarui Peta</span>
            </button>
        </div>
    </div>

    <!-- Summary Metrics Card -->
    <div class="clay-card" style="padding: 14px 16px;">
        <div style="font-size: 13px; font-weight: 700; color: var(--md-sys-color-on-surface); margin-bottom: 10px;" class="font-editorial-grotesk">
            Ringkasan Operasional Hari Ini
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); text-align: center; gap: 4px;">
            <div style="padding: 4px;">
                <div style="font-size: 10.5px; color: var(--md-sys-color-outline); font-weight: 600;">Jarak Tempuh</div>
                <div id="statDistance" class="font-editorial-grotesk" style="font-size: 16px; font-weight: 800; color: var(--color-ocean-blue); margin-top: 2px;">
                    0.00 km
                </div>
            </div>

            <div style="padding: 4px; border-left: 1px solid var(--md-sys-color-outline-variant);">
                <div style="font-size: 10.5px; color: var(--md-sys-color-outline); font-weight: 600;">Titik Jejak</div>
                <div id="statPoints" class="font-editorial-grotesk" style="font-size: 16px; font-weight: 800; color: var(--md-sys-color-on-surface); margin-top: 2px;">
                    {{ $tracks->count() }}
                </div>
            </div>

            <div style="padding: 4px; border-left: 1px solid var(--md-sys-color-outline-variant);">
                <div style="font-size: 10.5px; color: var(--md-sys-color-outline); font-weight: 600;">Mulai Kerja</div>
                <div class="font-editorial-grotesk" style="font-size: 14px; font-weight: 700; color: var(--md-custom-color-success); margin-top: 3px;">
                    {{ $todayAttendance?->time_in ? \Carbon\Carbon::parse($todayAttendance->time_in)->format('H:i') : '--:--' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Notice Card -->
    <div class="clay-inset-box" style="padding: 12px 14px; font-size: 11.5px; color: var(--md-sys-color-outline); line-height: 1.4;">
        <span class="material-symbols-rounded" style="font-size: 15px; vertical-align: text-bottom; color: var(--color-ocean-blue); margin-right: 3px;">schedule</span>
        Data jejak lokasi pada halaman ini akan otomatis direset setiap pergantian hari. Riwayat bulanan (siklus 25-25) terekam secara aman dan dapat dipantau oleh Admin/HRD.
    </div>

</div>
@endsection

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    let map = null;
    let polylineLayer = null;
    let markersLayer = null;

    document.addEventListener('DOMContentLoaded', () => {
        initMap();
        loadTodayTracksData();
    });

    function initMap() {
        // Koordinat default (Samarinda / Kaltim)
        const defaultLat = -0.502106;
        const defaultLng = 117.153709;

        map = L.map('today-tracking-map', {
            zoomControl: true,
            attributionControl: false
        }).setView([defaultLat, defaultLng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        markersLayer = L.featureGroup().addTo(map);
    }

    function calculateDistanceMeters(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    async function loadTodayTracksData() {
        try {
            const response = await fetch("{{ route('karyawan.tracking.today') }}", {
                headers: {
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();

            if (!data.success) return;

            renderTracksOnMap(data.tracks);
        } catch (error) {
            console.error('Gagal memuat data jejak rute hari ini:', error);
        }
    }

    function renderTracksOnMap(tracks) {
        if (!map) return;

        markersLayer.clearLayers();
        if (polylineLayer) {
            map.removeLayer(polylineLayer);
            polylineLayer = null;
        }

        if (!tracks || tracks.length === 0) {
            document.getElementById('statDistance').textContent = '0.00 km';
            document.getElementById('statPoints').textContent = '0';
            return;
        }

        document.getElementById('statPoints').textContent = tracks.length;

        const latLngs = [];
        let totalDistanceMeters = 0;

        tracks.forEach((point, index) => {
            const lat = parseFloat(point.latitude);
            const lng = parseFloat(point.longitude);
            latLngs.push([lat, lng]);

            if (index > 0) {
                const prev = tracks[index - 1];
                totalDistanceMeters += calculateDistanceMeters(
                    parseFloat(prev.latitude),
                    parseFloat(prev.longitude),
                    lat,
                    lng
                );
            }
        });

        const totalKm = (totalDistanceMeters / 1000).toFixed(2);
        document.getElementById('statDistance').textContent = `${totalKm} km`;

        // 1. Gambar Polyline Jejak
        polylineLayer = L.polyline(latLngs, {
            color: '#2872A1',
            weight: 5,
            opacity: 0.85,
            lineJoin: 'round',
            dashArray: '1, 8'
        }).addTo(map);

        // 2. Marker Titik Awal (Hijau)
        const startPoint = tracks[0];
        const startIcon = L.divIcon({
            html: '<div style="background:#2E7D32; color:#fff; border-radius:50%; width:24px; height:24px; display:flex; align-items:center; justify-content:center; border:2px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.3); font-size:12px; font-weight:bold;">A</div>',
            className: '',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
        const startMarker = L.marker([startPoint.latitude, startPoint.longitude], { icon: startIcon })
            .bindPopup(`<b>Titik Awal (Check-In)</b><br>Waktu: ${new Date(startPoint.recorded_at).toLocaleTimeString('id-ID')}`);
        markersLayer.addLayer(startMarker);

        // 3. Marker Posisi Terkini / Akhir (Ocean Blue)
        if (tracks.length > 1) {
            const endPoint = tracks[tracks.length - 1];
            const endIcon = L.divIcon({
                html: '<div style="background:#2872A1; color:#fff; border-radius:50%; width:24px; height:24px; display:flex; align-items:center; justify-content:center; border:2px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.3); font-size:12px; font-weight:bold;">B</div>',
                className: '',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });
            const endMarker = L.marker([endPoint.latitude, endPoint.longitude], { icon: endIcon })
                .bindPopup(`<b>Posisi Terkini</b><br>Waktu: ${new Date(endPoint.recorded_at).toLocaleTimeString('id-ID')}`);
            markersLayer.addLayer(endMarker);
        }

        // Fit map bounds
        map.fitBounds(polylineLayer.getBounds(), { padding: [30, 30] });
    }

    function reloadTodayTracks() {
        loadTodayTracksData();
    }
</script>
@endpush
