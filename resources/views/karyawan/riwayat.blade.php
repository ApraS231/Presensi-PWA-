@extends('layouts.pwa')

@section('title', 'Riwayat Presensi - PT. Cahaya Anugrah Kalimantan')

@section('content')
<div style="display: flex; flex-direction: column; gap: 14px;">

    <!-- Header & Compact Filter -->
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
        <h2 class="font-editorial-serif" style="font-size: 22px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0; line-height: 1.2;">
            Riwayat Presensi
        </h2>

        <!-- Filter Form Bulanan -->
        <form method="GET" action="{{ route('karyawan.riwayat.index') }}" style="display: flex; gap: 6px;" id="filterForm">
            <select name="month" class="md-input" style="height: 34px; padding: 0 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; background: var(--color-cloudy-sky-light); border: 1px solid var(--color-cloudy-sky); color: var(--color-ocean-blue);" onchange="document.getElementById('filterForm').submit()">
                @foreach($months as $num => $name)
                    <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>

            <select name="year" class="md-input" style="height: 34px; padding: 0 8px; border-radius: 9999px; font-size: 12px; font-weight: 600; background: var(--color-cloudy-sky-light); border: 1px solid var(--color-cloudy-sky); color: var(--color-ocean-blue);" onchange="document.getElementById('filterForm').submit()">
                @foreach($years as $yr)
                    <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>
                        {{ $yr }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Monthly Summary Bar (Minimal Single Card) -->
    <div class="clay-card" style="padding: 12px 10px; display: grid; grid-template-columns: repeat(4, 1fr); text-align: center; gap: 2px;">
        <div style="padding: 2px 4px;">
            <div style="font-size: 10px; font-weight: 700; color: var(--md-custom-color-success); text-transform: uppercase; letter-spacing: 0.2px;">Tepat Waktu</div>
            <div class="font-editorial-grotesk" style="font-size: 15px; font-weight: 800; color: var(--md-custom-color-success); margin-top: 2px;">{{ $totalTepatWaktu }} Hari</div>
        </div>

        <div style="padding: 2px 4px; border-left: 1px solid var(--md-sys-color-outline-variant);">
            <div style="font-size: 10px; font-weight: 700; color: var(--md-custom-color-warning); text-transform: uppercase; letter-spacing: 0.2px;">Terlambat</div>
            <div class="font-editorial-grotesk" style="font-size: 15px; font-weight: 800; color: var(--md-custom-color-warning); margin-top: 2px;">{{ $totalTerlambat }} Hari</div>
        </div>

        <div style="padding: 2px 4px; border-left: 1px solid var(--md-sys-color-outline-variant);">
            <div style="font-size: 10px; font-weight: 700; color: var(--color-ocean-blue); text-transform: uppercase; letter-spacing: 0.2px;">Izin/Cuti</div>
            <div class="font-editorial-grotesk" style="font-size: 15px; font-weight: 800; color: var(--color-ocean-blue); margin-top: 2px;">{{ $totalIzinCuti }} Hari</div>
        </div>

        <div style="padding: 2px 4px; border-left: 1px solid var(--md-sys-color-outline-variant);">
            <div style="font-size: 10px; font-weight: 700; color: var(--md-sys-color-error); text-transform: uppercase; letter-spacing: 0.2px;">Alpha</div>
            <div class="font-editorial-grotesk" style="font-size: 15px; font-weight: 800; color: var(--md-sys-color-error); margin-top: 2px;">{{ $totalAlpha }} Hari</div>
        </div>
    </div>

    <!-- Attendance Cards List (Simple & Clean) -->
    <div style="display: flex; flex-direction: column; gap: 10px;">
        @forelse($attendances as $att)
            @php
                $badgeClass = match($att->status) {
                    'tepat_waktu' => 'md-badge-tepat-waktu',
                    'terlambat'   => 'md-badge-terlambat',
                    'izin'        => 'md-badge-izin',
                    'sakit'       => 'md-badge-sakit',
                    'cuti'        => 'md-badge-cuti',
                    'alpha'       => 'md-badge-alpha',
                    default       => 'md-badge-tepat-waktu',
                };
            @endphp
            <div class="clay-card" style="padding: 14px 16px;">
                <!-- Header: Date & Badge -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                    <div style="font-size: 13.5px; font-weight: 700; color: var(--md-sys-color-on-surface);" class="font-editorial-grotesk">
                        {{ \Carbon\Carbon::parse($att->date)->isoFormat('dddd, D MMMM Y') }}
                    </div>
                    <span class="md-badge {{ $badgeClass }}" style="font-size: 9.5px; padding: 2px 8px;">
                        {{ strtoupper(str_replace('_', ' ', $att->status)) }}
                    </span>
                </div>

                <!-- Content: Time In, Time Out, and Proof -->
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--md-sys-color-outline-variant); font-size: 12.5px;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div>
                            <span style="color: var(--md-sys-color-outline); font-size: 10.5px;">Masuk:</span>
                            <b style="color: var(--md-custom-color-success); margin-left: 2px;">{{ $att->time_in ? \Carbon\Carbon::parse($att->time_in)->format('H:i') . ' WITA' : '-' }}</b>
                        </div>
                        <div>
                            <span style="color: var(--md-sys-color-outline); font-size: 10.5px;">Pulang:</span>
                            <b style="color: var(--color-ocean-blue); margin-left: 2px;">{{ $att->time_out ? \Carbon\Carbon::parse($att->time_out)->format('H:i') . ' WITA' : '--:--' }}</b>
                            @if($att->auto_checkout)
                                <span class="md-badge" style="font-size: 8px; padding: 1px 4px; margin-left: 2px;">Auto</span>
                            @endif
                        </div>
                    </div>

                    @if($att->photo_in || $att->photo_out)
                        <button type="button" class="clay-btn clay-btn-cloudy" style="height: 26px; padding: 0 10px; font-size: 11px; gap: 4px;"
                                onclick="openPhotoPreview('{{ $att->photo_in ? asset('storage/' . $att->photo_in) : '' }}', '{{ $att->photo_out ? asset('storage/' . $att->photo_out) : '' }}', '{{ \Carbon\Carbon::parse($att->date)->isoFormat('D MMMM Y') }}')">
                            <span class="material-symbols-rounded" style="font-size: 14px;">photo_camera</span>
                            <span>Foto</span>
                        </button>
                    @endif
                </div>

                <!-- Sub-info: Location & Geofence (if available) -->
                @if($att->location || $att->distance_meters)
                    <div style="margin-top: 6px; font-size: 11px; color: var(--md-sys-color-outline); display: flex; align-items: center; gap: 4px;">
                        <span class="material-symbols-rounded" style="font-size: 14px; color: var(--color-ocean-blue);">location_on</span>
                        <span>{{ $att->location ? $att->location->name : 'Koordinat GPS' }}</span>
                        @if($att->distance_meters)
                            <span>&bull; {{ round($att->distance_meters) }}m</span>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="clay-card" style="text-align: center; padding: 36px 20px; color: var(--md-sys-color-outline);">
                <span class="material-symbols-rounded" style="font-size: 40px; opacity: 0.35; margin-bottom: 6px;">history_toggle_off</span>
                <div class="font-editorial-serif" style="font-size: 15px; font-weight: 700; color: var(--md-sys-color-on-surface);">Tidak Ada Catatan Presensi</div>
                <div style="font-size: 12px; color: var(--md-sys-color-outline); margin-top: 2px;">Belum ada riwayat kehadiran pada bulan yang dipilih.</div>
            </div>
        @endforelse
    </div>
