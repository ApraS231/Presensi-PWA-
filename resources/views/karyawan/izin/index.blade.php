@extends('layouts.pwa')

@section('title', 'Riwayat Pengajuan Perizinan - Presensi PT. CAK')

@section('content')
<div style="display: flex; flex-direction: column; gap: 18px;">

    <!-- Header & Action Button -->
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
        <div>
            <div class="clay-pill-tag" style="margin-bottom: 6px;">
                <span class="material-symbols-rounded" style="font-size: 13px;">fact_check</span>
                <span>Modul Perizinan</span>
            </div>
            <h2 class="font-editorial-serif" style="font-size: 24px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0; line-height: 1.2;">
                Riwayat Izin & Cuti
            </h2>
            <p style="font-size: 12.5px; color: var(--md-sys-color-on-surface-variant); margin: 2px 0 0 0;">
                Kelola permohonan izin, sakit, dan cuti kerja karyawan.
            </p>
        </div>
        <a href="{{ route('karyawan.izin.create') }}" class="clay-btn clay-btn-primary" style="height: 40px; padding: 0 18px;">
            <span class="material-symbols-rounded" style="font-size: 18px;">add</span>
            <span>Ajukan Izin</span>
        </a>
    </div>

    <!-- List of Leaves -->
    @forelse($leaves as $leave)
        <div class="clay-card" style="padding: 20px; display: flex; flex-direction: column; gap: 14px;">
            <!-- Card Header: Type Badge & Status -->
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                <span class="clay-pill-tag" style="color: var(--color-ocean-blue); border-color: var(--color-cloudy-sky);">
                    {{ strtoupper($leave->type) }}
                </span>

                @if($leave->status === 'pending')
                    <span class="md-badge md-badge-pending">Menunggu Review</span>
                @elseif($leave->status === 'approved')
                    <span class="md-badge md-badge-approved">Disetujui</span>
                @elseif($leave->status === 'rejected')
                    <span class="md-badge md-badge-rejected">Ditolak</span>
                @else
                    <span class="md-badge md-badge-alpha">Dibatalkan</span>
                @endif
            </div>

            <!-- Date Range & Duration -->
            <div>
                @php
                    $start = \Carbon\Carbon::parse($leave->start_date);
                    $end = \Carbon\Carbon::parse($leave->end_date);
                    $days = $start->diffInDays($end) + 1;
                @endphp
                <div class="font-editorial-grotesk" style="font-size: 17px; font-weight: 800; color: var(--md-sys-color-on-surface);">
                    {{ $start->isoFormat('D MMM Y') }} s/d {{ $end->isoFormat('D MMM Y') }}
                </div>
                <div style="font-size: 12px; color: var(--md-sys-color-outline); margin-top: 2px;">
                    Durasi: <b>{{ $days }}</b> hari kerja
                </div>
            </div>

            <!-- Reason (Clay Inset Box) -->
            <div class="clay-inset-box" style="font-size: 13px; line-height: 1.5;">
                <div style="font-size: 11px; font-weight: 700; color: var(--color-ocean-blue); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 3px;">Alasan Pengajuan:</div>
                <div style="color: var(--md-sys-color-on-surface);">{{ $leave->reason }}</div>
            </div>

            <!-- Review Note (if rejected/approved with note) -->
            @if($leave->review_note)
                <div class="clay-card-cloudy" style="padding: 12px 14px; font-size: 12.5px; border-left: 4px solid var(--color-ocean-blue);">
                    <div style="font-weight: 700; color: var(--color-ocean-blue); margin-bottom: 2px;">Catatan Evaluasi HRD:</div>
                    <div>{{ $leave->review_note }}</div>
                </div>
            @endif

            <!-- Attachment Link -->
            @if($leave->attachment_file)
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 16px; color: var(--color-ocean-blue);">attach_file</span>
                    <a href="{{ asset('storage/' . $leave->attachment_file) }}" target="_blank" style="font-size: 12.5px; color: var(--color-ocean-blue); text-decoration: none; font-weight: 600;">
                        Lihat Berkas Lampiran Bukti
                    </a>
                </div>
            @endif

            <!-- Actions (Pending Only) -->
            @if($leave->status === 'pending')
                <div style="display: flex; gap: 10px; margin-top: 4px; border-top: 1px solid var(--md-sys-color-outline-variant); padding-top: 14px;">
                    <a href="{{ route('karyawan.izin.edit', $leave->id) }}" class="clay-btn clay-btn-cloudy" style="flex: 1; height: 38px; font-size: 12.5px;">
                        <span class="material-symbols-rounded" style="font-size: 16px;">edit</span>
                        <span>Edit</span>
                    </a>

                    <form action="{{ route('karyawan.izin.cancel', $leave->id) }}" method="POST" style="flex: 1; margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan izin ini?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="clay-btn" style="width: 100%; height: 38px; font-size: 12.5px; background: var(--md-sys-color-error-container); color: var(--md-sys-color-on-error-container) !important; justify-content: center; box-shadow: 0 4px 10px rgba(186, 26, 26, 0.15);">
                            <span class="material-symbols-rounded" style="font-size: 16px;">close</span>
                            <span>Batalkan</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @empty
        <div class="clay-card" style="text-align: center; padding: 44px 20px;">
            <span class="material-symbols-rounded" style="font-size: 48px; color: var(--md-sys-color-outline); margin-bottom: 8px; opacity: 0.5;">description</span>
            <h3 class="font-editorial-serif" style="font-size: 18px; font-weight: 700; color: var(--md-sys-color-on-surface); margin-bottom: 4px;">Belum Ada Riwayat Izin</h3>
            <p style="font-size: 13px; color: var(--md-sys-color-on-surface-variant); margin-bottom: 18px;">
                Anda belum pernah mengajukan izin, cuti, atau sakit.
            </p>
            <a href="{{ route('karyawan.izin.create') }}" class="clay-btn clay-btn-primary" style="display: inline-flex;">
                <span class="material-symbols-rounded">add</span>
                <span>Ajukan Sekarang</span>
            </a>
        </div>
    @endforelse

    @if($leaves->hasPages())
        <div class="md-pagination-container">
            {{ $leaves->appends(request()->query())->links() }}
        </div>
    @endif

</div>
@endsection
