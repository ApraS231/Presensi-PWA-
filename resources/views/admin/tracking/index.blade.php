@extends('layouts.admin')

@section('title', 'Pemantauan SPG Keliling - Presensi PT. CAK')
@section('page_title', 'Pemantauan Jejak Operasional SPG')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    .tracking-filter-card {
        background-color: var(--md-sys-color-surface-container-low);
        border: 1px solid var(--md-sys-color-outline-variant);
        border-radius: var(--md-sys-shape-corner-large);
        padding: 20px 24px;
        margin-bottom: 24px;
    }

    .tracking-grid-layout {
        display: grid;
        grid-template-columns: 1.15fr 1fr;
        gap: 24px;
        align-items: start;
    }

    @media (max-width: 1100px) {
        .tracking-grid-layout {
            grid-template-columns: 1fr;
        }
    }

    #admin-tracking-map {
        width: 100%;
        height: 520px;
        border-radius: var(--md-sys-shape-corner-large);
        z-index: 1;
        border: 1px solid var(--md-sys-color-outline-variant);
    }

    .selected-spg-row {
        background-color: rgba(40, 114, 161, 0.08) !important;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<!-- Breadcrumbs Navigation -->
<div class="md-breadcrumb">
    <a href="{{ $isSuperAdmin ? route('superadmin.dashboard') : route('admin.dashboard') }}" class="md-breadcrumb-item">
        <span class="material-symbols-rounded" style="font-size: 16px;">home</span>
        <span>Dashboard</span>
    </a>
    <span class="md-breadcrumb-separator">/</span>
    <span class="md-breadcrumb-item active">Pemantauan SPG Keliling</span>
</div>

<!-- Header Description -->
<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font: var(--md-sys-typescale-headline-small); font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0;">
            Pemantauan Jejak Lapangan SPG (Live Tracking)
        </h2>
        <p style="font: var(--md-sys-typescale-body-medium); color: var(--md-sys-color-outline); margin: 4px 0 0 0;">
            Pemantauan rute perjalanan operasional SPG selama jam kerja aktif (Siklus Bulanan: Tanggal 25 s.d. 25 &bull; View-Only).
        </p>
    </div>

    <div style="display: flex; align-items: center; gap: 8px;">
        <span class="md-badge md-badge-approved" style="padding: 6px 12px; font-size: 12px;">
            <span class="material-symbols-rounded" style="font-size: 16px; margin-right: 4px;">visibility</span>
            Mode Pemantauan (View-Only)
        </span>
    </div>
</div>

<!-- Filter Bar Card (Siklus 25-25 Bulanan) -->
<div class="tracking-filter-card">
    <form method="GET" action="{{ $isSuperAdmin ? route('superadmin.tracking.index') : route('admin.tracking.index') }}" id="trackingFilterForm">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) auto; gap: 16px; align-items: flex-end;">
            
            <!-- Periode 25-25 -->
            <div>
                <label style="display: block; font: var(--md-sys-typescale-label-medium); font-weight: 600; color: var(--md-sys-color-on-surface); margin-bottom: 6px;">
                    Siklus Laporan Bulanan (25-25)
                </label>
                <select name="period" class="md-input" onchange="document.getElementById('trackingFilterForm').submit()">
                    @foreach($availablePeriods as $key => $lbl)
                        <option value="{{ $key }}" {{ $selectedPeriod == $key ? 'selected' : '' }}>
                            {{ $lbl }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Departemen -->
            <div>
                <label style="display: block; font: var(--md-sys-typescale-label-medium); font-weight: 600; color: var(--md-sys-color-on-surface); margin-bottom: 6px;">
                    Departemen
                </label>
                <select name="department" class="md-input" onchange="document.getElementById('trackingFilterForm').submit()">
                    <option value="">-- Semua Departemen --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ $department == $dept ? 'selected' : '' }}>
                            {{ $dept }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Pencarian Karyawan -->
            <div>
                <label style="display: block; font: var(--md-sys-typescale-label-medium); font-weight: 600; color: var(--md-sys-color-on-surface); margin-bottom: 6px;">
                    Cari Nama / NIK SPG
                </label>
                <input type="text" name="search" class="md-input" value="{{ $search }}" placeholder="Contoh: Budi / KAR001">
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="md-btn-filled" style="height: 44px;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">search</span>
                    <span>Filter</span>
                </button>
                <a href="{{ $isSuperAdmin ? route('superadmin.tracking.index') : route('admin.tracking.index') }}" class="md-btn-outlined" style="height: 44px; text-decoration: none;" title="Reset Filter">
                    <span class="material-symbols-rounded" style="font-size: 18px;">restart_alt</span>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Key Performance & Tracking KPI Metrics -->
@php
    $totalSPG = $employees->count();
    $activeSPG = collect($trackingSummaries)->filter(fn($s) => $s['total_points'] > 0)->count();
    $totalRecordedPoints = collect($trackingSummaries)->sum('total_points');
    $totalDistanceKm = collect($trackingSummaries)->sum('total_distance_km');
@endphp

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="md-card md-card-elevated" style="padding: 16px 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="font: var(--md-sys-typescale-label-medium); color: var(--md-sys-color-outline);">Total SPG Terdata</div>
                <div style="font-size: 26px; font-weight: 800; color: var(--md-sys-color-on-surface); margin-top: 4px;">{{ $totalSPG }}</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: var(--md-sys-color-primary-container); color: var(--md-sys-color-primary); display: flex; align-items: center; justify-content: center;">
                <span class="material-symbols-rounded">groups</span>
            </div>
        </div>
        <div style="font-size: 12px; color: var(--md-sys-color-outline); margin-top: 6px;">{{ $periodLabel }}</div>
    </div>

    <div class="md-card md-card-elevated" style="padding: 16px 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="font: var(--md-sys-typescale-label-medium); color: var(--md-sys-color-outline);">SPG Aktif Lapangan</div>
                <div style="font-size: 26px; font-weight: 800; color: var(--md-custom-color-success); margin-top: 4px;">{{ $activeSPG }}</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(46, 125, 50, 0.15); color: var(--md-custom-color-success); display: flex; align-items: center; justify-content: center;">
                <span class="material-symbols-rounded">person_pin_circle</span>
            </div>
        </div>
        <div style="font-size: 12px; color: var(--md-sys-color-outline); margin-top: 6px;">Tercatat jejak pada periode</div>
    </div>

    <div class="md-card md-card-elevated" style="padding: 16px 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="font: var(--md-sys-typescale-label-medium); color: var(--md-sys-color-outline);">Titik Jejak GPS</div>
                <div style="font-size: 26px; font-weight: 800; color: var(--color-ocean-blue); margin-top: 4px;">{{ number_format($totalRecordedPoints, 0, ',', '.') }}</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(40, 114, 161, 0.15); color: var(--color-ocean-blue); display: flex; align-items: center; justify-content: center;">
                <span class="material-symbols-rounded">timeline</span>
            </div>
        </div>
        <div style="font-size: 12px; color: var(--md-sys-color-outline); margin-top: 6px;">Interval tracking 5 menit</div>
    </div>

    <div class="md-card md-card-elevated" style="padding: 16px 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="font: var(--md-sys-typescale-label-medium); color: var(--md-sys-color-outline);">Est. Total Jarak Tempuh</div>
                <div style="font-size: 26px; font-weight: 800; color: var(--md-custom-color-warning); margin-top: 4px;">{{ number_format($totalDistanceKm, 1, ',', '.') }} <span style="font-size: 16px; font-weight: 600;">km</span></div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(239, 108, 0, 0.15); color: var(--md-custom-color-warning); display: flex; align-items: center; justify-content: center;">
                <span class="material-symbols-rounded">route</span>
            </div>
        </div>
        <div style="font-size: 12px; color: var(--md-sys-color-outline); margin-top: 6px;">Akumulasi seluruh SPG</div>
    </div>
</div>

<!-- Main Split Layout: Table of SPG (Left) + Interactive Map & Trail Inspector (Right) -->
<div class="tracking-grid-layout">

    <!-- Left Column: Table of SPG in Period -->
    <div class="md-card md-card-elevated" style="padding: 0; overflow: hidden;">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--md-sys-color-outline-variant); display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="font: var(--md-sys-typescale-title-medium); font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0;">
                    Daftar SPG Lapangan
                </h3>
                <span style="font-size: 12px; color: var(--md-sys-color-outline);">
                    Klik tombol "Lihat Rute" untuk memvisualisasikan rute pada peta
                </span>
            </div>
            <span class="md-badge md-badge-tepat-waktu" style="font-size: 11px;">
                {{ $employees->count() }} Karyawan
            </span>
        </div>

        <div style="overflow-x: auto;">
            <table class="md-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">SPG / NIK</th>
                        <th>Departemen</th>
                        <th style="text-align: center;">Hari Aktif</th>
                        <th style="text-align: center;">Titik Jejak</th>
                        <th style="text-align: right;">Est. Jarak</th>
                        <th style="text-align: center; padding-right: 20px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        @php
                            $sum = $trackingSummaries[$emp->id] ?? null;
                        @endphp
                        <tr id="spg-row-{{ $emp->id }}">
                            <td style="padding-left: 20px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--color-ocean-blue); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">
                                        {{ strtoupper(substr($emp->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--md-sys-color-on-surface);">{{ $emp->name }}</div>
                                        <div style="font-size: 11px; color: var(--md-sys-color-outline); font-family: monospace;">{{ $emp->nik }} &bull; {{ $emp->jabatan ?? 'SPG' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="md-badge" style="background: var(--md-sys-color-surface-container); color: var(--md-sys-color-on-surface-variant); font-size: 11px;">
                                    {{ $emp->department ?? '-' }}
                                </span>
                            </td>
                            <td style="text-align: center; font-weight: 700;">
                                {{ $sum['active_days'] ?? 0 }} hari
                            </td>
                            <td style="text-align: center; color: var(--color-ocean-blue); font-weight: 700;">
                                {{ $sum['total_points'] ?? 0 }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: var(--md-custom-color-warning);">
                                {{ $sum['total_distance_km'] ?? 0 }} km
                            </td>
                            <td style="text-align: center; padding-right: 20px;">
                                <button type="button" class="md-btn-filled" style="height: 32px; padding: 0 12px; font-size: 12px; gap: 4px;"
                                        onclick="inspectSpgTrail('{{ $emp->id }}', '{{ addslashes($emp->name) }}', '{{ $emp->nik }}')">
                                    <span class="material-symbols-rounded" style="font-size: 16px;">route</span>
                                    <span>Lihat Rute</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px 20px; color: var(--md-sys-color-outline);">
                                <span class="material-symbols-rounded" style="font-size: 40px; opacity: 0.4;">person_off</span>
                                <div style="font-weight: 600; margin-top: 6px;">Tidak ada data karyawan ditemukan.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Column: Interactive Map & Trail Inspector (Sticky) -->
    <div class="md-card md-card-elevated" style="padding: 20px; position: sticky; top: 80px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 8px;">
            <div>
                <h3 id="inspectorTitle" style="font: var(--md-sys-typescale-title-medium); font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0;">
                    Peta Jejak Operasional
                </h3>
                <span id="inspectorSubtitle" style="font-size: 12px; color: var(--md-sys-color-outline);">
                    Pilih salah satu SPG untuk memuat rute perjalanan
                </span>
            </div>

            <!-- Date Selector for the Trail -->
            <div style="display: flex; align-items: center; gap: 6px;">
                <input type="date" id="trailDatePicker" class="md-input" style="height: 36px; font-size: 12px; width: 145px;"
                       value="{{ $selectedDate }}" onchange="onDateChanged()">
                <button type="button" class="md-btn-outlined" style="height: 36px; padding: 0 10px;" onclick="refreshCurrentTrail()" title="Muat Ulang Jejak">
                    <span class="material-symbols-rounded" style="font-size: 18px;">sync</span>
                </button>
            </div>
        </div>

        <!-- Metric Details Strip -->
        <div id="inspectorMetrics" style="display: none; background: var(--md-sys-color-surface-container); border-radius: var(--md-sys-shape-corner-medium); padding: 10px 14px; margin-bottom: 14px; grid-template-columns: repeat(4, 1fr); text-align: center; gap: 6px; font-size: 12px;">
            <div>
                <div style="color: var(--md-sys-color-outline); font-size: 10.5px;">Presensi Masuk</div>
                <div id="metricTimeIn" style="font-weight: 700; color: var(--md-custom-color-success); margin-top: 2px;">--:--</div>
            </div>
            <div style="border-left: 1px solid var(--md-sys-color-outline-variant);">
                <div style="color: var(--md-sys-color-outline); font-size: 10.5px;">Presensi Pulang</div>
                <div id="metricTimeOut" style="font-weight: 700; color: var(--color-ocean-blue); margin-top: 2px;">--:--</div>
            </div>
            <div style="border-left: 1px solid var(--md-sys-color-outline-variant);">
                <div style="color: var(--md-sys-color-outline); font-size: 10.5px;">Titik GPS</div>
                <div id="metricPoints" style="font-weight: 700; color: var(--md-sys-color-on-surface); margin-top: 2px;">0</div>
            </div>
            <div style="border-left: 1px solid var(--md-sys-color-outline-variant);">
                <div style="color: var(--md-sys-color-outline); font-size: 10.5px;">Jarak Rute</div>
                <div id="metricDistance" style="font-weight: 800; color: var(--md-custom-color-warning); margin-top: 2px;">0.00 km</div>
            </div>
        </div>

        <!-- Map Canvas -->
        <div id="admin-tracking-map"></div>

        <!-- Map Legend & Note -->
        <div style="margin-top: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; font-size: 11.5px; color: var(--md-sys-color-outline);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="display: flex; align-items: center; gap: 4px;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #2E7D32; display: inline-block;"></span>
                    Awal Check-In (A)
                </span>
                <span style="display: flex; align-items: center; gap: 4px;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #C62828; display: inline-block;"></span>
                    Akhir / Pulang (B)
                </span>
            </div>
            <div>
                <span>Retensi Data: 30 Hari &bull; Siklus 25-25</span>
            </div>
        </div>
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
    let activeUserId = null;
    let activeUserName = '';
    let activeUserNik = '';

    const baseUrlTrail = "{{ $isSuperAdmin ? url('/superadmin/tracking') : url('/admin/tracking') }}";

    document.addEventListener('DOMContentLoaded', () => {
        initAdminMap();

        // Otomatis inspeksi SPG pertama jika ada
        @if($employees->isNotEmpty())
            const firstEmpId = "{{ $employees->first()->id }}";
            const firstEmpName = "{{ addslashes($employees->first()->name) }}";
            const firstEmpNik = "{{ $employees->first()->nik }}";
            inspectSpgTrail(firstEmpId, firstEmpName, firstEmpNik);
        @endif
    });

    function initAdminMap() {
        const defaultLat = -0.502106;
        const defaultLng = 117.153709;

        map = L.map('admin-tracking-map', {
            zoomControl: true,
            attributionControl: false
        }).setView([defaultLat, defaultLng], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        markersLayer = L.featureGroup().addTo(map);
    }

    async function inspectSpgTrail(userId, userName, userNik) {
        activeUserId = userId;
        activeUserName = userName;
        activeUserNik = userNik;

        // Highlight selected row in table
        document.querySelectorAll('tr[id^="spg-row-"]').forEach(r => r.classList.remove('selected-spg-row'));
        const row = document.getElementById(`spg-row-${userId}`);
        if (row) row.classList.add('selected-spg-row');

        document.getElementById('inspectorTitle').textContent = `Rute: ${userName}`;
        document.getElementById('inspectorSubtitle').textContent = `NIK: ${userNik} &bull; Tanggal: ${document.getElementById('trailDatePicker').value}`;

        await loadSpgTrailData();
    }

    function onDateChanged() {
        if (activeUserId) {
            document.getElementById('inspectorSubtitle').textContent = `NIK: ${activeUserNik} &bull; Tanggal: ${document.getElementById('trailDatePicker').value}`;
            loadSpgTrailData();
        }
    }

    function refreshCurrentTrail() {
        if (activeUserId) {
            loadSpgTrailData();
        }
    }

    async function loadSpgTrailData() {
        if (!activeUserId) return;

        const date = document.getElementById('trailDatePicker').value;
        const url = `${baseUrlTrail}/${activeUserId}/trail?date=${date}`;

        try {
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (!data.success) return;

            renderTrailOnMap(data);
        } catch (err) {
            console.error('Gagal mengambil data jejak rute SPG:', err);
        }
    }

    function renderTrailOnMap(data) {
        if (!map) return;

        markersLayer.clearLayers();
        if (polylineLayer) {
            map.removeLayer(polylineLayer);
            polylineLayer = null;
        }

        const metricsBox = document.getElementById('inspectorMetrics');
        metricsBox.style.display = 'grid';

        document.getElementById('metricTimeIn').textContent = data.attendance && data.attendance.time_in ? data.attendance.time_in.substring(0, 5) + ' WITA' : '--:--';
        document.getElementById('metricTimeOut').textContent = data.attendance && data.attendance.time_out ? data.attendance.time_out.substring(0, 5) + ' WITA' : '--:--';
        document.getElementById('metricPoints').textContent = data.total_points;
        document.getElementById('metricDistance').textContent = `${data.total_distance_km} km`;

        if (!data.points || data.points.length === 0) {
            return;
        }

        const latLngs = [];
        data.points.forEach((p, idx) => {
            latLngs.push([p.lat, p.lng]);

            // Add circle marker for intermediate checkpoints
            if (idx > 0 && idx < data.points.length - 1) {
                const circle = L.circleMarker([p.lat, p.lng], {
                    radius: 4,
                    fillColor: '#2872A1',
                    color: '#ffffff',
                    weight: 1.5,
                    opacity: 1,
                    fillOpacity: 0.8
                }).bindPopup(`<b>Checkpoint #${idx + 1}</b><br>Waktu: ${p.time}<br>Akurasi GPS: ${p.accuracy}m`);
                markersLayer.addLayer(circle);
            }
        });

        // 1. Polyline Trail
        polylineLayer = L.polyline(latLngs, {
            color: '#2872A1',
            weight: 5,
            opacity: 0.85,
            lineJoin: 'round'
        }).addTo(map);

        // 2. Start Marker (A) - Green
        const startPoint = data.points[0];
        const startIcon = L.divIcon({
            html: '<div style="background:#2E7D32; color:#fff; border-radius:50%; width:26px; height:26px; display:flex; align-items:center; justify-content:center; border:2px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.3); font-size:13px; font-weight:bold;">A</div>',
            className: '',
            iconSize: [26, 26],
            iconAnchor: [13, 13]
        });
        const startMarker = L.marker([startPoint.lat, startPoint.lng], { icon: startIcon })
            .bindPopup(`<b>Titik Awal Check-In</b><br>SPG: ${data.user.name}<br>Waktu: ${startPoint.time}`);
        markersLayer.addLayer(startMarker);

        // 3. End Marker (B) - Red
        if (data.points.length > 1) {
            const endPoint = data.points[data.points.length - 1];
            const endIcon = L.divIcon({
                html: '<div style="background:#C62828; color:#fff; border-radius:50%; width:26px; height:26px; display:flex; align-items:center; justify-content:center; border:2px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.3); font-size:13px; font-weight:bold;">B</div>',
                className: '',
                iconSize: [26, 26],
                iconAnchor: [13, 13]
            });
            const endMarker = L.marker([endPoint.lat, endPoint.lng], { icon: endIcon })
                .bindPopup(`<b>Posisi Terkini / Akhir</b><br>SPG: ${data.user.name}<br>Waktu: ${endPoint.time}`);
            markersLayer.addLayer(endMarker);
        }

        map.fitBounds(polylineLayer.getBounds(), { padding: [40, 40] });
    }
</script>
@endpush
