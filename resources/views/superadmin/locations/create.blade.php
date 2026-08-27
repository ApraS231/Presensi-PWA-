@extends('layouts.admin')

@section('title', 'Tambah Lokasi Baru - Super Admin PT. CAK')
@section('page_title', 'Tambah Titik Lokasi & Geofence')

@push('styles')
<style>
    #picker-map {
        width: 100%;
        height: 380px;
        border-radius: var(--md-sys-shape-corner-medium);
        border: 1px solid var(--md-sys-color-outline-variant);
        z-index: 1;
    }
</style>
@endpush

@section('content')
<div style="max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">

    <div class="md-card-elevated" style="padding: 24px;">
        <form action="{{ route('superadmin.locations.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 18px;">
            @csrf

            <!-- Nama Lokasi -->
            <div>
                <label for="inputName" class="md-form-label">Nama Lokasi / Kantor / Proyek <span style="color: var(--md-sys-color-error);">*</span></label>
                <input 
                    type="text" 
                    id="inputName" 
                    name="name" 
                    class="md-input" 
                    value="{{ old('name') }}" 
                    placeholder="Contoh: Kantor Pusat Bontang / Site Muara Badak"
                    required
                >
                @error('name')
                    <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Radius Geofence -->
            <div>
                <label for="inputRadius" class="md-form-label">Radius Toleransi Geofence (Meter) <span style="color: var(--md-sys-color-error);">*</span></label>
                <input 
                    type="number" 
                    id="inputRadius" 
                    name="radius_meters" 
                    class="md-input" 
                    value="{{ old('radius_meters', 100) }}" 
                    min="10" 
                    max="5000" 
                    required
                >
                <div style="font-size: 11px; color: var(--md-sys-color-outline); margin-top: 4px;">
                    Jarak maksimum karyawan dari titik tengah koordinat (minimal 10 meter).
                </div>
                @error('radius_meters')
                    <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Interactive Map Picker -->
            <div>
                <label class="md-form-label">Pilih Titik Tengah pada Peta (Klik atau Geser Pin)</label>
                <div id="picker-map"></div>
            </div>

            <!-- Grid: Latitude & Longitude -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div>
                    <label for="inputLat" class="md-form-label">Latitude <span style="color: var(--md-sys-color-error);">*</span></label>
                    <input 
                        type="text" 
                        id="inputLat" 
                        name="latitude" 
                        class="md-input" 
                        value="{{ old('latitude', '-0.1333000') }}" 
                        required
                    >
                    @error('latitude')
                        <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="inputLong" class="md-form-label">Longitude <span style="color: var(--md-sys-color-error);">*</span></label>
                    <input 
                        type="text" 
                        id="inputLong" 
                        name="longitude" 
                        class="md-input" 
                        value="{{ old('longitude', '117.4833000') }}" 
                        required
                    >
                    @error('longitude')
                        <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Status Aktif -->
            <div style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" id="inputActive" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <label for="inputActive" style="font: var(--md-sys-typescale-body-medium); cursor: pointer;">
                    Aktifkan titik lokasi ini untuk presensi karyawan
                </label>
            </div>

            <!-- Form Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <a href="{{ route('superadmin.locations.index') }}" class="md-btn-outlined">Batal</a>
                <button type="submit" class="md-btn-filled">
                    <span class="material-symbols-rounded">save</span>
                    <span>Simpan Lokasi</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const latInput = document.getElementById('inputLat');
        const lngInput = document.getElementById('inputLong');
        const radInput = document.getElementById('inputRadius');

        let currentLat = parseFloat(latInput.value) || -0.1333;
        let currentLng = parseFloat(lngInput.value) || 117.4833;
        let currentRad = parseInt(radInput.value, 10) || 100;

        const map = L.map('picker-map').setView([currentLat, currentLng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        const marker = L.marker([currentLat, currentLng], { draggable: true }).addTo(map);
        const circle = L.circle([currentLat, currentLng], {
            color: '#1565C0',
            fillColor: '#6750A4',
            fillOpacity: 0.2,
            radius: currentRad
        }).addTo(map);

        function updatePosition(lat, lng) {
            latInput.value = lat.toFixed(7);
            lngInput.value = lng.toFixed(7);
            marker.setLatLng([lat, lng]);
            circle.setLatLng([lat, lng]);
        }

        marker.on('dragend', (e) => {
            const pos = e.target.getLatLng();
            updatePosition(pos.lat, pos.lng);
        });

        map.on('click', (e) => {
            updatePosition(e.latlng.lat, e.latlng.lng);
        });

        radInput.addEventListener('input', () => {
            const rad = parseInt(radInput.value, 10) || 10;
            circle.setRadius(rad);
        });
    });
</script>
@endpush
