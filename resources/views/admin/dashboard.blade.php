@extends('layouts.admin')

@section('title', 'Dashboard Monitoring Kehadiran - Presensi PT. CAK')
@section('page_title', 'Monitoring Kehadiran Real-Time')

@push('styles')
<style>
    .stat-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .stat-value {
        font: var(--md-sys-typescale-display-small);
        font-weight: 700;
        margin: 6px 0 2px 0;
    }

    .photo-thumb {
        width: 44px;
        height: 44px;
        border-radius: var(--md-sys-shape-corner-small);
        object-fit: cover;
        cursor: pointer;
        border: 2px solid var(--md-sys-color-outline-variant);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .photo-thumb:hover {
        transform: scale(1.1);
        box-shadow: var(--md-sys-elevation-2);
        border-color: var(--md-sys-color-primary);
    }

    .live-pulse {
        display: inline-block;
        width: 8px;
        height: 8px;
        background-color: var(--md-custom-color-success);
        border-radius: 50%;
        margin-right: 6px;
        box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.7);
        animation: pulseLive 1.8s infinite;
    }

    @keyframes pulseLive {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(76, 175, 80, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(76, 175, 80, 0); }
    }

    /* Modal Dialog M3 */
    .md-modal-backdrop {
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.6);
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
        max-width: 480px;
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
        padding: 16px 20px;
        border-bottom: 1px solid var(--md-sys-color-outline-variant);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .md-modal-body {
        padding: 20px;
        text-align: center;
    }

    .md-modal-footer {
        padding: 14px 20px;
        border-top: 1px solid var(--md-sys-color-outline-variant);
        display: flex;
        justify-content: flex-end;
    }
