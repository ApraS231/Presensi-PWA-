@extends('layouts.pwa')

@section('title', 'Transaksi Presensi Harian - Presensi PT. CAK')

@push('styles')
<style>
    .camera-card {
        position: relative;
        width: 100%;
        background-color: #000000;
        border-radius: var(--md-sys-shape-corner-extra-large);
        overflow: hidden;
        aspect-ratio: 3/4;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--md-sys-elevation-2);
        border: 2px solid var(--md-sys-color-outline-variant);
    }

    #webcamVideo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1);
    }

    #overlayCanvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        transform: scaleX(-1);
    }

    .face-guide-circle {
        position: absolute;
        width: 210px;
        height: 270px;
        border: 3px dashed rgba(255, 255, 255, 0.7);
        border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
        pointer-events: none;
        box-sizing: border-box;
        transition: all 0.25s ease;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.4);
    }

    .face-guide-circle.ready {
        border: 3px solid #00E676;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.3), 0 0 15px rgba(0, 230, 118, 0.6);
    }

    .face-guide-circle.error {
        border: 3px solid #FF5252;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.3), 0 0 15px rgba(255, 82, 82, 0.6);
    }

    .verification-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .status-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: var(--md-sys-shape-corner-medium);
        background-color: var(--md-sys-color-surface-container);
        border: 1px solid var(--md-sys-color-outline-variant);
        font-size: 12px;
        font-weight: 500;
        line-height: 16px;
    }

    .status-pill.valid {
        background-color: var(--md-custom-color-success-container);
        color: var(--md-custom-color-on-success-container);
        border-color: var(--md-custom-color-success);
    }

    .status-pill.invalid {
        background-color: var(--md-sys-color-error-container);
        color: var(--md-sys-color-on-error-container);
        border-color: var(--md-sys-color-error);
    }
</style>
@endpush

@section('content')
<div style="display: flex; flex-direction: column; gap: 18px;">

    @if($mode === 'completed')
        <!-- Presensi Hari Ini Sudah Lengkap -->
        <div class="clay-card" style="text-align: center; padding: 40px 24px;">
            <div class="clay-combo-badge" style="width: 64px; height: 64px; background: var(--md-custom-color-success); margin: 0 auto 16px auto;">
                <span class="material-symbols-rounded" style="font-size: 34px; color: #FFFFFF;">task_alt</span>
            </div>
            <div class="clay-pill-tag" style="margin-bottom: 8px;">
                <span>Presensi Hari Ini Selesai</span>
            </div>
            <h2 class="font-editorial-serif" style="font-size: 24px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0 0 6px 0;">
                Tugas Presensi Lengkap
            </h2>
            <p style="font-size: 13px; color: var(--md-sys-color-on-surface-variant); margin: 0 0 20px 0; line-height: 1.5;">
                Anda telah menyelesaikan presensi masuk dan pulang untuk hari ini.
            </p>

            <div class="clay-inset-box" style="text-align: left; display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--md-sys-color-outline); font-size: 13px;">Jam Masuk</span>
                    <span style="font-weight: 700; color: var(--md-custom-color-success);">{{ $todayAttendance->time_in }} WITA</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--md-sys-color-outline); font-size: 13px;">Jam Pulang</span>
                    <span style="font-weight: 700; color: var(--color-ocean-blue);">{{ $todayAttendance->time_out }} WITA</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--md-sys-color-outline); font-size: 13px;">Status Kedisiplinan</span>
                    @php
                        $badgeClass = $todayAttendance->status === 'tepat_waktu' ? 'md-badge-tepat-waktu' : 'md-badge-terlambat';
                    @endphp
                    <span class="md-badge {{ $badgeClass }}">{{ strtoupper(str_replace('_', ' ', $todayAttendance->status)) }}</span>
                </div>
            </div>

            <a href="{{ route('karyawan.dashboard') }}" class="clay-btn clay-btn-primary" style="width: 100%; height: 48px; box-sizing: border-box;">
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    @else
        <!-- Mode Transaksi Check-In / Check-Out -->
        <div>
            @if($mode === 'check_in')
                <div class="clay-pill-tag" style="margin-bottom: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 13px;">login</span>
                    <span>Presensi Masuk</span>
                </div>
                <h2 class="font-editorial-serif" style="font-size: 24px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0 0 4px 0;">
                    Perekaman Kehadiran
                </h2>
                <p style="font-size: 12.5px; color: var(--md-sys-color-on-surface-variant); margin: 0; line-height: 1.5;">
                    Batas jam masuk: <b>{{ $jamMasuk }} WITA</b> (Toleransi: +{{ $toleransi }} menit).
                </p>
            @else
                <div class="clay-pill-tag" style="margin-bottom: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 13px;">logout</span>
                    <span>Presensi Pulang</span>
                </div>
                <h2 class="font-editorial-serif" style="font-size: 24px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0 0 4px 0;">
                    Perekaman Jam Pulang
                </h2>
                <p style="font-size: 12.5px; color: var(--md-sys-color-on-surface-variant); margin: 0; line-height: 1.5;">
                    Presensi masuk tercatat: <b>{{ $todayAttendance->time_in }} WITA</b>.
                </p>
            @endif
        </div>

        <!-- Live Camera Container -->
        <div class="camera-card">
            <video id="webcamVideo" autoplay playsinline muted></video>
            <canvas id="overlayCanvas"></canvas>
            <div class="face-guide-circle" id="guideCircle"></div>
        </div>

        <!-- Verification Indicators -->
        <div class="verification-grid">
            <div class="status-pill" id="facePill">
                <span class="material-symbols-rounded" id="faceIcon" style="font-size: 20px;">face</span>
                <div>
                    <div style="font-weight: 700; font-size: 12px;">Biometrik Wajah</div>
                    <div id="faceText" style="font-size: 11px; color: inherit;">Memindai wajah...</div>
                </div>
            </div>

            <div class="status-pill" id="gpsPill">
                <span class="material-symbols-rounded" id="gpsIcon" style="font-size: 20px;">location_on</span>
                <div>
                    <div style="font-weight: 700; font-size: 12px;">Lokasi Geofence</div>
                    <div id="gpsText" style="font-size: 11px; color: inherit;">Mencari GPS...</div>
                </div>
            </div>
        </div>

        <!-- Action Button -->
        <button type="button" class="clay-btn clay-btn-primary" id="btnSubmitPresensi" style="width: 100%; height: 50px;" disabled>
            <span class="material-symbols-rounded">check_circle</span>
            <span>{{ $mode === 'check_in' ? 'Kirim Presensi Masuk' : 'Kirim Presensi Pulang' }}</span>
        </button>
    @endif

