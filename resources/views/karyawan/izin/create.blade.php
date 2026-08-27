@extends('layouts.pwa')

@section('title', 'Form Pengajuan Izin / Cuti - Presensi PT. CAK')

@push('styles')
<style>
    .type-selector-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .type-option input {
        display: none;
    }

    .type-option label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 14px 8px;
        border-radius: 16px;
        background-color: var(--md-sys-color-surface-container);
        border: 2px solid var(--md-sys-color-outline-variant);
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        font-size: 13px;
        font-weight: 600;
        color: var(--md-sys-color-on-surface-variant);
    }

    .type-option input:checked + label {
        background: linear-gradient(135deg, var(--color-cloudy-sky-light) 0%, var(--color-cloudy-sky) 100%);
        color: var(--color-ocean-blue);
        border-color: var(--color-ocean-blue);
        font-weight: 800;
        box-shadow: 0 6px 14px rgba(40, 114, 161, 0.15), inset 2px 2px 4px rgba(255, 255, 255, 0.9);
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')
<div style="display: flex; flex-direction: column; gap: 18px;">

    <div>
        <div class="clay-pill-tag" style="margin-bottom: 6px;">
            <span class="material-symbols-rounded" style="font-size: 13px;">edit_document</span>
            <span>Formulir Digital</span>
        </div>
        <h2 class="font-editorial-serif" style="font-size: 24px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0; line-height: 1.2;">
            Pengajuan Izin / Cuti
        </h2>
        <p style="font-size: 12.5px; color: var(--md-sys-color-on-surface-variant); margin: 2px 0 0 0;">
            Isi formulir berikut dengan lengkap untuk ditinjau oleh HRD.
        </p>
    </div>

    <!-- Retroactive Notice Alert -->
    <div class="clay-card-cloudy" style="padding: 14px 18px; display: flex; align-items: center; gap: 12px;">
        <span class="material-symbols-rounded" style="color: var(--color-ocean-blue); font-size: 22px; flex-shrink: 0;">info</span>
        <div style="font-size: 12.5px; line-height: 1.5; color: var(--color-ocean-blue-on-container);">
            Pengajuan tanggal lampau (retroaktif) maksimal <b>{{ $maxRetro }} hari</b> ke belakang (minimal tanggal <b>{{ \Carbon\Carbon::parse($minDate)->isoFormat('D MMMM Y') }}</b>).
        </div>
    </div>

    <div class="clay-card" style="padding: 24px;">
        <form action="{{ route('karyawan.izin.store') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 18px;">
            @csrf

            <!-- Jenis Perizinan -->
            <div class="md-form-group">
                <label class="md-form-label font-editorial-grotesk" style="font-size: 13px; font-weight: 700; color: var(--md-sys-color-on-surface);">Jenis Perizinan</label>
                <div class="type-selector-grid" style="margin-top: 6px;">
                    <div class="type-option">
                        <input type="radio" id="typeIzin" name="type" value="izin" {{ old('type', 'izin') === 'izin' ? 'checked' : '' }}>
                        <label for="typeIzin">
                            <span class="material-symbols-rounded" style="font-size: 24px;">assignment</span>
                            <span>Izin</span>
                        </label>
                    </div>

                    <div class="type-option">
                        <input type="radio" id="typeSakit" name="type" value="sakit" {{ old('type') === 'sakit' ? 'checked' : '' }}>
                        <label for="typeSakit">
                            <span class="material-symbols-rounded" style="font-size: 24px;">medical_services</span>
                            <span>Sakit</span>
                        </label>
                    </div>

                    <div class="type-option">
                        <input type="radio" id="typeCuti" name="type" value="cuti" {{ old('type') === 'cuti' ? 'checked' : '' }}>
                        <label for="typeCuti">
                            <span class="material-symbols-rounded" style="font-size: 24px;">beach_access</span>
                            <span>Cuti</span>
                        </label>
                    </div>
                </div>
                @error('type')
                    <span style="color: var(--md-sys-color-error); font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <!-- Rentang Tanggal -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="md-form-group">
                    <label for="startDate" class="md-form-label font-editorial-grotesk" style="font-size: 12.5px; font-weight: 600;">Tanggal Mulai</label>
                    <input 
                        type="date" 
                        id="startDate" 
                        name="start_date" 
                        class="md-input" 
                        min="{{ $minDate }}" 
                        value="{{ old('start_date', date('Y-m-d')) }}" 
                        required
                    >
                    @error('start_date')
                        <span style="color: var(--md-sys-color-error); font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="md-form-group">
                    <label for="endDate" class="md-form-label font-editorial-grotesk" style="font-size: 12.5px; font-weight: 600;">Tanggal Selesai</label>
                    <input 
                        type="date" 
                        id="endDate" 
                        name="end_date" 
                        class="md-input" 
                        min="{{ $minDate }}" 
                        value="{{ old('end_date', date('Y-m-d')) }}" 
                        required
                    >
                    @error('end_date')
                        <span style="color: var(--md-sys-color-error); font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Alasan -->
            <div class="md-form-group">
                <label for="reasonInput" class="md-form-label font-editorial-grotesk" style="font-size: 12.5px; font-weight: 600;">Alasan / Keterangan Lengkap</label>
                <textarea 
                    id="reasonInput" 
                    name="reason" 
                    class="md-input" 
                    rows="3" 
                    placeholder="Contoh: Mengalami demam tinggi dan disarankan istirahat oleh dokter..." 
                    required
                >{{ old('reason') }}</textarea>
                @error('reason')
                    <span style="color: var(--md-sys-color-error); font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <!-- Upload Berkas Lampiran -->
            <div class="md-form-group">
                <label for="attachmentInput" class="md-form-label font-editorial-grotesk" style="font-size: 12.5px; font-weight: 600;">Berkas Lampiran Pendukung (Opsional)</label>
                <input 
                    type="file" 
                    id="attachmentInput" 
                    name="attachment_file" 
                    class="md-input" 
                    accept=".pdf,.jpg,.jpeg,.png"
                    style="padding: 8px;"
                >
                <small style="color: var(--md-sys-color-outline); font-size: 11px;">Format: PDF, JPG, PNG (Maksimal 2MB). Contoh: Surat keterangan dokter.</small>
                @error('attachment_file')
                    <span style="color: var(--md-sys-color-error); font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="clay-btn clay-btn-primary" style="width: 100%; height: 48px; margin-top: 8px;">
                <span class="material-symbols-rounded">send</span>
                <span>Kirim Pengajuan Izin</span>
            </button>
        </form>
    </div>

</div>
@endsection
