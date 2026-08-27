# Sprint 2.1 - Integrasi face-api.js & Enrollment Biometrik

- **Fase:** 2 (Biometrik Wajah & Geofencing Core)
- **Estimasi Durasi:** 4 Hari
- **Prasyarat:** Fase 1 selesai (Database, Auth, M3 Layout)

---

## 1. Kebutuhan Fungsional (FR)

- `FR-BIO-01`: Penyediaan aset bobot model TensorFlow.js (`public/models/`) untuk `tinyFaceDetector`, `faceLandmark68Net`, dan `faceRecognitionNet`.
- `FR-BIO-02`: Inisialisasi streaming kamera peramban (WebCam API) pada sisi klien dengan penanganan izin kamera yang ramah (Graceful Permission Handling).
- `FR-BIO-03`: Deteksi wajah real-time pada frame video stream dan visualisasi Bounding Box hijau M3 sebagai indikator liveness/wajah terdeteksi.
- `FR-BIO-04`: Modul **Enrollment Biometrik Wajah** (Akses HRD / Admin):
  - Memilih karyawan dari daftar karyawan dengan status `enrollment_status = 'pending'`.
  - Mengambil 3 hingga 5 sampel foto snapshot wajah dari berbagai sudut (lurus, sedikit miring kiri, sedikit miring kanan).
  - Mengekstraksi 128-dimensional facial descriptor dari setiap sampel snapshot.
  - Menghitung rata-rata matematis (*Mean Vector*) 128 float descriptor untuk meningkatkan akurasi toleransi pencahayaan dan ekspresi.
  - Menyimpan payload JSON descriptor ke tabel `face_descriptors` dan mengubah `users.enrollment_status = 'enrolled'`.
- `FR-BIO-05`: Fitur **Re-Enrollment Wajah** (Akses HRD / Admin):
  - Mengganti descriptor lama dengan template wajah baru saat terjadi perubahan fisik signifikan pada karyawan.

---

## 2. Kebutuhan Non-Fungsional (NFR)

- `NFR-PERF-01`: Komputasi ekstraksi descriptor 128-float dilakukan sepenuhnya di sisi klien (browser browser-side inference) untuk menjaga utilisasi CPU server Laravel tetap rendah.
- `NFR-PERF-02`: Kecepatan deteksi wajah di peramban <= 2 detik pada perangkat smartphone kelas menengah (Snapdragon 600 series / setara).
- `NFR-UX-01`: Penanganan kegagalan izin kamera yang instruktif (Graceful Error):
  - Pesan error jelas jika izin kamera ditolak.
  - Tombol "Coba Lagi" (maksimal 3 kali percobaan).
  - Pesan eskalasi: *"Hubungi HRD untuk bantuan akses kamera"* jika tetap gagal.

---

## 3. Langkah-Langkah Dekomposisi Teknis

### Langkah 1: Setup Aset Model face-api.js
Target folder: `public/models/` dan `public/js/`

1. Simpan library `face-api.min.js` di `public/js/face-api.min.js`.
2. Letakkan file bobot model di `public/models/`:
   - `tiny_face_detector_model-weights_manifest.json` & shard binary.
   - `face_landmark_68_model-weights_manifest.json` & shard binary.
   - `face_recognition_model-weights_manifest.json` & shard binary.

### Langkah 2: Script Ekstraksi & Komputasi Mean Vector
Target: `public/js/enrollment.js`

1. Memuat seluruh model saat halaman dibuka:
   ```javascript
   async function loadFaceApiModels() {
       const MODEL_URL = '/models';
       await Promise.all([
           faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
           faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
           faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
       ]);
   }
   ```
2. Fungsi aktivasi kamera dengan error boundary:
   ```javascript
   async function startCamera(videoElement) {
       try {
           const stream = await navigator.mediaDevices.getUserMedia({
               video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
           });
           videoElement.srcObject = stream;
       } catch (err) {
           handleCameraError(err);
       }
   }
   ```
3. Komputasi Mean Vector 128-float dari array sampel descriptor:
   ```javascript
   function calculateMeanDescriptor(descriptorsArray) {
       const numSamples = descriptorsArray.length;
       const meanVector = new Float32Array(128);
       for (let i = 0; i < 128; i++) {
           let sum = 0;
           for (let j = 0; j < numSamples; j++) {
               sum += descriptorsArray[j][i];
           }
           meanVector[i] = sum / numSamples;
       }
       return Array.from(meanVector); // Convert to regular array for JSON serialization
   }
   ```

### Langkah 3: Controller Enrollment HRD
Target: `app/Http/Controllers/Admin/EnrollmentController.php`

1. Method `index()`: Menampilkan daftar karyawan yang belum enrolled atau butuh re-enrollment.
2. Method `create($userId)`: Halaman form enrollment dengan interface kamera web.
3. Method `store(Request $request, $userId)`:
   - Validasi data input: `descriptor_data` (array valid 128 numeric values), `sample_photo` (base64 string image).
   - Simpan foto sampel ke `storage/app/public/avatars/`.
   - Update or Create record pada tabel `face_descriptors`:
     ```php
     FaceDescriptor::updateOrCreate(
         ['user_id' => $userId],
         [
             'descriptor_data' => $request->input('descriptor_data'),
             'sample_photo'    => $photoPath,
         ]
     );
     User::where('id', $userId)->update(['enrollment_status' => 'enrolled']);
     ```
   - Kirim notifikasi konfirmasi pendaftaran biometrik.

### Langkah 4: Tampilan Blade Enrollment
Target: `resources/views/admin/enrollment/index.blade.php` & `create.blade.php`

1. Tampilan split-view M3: Sisi kiri instruksi dan tombol trigger snapshot (1/3, 2/3, 3/3), sisi kanan live view kamera dengan canvas overlay bounding box.
2. Indikator progress enrollment (3/3 snapshot berhasil -> tombol "Simpan Biometrik" aktif).

---

## 4. Kriteria Penerimaan (Definition of Done)

- [ ] Model face-api.js ter-load sukses di browser tanpa peringatan CORS / 404.
- [ ] Kamera webcam desktop aktif dan mendeteksi wajah dengan bounding box real-time.
- [ ] Pengambilan 3-5 foto snapshot menghasilkan 128 float array yang valid setelah dihitung rata-ratanya.
- [ ] Data tersimpan di tabel `face_descriptors` dan `users.enrollment_status` berubah menjadi `enrolled`.
- [ ] Penolakan izin kamera menampilkan modal instruksi dan penanganan error yang jelas.
