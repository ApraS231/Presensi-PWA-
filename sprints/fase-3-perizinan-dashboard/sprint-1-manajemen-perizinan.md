# Sprint 3.1 - Modul Pengajuan, Edit, Batal & Approval Izin

- **Fase:** 3 (Perizinan, Dashboard Monitoring & Live Map)
- **Estimasi Durasi:** 4 Hari
- **Prasyarat:** Fase 1 (Tabel leaves, notifications, settings) & Fase 2 (Attendance)

---

## 1. Kebutuhan Fungsional (FR)

- `FR-LV-01`: **Formulir Pengajuan Izin/Sakit/Cuti (PWA Karyawan)**:
  - Pilihan tipe perizinan: `izin`, `sakit`, `cuti`.
  - Pilihan rentang tanggal: `start_date` sampai `end_date`.
  - Validasi batas tanggal retroaktif: Pengajuan tanggal lampau tidak boleh melebihi nilai `max_retroaktif_izin` hari dari tabel `settings` (default: 3 hari).
  - Input keterangan alasan perizinan.
  - Upload berkas bukti dokumen (foto surat dokter / formulir izin) format PDF, JPG, PNG (maksimal 2MB).
  - Status default: `pending`.
- `FR-LV-02`: **Edit & Batalkan Izin (PWA Karyawan)**:
  - Karyawan dapat mengedit data pengajuan jika status masih `pending`.
  - Karyawan dapat membatalkan pengajuan (`status = 'cancelled'`) jika status masih `pending`.
  - Pengajuan yang sudah berstatus `approved` atau `rejected` dikunci secara permanen dan tidak dapat diubah oleh karyawan.
- `FR-LV-03`: **Workflow Verifikasi & Approval HRD (Desktop Dashboard)**:
  - Halaman daftar pengajuan izin dengan filter status (`pending`, `approved`, `rejected`, `cancelled`).
  - Pratinjau berkas lampiran pendukung langsung di browser.
  - Aksi Persetujuan: Tombol **Approve** atau **Reject** disertai textarea catatan evaluasi HRD (`review_note`).
- `FR-LV-04`: **Otomasi Record Presensi saat Izin Disetujui (Auto-Attendance Generation)**:
  - Saat izin berstatus `approved`, sistem secara otomatis membuat/memperbarui record pada tabel `attendances` untuk setiap hari kerja dalam rentang tanggal `start_date` sampai `end_date`.
  - Hari libur / akhir pekan (di luar parameter `hari_kerja` pada tabel `settings`) otomatis dilewati (skip).
  - Status kehadiran pada record `attendances` diisi sesuai tipe izin: `izin`, `sakit`, atau `cuti`.
- `FR-LV-05`: **Notifikasi Perubahan Status Izin**:
  - Sistem otomatis mengirim notifikasi in-app ke karyawan terkait via `NotificationService` saat perizinan disetujui atau ditolak.

---

## 2. Kebutuhan Non-Fungsional (NFR)

- `NFR-SEC-01`: File upload disimpan di storage privat/terproteksi dengan sanitasi nama berkas (UUID / timestamp random) untuk mencegah eksekusi skrip berbahaya.
- `NFR-DATA-01`: Sinkronisasi status izin dan generate record attendances dijalankan dalam Database Transaction atomic.

---

## 3. Langkah-Langkah Dekomposisi Teknis

### Langkah 1: Controller Perizinan Karyawan
Target: `app/Http/Controllers/Karyawan/LeaveController.php`

1. Method `create()` & `store()`:
   - Validasi form pengajuan:
     ```php
     $request->validate([
         'type'            => 'required|in:izin,sakit,cuti',
         'start_date'      => 'required|date',
         'end_date'        => 'required|date|after_or_equal:start_date',
         'reason'          => 'required|string|min:10',
         'attachment_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
     ]);
     ```
   - Validasi batas retroaktif:
     ```php
     $maxRetro = (int) Setting::getValue('max_retroaktif_izin', '3');
     $minAllowedDate = Carbon::today()->subDays($maxRetro);
     if (Carbon::parse($request->start_date)->lt($minAllowedDate)) {
         return back()->withErrors(['start_date' => "Pengajuan tanggal lampau maksimal {$maxRetro} hari ke belakang."]);
     }
     ```
   - Simpan berkas ke `storage/app/public/leaves/`.
