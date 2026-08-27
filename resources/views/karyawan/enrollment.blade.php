@extends('layouts.pwa')

@section('title', 'Pendaftaran Biometrik Wajah - Presensi PT. CAK')

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
        transform: scaleX(-1); /* Mirror view */
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

    /* Face Alignment Guide Frame */
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

    .status-badge-live {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: var(--md-sys-shape-corner-full);
        font-size: 13px;
        font-weight: 600;
        background-color: var(--md-sys-color-surface-container);
        color: var(--md-sys-color-on-surface);
        border: 1px solid var(--md-sys-color-outline-variant);
        text-align: center;
        width: 100%;
        justify-content: center;
    }
</style>
@endpush

@section('content')
<div style="display: flex; flex-direction: column; gap: 18px;">

    <!-- Header Information -->
    <div>
        <div class="clay-pill-tag" style="margin-bottom: 6px;">
            <span class="material-symbols-rounded" style="font-size: 13px;">face</span>
            <span>Enrollment Biometrik</span>
        </div>
        <h2 class="font-editorial-serif" style="font-size: 24px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0 0 4px 0; line-height: 1.2;">
            Pendaftaran Wajah
        </h2>
        <p style="font-size: 12.5px; color: var(--md-sys-color-on-surface-variant); margin: 0; line-height: 1.5;">
            Posisikan wajah Anda di dalam area oval dengan pencahayaan yang cukup dan ekspresi netral.
        </p>
    </div>

    @if(session('warning'))
        <div class="clay-card-cloudy" style="padding: 14px 18px; display: flex; align-items: center; gap: 12px;">
            <span class="material-symbols-rounded" style="color: var(--color-ocean-blue); font-size: 22px; flex-shrink: 0;">info</span>
            <div style="font-size: 12.5px; color: var(--color-ocean-blue-on-container);">{{ session('warning') }}</div>
        </div>
    @endif

    <!-- Live Camera Container -->
    <div class="camera-card">
        <video id="webcamVideo" autoplay playsinline muted></video>
        <canvas id="overlayCanvas"></canvas>
        <div class="face-guide-circle" id="guideCircle"></div>
    </div>

    <!-- Live Status Indicator -->
    <div class="clay-pill-tag" id="statusBox" style="width: 100%; height: 42px; justify-content: center; box-sizing: border-box; font-size: 12px;">
        <span class="material-symbols-rounded" id="statusIcon" style="color: var(--color-ocean-blue); font-size: 18px;">hourglass_top</span>
        <span id="statusText">Memuat Model AI Biometrik...</span>
    </div>

    <!-- Action Button -->
    <button type="button" class="clay-btn clay-btn-primary" id="btnCapture" style="width: 100%; height: 50px;" disabled>
        <span class="material-symbols-rounded">camera</span>
        <span>Ambil Sampel & Simpan Wajah</span>
    </button>

    <!-- Tips Card -->
    <div class="clay-card" style="padding: 18px 20px;">
        <div class="font-editorial-grotesk" style="font-size: 13.5px; font-weight: 700; color: var(--md-sys-color-on-surface); margin-bottom: 8px;">
            Petunjuk Pendaftaran:
        </div>
        <ul style="padding-left: 20px; font-size: 12.5px; color: var(--md-sys-color-on-surface-variant); line-height: 1.6; margin: 0;">
            <li>Lepas kacamata hitam atau masker penutup wajah.</li>
            <li>Pastikan tidak ada orang lain di latar belakang kamera.</li>
            <li>Jaga perangkat tetap stabil hingga indikator berubah hijau.</li>
        </ul>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script>
    const video = document.getElementById('webcamVideo');
    const overlayCanvas = document.getElementById('overlayCanvas');
    const guideCircle = document.getElementById('guideCircle');
    const statusBox = document.getElementById('statusBox');
    const statusIcon = document.getElementById('statusIcon');
    const statusText = document.getElementById('statusText');
    const btnCapture = document.getElementById('btnCapture');

    let currentDescriptor = null;
    let isDetecting = false;
    let modelsLoaded = false;

    // 1. Inisialisasi dan Muat Model Bobot face-api.js
    async function loadModels() {
        try {
            statusText.textContent = 'Mengunduh model neural network...';
            const MODEL_URL = '/models';

            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);

            modelsLoaded = true;
            statusText.textContent = 'Membuka kamera...';
            startCamera();
        } catch (err) {
            console.error('Gagal memuat model face-api:', err);
            updateStatus('error', 'Gagal memuat modul AI. Muat ulang halaman.');
        }
    }

    // 2. Akses Kamera Depan
    async function startCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: false
            });
            video.srcObject = stream;
        } catch (err) {
            console.error('Izin kamera ditolak:', err);
            updateStatus('error', 'Izin akses kamera diperlukan untuk enrollment.');
        }
    }

    video.addEventListener('play', () => {
        const displaySize = { width: video.videoWidth || 640, height: video.videoHeight || 480 };
        faceapi.matchDimensions(overlayCanvas, displaySize);

        setInterval(async () => {
            if (!modelsLoaded || isDetecting) return;

            isDetecting = true;
            try {
                // Deteksi wajah dengan TinyFaceDetector
                const detections = await faceapi.detectAllFaces(
                    video,
                    new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 })
                ).withFaceLandmarks().withFaceDescriptors();

                if (detections.length === 0) {
                    guideCircle.className = 'face-guide-circle';
                    btnCapture.disabled = true;
                    currentDescriptor = null;
                    updateStatus('info', 'Arahkan wajah ke dalam lingkaran panduan.');
                } else if (detections.length > 1) {
                    guideCircle.className = 'face-guide-circle error';
                    btnCapture.disabled = true;
                    currentDescriptor = null;
                    updateStatus('error', 'Terdeteksi lebih dari 1 wajah. Pastikan hanya Anda sendiri.');
                } else {
                    // Tepat 1 wajah terdeteksi
                    const detection = detections[0];
                    guideCircle.className = 'face-guide-circle ready';
                    currentDescriptor = Array.from(detection.descriptor);
                    btnCapture.disabled = false;
                    updateStatus('ready', 'Wajah terdeteksi jelas. Siap mendaftar!');
                }
            } catch (error) {
                console.error('Error saat deteksi frame:', error);
            } finally {
                isDetecting = false;
            }
        }, 300);
    });

    function updateStatus(type, message) {
        statusText.textContent = message;
        if (type === 'ready') {
            statusIcon.textContent = 'check_circle';
            statusIcon.style.color = '#2E7D32';
        } else if (type === 'error') {
            statusIcon.textContent = 'warning';
            statusIcon.style.color = '#BA1A1A';
        } else {
            statusIcon.textContent = 'face';
            statusIcon.style.color = 'var(--md-sys-color-primary)';
        }
    }

    // 3. Tangkap Snapshot Frame dan Kirim ke Backend
    btnCapture.addEventListener('click', async () => {
        if (!currentDescriptor || currentDescriptor.length !== 128) {
            alert('Wajah belum terdeteksi secara optimal. Silakan posisikan wajah kembali.');
            return;
        }

        btnCapture.disabled = true;
        btnCapture.innerHTML = '<span class="material-symbols-rounded md-pulse">sync</span><span>Menyimpan Biometrik...</span>';

        try {
            // Render video frame ke canvas tersembunyi
            const snapCanvas = document.createElement('canvas');
            snapCanvas.width = video.videoWidth || 640;
            snapCanvas.height = video.videoHeight || 480;
            const ctx = snapCanvas.getContext('2d');
            
            // Mirror flip untuk hasil simpan natural
            ctx.translate(snapCanvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, snapCanvas.width, snapCanvas.height);

            const samplePhotoBase64 = snapCanvas.toDataURL('image/jpeg', 0.85);

            // Kirim payload ke endpoint Laravel
            const response = await fetch('{{ route("karyawan.enrollment.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    descriptor_data: currentDescriptor,
                    sample_photo: samplePhotoBase64
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Matikan stream kamera
                if (video.srcObject) {
                    video.srcObject.getTracks().forEach(track => track.stop());
                }
                window.location.href = result.redirect || '{{ route("karyawan.dashboard") }}';
            } else {
                alert(result.message || 'Terjadi kesalahan saat menyimpan data biometrik.');
                btnCapture.disabled = false;
                btnCapture.innerHTML = '<span class="material-symbols-rounded">camera</span><span>Ambil Sampel & Simpan Wajah</span>';
            }
        } catch (err) {
            console.error('Gagal mengirim enrollment:', err);
            alert('Koneksi terputus. Pastikan koneksi internet stabil.');
            btnCapture.disabled = false;
            btnCapture.innerHTML = '<span class="material-symbols-rounded">camera</span><span>Ambil Sampel & Simpan Wajah</span>';
        }
    });

    // Mulai muat model saat DOM siap
    document.addEventListener('DOMContentLoaded', loadModels);
</script>
@endpush
