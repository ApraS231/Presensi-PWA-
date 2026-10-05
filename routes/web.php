<?php

use App\Http\Controllers\Admin\EnrollmentController as AdminEnrollmentController;
use App\Http\Controllers\Admin\LeaveApprovalController as AdminLeaveApprovalController;
use App\Http\Controllers\Admin\LocationController as AdminLocationController;
use App\Http\Controllers\Admin\MonitoringController as AdminMonitoringController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\TrackingViewController as AdminTrackingViewController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Karyawan\AttendanceController as KaryawanAttendanceController;
use App\Http\Controllers\Karyawan\DashboardController as KaryawanDashboardController;
use App\Http\Controllers\Karyawan\EnrollmentController as KaryawanEnrollmentController;
use App\Http\Controllers\Karyawan\GeofenceController as KaryawanGeofenceController;
use App\Http\Controllers\Karyawan\LeaveController as KaryawanLeaveController;
use App\Http\Controllers\Karyawan\LocationTrackController as KaryawanLocationTrackController;
use App\Http\Controllers\Karyawan\ProfileController as KaryawanProfileController;
use App\Http\Controllers\Karyawan\RiwayatController as KaryawanRiwayatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboardController;
use App\Http\Controllers\Superadmin\LocationController as SuperadminLocationController;
use App\Http\Controllers\Superadmin\SettingController as SuperadminSettingController;
use App\Http\Controllers\Superadmin\UserController as SuperadminUserController;
use Illuminate\Support\Facades\Route;

// 1. Rute Publik / Tamu
Route::get('/', [LoginController::class, 'showLoginForm']);
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])->name('logout');

