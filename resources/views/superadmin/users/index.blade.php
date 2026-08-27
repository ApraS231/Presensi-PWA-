@extends('layouts.admin')

@section('title', 'Manajemen Karyawan & Akun - Super Admin PT. CAK')
@section('page_title', 'Manajemen Pengguna & Karyawan')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Top Action Toolbar & Filter -->
    <div class="md-card-filled" style="padding: 16px 20px;">
        <form action="{{ route('superadmin.users.index') }}" method="GET" style="display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
                <!-- Search Box -->
                <div style="flex: 2; min-width: 200px;">
                    <label for="searchUser" class="md-form-label" style="font-size: 11px;">Cari Pengguna</label>
                    <input 
                        type="text" 
                        id="searchUser" 
                        name="search" 
                        class="md-input" 
                        placeholder="Nama, NIK, atau Email..." 
                        value="{{ request('search') }}"
                        style="height: 38px;"
                    >
                </div>

                <!-- Role Filter -->
                <div style="flex: 1; min-width: 140px;">
                    <label for="roleFilter" class="md-form-label" style="font-size: 11px;">Role Akun</label>
                    <select id="roleFilter" name="role" class="md-input" style="height: 38px;">
                        <option value="">Semua Role</option>
                        <option value="karyawan" {{ request('role') === 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>HRD / Admin</option>
                        <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>

                <!-- Department Filter -->
                <div style="flex: 1; min-width: 160px;">
                    <label for="deptFilter" class="md-form-label" style="font-size: 11px;">Departemen</label>
                    <select id="deptFilter" name="department" class="md-input" style="height: 38px;">
                        <option value="">Semua Dept</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div style="flex: 1; min-width: 130px;">
                    <label for="statusFilter" class="md-form-label" style="font-size: 11px;">Status Akun</label>
                    <select id="statusFilter" name="status" class="md-input" style="height: 38px;">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="md-btn-filled" style="height: 38px; padding: 0 14px;">
                        <span class="material-symbols-rounded">search</span>
                    </button>
                    <a href="{{ route('superadmin.users.index') }}" class="md-btn-outlined" style="height: 38px; padding: 0 10px;" title="Reset Filter">
                        <span class="material-symbols-rounded">restart_alt</span>
                    </a>
                    <a href="{{ route('superadmin.users.create') }}" class="md-btn-filled" style="height: 38px; padding: 0 16px; background-color: var(--md-sys-color-primary);">
                        <span class="material-symbols-rounded">person_add</span>
                        <span>Tambah Karyawan</span>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- User Table -->
    <div class="md-table-container">
        <div class="md-table-toolbar">
            <div>
                <div class="md-table-title">
                    <span class="material-symbols-rounded" style="color: var(--md-sys-color-primary);">manage_accounts</span>
                    <span>Daftar Pengguna Sistem</span>
                </div>
                <div class="md-table-subtitle">
                    Manajemen akun Karyawan, Admin HRD, dan Super Administrator
                </div>
            </div>
            <span class="md-badge" style="background-color: var(--md-sys-color-surface-container-high); color: var(--md-sys-color-on-surface-variant); font-size: 11px;">
                Total: {{ $users->total() }} Pengguna
            </span>
        </div>

        <div class="md-table-wrapper">
            <table class="md-table">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Role</th>
                        <th>Departemen / Jabatan</th>
                        <th style="text-align: center;">Biometrik Wajah</th>
                        <th style="text-align: center;">Status Akun</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <!-- User Name & NIK -->
                            <td>
                                <div class="md-table-user-name">{{ $user->name }}</div>
                                <div class="md-table-user-sub">
                                    NIK: <b>{{ $user->nik }}</b> • {{ $user->email }}
                                </div>
                            </td>

                            <!-- Role -->
                            <td>
                                @php
                                    $roleBadge = match($user->role) {
                                        'superadmin' => 'background-color: var(--md-sys-color-tertiary-container); color: var(--md-sys-color-on-tertiary-container);',
                                        'admin'      => 'background-color: var(--md-sys-color-primary-container); color: var(--md-sys-color-on-primary-container);',
                                        default      => 'background-color: var(--md-sys-color-surface-container-high); color: var(--md-sys-color-on-surface);',
                                    };
                                @endphp
                                <span class="md-badge" style="{{ $roleBadge }} font-size: 10px;">
                                    {{ strtoupper($user->role) }}
                                </span>
                            </td>

                            <!-- Departemen & Jabatan -->
                            <td>
                                <div style="font-weight: 500;">{{ $user->department ?? '-' }}</div>
                                <div class="md-table-user-sub">{{ $user->jabatan ?? '-' }}</div>
                            </td>

                            <!-- Biometrik Wajah -->
                            <td style="text-align: center;">
                                @if($user->enrollment_status === 'enrolled')
                                    <span class="md-badge md-badge-approved" style="font-size: 10px;">Terdaftar</span>
                                @else
                                    <span class="md-badge md-badge-alpha" style="font-size: 10px;">Pending</span>
                                @endif
                            </td>

                            <!-- Status Akun -->
                            <td style="text-align: center;">
                                @if($user->is_active)
                                    <span class="md-badge md-badge-approved" style="font-size: 10px;">Aktif</span>
                                @else
                                    <span class="md-badge md-badge-rejected" style="font-size: 10px;">Nonaktif</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td style="text-align: right;">
                                <div style="display: inline-flex; align-items: center; gap: 6px;">
                                    <!-- Reset Wajah -->
                                    @if($user->role === 'karyawan' && $user->enrollment_status === 'enrolled')
                                        <form action="{{ route('superadmin.users.reset-face', $user) }}" method="POST" onsubmit="return confirm('Reset biometrik wajah {{ $user->name }}?')">
                                            @csrf
                                            <button type="submit" class="md-btn-outlined" style="height: 30px; padding: 0 8px; font-size: 11px;" title="Reset Biometrik Wajah">
                                                <span class="material-symbols-rounded" style="font-size: 14px;">face</span>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Toggle Active -->
                                    @if($user->id !== Auth::id())
                                        <form action="{{ route('superadmin.users.toggle', $user) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="md-btn-outlined" style="height: 30px; padding: 0 8px; font-size: 11px; {{ $user->is_active ? 'color: var(--md-sys-color-error);' : 'color: var(--md-custom-color-success);' }}" title="{{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                                                <span class="material-symbols-rounded" style="font-size: 14px;">{{ $user->is_active ? 'block' : 'check_circle' }}</span>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Edit -->
                                    <a href="{{ route('superadmin.users.edit', $user) }}" class="md-btn-outlined" style="height: 30px; padding: 0 8px;" title="Edit Akun">
                                        <span class="material-symbols-rounded" style="font-size: 14px;">edit</span>
                                    </a>

                                    <!-- Delete -->
                                    @if($user->id !== Auth::id())
                                        <form action="{{ route('superadmin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus permanen akun {{ $user->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="md-btn-outlined" style="height: 30px; padding: 0 8px; color: var(--md-sys-color-error);" title="Hapus Akun">
                                                <span class="material-symbols-rounded" style="font-size: 14px;">delete</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="md-table-empty">
                                <span class="material-symbols-rounded">group_off</span>
                                <div class="md-table-empty-title">Tidak Ada Pengguna</div>
                                <div class="md-table-empty-sub">Tidak ada data pengguna yang ditemukan.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="md-pagination-container">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
