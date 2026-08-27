@extends('layouts.pwa')

@section('title', 'Beranda Karyawan - Presensi PT. CAK')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Enrollment Alert if Pending -->
    @if($user->enrollment_status === 'pending')
        <div class="clay-card" style="border-left: 5px solid var(--md-sys-color-warning); background: linear-gradient(135deg, #FFFDE7 0%, #FFF9C4 100%); padding: 22px;">
            <div style="display: flex; align-items: flex-start; gap: 14px;">
                <div class="clay-combo-badge" style="background: #E65100; flex-shrink: 0;">
                    <span class="material-symbols-rounded" style="font-size: 22px; color: #FFFFFF;">face</span>
                </div>
                <div style="flex: 1;">
                    <div class="clay-pill-tag" style="color: #E65100; border-color: #FFE082; background: rgba(255,255,255,0.9); margin-bottom: 6px;">
                        Pendaftaran Diperlukan
                    </div>
                    <h3 class="font-editorial-grotesk" style="font-size: 16px; font-weight: 800; color: #E65100; margin: 0 0 4px 0;">
                        Biometrik Wajah Belum Terdaftar
                    </h3>
                    <p style="font-size: 13px; color: #5D4037; margin: 0 0 16px 0; line-height: 1.5;">
                        Wajah Anda belum terdaftar di sistem. Anda wajib melakukan pendaftaran biometrik sebelum dapat melakukan presensi kehadiran harian.
                    </p>
                    <a href="{{ route('karyawan.enrollment') }}" class="clay-btn clay-btn-primary" style="background: #E65100; box-shadow: 0 8px 18px rgba(230, 81, 0, 0.35);">
                        <span class="material-symbols-rounded" style="font-size: 18px;">camera_front</span>
                        <span>Daftarkan Wajah Sekarang</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="pwa-dashboard-grid">
        <!-- Left Column: Welcome & Attendance Status Card -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="clay-card" style="padding: 24px;">
                <!-- Header Tags (Reference Style) -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                    <div class="clay-pill-tag">
                        <span class="material-symbols-rounded" style="font-size: 13px;">shield_person</span>
                        <span>Portal Presensi</span>
                    </div>
                    <span class="md-badge md-badge-approved" style="font-size: 10px; padding: 4px 10px;">
                        AKTIF
                    </span>
                </div>

                <!-- Editorial Typography Title (Image 1 Style: ID Grotesk + Times pairing) -->
                <div style="margin-top: 14px;">
                    <div style="font-size: 12px; font-weight: 700; color: var(--color-ocean-blue); letter-spacing: 0.8px; text-transform: uppercase;" class="font-editorial-grotesk">
                        PT. Cahaya Anugrah Kalimantan
                    </div>
                    <h2 class="font-editorial-serif" style="font-size: 26px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 4px 0 6px 0; line-height: 1.25;">
                        Selamat Datang, {{ $user->name }}
                    </h2>
                    <p style="font-size: 13px; color: var(--md-sys-color-on-surface-variant); margin: 0; line-height: 1.5;">
                        Pastikan Anda berada di area radius kantor/proyek dan pencahayaan memadai sebelum melakukan verifikasi presensi.
                    </p>
                </div>

                <!-- Status Box with Claymorphic Dual Insets -->
                <div class="clay-card-cloudy" style="margin-top: 20px; padding: 18px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 18px; color: var(--color-ocean-blue);">event_available</span>
                            <span style="font-size: 11.5px; font-weight: 700; color: var(--color-ocean-blue); text-transform: uppercase; letter-spacing: 0.4px;">
                                Presensi Hari Ini ({{ \Carbon\Carbon::today()->isoFormat('D MMMM Y') }})
                            </span>
                        </div>
                        @if($todayAttendance)
                            @php
                                $badgeClass = match($todayAttendance->status) {
                                    'tepat_waktu' => 'md-badge-tepat-waktu',
                                    'terlambat'   => 'md-badge-terlambat',
                                    'izin'        => 'md-badge-izin',
                                    'sakit'       => 'md-badge-sakit',
                                    'cuti'        => 'md-badge-cuti',
                                    default       => 'md-badge-alpha'
                                };
                            @endphp
                            <span class="md-badge {{ $badgeClass }}">
                                {{ strtoupper(str_replace('_', ' ', $todayAttendance->status)) }}
                            </span>
                        @else
                            <span class="md-badge md-badge-alpha">Belum Absen</span>
                        @endif
                    </div>

                    <!-- Dual Time Inset Cards -->
                    <div style="margin-top: 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="clay-inset-box">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-size: 11px; font-weight: 600; color: var(--md-sys-color-outline);">Jam Masuk</span>
                                <span class="material-symbols-rounded" style="font-size: 15px; color: var(--color-ocean-blue);">login</span>
                            </div>
                            <div style="font-size: 18px; font-weight: 800; color: var(--color-ocean-blue); margin-top: 4px;" class="font-editorial-grotesk">
                                {{ $todayAttendance?->time_in ?? '--:--' }} <span style="font-size: 11px; font-weight: 600;">WITA</span>
                            </div>
                        </div>
                        <div class="clay-inset-box">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-size: 11px; font-weight: 600; color: var(--md-sys-color-outline);">Jam Pulang</span>
                                <span class="material-symbols-rounded" style="font-size: 15px; color: var(--md-sys-color-secondary);">logout</span>
                            </div>
                            <div style="font-size: 18px; font-weight: 800; color: var(--md-sys-color-secondary); margin-top: 4px;" class="font-editorial-grotesk">
                                {{ $todayAttendance?->time_out ?? '--:--' }} <span style="font-size: 11px; font-weight: 600;">WITA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Petunjuk Jam Kerja Card -->
            <div class="clay-card" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 13.5px; font-weight: 700; color: var(--md-sys-color-on-surface); margin-bottom: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 18px; color: var(--color-ocean-blue);">schedule</span>
                    <span class="font-editorial-grotesk">Ketentuan Waktu Kerja</span>
                </div>
                <div style="font-size: 12.5px; color: var(--md-sys-color-on-surface-variant); line-height: 1.6;">
                    Waktu presensi masuk dimulai pukul <b>08:00 WITA</b> dengan batas toleransi <b>15 menit</b>. Presensi setelah batas toleransi akan otomatis terhitung sebagai keterlambatan.
                </div>
            </div>
        </div>

        <!-- Right Column: Quick Action Card & Profile Info -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Actions Card with Clay Buttons -->
            <div class="clay-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded" style="font-size: 20px; color: var(--color-ocean-blue);">touch_app</span>
                        <h3 class="font-editorial-serif" style="font-size: 17px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0;">
                            Aksi Presensi & Izin
                        </h3>
                    </div>
                    <div class="clay-combo-badge" style="width: 32px; height: 32px; font-size: 9px; background: var(--color-ocean-blue);">
                        <span>CAK</span>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @if($user->enrollment_status === 'pending')
                        <a href="{{ route('karyawan.enrollment') }}" class="clay-btn clay-btn-primary" style="width: 100%; height: 48px; box-sizing: border-box;">
                            <span class="material-symbols-rounded" style="font-size: 20px;">face</span>
                            <span>Pendaftaran Biometrik Wajah</span>
                        </a>
                    @elseif(!$todayAttendance || !$todayAttendance->time_in)
                        <a href="{{ route('karyawan.presensi.index') }}" class="clay-btn clay-btn-primary" style="width: 100%; height: 48px; box-sizing: border-box;">
                            <span class="material-symbols-rounded" style="font-size: 20px;">how_to_reg</span>
                            <span>Presensi Masuk Sekarang</span>
                        </a>
                    @elseif(!$todayAttendance->time_out)
                        <a href="{{ route('karyawan.presensi.index') }}" class="clay-btn clay-btn-primary" style="width: 100%; height: 48px; box-sizing: border-box; background: var(--md-sys-color-secondary);">
                            <span class="material-symbols-rounded" style="font-size: 20px;">logout</span>
                            <span>Presensi Pulang</span>
                        </a>
                    @else
                        <div class="clay-inset-box" style="text-align: center; padding: 14px; color: var(--md-custom-color-success); font-weight: 700; font-size: 13px;">
                            <span class="material-symbols-rounded" style="vertical-align: middle; font-size: 18px; margin-right: 4px;">check_circle</span>
                            Presensi hari ini telah lengkap.
                        </div>
                    @endif

                    <a href="{{ route('karyawan.izin.create') }}" class="clay-btn clay-btn-cloudy" style="width: 100%; height: 46px; box-sizing: border-box;">
                        <span class="material-symbols-rounded" style="font-size: 20px;">description</span>
                        <span>Ajukan Izin / Cuti / Sakit</span>
                    </a>
                </div>
            </div>

            <!-- Profile Overview Card -->
            <div class="clay-card" style="padding: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <div style="font-size: 13.5px; font-weight: 700; color: var(--md-sys-color-on-surface);" class="font-editorial-grotesk">
                        Informasi Akun Pegawai
                    </div>
                    <span class="material-symbols-rounded" style="font-size: 18px; color: var(--md-sys-color-outline);">badge</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 12.5px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid var(--md-sys-color-outline-variant);">
                        <span style="color: var(--md-sys-color-outline);">NIK:</span>
                        <span style="font-weight: 700; font-family: monospace; color: var(--color-ocean-blue);">{{ $user->nik }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid var(--md-sys-color-outline-variant);">
                        <span style="color: var(--md-sys-color-outline);">Departemen:</span>
                        <span style="font-weight: 600;">{{ $user->department ?? '-' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid var(--md-sys-color-outline-variant);">
                        <span style="color: var(--md-sys-color-outline);">Jabatan:</span>
                        <span style="font-weight: 600;">{{ $user->jabatan ?? '-' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid var(--md-sys-color-outline-variant);">
                        <span style="color: var(--md-sys-color-outline);">Status Wajah:</span>
                        <span class="md-badge {{ $user->enrollment_status === 'enrolled' ? 'md-badge-approved' : 'md-badge-pending' }}" style="font-size: 9px; padding: 2px 8px;">
                            {{ strtoupper($user->enrollment_status) }}
                        </span>
                    </div>

                    <div style="margin-top: 4px;">
                        <a href="{{ route('karyawan.profile') }}" class="clay-btn clay-btn-cloudy" style="width: 100%; height: 38px; font-size: 12px; box-sizing: border-box; justify-content: center;">
                            <span class="material-symbols-rounded" style="font-size: 16px;">manage_accounts</span>
                            <span>Kelola Pengaturan Profil</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
