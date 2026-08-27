@extends('layouts.admin')

@section('title', 'Kebijakan Sistem Presensi - Super Admin PT. CAK')
@section('page_title', 'Pengaturan Kebijakan Sistem Global')

@push('styles')
<style>
    .settings-container {
        max-width: 860px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .settings-card {
        background-color: var(--md-sys-color-surface-container-lowest);
        border-radius: var(--md-sys-shape-corner-large);
        border: 1px solid var(--md-sys-color-outline-variant);
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        padding: 24px 28px;
        display: flex;
        flex-direction: column;
        gap: 18px;
        transition: box-shadow 0.2s ease;
    }

    .settings-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.08);
    }

    .settings-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--md-sys-color-outline-variant);
    }

    .settings-icon-badge {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .form-grid-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    @media (max-width: 640px) {
        .form-grid-2col {
            grid-template-columns: 1fr;
        }
    }

    .helper-text {
        font-size: 11.5px;
        color: var(--md-sys-color-outline);
        margin-top: 5px;
        line-height: 1.4;
    }
</style>
@endpush

@section('content')
<div class="settings-container">

    <form action="{{ route('superadmin.settings.batch') }}" method="POST" style="display: flex; flex-direction: column; gap: 24px;">
        @csrf

        <!-- Section 1: Jam Kerja & Toleransi -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-icon-badge" style="background-color: var(--md-sys-color-primary-container); color: var(--md-sys-color-primary);">
                    <span class="material-symbols-rounded">schedule</span>
                </div>
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: var(--md-sys-color-on-surface);">
                        Jam Operasional Kerja & Toleransi
                    </h3>
                    <div style="font-size: 12px; color: var(--md-sys-color-outline); margin-top: 2px;">
                        Aturan batas waktu presensi masuk, presensi pulang, dan batas toleransi keterlambatan harian.
                    </div>
                </div>
            </div>

            <div class="form-grid-2col">
                <!-- Jam Masuk -->
                <div>
                    <label for="inputJamMasuk" class="md-form-label">
                        Jam Masuk Kerja (WITA) <span style="color: var(--md-sys-color-error);">*</span>
                    </label>
                    <input 
                        type="time" 
                        id="inputJamMasuk" 
                        name="settings[jam_masuk]" 
                        class="md-input" 
                        value="{{ old('settings.jam_masuk', $settings['jam_masuk']) }}" 
                        required
                    >
                    <div class="helper-text">Jam mulai operasional kerja harian.</div>
                    @error('settings.jam_masuk')
                        <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Jam Pulang -->
                <div>
                    <label for="inputJamPulang" class="md-form-label">
                        Jam Pulang Kerja (WITA) <span style="color: var(--md-sys-color-error);">*</span>
                    </label>
                    <input 
                        type="time" 
                        id="inputJamPulang" 
                        name="settings[jam_pulang]" 
                        class="md-input" 
                        value="{{ old('settings.jam_pulang', $settings['jam_pulang']) }}" 
                        required
                    >
                    <div class="helper-text">Jam mulai diizinkannya presensi check-out.</div>
                    @error('settings.jam_pulang')
                        <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Toleransi Terlambat -->
            <div>
                <label for="inputToleransi" class="md-form-label">
                    Batas Toleransi Keterlambatan (Menit) <span style="color: var(--md-sys-color-error);">*</span>
                </label>
                <input 
                    type="number" 
                    id="inputToleransi" 
                    name="settings[toleransi_terlambat]" 
                    class="md-input" 
                    value="{{ old('settings.toleransi_terlambat', $settings['toleransi_terlambat']) }}" 
                    min="0" 
                    max="120" 
                    required
                >
                <div class="helper-text">
                    Contoh: <b>15 menit</b>. Presensi masuk hingga pukul 08:15 WITA tetap terhitung tepat waktu tanpa penalti keterlambatan.
                </div>
                @error('settings.toleransi_terlambat')
                    <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Section 2: Durasi Istirahat & Hari Kerja -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-icon-badge" style="background-color: var(--md-custom-color-success-container); color: var(--md-custom-color-success);">
                    <span class="material-symbols-rounded">free_breakfast</span>
                </div>
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: var(--md-sys-color-on-surface);">
                        Durasi Istirahat & Kalender Hari Kerja
                    </h3>
                    <div style="font-size: 12px; color: var(--md-sys-color-outline); margin-top: 2px;">
                        Pengaturan potongan durasi istirahat dan penentuan hari kerja operasional aktif.
                    </div>
                </div>
            </div>

            <div class="form-grid-2col">
                <!-- Durasi Istirahat -->
                <div>
                    <label for="inputIstirahat" class="md-form-label">
                        Durasi Istirahat Harian (Menit) <span style="color: var(--md-sys-color-error);">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="inputIstirahat" 
                        name="settings[jam_istirahat]" 
                        class="md-input" 
                        value="{{ old('settings.jam_istirahat', $settings['jam_istirahat']) }}" 
                        min="0" 
                        max="180" 
                        required
                    >
                    <div class="helper-text">
                        Potongan waktu istirahat dalam formula perhitungan jam kerja bersih payroll (default: <b>60 menit</b>).
                    </div>
                    @error('settings.jam_istirahat')
                        <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Hari Kerja Aktif -->
                <div>
                    <label for="inputHariKerja" class="md-form-label">
                        Daftar Hari Kerja Aktif <span style="color: var(--md-sys-color-error);">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="inputHariKerja" 
                        name="settings[hari_kerja]" 
                        class="md-input" 
                        value="{{ old('settings.hari_kerja', $settings['hari_kerja']) }}" 
                        placeholder="senin,selasa,rabu,kamis,jumat"
                        required
                    >
                    <div class="helper-text">
                        Gunakan huruf kecil dan pisahkan dengan tanda koma (contoh: <code>senin,selasa,rabu,kamis,jumat</code>).
                    </div>
                    @error('settings.hari_kerja')
                        <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section 3: Kebijakan Izin Retroaktif -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-icon-badge" style="background-color: var(--md-custom-color-warning-container); color: var(--md-custom-color-warning);">
                    <span class="material-symbols-rounded">history_toggle_off</span>
                </div>
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: var(--md-sys-color-on-surface);">
                        Kebijakan Pengajuan Izin Retroaktif
                    </h3>
                    <div style="font-size: 12px; color: var(--md-sys-color-outline); margin-top: 2px;">
                        Batas toleransi maksimal hari lampau yang diizinkan untuk pengajuan izin atau sakit.
                    </div>
                </div>
            </div>

            <div>
                <label for="inputRetro" class="md-form-label">
                    Batas Maksimal Hari Mundur (Hari Kalender) <span style="color: var(--md-sys-color-error);">*</span>
                </label>
                <input 
                    type="number" 
                    id="inputRetro" 
                    name="settings[max_retroaktif_izin]" 
                    class="md-input" 
                    value="{{ old('settings.max_retroaktif_izin', $settings['max_retroaktif_izin']) }}" 
                    min="0" 
                    max="30" 
                    required
                >
                <div class="helper-text">
                    Batas hari mundur maksimal ke belakang yang dapat dipilih oleh karyawan pada form izin/sakit (default: <b>3 hari</b>).
                </div>
                @error('settings.max_retroaktif_izin')
                    <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Sticky / Elevated Submit Action -->
        <div style="display: flex; justify-content: flex-end; padding: 10px 0;">
            <button type="submit" class="md-btn-filled" style="height: 46px; padding: 0 28px; font-size: 14px;">
                <span class="material-symbols-rounded" style="font-size: 20px;">save</span>
                <span>Simpan Perubahan Kebijakan</span>
            </button>
        </div>
    </form>

</div>
@endsection
