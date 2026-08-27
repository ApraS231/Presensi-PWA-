# Sprint 2.3 - Transaksi Presensi Masuk & Pulang

- **Fase:** 2 (Biometrik Wajah & Geofencing Core)
- **Estimasi Durasi:** 4 Hari
- **Prasyarat:** Sprint 2.1 (face-api.js & Enrollment) & Sprint 2.2 (Geofencing)

---

## 1. Kebutuhan Fungsional (FR)

- `FR-ATT-01`: **Pemeriksaan Prasyarat Presensi**:
  - Validasi `users.enrollment_status === 'enrolled'`. Jika `pending`, alihkan dengan pesan: *"Akun Anda belum terdaftar biometrik. Silakan hubungi HRD."*
  - Pemeriksaan status presensi hari berjalan untuk mencegah presensi ganda (Double Check-In):
    - Belum absen: Masuk ke flow **Presensi Masuk**.
    - Sudah absen masuk, belum pulang: Masuk ke flow **Presensi Pulang**.
    - Sudah absen masuk dan pulang: Tampilkan ringkasan *"Anda sudah menyelesaikan presensi hari ini."*
- `FR-ATT-02`: **Verifikasi Biometrik Wajah (Face Matching)**:
  - Mengambil template descriptor tersimpan dari user yang sedang login.
  - Membandingkan descriptor wajah saat presensi dengan template terdaftar menggunakan Euclidean Distance.
  - Ambang batas kecocokan: `Euclidean Distance <= 0.50` = **MATCH (Wajah Cocok)**.
  - Jika `distance > 0.50` = Notifikasi *"Wajah tidak dikenali, posisikan wajah Anda lebih jelas dan ulangi."*
- `FR-ATT-03`: **Pencatatan Presensi Masuk**:
  - Kirim payload terverifikasi: Foto snapshot (Base64), Koordinat GPS (Lat, Long), Timestamp.
  - Backend memvalidasi CSRF, re-kalkulasi Haversine, evaluasi jam masuk terhadap parameter `settings` (`jam_masuk` + `toleransi_terlambat`).
  - Simpan record ke tabel `attendances`: `time_in`, `lat_in`, `long_in`, `photo_in`, status (`tepat_waktu` atau `terlambat`), `distance_meters`.
- `FR-ATT-04`: **Pencatatan Presensi Pulang**:
  - Menjalankan verifikasi biometrik dan geofencing identik dengan presensi masuk.
  - Memperbarui baris data harian yang ada (`update` record): `time_out`, `lat_out`, `long_out`, `photo_out`.
- `FR-ATT-05`: **Manajemen Storage Foto**:
  - Konversi base64 ke file gambar JPEG, kompresi kualitas ke 70% untuk menghemat kapasitas server.
  - Simpan di folder terstruktur: `storage/app/public/attendances/{user_id}/{Y-m-d}/photo_in.jpg` dan `photo_out.jpg`.

---

## 2. Kebutuhan Non-Fungsional (NFR)

- `NFR-ACC-01`: Tingkat kecocokan biometrik (Face Recognition Accuracy) >= 95% pada kondisi pencahayaan wajar.
- `NFR-SEC-01`: Pencegahan manipulasi gambar: Foto snapshot diambil langsung dari video stream kamera secara programatis saat descriptor valid, bukan dari file upload galeri.
- `NFR-PERF-01`: Waktu simpan transaksi presensi server-side < 500ms.
- `NFR-STORE-01`: Ukuran file foto presensi terkompresi berkisar antara 40KB - 80KB per snapshot.

---

## 3. Langkah-Langkah Dekomposisi Teknis

### Langkah 1: Script Face Matching di Sisi Klien
Target: `public/js/presensi.js`

```javascript
// Menghitung Euclidean Distance antara dua 128-float vectors
function euclideanDistance(desc1, desc2) {
    let sum = 0;
    for (let i = 0; i < 128; i++) {
        const diff = desc1[i] - desc2[i];
        sum += diff * diff;
    }
    return Math.sqrt(sum);
}

// Proses verifikasi saat tombol ditekan
async function verifyAndSubmitAttendance(type) {
    const video = document.getElementById('webcam-video');
    const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptor();

    if (!detection) {
        showToast('Wajah tidak terdeteksi dalam frame. Pastikan pencahayaan cukup.', 'error');
        return;
    }

    const currentDescriptor = Array.from(detection.descriptor);
    const registeredDescriptor = window.USER_REGISTERED_DESCRIPTOR; // Diinjeksi dari Blade backend

    const distance = euclideanDistance(currentDescriptor, registeredDescriptor);
    const MATCH_THRESHOLD = 0.50;

    if (distance > MATCH_THRESHOLD) {
        showToast(`Wajah tidak cocok (Skor deviasi: ${distance.toFixed(2)}). Silakan coba lagi.`, 'error');
        return;
    }

    // Ambil foto snapshot dari video frame
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);
    const photoBase64 = canvas.toDataURL('image/jpeg', 0.7);

    // Ambil posisi GPS terbaru
    const gps = await getCurrentGPSPosition();

    // Kirim payload ke backend Laravel
    submitPayloadToServer({
        type: type, // 'in' atau 'out'
        photo: photoBase64,
        latitude: gps.latitude,
        longitude: gps.longitude
    });
}
```

