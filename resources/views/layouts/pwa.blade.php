<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1565C0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Presensi CAK">
    <meta name="description" content="Sistem Presensi Karyawan PWA PT. Cahaya Anugrah Kalimantan">

    <title>@yield('title', 'Presensi PT. CAK')</title>

    <!-- PWA Web App Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans, Newsreader & Material Symbols Rounded -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Newsreader:ital,opsz,wght@0,6..72,500;0,6..72,600;0,6..72,700;1,6..72,400;1,6..72,600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">

    <!-- Material Design 3 CSS Modules -->
    <link rel="stylesheet" href="{{ asset('css/m3-tokens.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/m3-components.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/m3-utilities.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/m3-layout-pwa.css') }}?v={{ time() }}">

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
            background-color: #FFFFFF;
            border-radius: var(--md-sys-shape-corner-extra-large);
            width: 100%;
            max-width: 420px;
            box-shadow: var(--md-sys-elevation-4);
            border: 1px solid var(--md-sys-color-outline-variant);
            overflow: hidden;
            animation: modalScale 0.2s var(--md-sys-motion-easing-emphasized);
        }

        @keyframes modalScale {
            from { transform: scale(0.92); opacity: 0; }
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
        }

        .md-modal-footer {
            padding: 14px 20px;
            border-top: 1px solid var(--md-sys-color-outline-variant);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            background-color: var(--md-sys-color-surface-container-low);
        }

        /* PWA Install Bottom Sheet / Banner */
        .pwa-install-bottom-sheet {
            position: fixed;
            bottom: calc(76px + env(safe-area-inset-bottom));
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 460px;
            padding: 0 14px;
            z-index: 1000;
            box-sizing: border-box;
            animation: pwaSlideUp 0.3s cubic-bezier(0.2, 0, 0, 1);
        }

        @keyframes pwaSlideUp {
            from { transform: translate(-50%, 30px); opacity: 0; }
            to   { transform: translate(-50%, 0); opacity: 1; }
        }

        .pwa-install-card {
            background-color: #FFFFFF;
            border-radius: 18px;
            border: 1px solid var(--md-sys-color-outline-variant);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.16), 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 16px 18px;
        }
    </style>

    @stack('styles')