</style>
@endpush

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Pending Enrollment Alert Banner -->
    @if($pendingEnrollmentCount > 0)
        <div class="md-alert md-alert-warning" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="material-symbols-rounded">warning</span>
                <div>
                    Terdapat <b>{{ $pendingEnrollmentCount }} karyawan aktif</b> yang belum menyelesaikan pendaftaran biometrik wajah.
                </div>
            </div>
            <a href="{{ route('admin.enrollment.index') }}" class="md-btn-filled" style="height: 32px; padding: 0 12px; font-size: 12px;">
                <span>Lihat Daftar</span>
            </a>
        </div>
    @endif

    <!-- Real-Time Statistics Cards Grid -->
    <div class="stat-card-grid">
        <!-- Card 1: Tingkat Kehadiran -->
        <div class="md-card-elevated">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font: var(--md-sys-typescale-body-small); color: var(--md-sys-color-on-surface-variant);">Tingkat Kehadiran</span>
                <span style="font-size: 12px; color: var(--md-sys-color-primary); font-weight: 600;">
                    <span class="live-pulse"></span>Live
                </span>
            </div>
            <div class="stat-value" id="statPersentase" style="color: var(--md-sys-color-primary);">
                {{ $persentaseKehadiran }}%
            </div>
            <div style="font: var(--md-sys-typescale-body-small); color: var(--md-sys-color-outline);">
                Dari total <span id="statTotal">{{ $totalEmployees }}</span> karyawan aktif
            </div>
        </div>

        <!-- Card 2: Tepat Waktu -->
        <div class="md-card-elevated">
            <div style="font: var(--md-sys-typescale-body-small); color: var(--md-custom-color-success);">Hadir Tepat Waktu</div>
            <div class="stat-value" id="statTepatWaktu" style="color: var(--md-custom-color-success);">
                {{ $tepatWaktuCount }}
            </div>
            <div style="font: var(--md-sys-typescale-body-small); color: var(--md-sys-color-outline);">
                Check-in sebelum batas toleransi
            </div>
        </div>

        <!-- Card 3: Terlambat -->
        <div class="md-card-elevated">
            <div style="font: var(--md-sys-typescale-body-small); color: #E65100;">Hadir Terlambat</div>
            <div class="stat-value" id="statTerlambat" style="color: #E65100;">
                {{ $terlambatCount }}
            </div>
            <div style="font: var(--md-sys-typescale-body-small); color: var(--md-sys-color-outline);">
                Check-in lewat batas toleransi
            </div>
        </div>

        <!-- Card 4: Izin / Cuti / Sakit -->
        <div class="md-card-elevated">
            <div style="font: var(--md-sys-typescale-body-small); color: var(--md-sys-color-secondary);">Izin / Cuti / Sakit</div>
            <div class="stat-value" id="statIzin" style="color: var(--md-sys-color-secondary);">
                {{ $izinCount }}
            </div>
            <div style="font: var(--md-sys-typescale-body-small); color: var(--md-sys-color-outline);">
                Pengajuan telah disetujui
            </div>
        </div>

        <!-- Card 5: Alpha / Belum Hadir -->
        <div class="md-card-elevated">
            <div style="font: var(--md-sys-typescale-body-small); color: var(--md-sys-color-error);">Alpha / Belum Hadir</div>
            <div class="stat-value" id="statAlpha" style="color: var(--md-sys-color-error);">
                {{ $alphaCount > 0 ? $alphaCount : $belumHadirCount }}
            </div>
            <div style="font: var(--md-sys-typescale-body-small); color: var(--md-sys-color-outline);">
                {{ $alphaCount > 0 ? 'Tercatat Alpha' : 'Belum melakukan absensi' }}
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="md-card-filled" style="padding: 14px 16px;">
        <form action="{{ route('admin.dashboard') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <!-- Filter Tanggal -->
            <div style="flex: 1; min-width: 180px;">
                <label for="filterDate" class="md-form-label" style="font-size: 11px;">Tanggal Pantau</label>
                <input 
                    type="date" 
                    id="filterDate" 
                    name="date" 
                    class="md-input" 
                    value="{{ $formattedDate }}"
                    style="height: 38px;"
                >
            </div>

            <!-- Filter Departemen -->
            <div style="flex: 1; min-width: 180px;">
                <label for="filterDept" class="md-form-label" style="font-size: 11px;">Departemen</label>
                <select id="filterDept" name="department" class="md-input" style="height: 38px;">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div style="flex: 1; min-width: 180px;">
                <label for="filterStatus" class="md-form-label" style="font-size: 11px;">Status Kehadiran</label>
                <select id="filterStatus" name="status" class="md-input" style="height: 38px;">
                    <option value="">Semua Status</option>
                    <option value="tepat_waktu" {{ $status === 'tepat_waktu' ? 'selected' : '' }}>Tepat Waktu</option>
                    <option value="terlambat" {{ $status === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                    <option value="izin_cuti" {{ $status === 'izin_cuti' ? 'selected' : '' }}>Izin / Cuti / Sakit</option>
                    <option value="alpha" {{ $status === 'alpha' ? 'selected' : '' }}>Alpha</option>
                </select>
            </div>

            <!-- Buttons -->
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="md-btn-filled" style="height: 38px; padding: 0 16px;">
                    <span class="material-symbols-rounded">filter_alt</span>
                    <span>Terapkan</span>
                </button>
                <a href="{{ route('admin.dashboard') }}" class="md-btn-outlined" style="height: 38px; padding: 0 12px;" title="Reset Filter">
                    <span class="material-symbols-rounded">restart_alt</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Live Map Sebaran Widget -->
    <div class="md-card-elevated" style="padding: 0; overflow: hidden;">
        <div style="padding: 14px 20px; border-bottom: 1px solid var(--md-sys-color-outline-variant); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--md-sys-color-primary);">map</span>
                <span style="font: var(--md-sys-typescale-title-small); font-weight: 600;">Peta Sebaran Presensi Real-Time</span>
            </div>
            <a href="{{ route('admin.map.index', ['date' => $formattedDate]) }}" class="md-btn-outlined" style="height: 30px; padding: 0 10px; font-size: 12px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">open_in_full</span>
                <span>Buka Layar Penuh</span>
            </a>
        </div>
        <div id="dashboard-mini-map" style="width: 100%; height: 320px; z-index: 1;"></div>
    </div>

    <!-- Attendance Log Table -->
    <div class="md-table-container">
        <div class="md-table-toolbar">
            <div>
                <div class="md-table-title">
                    <span class="material-symbols-rounded" style="color: var(--md-sys-color-primary);">schedule</span>
                    <span>Log Aktivitas Presensi</span>
                </div>
                <div class="md-table-subtitle">
                    {{ \Carbon\Carbon::parse($formattedDate)->isoFormat('dddd, D MMMM Y') }}
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="md-badge" style="background-color: var(--md-sys-color-surface-container-high); color: var(--md-sys-color-on-surface-variant); font-size: 11px;" id="pollingIndicator">
                    Update otomatis: 30s
                </span>
            </div>
        </div>

        <div class="md-table-wrapper">
            <table class="md-table">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Departemen</th>
                        <th>Lokasi Presensi</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                        <th style="text-align: center;">Bukti Foto</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody">
                    @forelse($attendances as $att)
                        <tr>
                            <!-- Karyawan -->
                            <td>
                                <div class="md-table-user-name">{{ $att->user->name ?? '-' }}</div>
                                <div class="md-table-user-sub">NIK: <b>{{ $att->user->nik ?? '-' }}</b></div>
                            </td>

                            <!-- Departemen -->
                            <td>
                                <span style="font-weight: 500;">{{ $att->user->department ?? '-' }}</span>
                            </td>

                            <!-- Lokasi -->
                            <td>
                                <div style="font-weight: 500;">{{ $att->location->name ?? '-' }}</div>
                                @if($att->distance_meters !== null)
                                    <div class="md-table-user-sub">Jarak: {{ round($att->distance_meters) }}m</div>
                                @endif
                            </td>

                            <!-- Jam Masuk -->
                            <td>
                                @if($att->time_in)
                                    <div style="font-weight: 600; color: var(--md-custom-color-success);">{{ $att->time_in }} WITA</div>
                                @else
                                    <span style="color: var(--md-sys-color-outline);">--:--</span>
                                @endif
                            </td>

                            <!-- Jam Pulang -->
                            <td>
                                @if($att->time_out)
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span style="font-weight: 600; color: var(--md-sys-color-primary);">{{ $att->time_out }} WITA</span>
                                        @if($att->auto_checkout)
                                            <span class="md-badge" style="background-color: var(--md-sys-color-surface-container-high); color: var(--md-sys-color-outline); font-size: 10px; padding: 2px 6px;">Auto</span>
                                        @endif
                                    </div>
                                @else
                                    <span style="color: var(--md-sys-color-outline);">--:--</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td>
                                @php
                                    $badgeClass = match($att->status) {
                                        'tepat_waktu' => 'md-badge-tepat-waktu',
                                        'terlambat'   => 'md-badge-terlambat',
                                        'izin'        => 'md-badge-izin',
                                        'sakit'       => 'md-badge-sakit',
                                        'cuti'        => 'md-badge-cuti',
                                        'alpha'       => 'md-badge-alpha',
                                        default       => 'md-badge-alpha',
                                    };
                                @endphp
                                <span class="md-badge {{ $badgeClass }}">
                                    {{ strtoupper(str_replace('_', ' ', $att->status)) }}
                                </span>
                            </td>

                            <!-- Foto Snapshot -->
                            <td style="text-align: center;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    @if($att->photo_in)
                                        <img 
                                            src="{{ asset('storage/' . $att->photo_in) }}" 
                                            alt="Foto Masuk" 
                                            class="photo-thumb" 
                                            title="Foto Masuk: {{ $att->user->name }}"
                                            onclick="openPhotoModal('{{ asset('storage/' . $att->photo_in) }}', 'Foto Masuk: {{ $att->user->name }} ({{ $att->time_in }} WITA)')"
                                        >
                                    @endif

                                    @if($att->photo_out)
                                        <img 
                                            src="{{ asset('storage/' . $att->photo_out) }}" 
                                            alt="Foto Pulang" 
                                            class="photo-thumb" 
                                            title="Foto Pulang: {{ $att->user->name }}"
                                            onclick="openPhotoModal('{{ asset('storage/' . $att->photo_out) }}', 'Foto Pulang: {{ $att->user->name }} ({{ $att->time_out }} WITA)')"
                                        >
                                    @endif

                                    @if(!$att->photo_in && !$att->photo_out)
                                        <span style="color: var(--md-sys-color-outline); font-size: 12px;">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="md-table-empty">
                                <span class="material-symbols-rounded">event_busy</span>
                                <div class="md-table-empty-title">Tidak Ada Catatan Presensi</div>
                                <div class="md-table-empty-sub">Tidak ada aktivitas presensi yang tercatat untuk tanggal dan filter ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="md-pagination-container">
                {{ $attendances->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Pratinjau Foto Snapshot Resolusi Penuh -->
<div class="md-modal-backdrop" id="photoModal">
    <div class="md-modal-dialog">
        <div class="md-modal-header">
            <h3 style="font: var(--md-sys-typescale-title-small); margin: 0;" id="photoModalTitle">Bukti Foto Presensi</h3>
            <button type="button" style="background: none; border: none; cursor: pointer; color: var(--md-sys-color-outline);" onclick="closePhotoModal()">
                <span class="material-symbols-rounded">close</span>
            </button>
        </div>
        <div class="md-modal-body">
            <img id="photoModalImage" src="" alt="Bukti Foto Presensi" style="width: 100%; max-height: 380px; object-fit: contain; border-radius: 12px; background: #000;">
        </div>
        <div class="md-modal-footer">
            <button type="button" class="md-btn-filled" onclick="closePhotoModal()">Tutup</button>
        </div>
    </div>
</div>

<script>
    const photoModal = document.getElementById('photoModal');
    const photoModalImage = document.getElementById('photoModalImage');
    const photoModalTitle = document.getElementById('photoModalTitle');

    function openPhotoModal(imgSrc, title) {
        photoModalImage.src = imgSrc;
        photoModalTitle.textContent = title;
        photoModal.classList.add('open');
    }

    function closePhotoModal() {
        photoModal.classList.remove('open');
        photoModalImage.src = '';
    }

    photoModal.addEventListener('click', (e) => {
        if (e.target === photoModal) closePhotoModal();
    });

    // Inisialisasi Widget Mini Map
    const activeLocations = @json($activeLocations);
    const mappedAttendances = @json($mappedAttendances);

    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('dashboard-mini-map')) {
            initAttendanceLiveMap('dashboard-mini-map', activeLocations, mappedAttendances, { zoom: 12 });
        }
    });

    // Client-side Polling Berkala (Interval 30 Detik)
    const POLLING_URL = '{{ route("admin.monitoring.data") }}?date={{ $formattedDate }}&department={{ $department }}';

    setInterval(async () => {
        try {
            const response = await fetch(POLLING_URL, { headers: { 'Accept': 'application/json' } });
            if (response.ok) {
                const data = await response.json();
                document.getElementById('statPersentase').textContent = `${data.persentase_kehadiran}%`;
                document.getElementById('statTotal').textContent = data.total_employees;
                document.getElementById('statTepatWaktu').textContent = data.tepat_waktu;
                document.getElementById('statTerlambat').textContent = data.terlambat;
                document.getElementById('statIzin').textContent = data.izin;
                document.getElementById('statAlpha').textContent = data.alpha > 0 ? data.alpha : data.belum_hadir;
            }
        } catch (err) {
            console.error('Polling update gagal:', err);
        }
    }, 30000);
</script>
@endsection
