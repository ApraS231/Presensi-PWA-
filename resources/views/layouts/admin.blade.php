<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard - Presensi PT. CAK')</title>

    <!-- Google Fonts: Inter & Material Symbols Rounded -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">

    <!-- Material Design 3 CSS Modules -->
    <link rel="stylesheet" href="{{ asset('css/m3-tokens.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/m3-components.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/m3-utilities.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/m3-layout-admin.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/leaflet-map.js') }}"></script>

    <style>
        .badge-counter {
            position: absolute;
            top: -2px;
            right: -2px;
            background-color: var(--md-sys-color-error);
            color: var(--md-sys-color-on-error);
            font-size: 10px;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
    </style>

    @stack('styles')
</head>
<body class="admin-body">

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeMobileSidebar()"></div>

    <div class="admin-layout-grid">
        <!-- Desktop / Mobile Drawer Sidebar Navigation -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-brand">
                <img src="{{ asset('images/logo-ptcak-emblem.svg') }}" alt="Logo PT. CAK">
                <div style="min-width: 0; flex: 1;">
                    <div class="sidebar-title">Presensi PT. CAK</div>
                    <div class="sidebar-subtitle">{{ Auth::user()->role === 'superadmin' ? 'Super Administrator' : 'HRD & Kepegawaian' }}</div>
                </div>
            </div>

            <nav class="sidebar-nav-list">
                @if(Auth::user()->role === 'superadmin')
                    <div class="sidebar-nav-header">Master Control</div>
                    <a href="{{ route('superadmin.dashboard') }}" class="md-sidebar-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                        <span class="material-symbols-rounded">dashboard</span>
                        <span>Dashboard Utama</span>
                    </a>
                    <a href="{{ route('superadmin.locations.index') }}" class="md-sidebar-item {{ request()->routeIs('superadmin.locations.*') ? 'active' : '' }}">
                        <span class="material-symbols-rounded">location_on</span>
                        <span>Master Lokasi & Geofence</span>
                    </a>
                    <a href="{{ route('superadmin.users.index') }}" class="md-sidebar-item {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}">
                        <span class="material-symbols-rounded">group</span>
                        <span>Manajemen Karyawan</span>
                    </a>
                    <a href="{{ route('superadmin.settings.index') }}" class="md-sidebar-item {{ request()->routeIs('superadmin.settings.*') ? 'active' : '' }}">
                        <span class="material-symbols-rounded">settings</span>
                        <span>Kebijakan Sistem</span>
                    </a>
                @endif

                @if(in_array(Auth::user()->role, ['admin', 'superadmin']))
                    <div class="sidebar-nav-header">Operasional HRD</div>
                    <a href="{{ route('admin.dashboard') }}" class="md-sidebar-item {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.monitoring.*') ? 'active' : '' }}">
                        <span class="material-symbols-rounded">analytics</span>
                        <span>Monitoring Real-Time</span>
                    </a>
                    <a href="{{ route('admin.map.index') }}" class="md-sidebar-item {{ request()->routeIs('admin.map.*') ? 'active' : '' }}">
                        <span class="material-symbols-rounded">map</span>
                        <span>Live Map Sebaran</span>
                    </a>
                    <a href="{{ route('admin.enrollment.index') }}" class="md-sidebar-item {{ request()->routeIs('admin.enrollment.*') ? 'active' : '' }}">
                        <span class="material-symbols-rounded">face</span>
                        <span>Enrollment Biometrik</span>
                    </a>
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.locations.index') }}" class="md-sidebar-item {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">
                            <span class="material-symbols-rounded">location_on</span>
                            <span>Master Lokasi</span>
                        </a>
                    @endif
                    <a href="{{ route('admin.leaves.index') }}" class="md-sidebar-item {{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}">
                        <span class="material-symbols-rounded">fact_check</span>
                        <span>Persetujuan Izin</span>
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="md-sidebar-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <span class="material-symbols-rounded">assessment</span>
                        <span>Rekapitulasi Laporan</span>
                    </a>
                @endif
            </nav>
        </aside>

        <!-- Main Wrapper -->
        <div class="admin-main-wrapper">
            <!-- Topbar Header -->
            <header class="admin-top-bar">
                <div class="admin-top-bar-left">
                    <button type="button" class="admin-sidebar-toggle" onclick="toggleMobileSidebar()" title="Buka Menu Navigasi">
                        <span class="material-symbols-rounded" style="font-size: 22px;">menu</span>
                    </button>
                    <div style="font: var(--md-sys-typescale-title-medium); font-weight: 700; color: var(--md-sys-color-on-surface);">
                        @yield('page_title', 'Dashboard')
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 12px;">
                    <!-- Theme Toggle Button -->
                    <button type="button" class="theme-toggle-btn" id="themeToggleBtn" title="Ganti Tema">
                        <span class="material-symbols-rounded" id="themeToggleIcon" style="font-size: 20px;">dark_mode</span>
                    </button>

                    <!-- Notifications Bell Button -->
                    @php
                        $adminUnreadCount = Auth::check() ? Auth::user()->notifications()->unread()->count() : 0;
                    @endphp
                    <a href="{{ route('notifications.index') }}" class="theme-toggle-btn" style="position: relative;" title="Notifikasi">
                        <span class="material-symbols-rounded" style="font-size: 20px;">notifications</span>
                        @if($adminUnreadCount > 0)
                            <span class="badge-counter">{{ $adminUnreadCount > 99 ? '99+' : $adminUnreadCount }}</span>
                        @endif
                    </a>

                    <!-- User Account Dropdown -->
                    <div class="md-dropdown">
                        <button type="button" class="md-dropdown-trigger" id="adminUserMenuBtn" onclick="toggleAdminDropdown(event)">
                            <div style="width: 28px; height: 28px; border-radius: 999px; background: var(--md-sys-color-primary-container); color: var(--md-sys-color-primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">
                                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                            </div>
                            <span>{{ Auth::user()->name }}</span>
                            <span class="material-symbols-rounded" style="font-size: 18px; color: var(--md-sys-color-outline);">arrow_drop_down</span>
                        </button>

                        <div class="md-dropdown-menu" id="adminDropdownMenu">
                            <div class="md-dropdown-header">
                                <div style="font-size: 13.5px; font-weight: 700; color: var(--md-sys-color-on-surface);">
                                    {{ Auth::user()->name }}
                                </div>
                                <div style="font-size: 11.5px; color: var(--md-sys-color-outline); margin-top: 2px;">
                                    NIK: {{ Auth::user()->nik }} &bull; {{ ucfirst(Auth::user()->role) }}
                                </div>
                            </div>

                            <a href="{{ route('notifications.index') }}" class="md-dropdown-item">
                                <span class="material-symbols-rounded" style="font-size: 18px; color: var(--md-sys-color-primary);">notifications</span>
                                <span>Pemberitahuan</span>
                                @if($adminUnreadCount > 0)
                                    <span class="md-badge md-badge-terlambat" style="margin-left: auto; font-size: 10px; padding: 2px 6px;">{{ $adminUnreadCount }}</span>
                                @endif
                            </a>

                            <button type="button" class="md-dropdown-item" onclick="openAdminPasswordModal()">
                                <span class="material-symbols-rounded" style="font-size: 18px; color: var(--md-sys-color-secondary);">key</span>
                                <span>Ubah Kata Sandi</span>
                            </button>

                            <div class="md-dropdown-divider"></div>

                            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="md-dropdown-item danger">
                                    <span class="material-symbols-rounded" style="font-size: 18px;">logout</span>
                                    <span>Keluar dari Akun</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="admin-content-area">
                <!-- Breadcrumb Navigation -->
                @if(View::hasSection('breadcrumbs'))
                    @yield('breadcrumbs')
                @else
                    @php
                        $userRole = Auth::user()->role ?? 'admin';
                        $homeRoute = $userRole === 'superadmin' ? route('superadmin.dashboard') : route('admin.dashboard');
                        $currentRoute = Route::currentRouteName();
                    @endphp
                    <nav class="md-breadcrumb" aria-label="Breadcrumb">
                        <a href="{{ $homeRoute }}" class="md-breadcrumb-item {{ in_array($currentRoute, ['superadmin.dashboard', 'admin.dashboard']) ? 'active' : '' }}">
                            <span class="material-symbols-rounded" style="font-size: 16px;">home</span>
                            <span>Dashboard</span>
                        </a>

                        @if(request()->routeIs('superadmin.users.*'))
                            <span class="md-breadcrumb-separator material-symbols-rounded">chevron_right</span>
                            @if(request()->routeIs('superadmin.users.index'))
                                <span class="md-breadcrumb-item active">Pengguna Sistem</span>
                            @else
                                <a href="{{ route('superadmin.users.index') }}" class="md-breadcrumb-item">Pengguna Sistem</a>
                                <span class="md-breadcrumb-separator material-symbols-rounded">chevron_right</span>
                                <span class="md-breadcrumb-item active">{{ request()->routeIs('superadmin.users.create') ? 'Tambah Pengguna' : 'Edit Pengguna' }}</span>
                            @endif
                        @elseif(request()->routeIs('superadmin.locations.*') || request()->routeIs('admin.locations.*'))
                            <span class="md-breadcrumb-separator material-symbols-rounded">chevron_right</span>
                            @php
                                $locIndex = Auth::user()->role === 'superadmin' ? route('superadmin.locations.index') : route('admin.locations.index');
                            @endphp
                            @if(request()->routeIs('superadmin.locations.index') || request()->routeIs('admin.locations.index'))
                                <span class="md-breadcrumb-item active">Master Lokasi</span>
                            @else
                                <a href="{{ $locIndex }}" class="md-breadcrumb-item">Master Lokasi</a>
                                <span class="md-breadcrumb-separator material-symbols-rounded">chevron_right</span>
                                <span class="md-breadcrumb-item active">{{ request()->routeIs('*.create') ? 'Tambah Lokasi' : 'Edit Lokasi' }}</span>
                            @endif
                        @elseif(request()->routeIs('superadmin.settings.*'))
                            <span class="md-breadcrumb-separator material-symbols-rounded">chevron_right</span>
                            <span class="md-breadcrumb-item active">Pengaturan Sistem</span>
                        @elseif(request()->routeIs('admin.map.*'))
                            <span class="md-breadcrumb-separator material-symbols-rounded">chevron_right</span>
                            <span class="md-breadcrumb-item active">Live Map Sebaran</span>
                        @elseif(request()->routeIs('admin.enrollment.*'))
                            <span class="md-breadcrumb-separator material-symbols-rounded">chevron_right</span>
                            <span class="md-breadcrumb-item active">Enrollment Biometrik</span>
                        @elseif(request()->routeIs('admin.leaves.*'))
                            <span class="md-breadcrumb-separator material-symbols-rounded">chevron_right</span>
                            <span class="md-breadcrumb-item active">Persetujuan Izin</span>
                        @elseif(request()->routeIs('admin.reports.*'))
                            <span class="md-breadcrumb-separator material-symbols-rounded">chevron_right</span>
                            <span class="md-breadcrumb-item active">Rekapitulasi Laporan</span>
                        @elseif(request()->routeIs('notifications.*'))
                            <span class="md-breadcrumb-separator material-symbols-rounded">chevron_right</span>
                            <span class="md-breadcrumb-item active">Pemberitahuan</span>
                        @endif
                    </nav>
                @endif

                @if(session('success'))
                    <div class="md-alert md-alert-success">
                        <span class="material-symbols-rounded">check_circle</span>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="md-alert md-alert-error">
                        <span class="material-symbols-rounded">error</span>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Modal Ubah Kata Sandi Admin -->
    <div class="md-modal-backdrop" id="adminPasswordModal" style="display: none;">
        <div class="md-modal-dialog">
            <div class="md-modal-header">
                <h3 style="font-size: 16px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0;">Ubah Kata Sandi</h3>
                <button type="button" style="background: none; border: none; cursor: pointer; color: var(--md-sys-color-outline);" onclick="closeAdminPasswordModal()">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="md-modal-body" style="display: flex; flex-direction: column; gap: 14px;">
                    <div class="md-form-group" style="margin: 0;">
                        <label for="adminCurrentPassword" class="md-form-label">Kata Sandi Saat Ini</label>
                        <input type="password" id="adminCurrentPassword" name="current_password" class="md-input" placeholder="Masukkan kata sandi lama" required>
                    </div>

                    <div class="md-form-group" style="margin: 0;">
                        <label for="adminNewPassword" class="md-form-label">Kata Sandi Baru</label>
                        <input type="password" id="adminNewPassword" name="password" class="md-input" placeholder="Minimal 8 karakter" required>
                    </div>

                    <div class="md-form-group" style="margin: 0;">
                        <label for="adminConfirmPassword" class="md-form-label">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" id="adminConfirmPassword" name="password_confirmation" class="md-input" placeholder="Ulangi kata sandi baru" required>
                    </div>
                </div>
                <div class="md-modal-footer">
                    <button type="button" class="md-btn-outlined" style="height: 38px;" onclick="closeAdminPasswordModal()">Batal</button>
                    <button type="submit" class="md-btn-filled" style="height: 38px;">Simpan Sandi Baru</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Dropdown, Modal & Responsive Sidebar Script -->
    <script>
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar && backdrop) {
                sidebar.classList.toggle('open');
                backdrop.classList.toggle('show');
            }
        }

        function closeMobileSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar && backdrop) {
                sidebar.classList.remove('open');
                backdrop.classList.remove('show');
            }
        }

        function toggleAdminDropdown(event) {
            event.stopPropagation();
            const menu = document.getElementById('adminDropdownMenu');
            menu.classList.toggle('show');
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('adminDropdownMenu');
            const btn = document.getElementById('adminUserMenuBtn');
            if (menu && !menu.contains(e.target) && !btn.contains(e.target)) {
                menu.classList.remove('show');
            }
        });

        function openAdminPasswordModal() {
            const menu = document.getElementById('adminDropdownMenu');
            if (menu) menu.classList.remove('show');
            const modal = document.getElementById('adminPasswordModal');
            if (modal) {
                modal.style.display = 'flex';
                modal.classList.add('open');
            }
        }

        function closeAdminPasswordModal() {
            const modal = document.getElementById('adminPasswordModal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('open');
            }
        }
    </script>

    <!-- Theme Toggle Script -->
    <script>
        const themeBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeToggleIcon');
        
        // Auto-load theme preference
        const savedTheme = localStorage.getItem('theme-preference') || 
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', savedTheme);
        if (themeIcon) themeIcon.textContent = savedTheme === 'dark' ? 'light_mode' : 'dark_mode';

        if (themeBtn) {
            themeBtn.addEventListener('click', () => {
                const current = document.documentElement.getAttribute('data-theme');
                const next = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', next);
                localStorage.setItem('theme-preference', next);
                if (themeIcon) themeIcon.textContent = next === 'dark' ? 'light_mode' : 'dark_mode';
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
