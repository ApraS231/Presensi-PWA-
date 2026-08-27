# Sprint 3.2 - Dashboard Monitoring Real-Time HRD

- **Fase:** 3 (Perizinan, Dashboard Monitoring & Live Map)
- **Estimasi Durasi:** 3 Hari
- **Prasyarat:** Sprint 2.3 (Presensi), Sprint 2.4 (Alpha Auto), & Sprint 3.1 (Perizinan)

---

## 1. Kebutuhan Fungsional (FR)

- `FR-DASH-01`: **Kartu Statistik Real-Time Harian**:
  - Total Karyawan Aktif.
  - Hadir Tepat Waktu.
  - Hadir Terlambat.
  - Izin / Sakit / Cuti.
  - Belum Hadir / Alpha.
  - Persentase Tingkat Kehadiran Harian (`(Hadir / Total Karyawan) * 100%`).
- `FR-DASH-02`: **Filter Data Presensi**:
  - Filter tanggal pantau (default: hari berjalan / Today).
  - Filter berdasarkan **Departemen** (`department` pada tabel `users`).
  - Filter berdasarkan status kehadiran.
- `FR-DASH-03`: **Reminder Badge Enrollment**:
  - Banner / Badge perhatian jika terdapat karyawan aktif yang belum menyelesaikan enrollment biometrik (`enrollment_status = 'pending'`).
- `FR-DASH-04`: **Tabel Monitoring Langsung**:
  - Daftar presensi karyawan hari berjalan dengan auto-refresh / polling berkala (interval 30 detik).
  - Kolom: NIK, Nama, Departemen, Jam Masuk, Jam Pulang, Jarak, Status Kedisiplinan, Foto Snapshot Thumbnail.

---

## 2. Kebutuhan Non-Fungsional (NFR)

- `NFR-PERF-01`: Agregasi query statistik dioptimalkan menggunakan indexing database dan eager loading (`with('user', 'location')`) dengan waktu response < 200ms.
- `NFR-UX-01`: Kartu statistik menerapkan standar M3 Elevated Card dengan transisi animasi micro-interaction saat angka data termuat.

---

## 3. Langkah-Langkah Dekomposisi Teknis

### Langkah 1: Controller Dashboard & Monitoring HRD
Target: `app/Http/Controllers/Admin/MonitoringController.php`

```php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        $targetDate = $request->query('date') ? Carbon::parse($request->date) : Carbon::today();
        $department = $request->query('department');
        $status = $request->query('status');

        // 1. Total Karyawan Aktif
        $totalEmployeesQuery = User::where('role', 'karyawan')->where('is_active', true);
        if ($department) {
            $totalEmployeesQuery->where('department', $department);
        }
        $totalEmployees = $totalEmployeesQuery->count();

        // 2. Query Attendances Hari Ini
        $attendancesQuery = Attendance::with('user', 'location')
            ->where('date', $targetDate)
            ->whereHas('user', function($q) use ($department) {
                $q->where('role', 'karyawan')->where('is_active', true);
                if ($department) {
                    $q->where('department', $department);
                }
            });

        if ($status) {
            $attendancesQuery->where('status', $status);
        }

        $attendances = $attendancesQuery->orderByDesc('time_in')->get();

        // 3. Statistik Agregasi
        $tepatWaktuCount = $attendances->where('status', 'tepat_waktu')->count();
        $terlambatCount  = $attendances->where('status', 'terlambat')->count();
        $izinCount       = $attendances->whereIn('status', ['izin', 'sakit', 'cuti'])->count();
        $alphaCount      = $attendances->where('status', 'alpha')->count();
        $totalHadir      = $tepatWaktuCount + $terlambatCount;

        $persentaseKehadiran = $totalEmployees > 0 ? round(($totalHadir / $totalEmployees) * 100, 1) : 0;

        // 4. Karyawan Belum Enrollment
        $pendingEnrollmentCount = User::where('role', 'karyawan')
            ->where('is_active', true)
            ->where('enrollment_status', 'pending')
            ->count();

        // 5. Daftar Departemen untuk Filter
        $departments = User::whereNotNull('department')->distinct()->pluck('department');

        return view('admin.monitoring', compact(
            'targetDate', 'department', 'status', 'departments',
            'totalEmployees', 'tepatWaktuCount', 'terlambatCount', 'izinCount', 'alphaCount',
            'persentaseKehadiran', 'pendingEnrollmentCount', 'attendances'
        ));
    }
}
```

### Langkah 2: Tampilan Blade Monitoring HRD
Target: `resources/views/admin/monitoring.blade.php`

- Grid 4 Kartu Statistik M3 (Hadir, Terlambat, Izin/Cuti, Alpha) dengan warna semantic tokens.
- Banner alert peringatan jumlah karyawan yang belum enrollment disertai link cepat ke menu Enrollment.
- Filter toolbar (Datepicker, Dropdown Departemen, Dropdown Status).
- Tabel data kehadiran dengan modal pratinjau foto snapshot `photo_in` & `photo_out`.

---

## 4. Kriteria Penerimaan (Definition of Done)

- [ ] Kartu statistik menampilkan kalkulasi akurat sesuai jumlah data di database.
- [ ] Filter departemen dan tanggal berhasil menyaring baris data presensi secara tepat.
- [ ] Banner reminder enrollment muncul otomatis jika ada user dengan status pending enrollment.
- [ ] Modal preview foto menampilkan foto selfie presensi karyawan secara jelas.
- [ ] Polling data berjalan otomatis setiap 30 detik memperbarui tabel tanpa reload penuh.
