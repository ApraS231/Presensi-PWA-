@extends('layouts.admin')

@section('title', 'Master Lokasi & Geofence - Super Admin PT. CAK')
@section('page_title', 'Master Titik Lokasi & Geofence')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Top Action Toolbar -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="font: var(--md-sys-typescale-title-large); margin: 0;">Titik Presensi Kantor & Proyek</h2>
            <p style="font: var(--md-sys-typescale-body-small); color: var(--md-sys-color-outline); margin: 2px 0 0 0;">
                Kelola titik koordinat pusat presensi dan batas toleransi radius geofence.
            </p>
        </div>

        <a href="{{ route('superadmin.locations.create') }}" class="md-btn-filled" style="height: 40px; padding: 0 16px;">
            <span class="material-symbols-rounded">add_location_alt</span>
            <span>Tambah Lokasi Baru</span>
        </a>
    </div>

    <!-- Locations Table Card -->
    <div class="md-table-container">
        <div class="md-table-toolbar">
            <div>
                <div class="md-table-title">
                    <span class="material-symbols-rounded" style="color: var(--md-sys-color-primary);">pin_drop</span>
                    <span>Daftar Titik Geofence Resmi</span>
                </div>
                <div class="md-table-subtitle">
                    Koordinat GPS presisi dan jangkauan radius validasi presensi
                </div>
            </div>
            <span class="md-badge" style="background-color: var(--md-sys-color-surface-container-high); color: var(--md-sys-color-on-surface-variant); font-size: 11px;">
                Total: {{ $locations->total() }} Titik
            </span>
        </div>

        <div class="md-table-wrapper">
            <table class="md-table">
                <thead>
                    <tr>
                        <th>Nama Lokasi / Proyek</th>
                        <th>Koordinat Latitude</th>
                        <th>Koordinat Longitude</th>
                        <th style="text-align: center;">Radius Geofence</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($locations as $loc)
                        <tr>
                            <!-- Name -->
                            <td>
                                <div style="font-weight: 600; color: var(--md-sys-color-on-surface); display: flex; align-items: center; gap: 8px;">
                                    <span class="material-symbols-rounded" style="color: var(--md-sys-color-primary); font-size: 20px;">domain</span>
                                    <span>{{ $loc->name }}</span>
                                </div>
                            </td>

                            <!-- Lat -->
                            <td style="font-family: monospace; font-size: 13px;">
                                {{ $loc->latitude }}
                            </td>

                            <!-- Long -->
                            <td style="font-family: monospace; font-size: 13px;">
                                {{ $loc->longitude }}
                            </td>

                            <!-- Radius -->
                            <td style="text-align: center;">
                                <span class="md-badge" style="background-color: var(--md-sys-color-surface-container-high); color: var(--md-sys-color-on-surface); font-size: 11px;">
                                    {{ $loc->radius_meters }} Meter
                                </span>
                            </td>

                            <!-- Status -->
                            <td style="text-align: center;">
                                @if($loc->is_active)
                                    <span class="md-badge md-badge-approved" style="font-size: 10px;">Aktif</span>
                                @else
                                    <span class="md-badge md-badge-rejected" style="font-size: 10px;">Nonaktif</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td style="text-align: right;">
                                <div style="display: inline-flex; align-items: center; gap: 6px;">
                                    <!-- Toggle Status -->
                                    <form action="{{ route('superadmin.locations.toggle', $loc) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="md-btn-outlined" style="height: 30px; padding: 0 8px; font-size: 11px; {{ $loc->is_active ? 'color: var(--md-sys-color-error);' : 'color: var(--md-custom-color-success);' }}" title="{{ $loc->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <span class="material-symbols-rounded" style="font-size: 14px;">{{ $loc->is_active ? 'block' : 'check_circle' }}</span>
                                        </button>
                                    </form>

                                    <!-- Edit -->
                                    <a href="{{ route('superadmin.locations.edit', $loc) }}" class="md-btn-outlined" style="height: 30px; padding: 0 8px;" title="Edit Lokasi">
                                        <span class="material-symbols-rounded" style="font-size: 14px;">edit</span>
                                    </a>

                                    <!-- Delete -->
                                    <form action="{{ route('superadmin.locations.destroy', $loc) }}" method="POST" onsubmit="return confirm('Hapus permanen lokasi {{ $loc->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="md-btn-outlined" style="height: 30px; padding: 0 8px; color: var(--md-sys-color-error);" title="Hapus Lokasi">
                                            <span class="material-symbols-rounded" style="font-size: 14px;">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="md-table-empty">
                                <span class="material-symbols-rounded">location_off</span>
                                <div class="md-table-empty-title">Tidak Ada Titik Lokasi</div>
                                <div class="md-table-empty-sub">Belum ada titik lokasi geofence yang terdaftar.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($locations->hasPages())
            <div class="md-pagination-container">
                {{ $locations->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
