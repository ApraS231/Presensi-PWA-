@extends('layouts.admin')

@section('title', 'Master Lokasi & Geofence - Presensi PT. CAK')
@section('page_title', 'Master Titik Lokasi & Geofence')

@push('styles')
<style>
    /* Modal Dialog M3 */
    .md-modal-backdrop {
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 20px;
    }

    .md-modal-backdrop.open {
        display: flex;
    }

    .md-modal-dialog {
        background-color: var(--md-sys-color-surface-container-lowest);
        border-radius: var(--md-sys-shape-corner-extra-large);
        width: 100%;
        max-width: 500px;
        box-shadow: var(--md-sys-elevation-4);
        border: 1px solid var(--md-sys-color-outline-variant);
        overflow: hidden;
        animation: modalScale 0.25s var(--md-sys-motion-easing-emphasized);
    }

    @keyframes modalScale {
        from { transform: scale(0.9); opacity: 0; }
        to   { transform: scale(1); opacity: 1; }
    }

    .md-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--md-sys-color-outline-variant);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .md-modal-body {
        padding: 24px;
    }

    .md-modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--md-sys-color-outline-variant);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background-color: var(--md-sys-color-surface-container-low);
    }
</style>
@endpush

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Summary Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        <div class="md-card-elevated">
            <div style="font: var(--md-sys-typescale-body-small); color: var(--md-sys-color-on-surface-variant);">Total Titik Lokasi</div>
            <div style="font: var(--md-sys-typescale-display-small); color: var(--md-sys-color-primary); margin-top: 6px;">
                {{ $locations->total() }}
            </div>
        </div>

        <div class="md-card-elevated">
            <div style="font: var(--md-sys-typescale-body-small); color: var(--md-custom-color-success);">Titik Geofence Aktif</div>
            <div style="font: var(--md-sys-typescale-display-small); color: var(--md-custom-color-success); margin-top: 6px;">
                {{ $totalActive }}
            </div>
        </div>

        <div class="md-card-elevated">
            <div style="font: var(--md-sys-typescale-body-small); color: var(--md-sys-color-outline);">Titik Nonaktif</div>
            <div style="font: var(--md-sys-typescale-display-small); color: var(--md-sys-color-outline); margin-top: 6px;">
                {{ $totalInactive }}
            </div>
        </div>
    </div>

    <!-- Top Action & Search Bar -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <form action="{{ route('admin.locations.index') }}" method="GET" style="display: flex; gap: 10px; flex: 1; min-width: 260px;">
            <input 
                type="text" 
                name="search" 
                class="md-input" 
                placeholder="Cari nama lokasi kantor / proyek..." 
                value="{{ request('search') }}"
                style="height: 42px;"
            >
            <button type="submit" class="md-btn-filled" style="height: 42px; padding: 0 16px;">
                <span class="material-symbols-rounded">search</span>
            </button>
        </form>

        <button type="button" class="md-btn-filled" onclick="openCreateModal()" style="height: 42px;">
            <span class="material-symbols-rounded">add_location_alt</span>
            <span>Tambah Titik Lokasi</span>
        </button>
    </div>

    <!-- Location Table -->
    <div class="md-card-elevated" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font: var(--md-sys-typescale-body-medium);">
                <thead>
                    <tr style="background-color: var(--md-sys-color-surface-container); border-bottom: 1px solid var(--md-sys-color-outline-variant);">
                        <th style="padding: 14px 16px; font-weight: 600; color: var(--md-sys-color-on-surface-variant);">Nama Lokasi Kantor / Proyek</th>
                        <th style="padding: 14px 16px; font-weight: 600; color: var(--md-sys-color-on-surface-variant);">Koordinat GPS (Lat, Long)</th>
                        <th style="padding: 14px 16px; font-weight: 600; color: var(--md-sys-color-on-surface-variant);">Radius Geofence</th>
                        <th style="padding: 14px 16px; font-weight: 600; color: var(--md-sys-color-on-surface-variant);">Status</th>
                        <th style="padding: 14px 16px; font-weight: 600; color: var(--md-sys-color-on-surface-variant); text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($locations as $loc)
                        <tr style="border-bottom: 1px solid var(--md-sys-color-outline-variant);">
                            <!-- Nama Lokasi -->
                            <td style="padding: 14px 16px;">
                                <div style="font-weight: 600; color: var(--md-sys-color-on-surface);">{{ $loc->name }}</div>
                            </td>

                            <!-- Koordinat -->
                            <td style="padding: 14px 16px; font-family: monospace; font-size: 13px;">
                                <a href="https://www.google.com/maps?q={{ $loc->latitude }},{{ $loc->longitude }}" target="_blank" style="color: var(--md-sys-color-primary); text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                    <span>{{ $loc->latitude }}, {{ $loc->longitude }}</span>
                                    <span class="material-symbols-rounded" style="font-size: 14px;">open_in_new</span>
                                </a>
                            </td>

                            <!-- Radius -->
                            <td style="padding: 14px 16px;">
                                <span class="md-badge" style="background-color: var(--md-sys-color-secondary-container); color: var(--md-sys-color-on-secondary-container);">
                                    {{ $loc->radius_meters }} Meter
                                </span>
                            </td>

                            <!-- Status -->
                            <td style="padding: 14px 16px;">
                                @if($loc->is_active)
                                    <span class="md-badge md-badge-approved">Aktif</span>
                                @else
                                    <span class="md-badge md-badge-alpha">Nonaktif</span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td style="padding: 14px 16px; text-align: center;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <button 
                                        type="button" 
                                        class="md-btn-outlined" 
                                        style="height: 32px; padding: 0 12px; font-size: 12px;"
                                        onclick="openEditModal({{ json_encode($loc) }})"
                                    >
                                        <span class="material-symbols-rounded" style="font-size: 14px;">edit</span>
                                        <span>Edit</span>
                                    </button>

                                    <form action="{{ route('admin.locations.toggle', $loc->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button 
                                            type="submit" 
                                            class="md-btn-tonal" 
                                            style="height: 32px; padding: 0 10px; font-size: 12px;"
                                            title="{{ $loc->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                        >
                                            <span class="material-symbols-rounded" style="font-size: 14px;">{{ $loc->is_active ? 'toggle_on' : 'toggle_off' }}</span>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.locations.destroy', $loc->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus titik lokasi {{ $loc->name }}?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="md-btn-tonal" 
                                            style="height: 32px; padding: 0 10px; font-size: 12px; background-color: var(--md-sys-color-error-container); color: var(--md-sys-color-on-error-container);"
                                            title="Hapus"
                                        >
                                            <span class="material-symbols-rounded" style="font-size: 14px;">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 32px; text-align: center; color: var(--md-sys-color-outline);">
                                Belum ada data titik lokasi geofence. Silakan klik tombol "Tambah Titik Lokasi".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($locations->hasPages())
            <div style="padding: 16px; border-top: 1px solid var(--md-sys-color-outline-variant);">
                {{ $locations->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Form Tambah / Edit Lokasi -->
<div class="md-modal-backdrop" id="locationModal">
    <div class="md-modal-dialog">
        <form id="locationForm" method="POST" action="{{ route('admin.locations.store') }}">
            @csrf
            <div id="methodContainer"></div>

            <div class="md-modal-header">
                <h3 style="font: var(--md-sys-typescale-title-medium); margin: 0;" id="modalTitle">Tambah Titik Lokasi</h3>
                <button type="button" style="background: none; border: none; cursor: pointer; color: var(--md-sys-color-outline);" onclick="closeModal()">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>

            <div class="md-modal-body">
                <div class="md-form-group">
                    <label for="locName" class="md-form-label">Nama Lokasi Kantor / Proyek</label>
                    <input type="text" id="locName" name="name" class="md-input" placeholder="Contoh: Kantor Cabang Balikpapan" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="md-form-group">
                        <label for="locLat" class="md-form-label">Latitude</label>
                        <input type="number" step="any" id="locLat" name="latitude" class="md-input" placeholder="-0.1333000" required>
                    </div>
                    <div class="md-form-group">
                        <label for="locLon" class="md-form-label">Longitude</label>
                        <input type="number" step="any" id="locLon" name="longitude" class="md-input" placeholder="117.4833000" required>
                    </div>
                </div>

                <div class="md-form-group">
                    <label for="locRadius" class="md-form-label">Radius Geofence (Meter)</label>
                    <input type="number" min="10" max="5000" id="locRadius" name="radius_meters" class="md-input" value="100" required>
                    <small style="color: var(--md-sys-color-outline); font-size: 11px;">Jarak toleransi maksimal karyawan dari titik tengah (default: 50-100 meter).</small>
                </div>

                <div style="display: flex; align-items: center; gap: 10px; margin-top: 8px;">
                    <input type="checkbox" id="locActive" name="is_active" value="1" checked style="width: 18px; height: 18px; accent-color: var(--md-sys-color-primary);">
                    <label for="locActive" style="font: var(--md-sys-typescale-body-medium); cursor: pointer;">Status Geofence Aktif</label>
                </div>
            </div>

            <div class="md-modal-footer">
                <button type="button" class="md-btn-outlined" onclick="closeModal()">Batal</button>
                <button type="submit" class="md-btn-filled" id="btnSaveModal">Simpan Lokasi</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('locationModal');
    const form = document.getElementById('locationForm');
    const modalTitle = document.getElementById('modalTitle');
    const methodContainer = document.getElementById('methodContainer');
    const locName = document.getElementById('locName');
    const locLat = document.getElementById('locLat');
    const locLon = document.getElementById('locLon');
    const locRadius = document.getElementById('locRadius');
    const locActive = document.getElementById('locActive');

    function openCreateModal() {
        form.action = "{{ route('admin.locations.store') }}";
        methodContainer.innerHTML = '';
        modalTitle.textContent = 'Tambah Titik Lokasi Baru';
        locName.value = '';
        locLat.value = '';
        locLon.value = '';
        locRadius.value = '100';
        locActive.checked = true;
        modal.classList.add('open');
    }

    function openEditModal(loc) {
        form.action = `/admin/locations/${loc.id}`;
        methodContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        modalTitle.textContent = `Edit Titik Lokasi: ${loc.name}`;
        locName.value = loc.name;
        locLat.value = loc.latitude;
        locLon.value = loc.longitude;
        locRadius.value = loc.radius_meters;
        locActive.checked = Boolean(loc.is_active);
        modal.classList.add('open');
    }

    function closeModal() {
        modal.classList.remove('open');
    }

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
</script>
@endsection