// 2. Rute Autentikasi Bersama (Semua Role)
Route::middleware(['auth', 'active'])->group(function () {
    Route::put('/password/update', [PasswordController::class, 'update'])->name('password.update');

    // Modul Notifikasi In-App
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

// 3. Rute Khusus Role Karyawan (Mobile PWA)
Route::middleware(['auth', 'active', 'role.karyawan'])
    ->prefix('karyawan')
    ->name('karyawan.')
    ->group(function () {
        Route::get('/dashboard', [KaryawanDashboardController::class, 'index'])->name('dashboard');

        // Modul Pendaftaran Biometrik Wajah (Enrollment)
        Route::get('/enrollment', [KaryawanEnrollmentController::class, 'showForm'])->name('enrollment');
        Route::post('/enrollment', [KaryawanEnrollmentController::class, 'store'])->name('enrollment.store');

        // Verifikasi Geofence GPS Real-Time
        Route::post('/geofence/verify', [KaryawanGeofenceController::class, 'verify'])->name('geofence.verify');

        // Modul Transaksi Presensi Harian (Dilindungi CheckEnrolled)
        Route::middleware(['enrolled'])->group(function () {
            Route::get('/presensi', [KaryawanAttendanceController::class, 'index'])->name('presensi.index');
            Route::post('/presensi/check-in', [KaryawanAttendanceController::class, 'checkIn'])->name('presensi.check-in');
            Route::post('/presensi/check-out', [KaryawanAttendanceController::class, 'checkOut'])->name('presensi.check-out');
        });

        // Modul Pengajuan Izin / Cuti / Sakit Karyawan
        Route::get('/izin', [KaryawanLeaveController::class, 'index'])->name('izin.index');
        Route::get('/izin/create', [KaryawanLeaveController::class, 'create'])->name('izin.create');
        Route::post('/izin', [KaryawanLeaveController::class, 'store'])->name('izin.store');
        Route::get('/izin/{leave}/edit', [KaryawanLeaveController::class, 'edit'])->name('izin.edit');
        Route::put('/izin/{leave}', [KaryawanLeaveController::class, 'update'])->name('izin.update');
        Route::patch('/izin/{leave}/cancel', [KaryawanLeaveController::class, 'cancel'])->name('izin.cancel');

        // Modul Riwayat Presensi Mandiri Karyawan
        Route::get('/riwayat', [KaryawanRiwayatController::class, 'index'])->name('riwayat.index');

        // Modul Pengaturan Profil Mandiri Karyawan
        Route::get('/profile', [KaryawanProfileController::class, 'edit'])->name('profile');
        Route::put('/profile', [KaryawanProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [KaryawanProfileController::class, 'updatePassword'])->name('profile.password');

        // Modul Pelacakan Jejak Lokasi SPG / Karyawan (Live Tracking)
        Route::get('/tracking', [KaryawanLocationTrackController::class, 'index'])->name('tracking.index');
        Route::get('/tracking/status', [KaryawanLocationTrackController::class, 'status'])->name('tracking.status');
        Route::post('/tracking/ping', [KaryawanLocationTrackController::class, 'ping'])->name('tracking.ping');
        Route::get('/tracking/today', [KaryawanLocationTrackController::class, 'today'])->name('tracking.today');
    });

// 4. Rute Khusus Role Admin & Super Admin (Desktop Dashboard)
Route::middleware(['auth', 'active', 'role.admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Monitoring Kehadiran Real-Time & Dashboard HRD
        Route::get('/dashboard', [AdminMonitoringController::class, 'index'])->name('dashboard');
        Route::get('/monitoring', [AdminMonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('/monitoring/data', [AdminMonitoringController::class, 'data'])->name('monitoring.data');

        // Visualisasi Live Map Leaflet.js
        Route::get('/map', [AdminMonitoringController::class, 'liveMap'])->name('map.index');

        // Pemantauan Jejak Lokasi Operasional SPG / Karyawan (View-Only)
        Route::get('/tracking', [AdminTrackingViewController::class, 'index'])->name('tracking.index');
        Route::get('/tracking/{user}/trail', [AdminTrackingViewController::class, 'trail'])->name('tracking.trail');

        // Manajemen Pendaftaran Biometrik Karyawan
        Route::get('/enrollment', [AdminEnrollmentController::class, 'index'])->name('enrollment.index');
        Route::post('/enrollment/{user}/reset', [AdminEnrollmentController::class, 'reset'])->name('enrollment.reset');

        // Master Lokasi & Geofence (Admin)
        Route::get('/locations', [AdminLocationController::class, 'index'])->name('locations.index');
        Route::post('/locations', [AdminLocationController::class, 'store'])->name('locations.store');
        Route::put('/locations/{location}', [AdminLocationController::class, 'update'])->name('locations.update');
        Route::patch('/locations/{location}/toggle', [AdminLocationController::class, 'toggleStatus'])->name('locations.toggle');
        Route::delete('/locations/{location}', [AdminLocationController::class, 'destroy'])->name('locations.destroy');

        // Workflow Evaluasi & Approval Izin HRD
        Route::get('/leaves', [AdminLeaveApprovalController::class, 'index'])->name('leaves.index');
        Route::post('/leaves/{leave}/process', [AdminLeaveApprovalController::class, 'process'])->name('leaves.process');

        // Rekapitulasi & Export Laporan Excel / PDF
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/excel', [AdminReportController::class, 'exportExcel'])->name('reports.export.excel');
        Route::get('/reports/export/pdf', [AdminReportController::class, 'exportPdf'])->name('reports.export.pdf');
    });

// 5. Rute Khusus Role Super Admin (System Master Control)
Route::middleware(['auth', 'active', 'role.superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->name('dashboard');

        // Pemantauan Jejak Lokasi Operasional SPG / Karyawan (View-Only Super Admin)
        Route::get('/tracking', [AdminTrackingViewController::class, 'index'])->name('tracking.index');
        Route::get('/tracking/{user}/trail', [AdminTrackingViewController::class, 'trail'])->name('tracking.trail');

        // Master Karyawan & Akun Pengguna
        Route::resource('users', SuperadminUserController::class)->except(['show']);
        Route::patch('/users/{user}/toggle', [SuperadminUserController::class, 'toggleActive'])->name('users.toggle');
        Route::post('/users/{user}/reset-face', [SuperadminUserController::class, 'resetFace'])->name('users.reset-face');

        // Master Lokasi & Geofence (Super Admin)
        Route::resource('locations', SuperadminLocationController::class)->except(['show']);
        Route::patch('/locations/{location}/toggle', [SuperadminLocationController::class, 'toggleStatus'])->name('locations.toggle');

        // Master Pengaturan Kebijakan Sistem Global
        Route::get('/settings', [SuperadminSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/batch', [SuperadminSettingController::class, 'updateBatch'])->name('settings.batch');
    });