2. Method `edit($id)`, `update($id)`: Hanya izinkan jika `status === 'pending'` dan `user_id === Auth::id()`.
3. Method `cancel($id)`: Ubah status menjadi `cancelled` jika masih `pending`.

### Langkah 2: Controller Approval HRD
Target: `app/Http/Controllers/Admin/LeaveApprovalController.php`

```php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Setting;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveApprovalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $leaves = Leave::with('user')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.leaves.index', compact('leaves', 'status'));
    }

    public function process(Request $request, $id)
    {
        $request->validate([
            'action'      => 'required|in:approve,reject',
            'review_note' => 'nullable|string|max:500',
        ]);

        $leave = Leave::findOrFail($id);

        DB::transaction(function () use ($request, $leave) {
            $newStatus = $request->action === 'approve' ? 'approved' : 'rejected';

            $leave->update([
                'status'      => $newStatus,
                'approved_by' => Auth::id(),
                'review_note' => $request->review_note,
            ]);

            // Jika Approve, buat record attendance untuk seluruh hari kerja
            if ($newStatus === 'approved') {
                $hariKerjaSetting = Setting::getValue('hari_kerja', 'senin,selasa,rabu,kamis,jumat');
                $hariKerjaList = array_map('trim', explode(',', strtolower($hariKerjaSetting)));

                $current = Carbon::parse($leave->start_date);
                $end = Carbon::parse($leave->end_date);

                while ($current->lte($end)) {
                    $dayNameIndo = strtolower($current->locale('id')->isoFormat('dddd'));

                    if (in_array($dayNameIndo, $hariKerjaList)) {
                        Attendance::updateOrCreate(
                            ['user_id' => $leave->user_id, 'date' => $current->toDateString()],
                            [
                                'status'          => $leave->type, // 'izin', 'sakit', 'cuti'
                                'time_in'         => null,
                                'time_out'        => null,
                                'distance_meters' => null,
                            ]
                        );
                    }
                    $current->addDay();
                }
            }

            // Kirim Notifikasi ke Karyawan
            $title = $newStatus === 'approved' ? 'Pengajuan Izin Disetujui' : 'Pengajuan Izin Ditolak';
            $msg = "Pengajuan {$leave->type} Anda untuk tanggal {$leave->start_date} s/d {$leave->end_date} telah " . ($newStatus === 'approved' ? 'disetujui' : 'ditolak') . " oleh HRD.";
            NotificationService::send($leave->user_id, $title, $msg, 'leave_' . $newStatus);
        });

        return back()->with('success', 'Status perizinan berhasil diperbarui.');
    }
}
```

### Langkah 3: Antarmuka Blade
Target: `resources/views/karyawan/izin/` dan `resources/views/admin/leaves/`

1. Form pengajuan M3 di PWA Karyawan dengan file picker dan date range.
2. List pengajuan perizinan karyawan dengan chip status (`pending`, `approved`, `rejected`, `cancelled`) dan tombol edit/cancel.
3. Tabel approval HRD dengan modal preview lampiran surat dan tombol aksi konfirmasi.

---

## 4. Kriteria Penerimaan (Definition of Done)

- [ ] Karyawan berhasil submit perizinan dengan lampiran berkas <= 2MB.
- [ ] Pengajuan tanggal retroaktif melampaui setting ditolak sistem.
- [ ] Pengajuan status `pending` dapat diubah atau dibatalkan oleh karyawan.
- [ ] HRD berhasil menyetujui (Approve) atau menolak (Reject) perizinan disertai catatan.
- [ ] Izin yang di-approve otomatis membentuk baris `attendances` pada hari kerja yang bersangkutan (hari libur di-skip).
- [ ] Karyawan menerima notifikasi in-app saat perizinan selesai diproses oleh HRD.
