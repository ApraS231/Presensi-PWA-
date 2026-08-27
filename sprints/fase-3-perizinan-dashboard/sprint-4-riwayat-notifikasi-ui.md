# Sprint 3.4 - Riwayat Presensi & UI Notifikasi PWA

- **Fase:** 3 (Perizinan, Dashboard Monitoring & Live Map)
- **Estimasi Durasi:** 2 Hari
- **Prasyarat:** Sprint 2.3 (Presensi) & Sprint 1.4 (Notification Service)

---

## 1. Kebutuhan Fungsional (FR)

- `FR-HIST-01`: **Halaman Riwayat Presensi Mandiri (PWA Karyawan)**:
  - Menampilkan daftar catatan kehadiran pengguna yang sedang login.
  - Filter rentang bulan dan tahun.
  - Kartu riwayat per hari memuat: Tanggal, Jam Masuk, Jam Pulang, Lokasi Presensi, Status Badge (`tepat_waktu`, `terlambat`, `izin`, `sakit`, `cuti`, `alpha`).
  - Penanda visual khusus jika record mengalami **Auto-Checkout** (karyawan lupa presensi pulang).
  - Modal detail: Menampilkan foto snapshot masuk & pulang beserta titik koordinat.
- `FR-NOTIF-UI-01`: **Pusat Notifikasi PWA**:
  - Halaman / Drawer Notifikasi yang memuat riwayat pemberitahuan.
  - Status baca (sudah/belum dibaca), tombol "Tandai Semua Dibaca".

---

## 2. Kebutuhan Non-Fungsional (NFR)

- `NFR-RESP-01`: Daftar riwayat presensi mobile mendukung Infinite Scroll atau Pagination ringan (15 item per batch) dengan performa scroll 60fps.
- `NFR-SEC-01`: Karyawan dibatasi secara mutlak hanya dapat mengakses riwayat kehadiran dan notifikasi miliknya sendiri.

---

## 3. Langkah-Langkah Dekomposisi Teknis

### Langkah 1: Controller Riwayat Presensi Karyawan
Target: `app/Http/Controllers/Karyawan/RiwayatController.php`

```php
namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedMonth = $request->query('month', Carbon::now()->month);
        $selectedYear  = $request->query('year', Carbon::now()->year);

        $attendances = Attendance::with('location')
            ->where('user_id', $user->id)
            ->whereMonth('date', $selectedMonth)
            ->whereYear('date', $selectedYear)
            ->orderByDesc('date')
            ->paginate(15);

        // Rekap ringkas bulan terpilih
        $totalHadir = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $selectedMonth)
            ->whereYear('date', $selectedYear)
            ->whereIn('status', ['tepat_waktu', 'terlambat'])
            ->count();

        $totalTerlambat = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $selectedMonth)
            ->whereYear('date', $selectedYear)
            ->where('status', 'terlambat')
            ->count();

        $totalIzin = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $selectedMonth)
            ->whereYear('date', $selectedYear)
            ->whereIn('status', ['izin', 'sakit', 'cuti'])
            ->count();

        return view('karyawan.riwayat', compact(
            'attendances', 'selectedMonth', 'selectedYear',
            'totalHadir', 'totalTerlambat', 'totalIzin'
        ));
    }
}
```

### Langkah 2: Tampilan Blade Riwayat PWA
Target: `resources/views/karyawan/riwayat.blade.php`

- Filter bar bulan dan tahun (Dropdown M3).
- Kartu ringkasan bulanan (Total Hadir, Total Terlambat, Total Izin).
- List kartu riwayat per hari (`.md-card-outlined`) dengan expandable modal preview foto.

---

## 4. Kriteria Penerimaan (Definition of Done)

- [ ] Riwayat presensi menampilkan seluruh record kehadiran pengguna yang login secara kronologis.
- [ ] Filter bulan/tahun berhasil menyaring data.
- [ ] Record hasil auto-checkout menampilkan badge atau icon penanda peringatan.
- [ ] Foto selfie presensi dapat dibuka dan diperbesar via modal detail.
- [ ] Keamanan data terjamin: URL manipulasi ID user lain dicegah.
