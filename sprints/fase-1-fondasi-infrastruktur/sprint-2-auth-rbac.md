# Sprint 1.2 - Autentikasi, Status Akun & Otorisasi RBAC

- **Fase:** 1 (Fondasi Proyek & Infrastruktur)
- **Estimasi Durasi:** 3 Hari
- **Prasyarat:** Sprint 1.1 (Database & Model User selesai)

---

## 1. Kebutuhan Fungsional (FR)

- `FR-AUTH-01`: Login aman berbasis kredensial ganda (kombinasi NIK atau Email bersama Kata Sandi).
- `FR-AUTH-02`: Validasi status akun (`is_active`). Jika akun dinonaktifkan (`is_active = false`), proses autentikasi ditolak dengan pesan: *"Akun Anda telah dinonaktifkan. Hubungi Admin."*
- `FR-AUTH-03`: Role-Based Access Control (RBAC) dengan 3 peran pengguna:
  1. `karyawan`: Akses dibatasi ke antarmuka Mobile PWA (`/karyawan/*`).
  2. `admin` (HRD): Akses ke Dashboard Operasional HRD (`/admin/*`).
  3. `superadmin` (IT): Akses ke Master Control & Konfigurasi Sistem (`/superadmin/*`).
- `FR-AUTH-04`: Pengalihan rute otomatis (Redirect Handler) setelah login berhasil berdasarkan peran masing-masing aktor.
- `FR-AUTH-05`: Fitur Logout aman yang memusnahkan session server dan meregenerasi token CSRF.
- `FR-AUTH-06`: Fitur ganti kata sandi mandiri untuk seluruh role dengan verifikasi kata sandi lama.

---

## 2. Kebutuhan Non-Fungsional (NFR)

- `NFR-SEC-01`: Hashing seluruh kata sandi menggunakan algoritma Bcrypt (Work Factor minimum 10).
- `NFR-SEC-02`: Penanganan Brute Force Attack menggunakan rate limiter (maksimal 5 percobaan login gagal per menit per IP/akun).
- `NFR-SEC-03`: Middleware verifikasi status aktif (`CheckActive`) dieksekusi pada *setiap* HTTP request terotentikasi, bukan hanya saat proses login awal. Jika akun dinonaktifkan di tengah sesi aktif, sesi langsung diputus.

---

## 3. Langkah-Langkah Dekomposisi Teknis

### Langkah 1: Pembuatan Middleware RBAC & Keamanan Sesi
Target folder: `app/Http/Middleware/`

1. **Middleware `CheckActive.php`**:
   ```php
   namespace App\Http\Middleware;

   use Closure;
   use Illuminate\Http\Request;
   use Illuminate\Support\Facades\Auth;

   class CheckActive
   {
       public function handle(Request $request, Closure $next)
       {
           if (Auth::check() && !Auth::user()->is_active) {
               Auth::logout();
               $request->session()->invalidate();
               $request->session()->regenerateToken();
               return redirect()->route('login')->withErrors([
                   'login' => 'Akun Anda telah dinonaktifkan. Hubungi Administrator.'
               ]);
           }
           return $next($request);
       }
   }
   ```
2. **Middleware `RoleKaryawan.php`**:
   - Memastikan `Auth::user()->role === 'karyawan'`.
   - Jika bukan, tolak dengan response 403 Forbidden atau redirect ke dashboard rolenya.
3. **Middleware `RoleAdmin.php`**:
   - Memastikan `Auth::user()->role === 'admin' || Auth::user()->role === 'superadmin'`.
4. **Middleware `RoleSuperadmin.php`**:
   - Memastikan `Auth::user()->role === 'superadmin'`.
5. Daftarkan alias middleware pada `bootstrap/app.php` (Laravel 11 standard):
   ```php
   ->withMiddleware(function (Middleware $middleware) {
       $middleware->alias([
           'active' => \App\Http\Middleware\CheckActive::class,
           'role.karyawan' => \App\Http\Middleware\RoleKaryawan::class,
           'role.admin' => \App\Http\Middleware\RoleAdmin::class,
           'role.superadmin' => \App\Http\Middleware\RoleSuperadmin::class,
       ]);
   })
   ```

### Langkah 2: Controller Autentikasi
Target: `app/Http/Controllers/Auth/LoginController.php`

1. Logic verifikasi NIK atau Email:
   ```php
   $loginType = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'nik';
   $credentials = [
       $loginType => $request->input('login'),
       'password' => $request->input('password'),
   ];
   ```
2. Cek apakah autentikasi berhasil:
   ```php
   if (Auth::attempt($credentials, $request->boolean('remember'))) {
       $user = Auth::user();
       if (!$user->is_active) {
           Auth::logout();
           return back()->withErrors(['login' => 'Akun Anda telah dinonaktifkan. Hubungi Admin.']);
       }
       $request->session()->regenerate();
       return match($user->role) {
           'superadmin' => redirect()->intended(route('superadmin.dashboard')),
           'admin'      => redirect()->intended(route('admin.dashboard')),
           'karyawan'   => redirect()->intended(route('karyawan.dashboard')),
           default      => redirect()->route('login'),
       };
   }
   ```

### Langkah 3: Antarmuka Halaman Login (Material Design 3)
Target: `resources/views/auth/login.blade.php`

1. Buat tampilan modern berbasis token M3 (warna brand biru PT. CAK).
2. Input field responsif untuk NIK/Email dan Password dengan toggle visibility mata.
3. Error alert M3 jika kredensial salah atau akun dinonaktifkan.

### Langkah 4: Rute Web Terproteksi
Target: `routes/web.php`

1. Rute publik: `GET /login`, `POST /login`, `POST /logout`.
2. Grup Karyawan: `middleware(['auth', 'active', 'role.karyawan'])->prefix('karyawan')->name('karyawan.')`.
3. Grup Admin: `middleware(['auth', 'active', 'role.admin'])->prefix('admin')->name('admin.')`.
4. Grup Superadmin: `middleware(['auth', 'active', 'role.superadmin'])->prefix('superadmin')->name('superadmin.')`.

---

## 4. Kriteria Penerimaan (Definition of Done)

- [ ] Login menggunakan NIK valid berhasil dan dialihkan ke dashboard yang sesuai rolenya.
- [ ] Login menggunakan Email valid berhasil.
- [ ] Akun dengan `is_active = false` gagal login dan menerima pesan kesalahan spesifik.
- [ ] Jika status akun diubah menjadi nonaktif saat sesi berjalan, request berikutnya otomatis me-logout user.
- [ ] Pengguna role `karyawan` tidak dapat mengakses URL `/admin/*` atau `/superadmin/*` (HTTP 403 / Redirect).
- [ ] Percobaan login gagal lebih dari 5 kali memicu throttling pembatasan waktu.