</head>
<body class="pwa-body">

    <div class="pwa-app-wrapper">
        <!-- PWA Top App Bar -->
        <header class="pwa-top-bar">
            <div class="pwa-profile-info">
                @if(Auth::check() && Auth::user()->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists(Auth::user()->avatar))
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" style="width: 38px; height: 38px; border-radius: 12px; object-fit: cover; flex-shrink: 0; box-shadow: 0 2px 6px rgba(40, 114, 161, 0.2); border: 1.5px solid var(--color-cloudy-sky);">
                @else
                    <img src="{{ asset('images/logo-ptcak-emblem.svg') }}" alt="Logo PT. CAK" style="width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(21, 101, 192, 0.2);">
                @endif
                <div class="pwa-user-details">
                    <div class="pwa-user-name">{{ Auth::user()->name ?? 'Karyawan' }}</div>
                    <div class="pwa-user-meta">NIK: {{ Auth::user()->nik ?? '-' }} &bull; {{ Auth::user()->department ?? 'Staff' }}</div>
                </div>
            </div>

            <div class="pwa-top-actions">
                @php
                    $unreadNotifCount = Auth::check() ? Auth::user()->notifications()->unread()->count() : 0;
                @endphp

                <!-- Live GPS Tracking Indicator Badge -->
                <a href="{{ route('karyawan.tracking.index') }}" id="liveTrackingIndicator" style="display: none; align-items: center; gap: 5px; background: rgba(46, 125, 50, 0.12); color: var(--md-custom-color-success); border: 1px solid rgba(46, 125, 50, 0.3); border-radius: 9999px; padding: 4px 10px; font-size: 11px; font-weight: 700; text-decoration: none;" title="Pelacakan Lokasi Operasional Aktif">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--md-custom-color-success); display: inline-block; box-shadow: 0 0 6px rgba(46, 125, 50, 0.8);"></span>
                    <span>GPS Aktif</span>
                </a>
                
                <!-- Notification Bell Button -->
                <a href="{{ route('notifications.index') }}" class="theme-toggle-btn" style="position: relative;" title="Notifikasi">
                    <span class="material-symbols-rounded" style="font-size: 20px;">notifications</span>
                    @if($unreadNotifCount > 0)
                        <span class="badge-counter">{{ $unreadNotifCount > 99 ? '99+' : $unreadNotifCount }}</span>
                    @endif
                </a>

                <!-- User Profile & Action Dropdown Menu -->
                <div class="md-dropdown">
                    <button type="button" class="theme-toggle-btn" id="userMenuBtn" onclick="toggleUserDropdown(event)" title="Menu Pengguna">
                        <span class="material-symbols-rounded" style="font-size: 20px;">more_vert</span>
                    </button>

                    <div class="md-dropdown-menu" id="userDropdownMenu">
                        <div class="md-dropdown-header">
                            <div style="font-size: 13.5px; font-weight: 700; color: var(--md-sys-color-on-surface); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ Auth::user()->name ?? 'Pengguna' }}
                            </div>
                            <div style="font-size: 11.5px; color: var(--md-sys-color-outline); margin-top: 2px;">
                                NIK: {{ Auth::user()->nik ?? '-' }} &bull; {{ ucfirst(Auth::user()->role ?? 'karyawan') }}
                            </div>
                        </div>

                        <a href="{{ route('karyawan.profile') }}" class="md-dropdown-item">
                            <span class="material-symbols-rounded" style="font-size: 18px; color: var(--color-ocean-blue);">person</span>
                            <span>Pengaturan Profil</span>
                        </a>

                        <a href="{{ route('karyawan.tracking.index') }}" class="md-dropdown-item">
                            <span class="material-symbols-rounded" style="font-size: 18px; color: var(--md-custom-color-success);">route</span>
                            <span>Perjalanan Hari Ini</span>
                        </a>

                        <a href="{{ route('notifications.index') }}" class="md-dropdown-item">
                            <span class="material-symbols-rounded" style="font-size: 18px; color: var(--md-sys-color-primary);">notifications</span>
                            <span>Pemberitahuan</span>
                            @if($unreadNotifCount > 0)
                                <span class="md-badge md-badge-terlambat" style="margin-left: auto; font-size: 10px; padding: 2px 6px;">{{ $unreadNotifCount }}</span>
                            @endif
                        </a>

                        <button type="button" class="md-dropdown-item" onclick="handleManualInstallClick()">
                            <span class="material-symbols-rounded" style="font-size: 18px; color: var(--md-sys-color-primary);">install_mobile</span>
                            <span>Pasang Aplikasi di HP</span>
                        </button>

                        <button type="button" class="md-dropdown-item" onclick="openPasswordModal()">
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

        <!-- Main Content Area -->
        <main class="pwa-content">
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

        <!-- M3 Bottom Navigation Bar -->
        <nav class="md-bottom-nav">
            <a href="{{ route('karyawan.dashboard') }}" class="md-bottom-nav-item {{ request()->routeIs('karyawan.dashboard') ? 'active' : '' }}">
                <div class="md-nav-indicator">
                    <span class="material-symbols-rounded">home</span>
                </div>
                <span class="md-nav-label">Beranda</span>
            </a>

            <a href="{{ route('karyawan.presensi.index') }}" class="md-bottom-nav-item {{ request()->routeIs('karyawan.presensi.*') ? 'active' : '' }}">
                <div class="md-nav-indicator">
                    <span class="material-symbols-rounded">how_to_reg</span>
                </div>
                <span class="md-nav-label">Absen</span>
            </a>

            <a href="{{ route('karyawan.izin.index') }}" class="md-bottom-nav-item {{ request()->routeIs('karyawan.izin.*') ? 'active' : '' }}">
                <div class="md-nav-indicator">
                    <span class="material-symbols-rounded">description</span>
                </div>
                <span class="md-nav-label">Izin</span>
            </a>

            <a href="{{ route('karyawan.riwayat.index') }}" class="md-bottom-nav-item {{ request()->routeIs('karyawan.riwayat.*') ? 'active' : '' }}">
                <div class="md-nav-indicator">
                    <span class="material-symbols-rounded">history</span>
                </div>
                <span class="md-nav-label">Riwayat</span>
            </a>

            <a href="{{ route('karyawan.profile') }}" class="md-bottom-nav-item {{ request()->routeIs('karyawan.profile*') ? 'active' : '' }}">
                <div class="md-nav-indicator">
                    <span class="material-symbols-rounded">person</span>
                </div>
                <span class="md-nav-label">Profil</span>
            </a>
        </nav>
    </div>

    <!-- PWA Install Prompt Banner / Bottom Sheet -->
    <div id="pwaInstallBanner" class="pwa-install-bottom-sheet" style="display: none;">
        <div class="pwa-install-card">
            <div style="display: flex; align-items: flex-start; gap: 14px;">
                <img src="{{ asset('images/logo-ptcak-emblem.svg') }}" alt="PT. CAK Logo" style="width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0; box-shadow: 0 2px 8px rgba(21, 101, 192, 0.15);">
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <h4 style="font-size: 15px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0;">Presensi PT. CAK</h4>
                        <span class="md-badge md-badge-approved" style="font-size: 9px; padding: 2px 6px;">PWA</span>
                    </div>
                    <p style="font-size: 12.5px; color: var(--md-sys-color-on-surface-variant); margin: 4px 0 0 0; line-height: 1.4;">
                        Pasang aplikasi di layar utama HP untuk akses presensi instan, cepat & praktis.
                    </p>
                </div>
                <button type="button" style="background: none; border: none; cursor: pointer; color: var(--md-sys-color-outline); padding: 0;" onclick="dismissPwaInstallPrompt()" title="Tutup">
                    <span class="material-symbols-rounded" style="font-size: 20px;">close</span>
                </button>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 14px;">
                <button type="button" class="md-btn-outlined" style="flex: 1; height: 40px; font-size: 13px; justify-content: center;" onclick="dismissPwaInstallPrompt()">
                    Nanti Saja
                </button>
                <button type="button" class="md-btn-filled" style="flex: 1.4; height: 40px; font-size: 13px; justify-content: center;" id="btnInstallPwa" onclick="triggerPwaInstall()">
                    <span class="material-symbols-rounded" style="font-size: 18px;">download</span>
                    <span>Pasang Sekarang</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Petunjuk Install iOS Safari -->
    <div class="md-modal-backdrop" id="iosInstallModal">
        <div class="md-modal-dialog">
            <div class="md-modal-header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded" style="color: var(--md-sys-color-primary);">install_mobile</span>
                    <h3 style="font-size: 16px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0;">Pasang di iPhone / iPad</h3>
                </div>
                <button type="button" style="background: none; border: none; cursor: pointer; color: var(--md-sys-color-outline);" onclick="closeIosInstallModal()">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <div class="md-modal-body" style="display: flex; flex-direction: column; gap: 14px; font-size: 13.5px; color: var(--md-sys-color-on-surface-variant); line-height: 1.5;">
                <div>Untuk memasang aplikasi Presensi PT. CAK di perangkat iOS (Safari):</div>
                <ol style="padding-left: 20px; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                    <li>Ketuk tombol <b>Bagikan (Share)</b> <span class="material-symbols-rounded" style="vertical-align: middle; font-size: 18px; color: var(--md-sys-color-primary);">ios_share</span> di bilah navigasi Safari.</li>
                    <li>Gulir ke bawah dan pilih <b>Tambahkan ke Layar Utama (Add to Home Screen)</b> <span class="material-symbols-rounded" style="vertical-align: middle; font-size: 18px; color: var(--md-sys-color-primary);">add_box</span>.</li>
                    <li>Ketuk <b>Tambah (Add)</b> di sudut kanan atas.</li>
                </ol>
            </div>
            <div class="md-modal-footer">
                <button type="button" class="md-btn-filled" style="height: 38px; width: 100%; justify-content: center;" onclick="closeIosInstallModal()">Saya Mengerti</button>
            </div>
        </div>
    </div>

    <!-- Modal Ubah Kata Sandi -->
    <div class="md-modal-backdrop" id="passwordModal">
        <div class="md-modal-dialog">
            <div class="md-modal-header">
                <h3 style="font-size: 16px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0;">Ubah Kata Sandi</h3>
                <button type="button" style="background: none; border: none; cursor: pointer; color: var(--md-sys-color-outline);" onclick="closePasswordModal()">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="md-modal-body" style="display: flex; flex-direction: column; gap: 14px;">
                    <div class="md-form-group" style="margin: 0;">
                        <label for="modalCurrentPassword" class="md-form-label">Kata Sandi Saat Ini</label>
                        <input type="password" id="modalCurrentPassword" name="current_password" class="md-input" placeholder="Masukkan kata sandi lama" required>
                    </div>

                    <div class="md-form-group" style="margin: 0;">
                        <label for="modalNewPassword" class="md-form-label">Kata Sandi Baru</label>
                        <input type="password" id="modalNewPassword" name="password" class="md-input" placeholder="Minimal 8 karakter" required>
                    </div>

                    <div class="md-form-group" style="margin: 0;">
                        <label for="modalConfirmPassword" class="md-form-label">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" id="modalConfirmPassword" name="password_confirmation" class="md-input" placeholder="Ulangi kata sandi baru" required>
                    </div>
                </div>
                <div class="md-modal-footer">
                    <button type="button" class="md-btn-outlined" style="height: 38px;" onclick="closePasswordModal()">Batal</button>
                    <button type="submit" class="md-btn-filled" style="height: 38px;">Simpan Sandi Baru</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Dropdown & Modal Script -->
    <script>
        function toggleUserDropdown(event) {
            event.stopPropagation();
            const menu = document.getElementById('userDropdownMenu');
            menu.classList.toggle('show');
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('userDropdownMenu');
            const btn = document.getElementById('userMenuBtn');
            if (menu && !menu.contains(e.target) && !btn.contains(e.target)) {
                menu.classList.remove('show');
            }
        });

        function openPasswordModal() {
            const menu = document.getElementById('userDropdownMenu');
            if (menu) menu.classList.remove('show');
            document.getElementById('passwordModal').classList.add('open');
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').classList.remove('open');
        }
    </script>

    <!-- PWA Installation & Service Worker Scripts -->
    <script>
        let deferredPrompt = null;
        const isIos = /iphone|ipad|ipod/.test(navigator.userAgent.toLowerCase()) && !window.MSStream;
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;

            // Check if prompt was dismissed in last 3 days
            const dismissedTime = localStorage.getItem('pwa_prompt_dismissed');
            const now = Date.now();
            if (!dismissedTime || (now - parseInt(dismissedTime)) > 3 * 24 * 60 * 60 * 1000) {
                setTimeout(() => {
                    if (!isStandalone) {
                        showPwaInstallBanner();
                    }
                }, 1500);
            }
        });

        function showPwaInstallBanner() {
            const banner = document.getElementById('pwaInstallBanner');
            if (banner) banner.style.display = 'block';
        }

        function dismissPwaInstallPrompt() {
            const banner = document.getElementById('pwaInstallBanner');
            if (banner) banner.style.display = 'none';
            localStorage.setItem('pwa_prompt_dismissed', Date.now().toString());
        }

        async function triggerPwaInstall() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    console.log('Pengguna menyetujui pemasangan PWA');
                }
                deferredPrompt = null;
                dismissPwaInstallPrompt();
            } else if (isIos) {
                dismissPwaInstallPrompt();
                openIosInstallModal();
            } else {
                alert('Untuk memasang aplikasi, buka menu peramban (tiga titik di sudut kanan atas) lalu pilih "Install Aplikasi" atau "Tambahkan ke Layar Utama".');
                dismissPwaInstallPrompt();
            }
        }

        function handleManualInstallClick() {
            const menu = document.getElementById('userDropdownMenu');
            if (menu) menu.classList.remove('show');

            if (deferredPrompt) {
                triggerPwaInstall();
            } else if (isIos) {
                openIosInstallModal();
            } else if (isStandalone) {
                alert('Aplikasi Presensi PT. CAK sudah terpasang di perangkat Anda.');
            } else {
                showPwaInstallBanner();
            }
        }

        function openIosInstallModal() {
            document.getElementById('iosInstallModal').classList.add('open');
        }

        function closeIosInstallModal() {
            document.getElementById('iosInstallModal').classList.remove('open');
        }

        window.addEventListener('appinstalled', () => {
            console.log('PWA berhasil terpasang di perangkat');
            deferredPrompt = null;
            const banner = document.getElementById('pwaInstallBanner');
            if (banner) banner.style.display = 'none';
        });

        // Bottom Nav & PWA Navigation Transition Handler
        document.addEventListener('DOMContentLoaded', () => {
            const navItems = document.querySelectorAll('.md-bottom-nav-item');
            const content = document.querySelector('.pwa-content');

            navItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    const targetHref = this.getAttribute('href');
                    if (targetHref && targetHref !== window.location.href) {
                        navItems.forEach(i => i.classList.remove('active'));
                        this.classList.add('active');

                        if (content && !e.ctrlKey && !e.metaKey) {
                            content.classList.add('pwa-page-leaving');
                        }
                    }
                });
            });
        });

        // Background Operational GPS Tracking Service for Field Employees / SPG
        @if(Auth::check() && Auth::user()->role === 'karyawan')
        (function() {
            let trackingTimer = null;
            let isTrackingRunning = false;

            async function checkAndRunTracking() {
                try {
                    const response = await fetch("{{ route('karyawan.tracking.status') }}", {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!response.ok) return;
                    const data = await response.json();

                    const indicator = document.getElementById('liveTrackingIndicator');

                    if (data.tracking_active) {
                        if (indicator) indicator.style.display = 'inline-flex';

                        if (!isTrackingRunning) {
                            isTrackingRunning = true;
                            // Send initial ping immediately
                            sendGpsPing();
                            // Schedule recurrent pings
                            const intervalMs = (data.interval_minutes || 5) * 60 * 1000;
                            trackingTimer = setInterval(sendGpsPing, intervalMs);
                        }
                    } else {
                        if (indicator) indicator.style.display = 'none';
                        if (trackingTimer) {
                            clearInterval(trackingTimer);
                            trackingTimer = null;
                        }
                        isTrackingRunning = false;
                    }
                } catch (e) {
                    console.warn('Gagal memeriksa status pelacakan lokasi:', e);
                }
            }

            function sendGpsPing() {
                if (!navigator.geolocation) return;

                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        try {
                            const payload = {
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude,
                                accuracy: position.coords.accuracy,
                                recorded_at: new Date().toISOString()
                            };

                            const response = await fetch("{{ route('karyawan.tracking.ping') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(payload)
                            });

                            const resData = await response.json();
                            if (resData && resData.tracking_active === false) {
                                const indicator = document.getElementById('liveTrackingIndicator');
                                if (indicator) indicator.style.display = 'none';
                                if (trackingTimer) {
                                    clearInterval(trackingTimer);
                                    trackingTimer = null;
                                }
                                isTrackingRunning = false;
                            }
                        } catch (err) {
                            console.warn('Gagal mengirim koordinat jejak lokasi:', err);
                        }
                    },
                    (error) => {
                        console.warn('GPS error saat pelacakan operasional:', error.message);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 10000
                    }
                );
            }

            document.addEventListener('DOMContentLoaded', () => {
                checkAndRunTracking();
            });
        })();
        @endif

        // Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(registration => {
                        console.log('Service Worker terdaftar:', registration.scope);
                    })
                    .catch(error => {
                        console.log('Service Worker gagal terdaftar:', error);
                    });
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
