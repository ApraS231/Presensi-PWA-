@extends('layouts.admin')

@section('title', 'Pusat Kontrol Sistem - Super Admin PT. CAK')
@section('page_title', 'Master Control & Konfigurasi Sistem')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- System Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        <div class="md-card-elevated">
            <div style="font: var(--md-sys-typescale-body-medium); color: var(--md-sys-color-outline);">Total Akun Terdaftar</div>
            <div style="font: var(--md-sys-typescale-headline-medium); font-weight: 700; color: var(--md-sys-color-primary); margin-top: 4px;">
                {{ $totalUsers }}
            </div>
            <div style="font: var(--md-sys-typescale-label-small); color: var(--md-sys-color-outline); margin-top: 4px;">Karyawan, Admin & Super Admin</div>
        </div>

        <div class="md-card-elevated">
            <div style="font: var(--md-sys-typescale-body-medium); color: var(--md-sys-color-outline);">Karyawan Aktif</div>
            <div style="font: var(--md-sys-typescale-headline-medium); font-weight: 700; color: var(--md-custom-color-success); margin-top: 4px;">
                {{ $totalKaryawan }}
            </div>
            <div style="font: var(--md-sys-typescale-label-small); color: var(--md-sys-color-outline); margin-top: 4px;">Pengguna Mobile PWA</div>
        </div>

        <div class="md-card-elevated">
            <div style="font: var(--md-sys-typescale-body-medium); color: var(--md-sys-color-outline);">Titik Geofence</div>
            <div style="font: var(--md-sys-typescale-headline-medium); font-weight: 700; color: var(--md-sys-color-secondary); margin-top: 4px;">
                {{ $totalLocations }}
            </div>
            <div style="font: var(--md-sys-typescale-label-small); color: var(--md-sys-color-outline); margin-top: 4px;">Kantor & Lokasi Proyek</div>
        </div>

        <div class="md-card-elevated">
            <div style="font: var(--md-sys-typescale-body-medium); color: var(--md-sys-color-outline);">Parameter Kebijakan</div>
            <div style="font: var(--md-sys-typescale-headline-medium); font-weight: 700; color: var(--md-custom-color-warning); margin-top: 4px;">
                {{ $totalSettings }}
            </div>
            <div style="font: var(--md-sys-typescale-label-small); color: var(--md-sys-color-outline); margin-top: 4px;">Konfigurasi Aturan Aktif</div>
        </div>
    </div>

    <!-- Quick Actions Card Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
        <div class="md-card-filled" style="display: flex; flex-direction: column; justify-content: space-between; gap: 14px;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span class="material-symbols-rounded" style="color: var(--md-sys-color-primary);">group_add</span>
                    <h3 style="font: var(--md-sys-typescale-title-medium); margin: 0;">Kelola Karyawan</h3>
                </div>
                <p style="font: var(--md-sys-typescale-body-small); color: var(--md-sys-color-outline); margin: 0;">
                    Daftarkan karyawan baru, reset pendaftaran biometrik wajah, atau aktifkan/nonaktifkan akun.
                </p>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('superadmin.users.create') }}" class="md-btn-filled" style="height: 36px; padding: 0 14px; font-size: 12px;">
                    <span class="material-symbols-rounded">add</span>
                    <span>Tambah Akun</span>
                </a>
                <a href="{{ route('superadmin.users.index') }}" class="md-btn-outlined" style="height: 36px; padding: 0 14px; font-size: 12px;">
                    <span>Lihat Daftar</span>
                </a>
            </div>
        </div>

        <div class="md-card-filled" style="display: flex; flex-direction: column; justify-content: space-between; gap: 14px;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span class="material-symbols-rounded" style="color: var(--md-sys-color-primary);">add_location_alt</span>
                    <h3 style="font: var(--md-sys-typescale-title-medium); margin: 0;">Master Geofence</h3>
                </div>
                <p style="font: var(--md-sys-typescale-body-small); color: var(--md-sys-color-outline); margin: 0;">
                    Tambah titik koordinat kantor pusat atau proyek tambang baru dengan penentu radius interaktif.
                </p>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('superadmin.locations.create') }}" class="md-btn-filled" style="height: 36px; padding: 0 14px; font-size: 12px;">
                    <span class="material-symbols-rounded">add</span>
                    <span>Tambah Lokasi</span>
                </a>
                <a href="{{ route('superadmin.locations.index') }}" class="md-btn-outlined" style="height: 36px; padding: 0 14px; font-size: 12px;">
                    <span>Lihat Lokasi</span>
                </a>
            </div>
        </div>

        <div class="md-card-filled" style="display: flex; flex-direction: column; justify-content: space-between; gap: 14px;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span class="material-symbols-rounded" style="color: var(--md-sys-color-primary);">tune</span>
                    <h3 style="font: var(--md-sys-typescale-title-medium); margin: 0;">Kebijakan Sistem</h3>
                </div>
                <p style="font: var(--md-sys-typescale-body-small); color: var(--md-sys-color-outline); margin: 0;">
                    Atur jam masuk, jam pulang, batas toleransi keterlambatan, hari kerja, dan batas izin retroaktif.
                </p>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('superadmin.settings.index') }}" class="md-btn-filled" style="height: 36px; padding: 0 14px; font-size: 12px;">
                    <span class="material-symbols-rounded">settings</span>
                    <span>Ubah Kebijakan</span>
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
