@extends('layouts.admin')

@section('title', 'Rekapitulasi Laporan Presensi - Presensi PT. CAK')
@section('page_title', 'Rekapitulasi Laporan Presensi Karyawan')

@push('styles')
<style>
    .report-filter-card {
        background-color: var(--md-sys-color-surface-container-low);
        border: 1px solid var(--md-sys-color-outline-variant);
        border-radius: var(--md-sys-shape-corner-large);
        padding: 20px 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: 180px 180px 1fr auto;
        gap: 16px;
        align-items: flex-end;
    }

    @media (max-width: 1024px) {
        .filter-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 640px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }
    }

    .preset-pill-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        padding-top: 12px;
        border-top: 1px solid var(--md-sys-color-outline-variant);
    }

    .preset-pill-btn {
        height: 30px;
        padding: 0 12px;
        border-radius: var(--md-sys-shape-corner-full);
        border: 1px solid var(--md-sys-color-outline-variant);
        background-color: var(--md-sys-color-surface-container-lowest);
        color: var(--md-sys-color-on-surface-variant);
        font: var(--md-sys-typescale-label-small);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .preset-pill-btn:hover {
        background-color: var(--md-sys-color-primary-container);
        border-color: var(--md-sys-color-primary);
        color: var(--md-sys-color-primary);
    }

    .stats-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
    }

    .report-stat-card {
        padding: 20px;
        border-radius: var(--md-sys-shape-corner-large);
        background-color: var(--md-sys-color-surface-container-lowest);
        border: 1px solid var(--md-sys-color-outline-variant);
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .report-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.08);
    }

    .stat-icon-badge {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .report-stat-num {
        font-size: 22px;
        font-weight: 700;
        line-height: 1.2;
    }

    .report-table-wrapper {
        background-color: var(--md-sys-color-surface-container-lowest);
        border-radius: var(--md-sys-shape-corner-large);
        border: 1px solid var(--md-sys-color-outline-variant);
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13px;
    }

    .report-table th {
        background-color: var(--md-sys-color-surface-container);
        color: var(--md-sys-color-on-surface-variant);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--md-sys-color-outline-variant);
        white-space: nowrap;
    }

    .report-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--md-sys-color-outline-variant);
        color: var(--md-sys-color-on-surface);
        vertical-align: middle;
    }

    .report-table tr:hover td {
        background-color: var(--md-sys-color-surface-container-low);
    }
