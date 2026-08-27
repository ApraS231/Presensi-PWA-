@extends('layouts.admin')

@section('title', 'Live Map Sebaran Presensi - Presensi PT. CAK')
@section('page_title', 'Visualisasi Live Map Sebaran Presensi')

@push('styles')
<style>
    .map-filter-card {
        background-color: var(--md-sys-color-surface-container-low);
        border: 1px solid var(--md-sys-color-outline-variant);
        border-radius: var(--md-sys-shape-corner-large);
        padding: 20px 24px;
    }

    .map-filter-grid {
        display: grid;
        grid-template-columns: 170px 1.2fr 1fr 150px auto;
        gap: 16px;
        align-items: flex-end;
    }

    @media (max-width: 1100px) {
        .map-filter-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 640px) {
        .map-filter-grid {
            grid-template-columns: 1fr;
        }
    }

    .map-grid-container {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 20px;
        align-items: start;
    }

    @media (max-width: 1024px) {
        .map-grid-container {
            grid-template-columns: 1fr;
        }
    }

    #live-attendance-map {
        width: 100%;
        height: 620px;
        border-radius: var(--md-sys-shape-corner-large);
        z-index: 1;
    }

    .legend-card {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 12px 20px;
        background-color: var(--md-sys-color-surface-container);
        border-radius: var(--md-sys-shape-corner-medium);
        border: 1px solid var(--md-sys-color-outline-variant);
        font: var(--md-sys-typescale-body-small);
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
    }

    .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }

    .employee-side-panel {
        background-color: var(--md-sys-color-surface-container-lowest);
        border-radius: var(--md-sys-shape-corner-large);
        border: 1px solid var(--md-sys-color-outline-variant);
        box-shadow: var(--md-sys-elevation-1);
        display: flex;
        flex-direction: column;
        height: 620px;
        overflow: hidden;
    }

    .employee-panel-header {
        padding: 18px 20px;
        border-bottom: 1px solid var(--md-sys-color-outline-variant);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: var(--md-sys-color-surface-container-low);
    }

    .employee-panel-list {
        padding: 12px 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        overflow-y: auto;
        flex: 1;
    }

    .employee-list-item {
        padding: 12px 14px;
        border-radius: 12px;
        background-color: var(--md-sys-color-surface-container-lowest);
        border: 1px solid var(--md-sys-color-outline-variant);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .employee-list-item:hover {
        background-color: var(--md-sys-color-primary-container);
        border-color: var(--md-sys-color-primary);
        transform: translateX(2px);
    }
</style>
@endpush

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Filter Toolbar -->
    <div class="map-filter-card">
        <form action="{{ route('admin.map.index') }}" method="GET" class="map-filter-grid">
            <!-- Filter Tanggal -->
            <div>
                <label for="filterDate" class="md-form-label">Tanggal Pantau</label>
                <input 
                    type="date" 
                    id="filterDate" 
                    name="date" 
                    class="md-input" 
                    value="{{ $formattedDate }}"
                >
            </div>

            <!-- Filter Lokasi Kantor -->
            <div>
                <label for="filterLoc" class="md-form-label">Titik Kantor / Proyek</label>
                <select id="filterLoc" name="location_id" class="md-input">
                    <option value="">Semua Lokasi Geofence</option>
                    @foreach($activeLocations as $loc)
                        <option value="{{ $loc->id }}" {{ $locationId == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Departemen -->
            <div>
                <label for="filterDept" class="md-form-label">Departemen</label>
                <select id="filterDept" name="department" class="md-input">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div>
                <label for="filterStatus" class="md-form-label">Status Kehadiran</label>
                <select id="filterStatus" name="status" class="md-input">
                    <option value="">Semua Status</option>
                    <option value="tepat_waktu" {{ $status === 'tepat_waktu' ? 'selected' : '' }}>Tepat Waktu</option>
                    <option value="terlambat" {{ $status === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                </select>
            </div>

            <!-- Buttons -->
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="md-btn-filled" title="Terapkan Filter">
                    <span class="material-symbols-rounded" style="font-size: 18px;">filter_alt</span>
                    <span>Terapkan</span>
                </button>
                <a href="{{ route('admin.map.index') }}" class="md-btn-outlined" style="padding: 0 14px;" title="Reset Filter">
                    <span class="material-symbols-rounded" style="font-size: 18px;">restart_alt</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Map Legend -->
    <div class="legend-card">
        <span style="font-weight: 700; color: var(--md-sys-color-on-surface); font-size: 13px;">Legenda Peta:</span>
        <div class="legend-item">
            <span class="material-symbols-rounded" style="font-size: 18px; color: var(--md-sys-color-primary);">corporate_fare</span>
            <span>Titik Kantor & Radius Geofence</span>
        </div>
        <div class="legend-item">
            <div class="legend-dot" style="background-color: #2E7D32;"></div>
            <span>Hadir Tepat Waktu</span>
        </div>
        <div class="legend-item">
            <div class="legend-dot" style="background-color: #E65100;"></div>
            <span>Hadir Terlambat</span>
        </div>
        <div style="margin-left: auto; color: var(--md-sys-color-outline); font-size: 12px;">
            Total Terpetakan: <b style="color: var(--md-sys-color-primary);">{{ $mappedAttendances->count() }} Karyawan</b>
        </div>
    </div>

    <!-- Map and Employee Side Panel Grid -->
    <div class="map-grid-container">
        <!-- Interactive Leaflet Map -->
        <div class="md-card-elevated" style="padding: 0; overflow: hidden; height: 620px;">
            <div id="live-attendance-map"></div>
        </div>

        <!-- Employee Presence List Panel -->
        <div class="employee-side-panel">
            <div class="employee-panel-header">
                <div>
                    <div style="font-size: 15px; font-weight: 700; color: var(--md-sys-color-on-surface);">
                        Karyawan Terpetakan
                    </div>
                    <div style="font-size: 11px; color: var(--md-sys-color-outline); margin-top: 1px;">
                        Klik item untuk zoom lokasi
                    </div>
                </div>
                <span class="md-badge" style="background-color: var(--md-sys-color-primary-container); color: var(--md-sys-color-primary);">
                    {{ $mappedAttendances->count() }} Orang
                </span>
            </div>

            <div class="employee-panel-list">
                @forelse($mappedAttendances as $att)
                    <div class="employee-list-item" onclick="focusOnEmployee({{ $att->lat_in }}, {{ $att->long_in }})" title="Klik untuk memusatkan peta ke lokasi karyawan ini">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            @if($att->photo_in)
                                <img src="{{ asset('storage/' . $att->photo_in) }}" alt="Foto" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 1.5px solid var(--md-sys-color-primary);">
                            @else
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--md-sys-color-primary-container); color: var(--md-sys-color-primary); display: flex; align-items: center; justify-content: center;">
                                    <span class="material-symbols-rounded" style="font-size: 20px;">person</span>
                                </div>
                            @endif

                            <div>
                                <div style="font-weight: 600; font-size: 13px; color: var(--md-sys-color-on-surface);">{{ $att->user->name ?? '-' }}</div>
                                <div style="font-size: 11px; color: var(--md-sys-color-outline); margin-top: 2px;">
                                    {{ $att->time_in }} WITA • {{ $att->distance_meters }}m dari kantor
                                </div>
                            </div>
                        </div>

                        <span class="md-badge {{ $att->status === 'tepat_waktu' ? 'md-badge-approved' : 'md-badge-terlambat' }}" style="font-size: 10px;">
                            {{ $att->status === 'tepat_waktu' ? 'Tepat' : 'Telat' }}
                        </span>
                    </div>
                @empty
                    <div style="text-align: center; padding: 48px 16px; color: var(--md-sys-color-outline); font-size: 13px;">
                        <span class="material-symbols-rounded" style="font-size: 32px; display: block; margin-bottom: 6px; opacity: 0.5;">location_off</span>
                        Tidak ada data koordinat presensi pada filter ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const locationsData = @json($activeLocations);
    const attendancesData = @json($mappedAttendances);

    let mapInstance = null;

    document.addEventListener('DOMContentLoaded', () => {
        mapInstance = initAttendanceLiveMap('live-attendance-map', locationsData, attendancesData, { zoom: 13 });
    });

    function focusOnEmployee(lat, lng) {
        if (mapInstance && lat && lng) {
            mapInstance.flyTo([lat, lng], 17, { duration: 1.2 });
        }
    }
</script>
@endpush