</div>

<!-- Photo Preview Modal -->
<div class="md-modal-backdrop" id="photoPreviewModal">
    <div class="md-modal-dialog">
        <div class="md-modal-header">
            <h3 style="font-size: 14px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0;" id="photoModalDateTitle">Bukti Foto Presensi</h3>
            <button type="button" style="background: none; border: none; cursor: pointer; color: var(--md-sys-color-outline);" onclick="closePhotoPreview()">
                <span class="material-symbols-rounded">close</span>
            </button>
        </div>
        <div class="md-modal-body" style="display: flex; flex-direction: column; gap: 12px;">
            <div id="containerPhotoIn" style="display: none;">
                <div style="font-size: 12px; font-weight: 600; color: var(--md-custom-color-success); margin-bottom: 4px;">Foto Presensi Masuk</div>
                <img id="imgPhotoIn" src="" alt="Foto Masuk" style="width: 100%; max-height: 220px; object-fit: contain; border-radius: 8px; background: #000;">
            </div>

            <div id="containerPhotoOut" style="display: none;">
                <div style="font-size: 12px; font-weight: 600; color: var(--md-custom-color-warning); margin-bottom: 4px;">Foto Presensi Pulang</div>
                <img id="imgPhotoOut" src="" alt="Foto Pulang" style="width: 100%; max-height: 220px; object-fit: contain; border-radius: 8px; background: #000;">
            </div>
        </div>
        <div class="md-modal-footer">
            <button type="button" class="md-btn-filled" onclick="closePhotoPreview()">Tutup</button>
        </div>
    </div>
</div>

<script>
    const photoModal = document.getElementById('photoPreviewModal');
    const titleDate = document.getElementById('photoModalDateTitle');
    const containerIn = document.getElementById('containerPhotoIn');
    const containerOut = document.getElementById('containerPhotoOut');
    const imgIn = document.getElementById('imgPhotoIn');
    const imgOut = document.getElementById('imgPhotoOut');

    function openPhotoPreview(inSrc, outSrc, dateText) {
        titleDate.textContent = `Bukti Foto (${dateText})`;

        if (inSrc) {
            imgIn.src = inSrc;
            containerIn.style.display = 'block';
        } else {
            containerIn.style.display = 'none';
        }

        if (outSrc) {
            imgOut.src = outSrc;
            containerOut.style.display = 'block';
        } else {
            containerOut.style.display = 'none';
        }

        photoModal.classList.add('open');
    }

    function closePhotoPreview() {
        photoModal.classList.remove('open');
        imgIn.src = '';
        imgOut.src = '';
    }

    photoModal.addEventListener('click', (e) => {
        if (e.target === photoModal) closePhotoPreview();
    });
</script>
@endsection
