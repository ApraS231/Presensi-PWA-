@extends('layouts.pwa')

@section('title', 'Edit Pengajuan Izin - Presensi PT. CAK')

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
            <span class="material-symbols-rounded" style="font-size: 13px;">edit_note</span>
            <span>Perbarui Pengajuan</span>
        </div>
        <h2 class="font-editorial-serif" style="font-size: 24px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0; line-height: 1.2;">
            Edit Pengajuan Izin
        </h2>
        <p style="font-size: 12.5px; color: var(--md-sys-color-on-surface-variant); margin: 2px 0 0 0;">
            Perbarui rincian pengajuan izin yang masih berstatus pending.
        </p>
    </div>

    <div class="clay-card" style="padding: 24px;">
        <form action="{{ route('karyawan.izin.update', $leave->id) }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 18px;">
            @csrf
            @method('PUT')

            <!-- Jenis Perizinan -->
            <div class="md-form-group">
                <label class="md-form-label font-editorial-grotesk" style="font-size: 13px; font-weight: 700; color: var(--md-sys-color-on-surface);">Jenis Perizinan</label>
                <div class="type-selector-grid" style="margin-top: 6px;">
                    <div class="type-option">
                        <input type="radio" id="typeIzin" name="type" value="izin" {{ old('type', $leave->type) === 'izin' ? 'checked' : '' }}>
                        <label for="typeIzin">
                            <span class="material-symbols-rounded" style="font-size: 24px;">assignment</span>
                            <span>Izin</span>
                        </label>
                    </div>

                    <div class="type-option">
                        <input type="radio" id="typeSakit" name="type" value="sakit" {{ old('type', $leave->type) === 'sakit' ? 'checked' : '' }}>
                        <label for="typeSakit">
                            <span class="material-symbols-rounded" style="font-size: 24px;">medical_services</span>
                            <span>Sakit</span>
                        </label>
                    </div>

                    <div class="type-option">
                        <input type="radio" id="typeCuti" name="type" value="cuti" {{ old('type', $leave->type) === 'cuti' ? 'checked' : '' }}>
                        <label for="typeCuti">
                            <span class="material-symbols-rounded" style="font-size: 24px;">beach_access</span>
                            <span>Cuti</span>
                        </label>
                    </div>
                </div>
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
                        value="{{ old('start_date', $leave->start_date) }}" 
                        required
                    >
                </div>

                <div class="md-form-group">
                    <label for="endDate" class="md-form-label font-editorial-grotesk" style="font-size: 12.5px; font-weight: 600;">Tanggal Selesai</label>
                    <input 
                        type="date" 
                        id="endDate" 
                        name="end_date" 
                        class="md-input" 
                        min="{{ $minDate }}" 
                        value="{{ old('end_date', $leave->end_date) }}" 
                        required
                    >
                </div>
            </div>

            <!-- Alasan -->
            <div class="md-form-group">
                <label for="reasonInput" class="md-form-label font-editorial-grotesk" style="font-size: 12.5px; font-weight: 600;">Alasan / Keterangan</label>
                <textarea 
                    id="reasonInput" 
                    name="reason" 
                    class="md-input" 
                    rows="3" 
                    required
                >{{ old('reason', $leave->reason) }}</textarea>
            </div>

            <!-- Upload Berkas Lampiran Baru -->
            <div class="md-form-group">
                <label for="attachmentInput" class="md-form-label font-editorial-grotesk" style="font-size: 12.5px; font-weight: 600;">Ganti Berkas Lampiran (Opsional)</label>
                @if($leave->attachment_file)
                    <div style="font-size: 12px; margin-bottom: 6px; color: var(--md-sys-color-outline);">
                        Berkas saat ini: <a href="{{ asset('storage/' . $leave->attachment_file) }}" target="_blank" style="color: var(--color-ocean-blue); font-weight: 600;">Lihat Lampiran</a>
                    </div>
                @endif
                <input 
                    type="file" 
                    id="attachmentInput" 
                    name="attachment_file" 
                    class="md-input" 
                    accept=".pdf,.jpg,.jpeg,.png"
                    style="padding: 8px;"
                >
            </div>

            <div style="display: flex; gap: 10px; margin-top: 8px;">
                <a href="{{ route('karyawan.izin.index') }}" class="clay-btn clay-btn-cloudy" style="flex: 1; height: 48px;">Batal</a>
                <button type="submit" class="clay-btn clay-btn-primary" style="flex: 2; height: 48px;">
                    <span class="material-symbols-rounded">save</span>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
