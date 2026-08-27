@extends('layouts.pwa')

@section('title', 'Pengaturan Profil Akun - Presensi PT. CAK')

@push('styles')
<style>
    .profile-avatar-wrapper {
        position: relative;
        width: 100px;
        height: 100px;
        margin: 0 auto 14px auto;
    }

    .profile-avatar-img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 8px 20px rgba(40, 114, 161, 0.2), inset 2px 2px 4px rgba(255, 255, 255, 0.9);
        border: 3px solid #FFFFFF;
        background: linear-gradient(135deg, var(--color-cloudy-sky) 0%, #B8D3E4 100%);
    }

    .profile-avatar-fallback {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--color-ocean-blue) 0%, #1D587D 100%);
        color: #FFFFFF;
        font-size: 34px;
        font-weight: 800;
        box-shadow: 0 8px 20px rgba(40, 114, 161, 0.25), inset 2px 2px 4px rgba(255, 255, 255, 0.4);
        border: 3px solid #FFFFFF;
    }

    .avatar-edit-badge {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--color-ocean-blue);
        color: #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        border: 2px solid #FFFFFF;
        transition: transform 0.2s ease;
    }

    .avatar-edit-badge:hover {
        transform: scale(1.1);
    }
</style>
@endpush

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Header Section -->
    <div>
        <div class="clay-pill-tag" style="margin-bottom: 6px;">
            <span class="material-symbols-rounded" style="font-size: 13px;">manage_accounts</span>
            <span>Pengaturan Akun</span>
        </div>
        <h2 class="font-editorial-serif" style="font-size: 26px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0; line-height: 1.2;">
            Profil Karyawan
        </h2>
        <p style="font-size: 12.5px; color: var(--md-sys-color-on-surface-variant); margin: 2px 0 0 0;">
            Kelola data diri, kontak, foto profil, dan kata sandi akun Anda.
        </p>
    </div>

    <!-- 1. Profile Overview Hero Card (Claymorphism) -->
    <div class="clay-card" style="padding: 24px; text-align: center;">
        <div class="profile-avatar-wrapper">
            @if($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar))
                <img id="avatarPreviewImg" src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="profile-avatar-img">
            @else
                <div id="avatarFallback" class="profile-avatar-fallback font-editorial-grotesk">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <img id="avatarPreviewImg" src="" alt="Pratinjau Avatar" class="profile-avatar-img" style="display: none;">
            @endif

            <label for="avatarInput" class="avatar-edit-badge" title="Ganti Foto Profil">
                <span class="material-symbols-rounded" style="font-size: 16px;">photo_camera</span>
            </label>
        </div>

        <h3 class="font-editorial-serif" style="font-size: 20px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0 0 4px 0;">
            {{ $user->name }}
        </h3>
        <div style="font-size: 12px; color: var(--color-ocean-blue); font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;" class="font-editorial-grotesk">
            {{ $user->jabatan ?? 'Karyawan' }} &bull; {{ $user->department ?? 'Operasional' }}
        </div>

        <div style="display: flex; justify-content: center; gap: 8px; margin-top: 12px; flex-wrap: wrap;">
            <span class="clay-pill-tag" style="font-size: 11px;">
                NIK: <b>{{ $user->nik }}</b>
            </span>
            <span class="md-badge {{ $user->enrollment_status === 'enrolled' ? 'md-badge-approved' : 'md-badge-pending' }}" style="font-size: 10px; padding: 4px 10px;">
                Biometrik: {{ strtoupper($user->enrollment_status) }}
            </span>
        </div>
    </div>

    <!-- 2. Form Edit Informasi Pribadi -->
    <div class="clay-card" style="padding: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--color-ocean-blue);">person</span>
                <h3 class="font-editorial-serif" style="font-size: 17px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0;">
                    Informasi Pribadi
                </h3>
            </div>
            <div class="clay-combo-badge" style="width: 28px; height: 28px; font-size: 9px; background: var(--color-ocean-blue);">
                <span class="material-symbols-rounded" style="font-size: 14px; color: #FFFFFF;">edit</span>
            </div>
        </div>

        <form action="{{ route('karyawan.profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm" style="display: flex; flex-direction: column; gap: 16px;">
            @csrf
            @method('PUT')

            <!-- Hidden File Input for Avatar Triggered from Badge or Button -->
            <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/jpg" style="display: none;" onchange="handleAvatarSelected(this)">
            <input type="hidden" id="removeAvatarInput" name="remove_avatar" value="0">

            <div id="avatarActionsContainer" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 8px 12px; background: var(--color-cloudy-sky-light); border-radius: 12px; margin-bottom: 4px;">
                <button type="button" class="clay-btn clay-btn-cloudy" style="height: 32px; font-size: 11.5px; padding: 0 12px;" onclick="document.getElementById('avatarInput').click()">
                    <span class="material-symbols-rounded" style="font-size: 16px;">upload</span>
                    <span>Upload Foto Baru</span>
                </button>
                @if($user->avatar)
                    <button type="button" class="clay-btn" style="height: 32px; font-size: 11.5px; padding: 0 12px; background: #FFEBEE; color: #C62828 !important;" onclick="removeCurrentAvatar()">
                        <span class="material-symbols-rounded" style="font-size: 16px;">delete</span>
                        <span>Hapus Foto</span>
                    </button>
                @endif
            </div>
            @error('avatar')
                <div style="color: var(--md-sys-color-error); font-size: 12px; margin-top: -8px;">{{ $message }}</div>
            @enderror

            <!-- Nama Lengkap -->
            <div class="md-form-group">
                <label for="nameInput" class="md-form-label font-editorial-grotesk" style="font-size: 12.5px; font-weight: 600;">
                    Nama Lengkap <span style="color: var(--md-sys-color-error);">*</span>
                </label>
                <input 
                    type="text" 
                    id="nameInput" 
                    name="name" 
                    class="md-input" 
                    value="{{ old('name', $user->name) }}" 
                    required 
                    placeholder="Masukkan nama lengkap"
                >
                @error('name')
                    <span style="color: var(--md-sys-color-error); font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <!-- Alamat Email -->
            <div class="md-form-group">
                <label for="emailInput" class="md-form-label font-editorial-grotesk" style="font-size: 12.5px; font-weight: 600;">
                    Alamat Email (Login) <span style="color: var(--md-sys-color-error);">*</span>
                </label>
                <input 
                    type="email" 
                    id="emailInput" 
                    name="email" 
                    class="md-input" 
                    value="{{ old('email', $user->email) }}" 
                    required 
                    placeholder="nama@ptcak.com"
                >
                @error('email')
                    <span style="color: var(--md-sys-color-error); font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <!-- Nomor Telepon / WhatsApp -->
            <div class="md-form-group">
                <label for="phoneInput" class="md-form-label font-editorial-grotesk" style="font-size: 12.5px; font-weight: 600;">
                    Nomor Telepon / WhatsApp
                </label>
                <input 
                    type="tel" 
                    id="phoneInput" 
                    name="no_telp" 
                    class="md-input" 
                    value="{{ old('no_telp', $user->no_telp) }}" 
                    placeholder="Contoh: 081234567890"
                >
                @error('no_telp')
                    <span style="color: var(--md-sys-color-error); font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="clay-btn clay-btn-primary" style="width: 100%; height: 46px; margin-top: 6px;">
                <span class="material-symbols-rounded">save</span>
                <span>Simpan Perubahan Data</span>
            </button>
        </form>
    </div>

    <!-- 3. Informasi Kepegawaian Perusahaan (Read-Only) -->
    <div class="clay-card" style="padding: 22px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--color-ocean-blue);">badge</span>
                <h3 class="font-editorial-serif" style="font-size: 17px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0;">
                    Data Kepegawaian PT. CAK
                </h3>
            </div>
            <span class="material-symbols-rounded" style="font-size: 18px; color: var(--md-sys-color-outline);" title="Data dikelola oleh HRD">lock</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px; font-size: 12.5px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid var(--md-sys-color-outline-variant);">
                <span style="color: var(--md-sys-color-outline);">Nomor Induk Karyawan (NIK):</span>
                <span style="font-weight: 700; font-family: monospace; color: var(--color-ocean-blue);">{{ $user->nik }}</span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid var(--md-sys-color-outline-variant);">
                <span style="color: var(--md-sys-color-outline);">Departemen:</span>
                <span style="font-weight: 600; color: var(--md-sys-color-on-surface);">{{ $user->department ?? '-' }}</span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid var(--md-sys-color-outline-variant);">
                <span style="color: var(--md-sys-color-outline);">Jabatan / Posisi:</span>
                <span style="font-weight: 600; color: var(--md-sys-color-on-surface);">{{ $user->jabatan ?? '-' }}</span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid var(--md-sys-color-outline-variant);">
                <span style="color: var(--md-sys-color-outline);">Status Biometrik Wajah:</span>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span class="md-badge {{ $user->enrollment_status === 'enrolled' ? 'md-badge-approved' : 'md-badge-pending' }}" style="font-size: 9px; padding: 2px 8px;">
                        {{ strtoupper($user->enrollment_status) }}
                    </span>
                    @if($user->enrollment_status === 'pending')
                        <a href="{{ route('karyawan.enrollment') }}" style="font-size: 11.5px; color: var(--color-ocean-blue); font-weight: 700; text-decoration: underline;">Daftar</a>
                    @endif
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: var(--md-sys-color-outline);">Terdaftar Sejak:</span>
                <span style="font-weight: 600; color: var(--md-sys-color-on-surface);">{{ $user->created_at ? $user->created_at->isoFormat('D MMMM Y') : '-' }}</span>
            </div>
        </div>

        <div class="clay-inset-box" style="margin-top: 14px; font-size: 11.5px; line-height: 1.5; color: var(--md-sys-color-on-surface-variant); display: flex; align-items: flex-start; gap: 8px;">
            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--color-ocean-blue); flex-shrink: 0; margin-top: 1px;">info</span>
            <span>Data kepegawaian (NIK, Departemen, Jabatan) bersifat read-only dan hanya dapat diubah oleh administrator atau HRD perusahaan.</span>
        </div>
    </div>

    <!-- 4. Keamanan Akun (Ganti Kata Sandi) -->
    <div class="clay-card" style="padding: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--color-ocean-blue);">lock_reset</span>
                <h3 class="font-editorial-serif" style="font-size: 17px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0;">
                    Keamanan & Kata Sandi
                </h3>
            </div>
            <div class="clay-combo-badge" style="width: 28px; height: 28px; font-size: 9px; background: var(--md-sys-color-secondary);">
                <span class="material-symbols-rounded" style="font-size: 14px; color: #FFFFFF;">key</span>
            </div>
        </div>

        <form action="{{ route('karyawan.profile.password') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
            @csrf
            @method('PUT')

            <div class="md-form-group">
                <label for="currentPasswordInput" class="md-form-label font-editorial-grotesk" style="font-size: 12.5px; font-weight: 600;">
                    Kata Sandi Saat Ini <span style="color: var(--md-sys-color-error);">*</span>
                </label>
                <input 
                    type="password" 
                    id="currentPasswordInput" 
                    name="current_password" 
                    class="md-input" 
                    placeholder="Masukkan kata sandi lama Anda" 
                    required
                >
                @error('current_password')
                    <span style="color: var(--md-sys-color-error); font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="md-form-group">
                <label for="newPasswordInput" class="md-form-label font-editorial-grotesk" style="font-size: 12.5px; font-weight: 600;">
                    Kata Sandi Baru <span style="color: var(--md-sys-color-error);">*</span>
                </label>
                <input 
                    type="password" 
                    id="newPasswordInput" 
                    name="password" 
                    class="md-input" 
                    placeholder="Minimal 8 karakter" 
                    required
                >
                @error('password')
                    <span style="color: var(--md-sys-color-error); font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="md-form-group">
                <label for="confirmPasswordInput" class="md-form-label font-editorial-grotesk" style="font-size: 12.5px; font-weight: 600;">
                    Konfirmasi Kata Sandi Baru <span style="color: var(--md-sys-color-error);">*</span>
                </label>
                <input 
                    type="password" 
                    id="confirmPasswordInput" 
                    name="password_confirmation" 
                    class="md-input" 
                    placeholder="Ulangi kata sandi baru" 
                    required
                >
            </div>

            <button type="submit" class="clay-btn clay-btn-cloudy" style="width: 100%; height: 46px; margin-top: 6px;">
                <span class="material-symbols-rounded">key</span>
                <span>Perbarui Kata Sandi</span>
            </button>
        </form>
    </div>

</div>

<script>
    function handleAvatarSelected(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                const img = document.getElementById('avatarPreviewImg');
                const fallback = document.getElementById('avatarFallback');

                img.src = e.target.result;
                img.style.display = 'block';
                if (fallback) fallback.style.display = 'none';

                // Reset remove flag
                document.getElementById('removeAvatarInput').value = '0';
            };

            reader.readAsDataURL(file);
        }
    }

    function removeCurrentAvatar() {
        if (confirm('Apakah Anda ingin menghapus foto profil dan kembali menggunakan inisial nama?')) {
            document.getElementById('removeAvatarInput').value = '1';
            document.getElementById('profileForm').submit();
        }
    }
</script>
@endsection
