@extends('layouts.admin')

@section('title', 'Evaluasi & Approval Izin - Presensi PT. CAK')
@section('page_title', 'Evaluasi & Approval Izin Karyawan')

@push('styles')
<style>
    /* Modal Dialog M3 */
    .md-modal-backdrop {
        position: fixed;
        inset: 0;
        background-color: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
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
        border-radius: 20px;
        width: 100%;
        max-width: 540px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--md-sys-color-outline-variant);
        overflow: hidden;
        animation: modalScale 0.2s var(--md-sys-motion-easing-emphasized);
    }

    .md-modal-dialog-large {
        max-width: 800px;
    }

    @keyframes modalScale {
        from { transform: scale(0.95); opacity: 0; }
        to   { transform: scale(1); opacity: 1; }
    }

    .md-modal-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--md-sys-color-outline-variant);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: var(--md-sys-color-surface-container-low);
    }

    .md-modal-body {
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .md-modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--md-sys-color-outline-variant);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background-color: var(--md-sys-color-surface-container-low);
    }

    .filter-tab {
        padding: 8px 16px;
        border-radius: var(--md-sys-shape-corner-full);
        font: var(--md-sys-typescale-label-large);
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        background-color: var(--md-sys-color-surface-container);
        color: var(--md-sys-color-on-surface-variant);
        border: 1px solid var(--md-sys-color-outline-variant);
    }

    .filter-tab:hover {
        background-color: var(--md-sys-color-surface-container-high);
        color: var(--md-sys-color-on-surface);
    }

    .filter-tab.active {
        background-color: var(--md-sys-color-primary-container);
        color: var(--md-sys-color-on-primary-container);
        border-color: var(--md-sys-color-primary);
    }

    /* Decision Radio Card */
    .decision-card-label {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        border: 1.5px solid var(--md-sys-color-outline-variant);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        background-color: var(--md-sys-color-surface-container-lowest);
    }

    .decision-card-label:hover {
        border-color: var(--md-sys-color-primary);
        background-color: var(--md-sys-color-surface-container-low);
    }

    .decision-card-label input[type="radio"]:checked + .decision-text-approve {
        color: var(--md-custom-color-success);
        font-weight: 700;
    }

    .decision-card-label input[type="radio"]:checked + .decision-text-reject {
        color: var(--md-sys-color-error);
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Filter Tabs -->
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.leaves.index', ['status' => 'pending']) }}" class="filter-tab {{ $status === 'pending' ? 'active' : '' }}">
            <span>Menunggu Evaluasi</span>
            <span class="md-badge" style="background-color: #FFF3E0; color: #E65100;">{{ $pendingCount }}</span>
        </a>

        <a href="{{ route('admin.leaves.index', ['status' => 'approved']) }}" class="filter-tab {{ $status === 'approved' ? 'active' : '' }}">
            <span>Disetujui</span>
            <span class="md-badge md-badge-approved">{{ $approvedCount }}</span>
        </a>

        <a href="{{ route('admin.leaves.index', ['status' => 'rejected']) }}" class="filter-tab {{ $status === 'rejected' ? 'active' : '' }}">
            <span>Ditolak</span>
            <span class="md-badge md-badge-terlambat">{{ $rejectedCount }}</span>
        </a>

        <a href="{{ route('admin.leaves.index', ['status' => 'all']) }}" class="filter-tab {{ $status === 'all' ? 'active' : '' }}">
            <span>Semua Pengajuan</span>
        </a>
    </div>

    <!-- Table of Leaves -->
    <div class="md-table-container">
        <div class="md-table-toolbar">
            <div>
                <div class="md-table-title">
                    <span class="material-symbols-rounded" style="color: var(--md-sys-color-primary);">fact_check</span>
                    <span>Daftar Pengajuan Izin & Cuti</span>
                </div>
                <div class="md-table-subtitle">
                    Tinjauan dan persetujuan pengajuan ketidakhadiran karyawan PT. CAK
                </div>
            </div>
            <span class="md-badge" style="background-color: var(--md-sys-color-surface-container-high); color: var(--md-sys-color-on-surface-variant); font-size: 11px;">
                Total: {{ $leaves->total() }} Data
            </span>
        </div>

        <div class="md-table-wrapper">
            <table class="md-table">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Jenis Izin</th>
                        <th>Rentang Tanggal</th>
                        <th>Alasan Pengajuan</th>
                        <th>Lampiran</th>
                        <th>Status</th>
                        <th style="text-align: center;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                        <tr>
                            <!-- Karyawan -->
                            <td>
                                <div class="md-table-user-name">{{ $leave->user->name ?? 'Karyawan' }}</div>
                                <div class="md-table-user-sub">NIK: <b>{{ $leave->user->nik ?? '-' }}</b></div>
                            </td>

                            <!-- Jenis -->
                            <td>
                                <span class="md-badge" style="background-color: var(--md-sys-color-secondary-container); color: var(--md-sys-color-on-secondary-container);">
                                    {{ $leave->type }}
                                </span>
                            </td>

                            <!-- Tanggal -->
                            <td>
                                @php
                                    $start = \Carbon\Carbon::parse($leave->start_date);
                                    $end = \Carbon\Carbon::parse($leave->end_date);
                                    $days = $start->diffInDays($end) + 1;
                                @endphp
                                <div style="font-weight: 600;">{{ $start->isoFormat('D MMM Y') }} s/d {{ $end->isoFormat('D MMM Y') }}</div>
                                <div class="md-table-user-sub">Durasi: {{ $days }} Hari Kerja</div>
                            </td>

                            <!-- Alasan -->
                            <td style="max-width: 240px;">
                                <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--md-sys-color-on-surface-variant);" title="{{ $leave->reason }}">
                                    {{ $leave->reason }}
                                </div>
                            </td>

                            <!-- Lampiran -->
                            <td>
                                @if($leave->attachment_file)
                                    <button 
                                        type="button" 
                                        class="md-btn-outlined" 
                                        style="height: 30px; padding: 0 10px; font-size: 11px; gap: 4px;"
                                        onclick="openAttachmentModal('{{ asset('storage/' . $leave->attachment_file) }}')"
                                    >
                                        <span class="material-symbols-rounded" style="font-size: 15px;">visibility</span>
                                        <span>Lihat Bukti</span>
                                    </button>
                                @else
                                    <span style="color: var(--md-sys-color-outline); font-size: 12px;">Tanpa Berkas</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td>
                                @if($leave->status === 'pending')
                                    <span class="md-badge md-badge-pending">Pending</span>
                                @elseif($leave->status === 'approved')
                                    <span class="md-badge md-badge-approved">Approved</span>
                                @elseif($leave->status === 'rejected')
                                    <span class="md-badge md-badge-rejected">Rejected</span>
                                @else
                                    <span class="md-badge md-badge-alpha">Cancelled</span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td style="text-align: center;">
                                @if($leave->status === 'pending')
                                    <button 
                                        type="button" 
                                        class="md-btn-filled" 
                                        style="height: 32px; padding: 0 12px; font-size: 11px; gap: 4px;"
                                        onclick="openProcessModal({{ json_encode($leave) }}, '{{ $leave->user->name }}')"
                                    >
                                        <span class="material-symbols-rounded" style="font-size: 15px;">rule</span>
                                        <span>Evaluasi</span>
                                    </button>
                                @else
                                    <div style="font-size: 11.5px; color: var(--md-sys-color-outline);">
                                        <div>Oleh: <b>{{ $leave->approver->name ?? 'Sistem' }}</b></div>
                                        @if($leave->review_note)
                                            <div style="font-style: italic; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 2px;" title="{{ $leave->review_note }}">
                                                "{{ $leave->review_note }}"
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="md-table-empty">
                                <span class="material-symbols-rounded">inbox</span>
                                <div class="md-table-empty-title">Tidak Ada Pengajuan Izin</div>
                                <div class="md-table-empty-sub">Tidak ada data pengajuan perizinan pada kategori filter ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaves->hasPages())
            <div class="md-pagination-container">
                {{ $leaves->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Evaluasi & Approval Izin -->
<div class="md-modal-backdrop" id="processModal">
    <div class="md-modal-dialog">
        <form id="processForm" method="POST" action="">
            @csrf

            <div class="md-modal-header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded" style="color: var(--md-sys-color-primary);">fact_check</span>
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Evaluasi Pengajuan Izin</h3>
                </div>
                <button type="button" style="background: none; border: none; cursor: pointer; color: var(--md-sys-color-outline); padding: 4px;" onclick="closeProcessModal()">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>

            <div class="md-modal-body">
                <!-- Info Karyawan Box -->
                <div style="background-color: var(--md-sys-color-surface-container); border: 1px solid var(--md-sys-color-outline-variant); padding: 14px 16px; border-radius: 12px; font-size: 13px; line-height: 1.5;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Karyawan:</span>
                        <b id="modalEmployeeName" style="color: var(--md-sys-color-on-surface);"></b>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 4px;">
                        <span>Periode / Jenis:</span>
                        <b id="modalLeavePeriod" style="color: var(--md-sys-color-primary);"></b>
                    </div>
                    <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--md-sys-color-outline-variant);">
                        <span style="font-size: 11px; color: var(--md-sys-color-outline); display: block;">Alasan:</span>
                        <span id="modalLeaveReason" style="color: var(--md-sys-color-on-surface); font-weight: 500;"></span>
                    </div>
                </div>

                <!-- Pilihan Tindakan -->
                <div>
                    <label class="md-form-label">Keputusan Evaluasi HRD</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <label class="decision-card-label">
                            <input type="radio" name="action" value="approve" checked style="accent-color: #2E7D32;">
                            <span class="decision-text-approve" style="font-size: 13px;">Setujui (Approve)</span>
                        </label>

                        <label class="decision-card-label">
                            <input type="radio" name="action" value="reject" style="accent-color: #C62828;">
                            <span class="decision-text-reject" style="font-size: 13px;">Tolak (Reject)</span>
                        </label>
                    </div>
                </div>

                <!-- Catatan Evaluasi -->
                <div>
                    <label for="reviewNote" class="md-form-label">Catatan Evaluasi / Alasan (Opsional)</label>
                    <textarea id="reviewNote" name="review_note" class="md-textarea" rows="3" placeholder="Tuliskan catatan atau instruksi bagi karyawan jika diperlukan..."></textarea>
                </div>
            </div>

            <div class="md-modal-footer">
                <button type="button" class="md-btn-outlined" onclick="closeProcessModal()">Batal</button>
                <button type="submit" class="md-btn-filled">Konfirmasi Keputusan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pratinjau Berkas Lampiran -->
<div class="md-modal-backdrop" id="attachmentModal">
    <div class="md-modal-dialog md-modal-dialog-large">
        <div class="md-modal-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Berkas Bukti Lampiran</h3>
            <button type="button" style="background: none; border: none; cursor: pointer; color: var(--md-sys-color-outline);" onclick="closeAttachmentModal()">
                <span class="material-symbols-rounded">close</span>
            </button>
        </div>

        <div class="md-modal-body" style="padding: 20px; text-align: center; max-height: 70vh; overflow-y: auto;">
            <div id="attachmentContainer"></div>
        </div>

        <div class="md-modal-footer">
            <a href="" id="btnDownloadAttachment" target="_blank" class="md-btn-filled">
                <span class="material-symbols-rounded" style="font-size: 18px;">download</span>
                <span>Buka di Tab Baru</span>
            </a>
            <button type="button" class="md-btn-outlined" onclick="closeAttachmentModal()">Tutup</button>
        </div>
    </div>
</div>

<script>
    const processModal = document.getElementById('processModal');
    const processForm = document.getElementById('processForm');
    const modalEmployeeName = document.getElementById('modalEmployeeName');
    const modalLeavePeriod = document.getElementById('modalLeavePeriod');
    const modalLeaveReason = document.getElementById('modalLeaveReason');
    const reviewNote = document.getElementById('reviewNote');

    const attachmentModal = document.getElementById('attachmentModal');
    const attachmentContainer = document.getElementById('attachmentContainer');
    const btnDownloadAttachment = document.getElementById('btnDownloadAttachment');

    function openProcessModal(leave, employeeName) {
        processForm.action = `/admin/leaves/${leave.id}/process`;
        modalEmployeeName.textContent = employeeName;
        modalLeavePeriod.textContent = `${leave.start_date} s/d ${leave.end_date} (${leave.type.toUpperCase()})`;
        modalLeaveReason.textContent = leave.reason;
        reviewNote.value = '';
        processModal.classList.add('open');
    }

    function closeProcessModal() {
        processModal.classList.remove('open');
    }

    function openAttachmentModal(fileUrl) {
        btnDownloadAttachment.href = fileUrl;
        const isPdf = fileUrl.toLowerCase().endsWith('.pdf');

        if (isPdf) {
            attachmentContainer.innerHTML = `<iframe src="${fileUrl}" style="width: 100%; height: 500px; border: none; border-radius: 8px;"></iframe>`;
        } else {
            attachmentContainer.innerHTML = `<img src="${fileUrl}" alt="Bukti Lampiran" style="max-width: 100%; max-height: 500px; object-fit: contain; border-radius: 8px;">`;
        }

        attachmentModal.classList.add('open');
    }

    function closeAttachmentModal() {
        attachmentModal.classList.remove('open');
        attachmentContainer.innerHTML = '';
    }

    window.addEventListener('click', (e) => {
        if (e.target === processModal) closeProcessModal();
        if (e.target === attachmentModal) closeAttachmentModal();
    });
</script>
@endsection