### Langkah 2: Controller Presensi Backend
Target: `app/Http/Controllers/Karyawan/PresensiController.php`

```php
namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Location;
use App\Models\Setting;
use App\Services\HaversineService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PresensiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->enrollment_status !== 'enrolled') {
            return redirect()->route('karyawan.dashboard')
                ->with('error', 'Akun Anda belum terdaftar biometrik wajah. Hubungi HRD.');
        }

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->first();

        $registeredDescriptor = $user->faceDescriptor?->descriptor_data;
        $activeLocations = Location::where('is_active', true)->get();

        return view('karyawan.presensi', compact('todayAttendance', 'registeredDescriptor', 'activeLocations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'      => 'required|in:in,out',
            'photo'     => 'required|string',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();
        $today = Carbon::today();

        // 1. Validasi Ulang Geofencing di Server
        $activeLocations = Location::where('is_active', true)->get();
        $geoResult = HaversineService::validateLocation(
            $request->latitude,
            $request->longitude,
            $activeLocations
        );

        if (!$geoResult['is_valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Presensi ditolak. Posisi Anda di luar radius kantor (' . $geoResult['distance_meters'] . 'm).'
            ], 422);
        }

        // 2. Simpan & Kompres Foto Snapshot
        $photoData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->photo));
        $dateStr = $today->format('Y-m-d');
        $fileName = ($request->type === 'in' ? 'photo_in.jpg' : 'photo_out.jpg');
        $filePath = "attendances/{$user->id}/{$dateStr}/{$fileName}";
        Storage::disk('public')->put($filePath, $photoData);

        // 3. Evaluasi Status Kehadiran (Masuk)
        if ($request->type === 'in') {
            $existing = Attendance::where('user_id', $user->id)->where('date', $today)->first();
            if ($existing && $existing->time_in) {
                return response()->json(['success' => false, 'message' => 'Anda sudah presensi masuk hari ini.'], 400);
            }

            $jamMasuk = Setting::getValue('jam_masuk', '08:00');
            $toleransi = (int) Setting::getValue('toleransi_terlambat', '15');
            $batasTerlambat = Carbon::createFromTimeString($jamMasuk)->addMinutes($toleransi);
            $currentTime = Carbon::now();

            $status = $currentTime->gt($batasTerlambat) ? 'terlambat' : 'tepat_waktu';

            Attendance::updateOrCreate(
                ['user_id' => $user->id, 'date' => $today],
                [
                    'location_id'     => $geoResult['location']->id,
                    'time_in'         => $currentTime->format('H:i:s'),
                    'lat_in'          => $request->latitude,
                    'long_in'         => $request->longitude,
                    'photo_in'        => $filePath,
                    'status'          => $status,
                    'distance_meters' => $geoResult['distance_meters'],
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Presensi Masuk Berhasil recorded. Status: ' . strtoupper(str_replace('_', ' ', $status)),
                'status'  => $status
            ]);
        }

        // 4. Update Presensi Pulang
        if ($request->type === 'out') {
            $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
            if (!$attendance || !$attendance->time_in) {
                return response()->json(['success' => false, 'message' => 'Anda belum presensi masuk hari ini.'], 400);
            }
            if ($attendance->time_out) {
                return response()->json(['success' => false, 'message' => 'Anda sudah menyelesaikan presensi pulang hari ini.'], 400);
            }

            $attendance->update([
                'time_out'  => Carbon::now()->format('H:i:s'),
                'lat_out'   => $request->latitude,
                'long_out'  => $request->longitude,
                'photo_out' => $filePath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Presensi Pulang Berhasil recorded. Terima kasih atas kerja keras Anda hari ini!'
            ]);
        }
    }
}
```

### Langkah 3: Halaman Blade Presensi Mobile PWA
Target: `resources/views/karyawan/presensi.blade.php`

- Viewfinder kamera M3 dengan overlay Bounding Box.
- Status badge jarak real-time dan nama lokasi terdekat.
- Tombol action dinamis ("Presensi Masuk" atau "Presensi Pulang").
- Feedback dialog M3 saat presensi sukses dengan foto preview dan jam kehadiran.

---

## 4. Kriteria Penerimaan (Definition of Done)

- [ ] Karyawan tanpa data enrollment tidak dapat membuka menu presensi.
- [ ] Deteksi dan matching wajah berjalan di browser: Wajah cocok (jarak <= 0.50) mengizinkan submit, wajah tidak cocok ditolak.
- [ ] Presensi Masuk berhasil menyimpan waktu masuk, foto terkompresi, koordinat, dan status kedisiplinan (tepat waktu / terlambat).
- [ ] Presensi Pulang berhasil memperbarui record harian tanpa membuat baris baru.
- [ ] Percobaan double check-in ditolak oleh sistem.
- [ ] Foto tersimpan pada storage publik dan dapat ditampilkan pada pratinjau kartu riwayat.