</style>
@endpush

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Filter Toolbar Form -->
    <div class="report-filter-card">
        <form id="reportFilterForm" action="{{ route('admin.reports.index') }}" method="GET" style="display: flex; flex-direction: column; gap: 14px;">
            <div class="filter-grid">
                <!-- Tanggal Mulai -->
                <div>
                    <label for="startDateInput" class="md-form-label">Tanggal Mulai</label>
                    <input 
                        type="date" 
                        id="startDateInput" 
                        name="start_date" 
                        class="md-input" 
                        value="{{ $startDateStr }}"
                    >
                </div>

                <!-- Tanggal Selesai -->
                <div>
                    <label for="endDateInput" class="md-form-label">Tanggal Selesai</label>
                    <input 
                        type="date" 
                        id="endDateInput" 
                        name="end_date" 
                        class="md-input" 
                        value="{{ $endDateStr }}"
                    >
                </div>

                <!-- Departemen -->
                <div>
                    <label for="deptSelect" class="md-form-label">Departemen</label>
                    <select id="deptSelect" name="department" class="md-input">
                        <option value="">Semua Departemen</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Buttons: Pratinjau, Excel, PDF -->
                <div style="display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap;">
                    <button type="submit" class="md-btn-filled" title="Filter Pratinjau">
                        <span class="material-symbols-rounded" style="font-size: 18px;">filter_alt</span>
                        <span>Pratinjau</span>
                    </button>

                    <a href="{{ route('admin.reports.export.excel', ['start_date' => $startDateStr, 'end_date' => $endDateStr, 'department' => $department]) }}" class="btn-action-excel" title="Download Excel (.xlsx)">
                        <span class="material-symbols-rounded" style="font-size: 18px;">table_view</span>
                        <span>Excel</span>
                    </a>

                    <a href="{{ route('admin.reports.export.pdf', ['start_date' => $startDateStr, 'end_date' => $endDateStr, 'department' => $department]) }}" class="btn-action-pdf" title="Download PDF (.pdf)">
                        <span class="material-symbols-rounded" style="font-size: 18px;">picture_as_pdf</span>
                        <span>PDF</span>
                    </a>
                </div>
            </div>

            <!-- Quick Presets Toolbar -->
            <div class="preset-pill-group">
                <span style="font-size: 12px; font-weight: 600; color: var(--md-sys-color-outline); margin-right: 4px;">Preset Periode:</span>
                <button type="button" class="preset-pill-btn" onclick="applyPreset('today')">Hari Ini</button>
                <button type="button" class="preset-pill-btn" onclick="applyPreset('7days')">7 Hari Terakhir</button>
                <button type="button" class="preset-pill-btn" onclick="applyPreset('this_month')">Bulan Ini</button>
                <button type="button" class="preset-pill-btn" onclick="applyPreset('last_month')">Bulan Lalu</button>
            </div>
        </form>
    </div>

    <!-- Summary Stat Cards Grid -->
    <div class="stats-summary-grid">
        <!-- 1. Total Karyawan -->
        <div class="report-stat-card">
            <div class="stat-icon-badge" style="background-color: var(--md-sys-color-primary-container); color: var(--md-sys-color-primary);">
                <span class="material-symbols-rounded">group</span>
            </div>
            <div>
                <div style="font-size: 12px; font-weight: 600; color: var(--md-sys-color-outline);">Total Karyawan</div>
                <div class="report-stat-num" style="color: var(--md-sys-color-primary);">{{ $totalKaryawan }} <span style="font-size: 14px; font-weight: 500;">Orang</span></div>
                <div style="font-size: 11px; color: var(--md-sys-color-outline); margin-top: 2px;">Terdata pada periode ini</div>
            </div>
        </div>

        <!-- 2. Jam Kerja Bersih -->
        <div class="report-stat-card">
            <div class="stat-icon-badge" style="background-color: #E8F5E9; color: #2E7D32;">
                <span class="material-symbols-rounded">schedule</span>
            </div>
            <div>
                <div style="font-size: 12px; font-weight: 600; color: #2E7D32;">Total Jam Kerja Bersih</div>
                <div class="report-stat-num" style="color: #2E7D32;">{{ number_format($totalSemuaJamKerja, 1) }} <span style="font-size: 14px; font-weight: 500;">Jam</span></div>
                <div style="font-size: 11px; color: var(--md-sys-color-outline); margin-top: 2px;">Akumulasi seluruh staf</div>
            </div>
        </div>

        <!-- 3. Hadir Terlambat -->
        <div class="report-stat-card">
            <div class="stat-icon-badge" style="background-color: #FFF3E0; color: #E65100;">
                <span class="material-symbols-rounded">warning</span>
            </div>
            <div>
                <div style="font-size: 12px; font-weight: 600; color: #E65100;">Total Terlambat</div>
                <div class="report-stat-num" style="color: #E65100;">{{ $totalSemuaTerlambat }} <span style="font-size: 14px; font-weight: 500;">Hari</span></div>
                <div style="font-size: 11px; color: var(--md-sys-color-outline); margin-top: 2px;">Lewat toleransi 15 mnt</div>
            </div>
        </div>

        <!-- 4. Izin & Cuti -->
        <div class="report-stat-card">
            <div class="stat-icon-badge" style="background-color: var(--md-sys-color-secondary-container); color: var(--md-sys-color-on-secondary-container);">
                <span class="material-symbols-rounded">event_note</span>
            </div>
            <div>
                <div style="font-size: 12px; font-weight: 600; color: var(--md-sys-color-primary);">Total Izin / Cuti</div>
                <div class="report-stat-num" style="color: var(--md-sys-color-primary);">{{ $totalSemuaIzinCuti }} <span style="font-size: 14px; font-weight: 500;">Hari</span></div>
                <div style="font-size: 11px; color: var(--md-sys-color-outline); margin-top: 2px;">Telah disetujui HRD</div>
            </div>
        </div>

        <!-- 5. Total Alpha -->
        <div class="report-stat-card">
            <div class="stat-icon-badge" style="background-color: var(--md-sys-color-error-container); color: var(--md-sys-color-on-error-container);">
                <span class="material-symbols-rounded">person_off</span>
            </div>
            <div>
                <div style="font-size: 12px; font-weight: 600; color: var(--md-sys-color-error);">Total Alpha</div>
                <div class="report-stat-num" style="color: var(--md-sys-color-error);">{{ $totalSemuaAlpha }} <span style="font-size: 14px; font-weight: 500;">Hari</span></div>
                <div style="font-size: 11px; color: var(--md-sys-color-outline); margin-top: 2px;">Tanpa konfirmasi hadir</div>
            </div>
        </div>
    </div>

    <!-- Data Preview Table -->
    <!-- Data Preview Table -->
    <div class="md-table-container">
        <div class="md-table-toolbar">
            <div>
                <div class="md-table-title">
                    <span class="material-symbols-rounded" style="color: var(--md-sys-color-primary);">analytics</span>
                    <span>Pratinjau Rekapitulasi Presensi Karyawan</span>
                </div>
                <div class="md-table-subtitle">
                    Rentang Waktu: <b>{{ $startDate->isoFormat('D MMMM Y') }}</b> s/d <b>{{ $endDate->isoFormat('D MMMM Y') }}</b>
                </div>
            </div>

            <span class="md-badge" style="background-color: var(--md-sys-color-surface-container-high); color: var(--md-sys-color-on-surface-variant); font-size: 11px;">
                Total Data: <b style="color: var(--md-sys-color-primary);">{{ $reportData->total() }} Karyawan</b>
            </span>
        </div>

        <div class="md-table-wrapper">
            <table class="md-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 48px;">No</th>
                        <th>NIK</th>
                        <th>Nama Karyawan</th>
                        <th>Departemen</th>
                        <th style="text-align: center;">Tepat</th>
                        <th style="text-align: center;">Telat</th>
                        <th style="text-align: center;">Total Hadir</th>
                        <th style="text-align: center;">Akumulasi Telat</th>
                        <th style="text-align: center;">Izin</th>
                        <th style="text-align: center;">Sakit</th>
                        <th style="text-align: center;">Cuti</th>
                        <th style="text-align: center;">Alpha</th>
                        <th style="text-align: center;">Jam Kerja Bersih</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $index => $row)
                        <tr>
                            <td style="text-align: center; color: var(--md-sys-color-outline); font-weight: 600;">
                                {{ ($reportData->currentPage() - 1) * $reportData->perPage() + $index + 1 }}
                            </td>
                            <td style="font-family: monospace; font-size: 12px; font-weight: 600; color: var(--md-sys-color-primary);">{{ $row['nik'] }}</td>
                            <td style="font-weight: 600; color: var(--md-sys-color-on-surface);">{{ $row['name'] }}</td>
                            <td style="font-size: 12.5px; color: var(--md-sys-color-on-surface-variant);">{{ $row['department'] }}</td>
                            <td style="text-align: center; color: var(--md-custom-color-success); font-weight: 700;">{{ $row['total_hadir_tepat_waktu'] }}</td>
                            <td style="text-align: center; color: var(--md-custom-color-warning); font-weight: 700;">{{ $row['total_terlambat'] }}</td>
                            <td style="text-align: center; font-weight: 800; font-size: 14px;">{{ $row['total_hadir'] }}</td>
                            <td style="text-align: center; font-size: 12px; color: {{ $row['total_menit_terlambat'] > 0 ? 'var(--md-custom-color-warning); font-weight: 700;' : 'var(--md-sys-color-outline);' }}">
                                {{ $row['total_menit_terlambat'] }} mnt
                            </td>
                            <td style="text-align: center; font-weight: 500;">{{ $row['total_izin'] }}</td>
                            <td style="text-align: center; font-weight: 500;">{{ $row['total_sakit'] }}</td>
                            <td style="text-align: center; font-weight: 500;">{{ $row['total_cuti'] }}</td>
                            <td style="text-align: center; {{ $row['total_alpha'] > 0 ? 'color: var(--md-sys-color-error); font-weight: 700;' : 'color: var(--md-sys-color-outline);' }}">
                                {{ $row['total_alpha'] }}
                            </td>
                            <td style="text-align: center; font-weight: 700; background-color: var(--md-sys-color-surface-container-low); color: var(--md-sys-color-primary);">
                                {{ $row['total_jam_kerja'] }} Jam
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="md-table-empty">
                                <span class="material-symbols-rounded">search_off</span>
                                <div class="md-table-empty-title">Data Tidak Ditemukan</div>
                                <div class="md-table-empty-sub">Tidak ada data presensi karyawan pada periode dan filter departemen yang dipilih.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reportData->hasPages())
            <div class="md-pagination-container">
                {{ $reportData->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    function applyPreset(preset) {
        const today = new Date();
        const startInput = document.getElementById('startDateInput');
        const endInput = document.getElementById('endDateInput');

        const formatDate = (date) => {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        };

        if (preset === 'today') {
            const todayStr = formatDate(today);
            startInput.value = todayStr;
            endInput.value = todayStr;
        } else if (preset === '7days') {
            const sevenDaysAgo = new Date();
            sevenDaysAgo.setDate(today.getDate() - 6);
            startInput.value = formatDate(sevenDaysAgo);
            endInput.value = formatDate(today);
        } else if (preset === 'this_month') {
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            startInput.value = formatDate(firstDay);
            endInput.value = formatDate(today);
        } else if (preset === 'last_month') {
            const firstDayLastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const lastDayLastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
            startInput.value = formatDate(firstDayLastMonth);
            endInput.value = formatDate(lastDayLastMonth);
        }

        document.getElementById('reportFilterForm').submit();
    }
</script>
@endpush
