<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Menampilkan form login M3.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        return view('auth.login');
    }

    /**
     * Memproses permintaan autentikasi.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('login')) . '|' . $request->ip());

        // 1. Rate Limiting (Maksimal 5 percobaan gagal per menit)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'login' => "Terlalu banyak percobaan login yang gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('login');
        }

        // 2. Deteksi format input: NIK atau Email
        $loginValue = $request->input('login');
        $fieldType = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'nik';

        $credentials = [
            $fieldType => $loginValue,
            'password'  => $request->input('password'),
        ];

        // 3. Verifikasi Kredensial
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors([
                'login' => 'NIK / Email atau kata sandi yang Anda masukkan salah.',
            ])->onlyInput('login');
        }

        // 4. Verifikasi Status Akun Aktif
        $user = Auth::user();
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'login' => 'Akun Anda telah dinonaktifkan. Hubungi Administrator.',
            ]);
        }

        // Reset rate limiter setelah berhasil login
        RateLimiter::clear($throttleKey);

        // Regenerasi Session ID untuk keamanan fixation
        $request->session()->regenerate();

        return $this->redirectBasedOnRole($user->role);
    }

    /**
     * Memproses logout pengguna.
     */
    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Mengalihkan ke dashboard sesuai peran pengguna.
     */
    protected function redirectBasedOnRole(string $role): RedirectResponse
    {
        return match ($role) {
            'superadmin' => redirect()->intended(route('superadmin.dashboard')),
            'admin'      => redirect()->intended(route('admin.dashboard')),
            'karyawan'   => redirect()->intended(route('karyawan.dashboard')),
            default      => redirect()->route('login'),
        };
    }
}
