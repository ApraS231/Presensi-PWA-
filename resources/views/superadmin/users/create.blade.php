@extends('layouts.admin')

@section('title', 'Tambah Pengguna Baru - Super Admin PT. CAK')
@section('page_title', 'Tambah Pengguna & Karyawan Baru')

@section('content')
<div style="max-width: 700px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">

    <div class="md-card-elevated" style="padding: 24px;">
        <form action="{{ route('superadmin.users.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 18px;">
            @csrf

            <!-- NIK -->
            <div>
                <label for="inputNik" class="md-form-label">Nomor Induk Karyawan (NIK) <span style="color: var(--md-sys-color-error);">*</span></label>
                <input 
                    type="text" 
                    id="inputNik" 
                    name="nik" 
                    class="md-input" 
                    value="{{ old('nik') }}" 
                    placeholder="Contoh: KAR001 atau NIK-2026-001"
                    required
                >
                @error('nik')
                    <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Nama Lengkap -->
            <div>
                <label for="inputName" class="md-form-label">Nama Lengkap <span style="color: var(--md-sys-color-error);">*</span></label>
                <input 
                    type="text" 
                    id="inputName" 
                    name="name" 
                    class="md-input" 
                    value="{{ old('name') }}" 
                    placeholder="Nama lengkap sesuai KTP"
                    required
                >
                @error('name')
                    <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="inputEmail" class="md-form-label">Alamat Email <span style="color: var(--md-sys-color-error);">*</span></label>
                <input 
                    type="email" 
                    id="inputEmail" 
                    name="email" 
                    class="md-input" 
                    value="{{ old('email') }}" 
                    placeholder="karyawan@ptcak.com"
                    required
                >
                @error('email')
                    <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Role -->
            <div>
                <label for="selectRole" class="md-form-label">Role Akun <span style="color: var(--md-sys-color-error);">*</span></label>
                <select id="selectRole" name="role" class="md-input" required>
                    <option value="karyawan" {{ old('role') === 'karyawan' ? 'selected' : '' }}>Karyawan (Mobile PWA Presensi)</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>HRD / Admin Presensi (Desktop Dashboard)</option>
                    <option value="superadmin" {{ old('role') === 'superadmin' ? 'selected' : '' }}>Super Admin (Master Control)</option>
                </select>
                @error('role')
                    <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Grid: Departemen & Jabatan -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div>
                    <label for="inputDept" class="md-form-label">Departemen</label>
                    <input 
                        type="text" 
                        id="inputDept" 
                        name="department" 
                        class="md-input" 
                        value="{{ old('department') }}" 
                        placeholder="Contoh: Operasional Tambang"
                    >
                    @error('department')
                        <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="inputJabatan" class="md-form-label">Jabatan</label>
                    <input 
                        type="text" 
                        id="inputJabatan" 
                        name="jabatan" 
                        class="md-input" 
                        value="{{ old('jabatan') }}" 
                        placeholder="Contoh: Staff Lapangan"
                    >
                    @error('jabatan')
                        <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- No. Telepon -->
            <div>
                <label for="inputNoTelp" class="md-form-label">Nomor WhatsApp / Telepon</label>
                <input 
                    type="text" 
                    id="inputNoTelp" 
                    name="no_telp" 
                    class="md-input" 
                    value="{{ old('no_telp') }}" 
                    placeholder="081234567890"
                >
                @error('no_telp')
                    <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="inputPass" class="md-form-label">Password Awal (Opsional)</label>
                <input 
                    type="password" 
                    id="inputPass" 
                    name="password" 
                    class="md-input" 
                    placeholder="Biarkan kosong untuk password default: ptcak123"
                >
                <div style="font-size: 11px; color: var(--md-sys-color-outline); margin-top: 4px;">
                    Jika dikosongkan, password awal otomatis diset ke <b>ptcak123</b>.
                </div>
                @error('password')
                    <div style="color: var(--md-sys-color-error); font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Form Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <a href="{{ route('superadmin.users.index') }}" class="md-btn-outlined">Batal</a>
                <button type="submit" class="md-btn-filled">
                    <span class="material-symbols-rounded">save</span>
                    <span>Simpan Pengguna</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
