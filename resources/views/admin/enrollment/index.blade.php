@extends('layouts.admin')

@section('title', 'Manajemen Biometrik Wajah - HRD PT. CAK')
@section('page_title', 'Status Pendaftaran Biometrik Karyawan')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Summary Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        <div class="md-card-elevated">
            <div style="font: var(--md-sys-typescale-body-small); color: var(--md-sys-color-on-surface-variant);">Total Karyawan</div>
            <div style="font: var(--md-sys-typescale-display-small); color: var(--md-sys-color-primary); margin-top: 6px;">
                {{ $totalEmployees }}
            </div>
        </div>

        <div class="md-card-elevated">
            <div style="font: var(--md-sys-typescale-body-small); color: var(--md-custom-color-success);">Sudah Terdaftar (Enrolled)</div>
            <div style="font: var(--md-sys-typescale-display-small); color: var(--md-custom-color-success); margin-top: 6px;">
                {{ $totalEnrolled }}
            </div>
        </div>

        <div class="md-card-elevated">
            <div style="font: var(--md-sys-typescale-body-small); color: var(--md-custom-color-warning);">Belum Terdaftar (Pending)</div>
            <div style="font: var(--md-sys-typescale-display-small); color: var(--md-custom-color-warning); margin-top: 6px;">
                {{ $totalPending }}
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="md-card-filled" style="padding: 16px;">
        <form action="{{ route('admin.enrollment.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 200px;">
                <input 
                    type="text" 
                    name="search" 
                    class="md-input" 
                    placeholder="Cari Nama atau NIK..." 
                    value="{{ request('search') }}"
                    style="height: 42px;"
                >
            </div>

            <div style="min-width: 160px;">
                <select name="status" class="md-input" style="height: 42px; padding: 0 12px;" onchange="this.form.submit()">
                    <option value="">-- Semua Status --</option>
                    <option value="enrolled" {{ request('status') === 'enrolled' ? 'selected' : '' }}>Enrolled</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            @if($departments->isNotEmpty())
                <div style="min-width: 160px;">
                    <select name="department" class="md-input" style="height: 42px; padding: 0 12px;" onchange="this.form.submit()">
                        <option value="">-- Semua Divisi --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button type="submit" class="md-btn-filled" style="height: 42px; padding: 0 18px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">search</span>
                <span>Filter</span>
            </button>

            @if(request()->hasAny(['search', 'status', 'department']))
                <a href="{{ route('admin.enrollment.index') }}" class="md-btn-outlined" style="height: 42px; padding: 0 14px;">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Employee Table -->
    <!-- Employee Table -->
    <div class="md-table-container">
        <div class="md-table-toolbar">
            <div>
                <div class="md-table-title">
                    <span class="material-symbols-rounded" style="color: var(--md-sys-color-primary);">face</span>
                    <span>Daftar Pendaftaran Biometrik Karyawan</span>
                </div>
                <div class="md-table-subtitle">
                    Status kelengkapan deskriptor wajah karyawan aktif PT. CAK
                </div>
            </div>
            <span class="md-badge" style="background-color: var(--md-sys-color-surface-container-high); color: var(--md-sys-color-on-surface-variant); font-size: 11px;">
                Total: {{ $employees->total() }} Karyawan
            </span>
        </div>

        <div class="md-table-wrapper">
            <table class="md-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">Foto</th>
                        <th>Karyawan</th>
                        <th>Departemen / Jabatan</th>
                        <th>Status Biometrik</th>
                        <th>Tanggal Pendaftaran</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <!-- Foto Sample -->
                            <td>
                                @if($employee->faceDescriptor && $employee->faceDescriptor->sample_photo)
                                    <img 
                                        src="{{ asset('storage/' . $employee->faceDescriptor->sample_photo) }}" 
                                        alt="{{ $employee->name }}" 
                                        class="md-table-avatar"
                                    >
                                @else
                                    <div class="md-table-avatar" style="display: flex; align-items: center; justify-content: center; color: var(--md-sys-color-outline);">
                                        <span class="material-symbols-rounded" style="font-size: 20px;">no_accounts</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Info Karyawan -->
                            <td>
                                <div class="md-table-user-name">{{ $employee->name }}</div>
                                <div class="md-table-user-sub">NIK: <b>{{ $employee->nik }}</b></div>
                            </td>

                            <!-- Departemen -->
                            <td>
                                <div style="font-weight: 500;">{{ $employee->department ?? '-' }}</div>
                                <div class="md-table-user-sub">{{ $employee->jabatan ?? '-' }}</div>
                            </td>

                            <!-- Status -->
                            <td>
                                @if($employee->enrollment_status === 'enrolled')
                                    <span class="md-badge md-badge-approved">
                                        <span class="material-symbols-rounded" style="font-size: 13px;">check_circle</span>
                                        Enrolled
                                    </span>
                                @else
                                    <span class="md-badge md-badge-pending">
                                        <span class="material-symbols-rounded" style="font-size: 13px;">pending</span>
                                        Pending
                                    </span>
                                @endif
                            </td>

                            <!-- Tanggal Pendaftaran -->
                            <td style="color: var(--md-sys-color-on-surface-variant); font-size: 12.5px;">
                                {{ $employee->faceDescriptor?->created_at?->isoFormat('D MMMM Y, HH:mm') ?? '-' }}
                            </td>

                            <!-- Aksi -->
                            <td style="text-align: center;">
                                @if($employee->enrollment_status === 'enrolled')
                                    <form action="{{ route('admin.enrollment.reset', $employee->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset pendaftaran biometrik karyawan {{ $employee->name }}? Karyawan akan diminta mendaftar ulang.');" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="md-btn-outlined" style="height: 30px; padding: 0 10px; font-size: 11px; color: var(--md-sys-color-error);" title="Reset Biometrik Wajah">
                                            <span class="material-symbols-rounded" style="font-size: 14px;">restart_alt</span>
                                            <span>Reset Wajah</span>
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size: 12px; color: var(--md-sys-color-outline);">Belum Terdaftar</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="md-table-empty">
                                <span class="material-symbols-rounded">search_off</span>
                                <div class="md-table-empty-title">Karyawan Tidak Ditemukan</div>
                                <div class="md-table-empty-sub">Tidak ditemukan data karyawan yang sesuai dengan kriteria pencarian.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
            <div class="md-pagination-container">
                {{ $employees->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