</div>
@endsection

@push('scripts')
@if($mode !== 'completed')
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script src="{{ asset('js/geofence-tracker.js') }}"></script>
<script>
    const ENROLLED_DESCRIPTOR = {{ json_encode($descriptor) }};
    const MODE = '{{ $mode }}';
    const SUBMIT_URL = '{{ $mode === "check_in" ? route("karyawan.presensi.check-in") : route("karyawan.presensi.check-out") }}';

    const video = document.getElementById('webcamVideo');
    const overlayCanvas = document.getElementById('overlayCanvas');
    const guideCircle = document.getElementById('guideCircle');
    const facePill = document.getElementById('facePill');
    const faceIcon = document.getElementById('faceIcon');
    const faceText = document.getElementById('faceText');
    const gpsPill = document.getElementById('gpsPill');
    const gpsIcon = document.getElementById('gpsIcon');
    const gpsText = document.getElementById('gpsText');
    const btnSubmit = document.getElementById('btnSubmitPresensi');

    let isFaceVerified = false;
    let isGpsVerified = false;
    let currentCoords = null;
    let currentNearestLocation = null;
    let isDetecting = false;
    let modelsLoaded = false;

    // 1. Muat Model AI & Start Kamera
    async function init() {
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('/models')
            ]);
            modelsLoaded = true;

            // Start camera & GPS concurrently
            startCamera();
            trackGpsLocation();
        } catch (err) {
            console.error('Inisialisasi presensi gagal:', err);
            faceText.textContent = 'Gagal memuat AI';
        }
    }

    async function startCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false
            });
            video.srcObject = stream;
        } catch (err) {
            console.error('Kamera gagal dibuka:', err);
            faceText.textContent = 'Akses kamera ditolak';
        }
    }

    // 2. Track GPS Lokasi via GeofenceTracker
    async function trackGpsLocation() {
        try {
            gpsText.textContent = 'Mengambil GPS...';
            currentCoords = await GeofenceTracker.getCurrentCoordinates();

            // Verifikasi ke server (Double Haversine)
            const verifyResult = await GeofenceTracker.verifyWithServer(
                currentCoords.latitude,
                currentCoords.longitude,
                null,
                '{{ csrf_token() }}'
            );

            if (verifyResult.is_valid && verifyResult.location) {
                isGpsVerified = true;
                currentNearestLocation = verifyResult.location;
                gpsPill.className = 'status-pill valid';
                gpsIcon.textContent = 'verified';
                gpsText.textContent = `${verifyResult.location.name} (${verifyResult.distance_meters}m)`;
            } else {
                isGpsVerified = false;
                gpsPill.className = 'status-pill invalid';
                gpsIcon.textContent = 'location_off';
                gpsText.textContent = `Luar radius (${verifyResult.distance_meters || 0}m)`;
            }
            updateSubmitButtonState();
        } catch (err) {
            console.error('GPS tracking error:', err);
            isGpsVerified = false;
            gpsPill.className = 'status-pill invalid';
            gpsIcon.textContent = 'location_off';
            gpsText.textContent = err.message || 'GPS tidak tersedia';
            updateSubmitButtonState();
        }
    }

    // 3. Deteksi & Perbandingan Euclidean Distance Wajah Live
    video.addEventListener('play', () => {
        const displaySize = { width: video.videoWidth || 640, height: video.videoHeight || 480 };
        faceapi.matchDimensions(overlayCanvas, displaySize);

        setInterval(async () => {
            if (!modelsLoaded || isDetecting || !ENROLLED_DESCRIPTOR || ENROLLED_DESCRIPTOR.length !== 128) return;

            isDetecting = true;
            try {
                const detections = await faceapi.detectAllFaces(
                    video,
                    new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 })
                ).withFaceLandmarks().withFaceDescriptors();

                if (detections.length === 1) {
                    const liveDescriptor = Array.from(detections[0].descriptor);
                    
                    // Rumus Euclidean Distance
                    const distance = faceapi.euclideanDistance(liveDescriptor, ENROLLED_DESCRIPTOR);
                    
                    // Ambang batas akurasi <= 0.50 (95%+ match)
                    if (distance <= 0.50) {
                        isFaceVerified = true;
                        guideCircle.className = 'face-guide-circle ready';
                        facePill.className = 'status-pill valid';
                        faceIcon.textContent = 'verified';
                        const scorePct = Math.round((1 - distance) * 100);
                        faceText.textContent = `Wajah Cocok (${scorePct}%)`;
                    } else {
                        isFaceVerified = false;
                        guideCircle.className = 'face-guide-circle error';
                        facePill.className = 'status-pill invalid';
                        faceIcon.textContent = 'person_cancel';
                        faceText.textContent = 'Wajah Tidak Cocok';
                    }
                } else {
                    isFaceVerified = false;
                    guideCircle.className = detections.length > 1 ? 'face-guide-circle error' : 'face-guide-circle';
                    facePill.className = 'status-pill';
                    faceIcon.textContent = 'face';
                    faceText.textContent = detections.length > 1 ? '>1 Wajah di Kamera' : 'Arahkan wajah ke kamera';
                }

                updateSubmitButtonState();
            } catch (err) {
                console.error('Face verification error:', err);
            } finally {
                isDetecting = false;
            }
        }, 300);
    });

    function updateSubmitButtonState() {
        btnSubmit.disabled = !(isFaceVerified && isGpsVerified && currentCoords);
    }

    // 4. Submit Transaksi Presensi
    btnSubmit.addEventListener('click', async () => {
        if (!isFaceVerified || !isGpsVerified || !currentCoords) {
            alert('Pastikan verifikasi wajah cocok dan GPS berada di dalam radius.');
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="material-symbols-rounded md-pulse">sync</span><span>Memproses Presensi...</span>';

        try {
            // Snapshot foto frame kamera
            const snapCanvas = document.createElement('canvas');
            snapCanvas.width = video.videoWidth || 640;
            snapCanvas.height = video.videoHeight || 480;
            const ctx = snapCanvas.getContext('2d');
            ctx.translate(snapCanvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, snapCanvas.width, snapCanvas.height);
            const photoBase64 = snapCanvas.toDataURL('image/jpeg', 0.85);

            const response = await fetch(SUBMIT_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    latitude: currentCoords.latitude,
                    longitude: currentCoords.longitude,
                    photo: photoBase64,
                    location_id: currentNearestLocation ? currentNearestLocation.id : null
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                if (video.srcObject) {
                    video.srcObject.getTracks().forEach(t => t.stop());
                }
                window.location.href = result.redirect || '{{ route("karyawan.dashboard") }}';
            } else {
                alert(result.message || 'Presensi gagal diproses.');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = `<span class="material-symbols-rounded">check_circle</span><span>${MODE === 'check_in' ? 'Kirim Presensi Masuk' : 'Kirim Presensi Pulang'}</span>`;
            }
        } catch (err) {
            console.error('Submit presensi error:', err);
            alert('Terjadi kesalahan jaringan. Coba lagi.');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = `<span class="material-symbols-rounded">check_circle</span><span>${MODE === 'check_in' ? 'Kirim Presensi Masuk' : 'Kirim Presensi Pulang'}</span>`;
        }
    });

    document.addEventListener('DOMContentLoaded', init);
</script>
@endif
@endpush
